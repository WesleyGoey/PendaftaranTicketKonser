<?php

namespace App\Http\Controllers;

use App\Models\Concert;

class ConcertController extends Controller
{
    /**
     * Display the specified concert.
     */
    public function show(Concert $concert)
    {
        return view('concerts.show', ['concert' => $concert]);
    }
}
