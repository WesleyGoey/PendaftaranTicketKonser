<?php

namespace App\Http\Controllers;

use App\Auth\SupabaseGuard;
use App\Services\SupabaseAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Padanan src/routes/authRoutes.js: register, login, logout lewat Supabase Auth.
 */
class AuthController extends Controller
{
    public function __construct(private SupabaseAuth $supabase) {}

    public function showLogin(Request $request): Response
    {
        return Inertia::render('auth/Login', ['status' => $request->session()->get('status')]);
    }

    public function showRegister(): Response
    {
        return Inertia::render('auth/Register', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', Password::default(), 'confirmed'],
        ]);

        $this->supabase->signUp(
            Str::lower($input['email']),
            $input['password'],
            ['display_name' => $input['name'], 'phone' => $input['phone']],
            route('login'),
        );

        return redirect()->route('login')->with('status', 'Check your email to confirm your account, then log in.');
    }

    public function login(Request $request): HttpResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $session = $this->supabase->signIn(Str::lower($credentials['email']), $credentials['password']);

        if (! $session) {
            throw ValidationException::withMessages(['email' => trans('auth.failed')]);
        }

        $request->session()->regenerate();
        $request->session()->put(SupabaseGuard::SESSION_KEY, SupabaseGuard::tokens($session));

        // Halaman login memakai Inertia, tujuannya halaman Blade: muat halaman penuh.
        return Inertia::location(redirect()->intended(route('home'))->getTargetUrl());
    }

    public function logout(Request $request): RedirectResponse
    {
        if ($token = $request->session()->get(SupabaseGuard::SESSION_KEY.'.access_token')) {
            $this->supabase->signOut($token);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
