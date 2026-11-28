<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationEmail;
use App\Models\Concert;
use App\Models\TicketPurchase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class RegistrationController extends Controller
{
    /**
     * Show the signed-in user's own bookings, newest first.
     */
    public function index(Request $request): View
    {
        return view('tickets.index', [
            'purchases' => $request->user()->ticketPurchases()->with('concert')->latest()->latest('id')->get(),
        ]);
    }

    /**
     * Store a new ticket booking for the signed-in user.
     */
    public function store(Request $request): RedirectResponse
    {
        $seats = config('tickets.seats');

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'concert_id' => ['required', 'exists:concerts,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'seat_type' => ['required', Rule::in(array_keys($seats))],
        ]);

        $quantity = (int) $validated['quantity'];
        $seatPrice = $seats[$validated['seat_type']]['price'];
        $user = $request->user();

        $purchase = DB::transaction(function () use ($validated, $quantity, $seatPrice, $user): ?TicketPurchase {
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
                'user_id' => $user->id,
                'concert_id' => $concert->id,
                'quantity' => $quantity,
                'seat_type' => $validated['seat_type'],
                'total_price' => $seatPrice * $quantity,
                'status' => 'pending',
            ]);

            $concert->decrement('available_tickets', $quantity);

            return $purchase->load('concert');
        });

        if (! $purchase) {
            return redirect()->back()->withInput()->with('error', 'There are not enough tickets available.');
        }

        try {
            Mail::to($user->email)->send(new RegistrationEmail($user->email, $user->name, $purchase));
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return redirect()->back()->with(
                'warning',
                'Your booking was saved, but we could not send the confirmation email. You can find your booking code in My Tickets.'
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
