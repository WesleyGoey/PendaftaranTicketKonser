<?php

namespace App\Auth;

use App\Services\SupabaseAuth;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Padanan middleware protect di repo kelas: token diperiksa ke Supabase.
 * Bedanya, token disimpan di sesi Laravel (server), bukan di localStorage.
 */
class SupabaseGuard
{
    public const SESSION_KEY = 'supabase';

    public function __construct(private SupabaseAuth $auth) {}

    public function __invoke(Request $request): ?GenericUser
    {
        if (! $request->hasSession() || ! $tokens = $request->session()->get(self::SESSION_KEY)) {
            return null;
        }

        try {
            if ($tokens['expires_at'] <= time() + 60) {
                $fresh = $this->auth->refresh($tokens['refresh_token']);

                if (! $fresh) {
                    return $this->forget($request);
                }

                $tokens = self::tokens($fresh);
                $request->session()->put(self::SESSION_KEY, $tokens);
            }

            $account = $this->auth->getUser($tokens['access_token']);
        } catch (ValidationException) {
            // Supabase sedang tidak bisa dihubungi: anggap tamu, tapi simpan sesinya.
            return null;
        }

        if (! $account) {
            return $this->forget($request);
        }

        $metadata = $account['user_metadata'] ?? [];

        return new GenericUser([
            'id' => $account['id'],
            'email' => $account['email'],
            'name' => $metadata['display_name'] ?? strstr($account['email'], '@', true),
            'phone' => $metadata['phone'] ?? null,
            'remember_token' => null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $session
     * @return array{access_token: string, refresh_token: string, expires_at: int}
     */
    public static function tokens(array $session): array
    {
        return [
            'access_token' => $session['access_token'],
            'refresh_token' => $session['refresh_token'],
            'expires_at' => time() + (int) ($session['expires_in'] ?? 3600),
        ];
    }

    private function forget(Request $request): null
    {
        $request->session()->forget(self::SESSION_KEY);

        return null;
    }
}
