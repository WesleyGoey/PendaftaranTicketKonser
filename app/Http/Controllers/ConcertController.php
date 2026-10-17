<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Concert;
use App\Models\TicketPurchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ConcertController extends Controller
{
    /**
     * Display a listing of all concerts.
     */
    public function index()
    {
        $concerts = Concert::where('available_tickets', '>', 0)
            ->orderBy('concert_date')
            ->get();

        if ($concerts->isEmpty()) {
            $concerts = collect([
                (new Concert)->setRawAttributes([
                    'id' => 1,
                    'title' => 'Konser Rock Malam Ini',
                    'artist' => 'The Rockers',
                    'concert_date' => \Illuminate\Support\Carbon::now()->addWeek(),
                    'venue' => 'Gelora Bung Karno',
                    'price' => 150000,
                    'available_tickets' => 50,
                    'total_tickets' => 100,
                    'description' => 'Konser rock terbesar tahun ini dengan penampilan khusus dari tamu spesial.',
                ]),
                (new Concert)->setRawAttributes([
                    'id' => 2,
                    'title' => 'Jazz Under the Stars',
                    'artist' => 'Sara Mendes & Quartet',
                    'concert_date' => \Illuminate\Support\Carbon::now()->addMonth(),
                    'venue' => 'Teater Jakarta',
                    'price' => 250000,
                    'available_tickets' => 30,
                    'total_tickets' => 50,
                    'description' => 'Malam jazz intim dengan nada soulful di bawah bintang-bintang.',
                ]),
                (new Concert)->setRawAttributes([
                    'id' => 3,
                    'title' => 'Pop Festival 2025',
                    'artist' => 'Various Artists',
                    'concert_date' => \Illuminate\Support\Carbon::now()->addMonths(2),
                    'venue' => 'Parkan Salihara',
                    'price' => 350000,
                    'available_tickets' => 0,
                    'total_tickets' => 200,
                    'description' => 'Festival musik pop dengan banyak penyanyi top Indonesia.',
                ]),
            ]);
        }

        return view('concerts.index', ['concerts' => $concerts]);
    }

    /**
     * Display the specified concert.
     */
    public function show(Concert $concert)
    {
        return view('concerts.show', ['concert' => $concert]);
    }

    /**
     * Store a newly created ticket purchase.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'concert_id' => 'required|exists:concerts,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $concert = Concert::findOrFail($request->concert_id);

        // Check if enough tickets are available
        if ($concert->available_tickets < $request->quantity) {
            return redirect()->back()->with('error', 'Not enough tickets available.');
        }

        // Calculate total price
        $totalPrice = $concert->price * $request->quantity;

        // Create ticket purchase
        $ticketPurchase = TicketPurchase::create([
            'user_id' => Auth::id(),
            'concert_id' => $concert->id,
            'quantity' => $request->quantity,
            'total_price' => $totalPrice,
            'status' => 'pending'
        ]);

        // Update available tickets
        $concert->decrement('available_tickets', $request->quantity);

        return redirect()->route('concerts.index')->with('success', 'Ticket purchase successful!');
    }
}
