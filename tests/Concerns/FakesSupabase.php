<?php

namespace Tests\Concerns;

use App\Auth\SupabaseGuard;
use Illuminate\Support\Facades\Http;

trait FakesSupabase
{
    protected const USER_ID = '11111111-1111-1111-1111-111111111111';

    /**
     * @return array<string, mixed>
     */
    protected function supabaseUser(): array
    {
        return [
            'id' => self::USER_ID,
            'email' => 'buyer@example.com',
            'user_metadata' => ['display_name' => 'Test Buyer', 'phone' => '+62 812-3456-7890'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function supabaseSession(string $token = 'user-access-token'): array
    {
        return ['access_token' => $token, 'refresh_token' => 'user-refresh-token', 'expires_in' => 3600, 'user' => $this->supabaseUser()];
    }

    /**
     * @return array<string, mixed>
     */
    protected function concertRow(array $overrides = []): array
    {
        return [
            'id' => 1,
            'title' => 'Bruno Mars Live in Surabaya',
            'artist' => 'Bruno Mars',
            'concert_date' => '2027-02-09T18:00:00',
            'venue' => 'Universitas Ciputra Surabaya, Indonesia',
            'total_tickets' => 100,
            'available_tickets' => 100,
            'price' => 1500000,
            'description' => 'A spectacular night.',
            ...$overrides,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function purchaseRow(array $overrides = []): array
    {
        return [
            'id' => 5,
            'confirmation_code' => 'MINE0001',
            'user_id' => self::USER_ID,
            'concert_id' => 1,
            'phone' => '+62 812-3456-7890',
            'quantity' => 2,
            'seat_type' => 'vip',
            'total_price' => 6000000,
            'status' => 'pending',
            'created_at' => '2026-11-28T02:52:00+00:00',
            ...$overrides,
        ];
    }

    /**
     * Supabase tiruan. Respons di $overrides menang atas bawaannya.
     *
     * @param  array<string, mixed>  $overrides
     */
    protected function fakeSupabase(array $overrides = []): void
    {
        Http::fake($overrides + [
            '*/auth/v1/token*' => Http::response($this->supabaseSession()),
            '*/auth/v1/user' => Http::response($this->supabaseUser()),
            '*/auth/v1/signup*' => Http::response(['id' => self::USER_ID]),
            '*/auth/v1/logout*' => Http::response(null, 204),
            '*/rest/v1/concerts*' => Http::response([$this->concertRow()]),
            '*/rest/v1/ticket_purchases*' => Http::response([$this->purchaseRow(['concert' => $this->concertRow()])]),
            '*/rest/v1/rpc/book_tickets' => Http::response($this->purchaseRow()),
        ]);
    }

    protected function signedIn(int $expiresIn = 3600): static
    {
        return $this->withSession([SupabaseGuard::SESSION_KEY => [
            'access_token' => 'user-access-token',
            'refresh_token' => 'user-refresh-token',
            'expires_at' => time() + $expiresIn,
        ]]);
    }
}
