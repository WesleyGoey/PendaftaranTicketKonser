<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use Illuminate\Support\Carbon;

class ConcertController extends Controller
{
    /**
     * Display a listing of all concerts.
     */
    public function index()
    {
        $concerts = Concert::where('artist', 'Bruno Mars')
            ->where('available_tickets', '>', 0)
            ->orderBy('concert_date')
            ->get();

        if ($concerts->isEmpty()) {
            $concerts = collect([
                (new Concert)->setRawAttributes([
                    'id' => 1,
                    'title' => 'Bruno Mars Live in Surabaya',
                    'artist' => 'Bruno Mars',
                    'concert_date' => Carbon::parse('2027-02-09 18:00:00'),
                    'venue' => 'Universitas Ciputra Surabaya, Indonesia',
                    'price' => 150000,
                    'available_tickets' => 50,
                    'total_tickets' => 100,
                    'description' => 'A spectacular Bruno Mars live experience in New York.',
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
}
