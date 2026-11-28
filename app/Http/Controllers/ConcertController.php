<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use App\Services\SupabaseException;
use App\Services\TicketStore;
use Illuminate\Contracts\View\View;

class ConcertController extends Controller
{
    public function __construct(private TicketStore $store) {}

    /**
     * Halaman utama. Kalau Supabase bermasalah, halaman tetap tampil tanpa form.
     */
    public function home(): View
    {
        try {
            $concert = $this->store->concerts()
                ->first(fn (Concert $concert) => $concert->available_tickets > 0);
        } catch (SupabaseException $exception) {
            report($exception);
            $concert = null;
        }

        return view('welcome', ['concert' => $concert]);
    }

    public function show(int $concert): View
    {
        try {
            $found = $this->store->concert($concert);
        } catch (SupabaseException $exception) {
            report($exception);
            abort(503);
        }

        abort_if(! $found, 404);

        return view('concerts.show', ['concert' => $found]);
    }
}
