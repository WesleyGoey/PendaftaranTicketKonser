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
