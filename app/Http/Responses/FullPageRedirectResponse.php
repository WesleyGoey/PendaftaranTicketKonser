<?php

namespace App\Http\Responses;

use Inertia\Inertia;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse;

/**
 * Halaman login/register memakai Inertia, tujuan setelahnya halaman Blade.
 * Inertia::location memaksa browser memuat halaman penuh; tanpa ini Blade
 * muncul sebagai pop-up di atas form.
 */
class FullPageRedirectResponse implements LoginResponse, RegisterResponse
{
    public function toResponse($request)
    {
        return Inertia::location(redirect()->intended(config('fortify.home'))->getTargetUrl());
    }
}
