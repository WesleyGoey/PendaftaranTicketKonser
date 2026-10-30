<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationEmail;
use App\Models\Concert;
use App\Models\TicketPurchase;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class RegistrationController extends Controller
{
    /**
     * Store a new ticket registration.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
            'notes' => 'nullable|string|max:2000',
            'concert_id' => 'required|exists:concerts,id',
            'quantity' => 'required|integer|min:1',
            'seat_type' => 'required|in:regular,premium,vip,diamond',
        ]);

        $quantity = (int) $validated['quantity'];
        $seatMultipliers = [
            'regular' => 1000000,
            'premium' => 2000000,
            'vip' => 3000000,
            'diamond' => 4000000,
        ];
        $seatMultiplier = $seatMultipliers[$validated['seat_type']];
        $user = User::firstOrNew(['email' => $validated['email']]);
        $user->name = $validated['name'];

        if (! $user->exists) {
            $user->password = Str::random(40);
        }

        $user->save();

        $purchase = DB::transaction(function () use ($validated, $quantity, $seatMultiplier, $user): ?TicketPurchase {
            $concert = Concert::query()
                ->whereKey($validated['concert_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($concert->available_tickets < $quantity) {
                return null;
            }

            $purchase = TicketPurchase::create([
                'confirmation_code' => $this->createConfirmationCode(),
                'phone' => $validated['phone'],
                'notes' => $validated['notes'] ?? null,
                'user_id' => $user->id,
                'concert_id' => $concert->id,
                'quantity' => $quantity,
                'seat_type' => $validated['seat_type'],
                'total_price' => $seatMultiplier * $quantity,
                'status' => 'pending',
            ]);

            $concert->decrement('available_tickets', $quantity);

            return $purchase->load('concert');
        });

        if (! $purchase) {
            return redirect()->back()->with('error', 'There are not enough tickets available.');
        }

        try {
            Mail::to($user->email)->send(new RegistrationEmail($user->email, $user->name, $purchase));
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return redirect()->back()->with(
                'warning',
                'Your booking was saved, but we could not send the confirmation email. Please verify your email settings.'
            );
        }

        return redirect()->back()->with('success', 'Please check your email to find your booking code.');
    }

    private function createConfirmationCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (TicketPurchase::where('confirmation_code', $code)->exists());

        return $code;
    }
}
