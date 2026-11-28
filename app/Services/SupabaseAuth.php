<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Validation\ValidationException;

/**
 * Panggilan ke Supabase Auth, hanya memakai SUPABASE_URL dan SUPABASE_KEY.
 */
class SupabaseAuth
{
    public const UNAVAILABLE = 'The login service cannot be reached right now. Please try again in a moment.';

    public function __construct(private SupabaseClient $client) {}

    /**
     * @param  array<string, string>  $metadata
     */
    public function signUp(string $email, string $password, array $metadata, string $redirectTo): void
    {
        $response = $this->send(fn (PendingRequest $http) => $http->post(
            '/auth/v1/signup?'.http_build_query(['redirect_to' => $redirectTo]),
            ['email' => $email, 'password' => $password, 'data' => $metadata],
        ));

        if ($response->clientError()) {
            throw ValidationException::withMessages([
                'email' => $response->json('msg') ?? $response->json('message') ?? 'We could not create your account.',
            ]);
        }

        $this->ensureAvailable($response);
    }

    /**
     * @return array<string, mixed>|null sesi (token dan user), atau null jika kredensial salah
     */
    public function signIn(string $email, string $password): ?array
    {
        $response = $this->send(fn (PendingRequest $http) => $http->post(
            '/auth/v1/token?grant_type=password',
            ['email' => $email, 'password' => $password],
        ));

        if (($response->json('error_code') ?? $response->json('code')) === 'email_not_confirmed') {
            throw ValidationException::withMessages([
                'email' => 'Please confirm your email first. Open the link we sent to your inbox.',
            ]);
        }

        if (in_array($response->status(), [400, 422], true)) {
            return null;
        }

        if ($response->status() === 429) {
            throw ValidationException::withMessages(['email' => 'Too many attempts. Please wait a minute and try again.']);
        }

        $this->ensureAvailable($response);

        return $response->json();
    }

    /**
     * Tukar refresh token dengan sesi baru. Null berarti user harus login lagi.
     *
     * @return array<string, mixed>|null
     */
    public function refresh(string $refreshToken): ?array
    {
        $response = $this->send(fn (PendingRequest $http) => $http->post(
            '/auth/v1/token?grant_type=refresh_token',
            ['refresh_token' => $refreshToken],
        ));

        if (in_array($response->status(), [400, 401, 403, 422], true)) {
            return null;
        }

        $this->ensureAvailable($response);

        return $response->json();
    }

    /**
     * Padanan supabaseAuth.auth.getUser(token) di middleware guru.
     *
     * @return array<string, mixed>|null
     */
    public function getUser(string $accessToken): ?array
    {
        $response = $this->send(fn (PendingRequest $http) => $http->withToken($accessToken)->get('/auth/v1/user'));

        if (in_array($response->status(), [401, 403, 404], true)) {
            return null;
        }

        $this->ensureAvailable($response);

        return $response->json();
    }

    /**
     * Cabut sesi milik token ini saja, bukan sesi user lain.
     */
    public function signOut(string $accessToken): void
    {
        try {
            $this->client->request()->withToken($accessToken)->post('/auth/v1/logout?scope=local');
        } catch (ConnectionException|SupabaseException $exception) {
            report($exception);
        }
    }

    private function send(callable $call): Response
    {
        try {
            return $call($this->client->request());
        } catch (ConnectionException|SupabaseException $exception) {
            report($exception);

            throw ValidationException::withMessages(['email' => self::UNAVAILABLE]);
        }
    }

    private function ensureAvailable(Response $response): void
    {
        if (! $response->successful()) {
            report(new SupabaseException('Supabase Auth responded with HTTP '.$response->status()));

            throw ValidationException::withMessages(['email' => self::UNAVAILABLE]);
        }
    }
}
