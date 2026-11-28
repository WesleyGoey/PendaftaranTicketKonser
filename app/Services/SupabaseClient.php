<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Padanan src/db.js guru: satu tempat membuat koneksi Supabase.
 */
class SupabaseClient
{
    public function request(): PendingRequest
    {
        $url = config('services.supabase.url');
        $key = config('services.supabase.key');

        if (blank($url) || blank($key)) {
            throw new SupabaseException('SUPABASE_URL / SUPABASE_KEY belum diisi.');
        }

        return Http::baseUrl(rtrim($url, '/'))
            ->withHeaders(['apikey' => $key])
            ->acceptJson()
            ->timeout(15);
    }
}
