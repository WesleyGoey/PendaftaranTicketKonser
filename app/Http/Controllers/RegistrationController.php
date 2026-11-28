<?php

namespace App\Http\Controllers;

use App\Auth\SupabaseGuard;
use App\Mail\RegistrationEmail;
use App\Services\SupabaseException;
use App\Services\TicketStore;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class RegistrationController extends Controller
{
    public function __construct(private TicketStore $store) {}

    /**
     * Riwayat pesanan user yang sedang login, terbaru di atas.
     */
    public function index(Request $request): View
    {
        try {
            $purchases = $this->store->purchases($this->token($request));
        } catch (SupabaseException $exception) {
            report($exception);
            abort(503);
        }

        return view('tickets.index', ['purchases' => $purchases]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'concert_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1'],
            'seat_type' => ['required', Rule::in(array_keys(config('tickets.seats')))],
        ]);

        try {
            $purchase = $this->store->book(
                $this->token($request),
                (int) $validated['concert_id'],
                (int) $validated['quantity'],
                $validated['seat_type'],
                $validated['phone'],
            );
        } catch (SupabaseException $exception) {
            if ($exception->getMessage() !== 'not_enough_tickets') {
                report($exception);
            }

            return redirect()->back()->withInput()->with('error', $exception->getMessage() === 'not_enough_tickets'
                ? 'There are not enough tickets available.'
                : 'We could not complete your booking. Check My Tickets before trying again.');
        }

        $user = $request->user();

        try {
            // Detail konser hanya untuk isi email.
            $purchase->setRelation('concert', $this->store->concert((int) $validated['concert_id']));

            Mail::to($user->email)->send(new RegistrationEmail($user->email, $user->name, $purchase));
        } catch (SupabaseException|TransportExceptionInterface $exception) {
            report($exception);

            return redirect()->back()->with(
                'warning',
                'Your booking was saved, but we could not send the confirmation email. You can find your booking code in My Tickets.'
            );
        }

        return redirect()->back()->with('success', 'Please check your email to find your booking code.');
    }

    private function token(Request $request): string
    {
        return (string) $request->session()->get(SupabaseGuard::SESSION_KEY.'.access_token');
    }
}
