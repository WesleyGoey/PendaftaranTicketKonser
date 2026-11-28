<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Middleware;
use Inertia\Support\Header;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Hanya login dan register yang memakai Inertia. Kalau kunjungan Inertia berakhir
     * di halaman Blade (misalnya diarahkan ke halaman utama karena sudah login),
     * minta browser memuat halaman penuh. Tanpa ini, Inertia menampilkannya dalam
     * kotak di atas halaman yang sedang dibuka.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = parent::handle($request, $next);

        if ($request->header(Header::INERTIA) && $request->isMethod('GET')
            && $response->isSuccessful() && ! $response->headers->has(Header::INERTIA)) {
            return Inertia::location($request->fullUrl());
        }

        return $response;
    }

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
        ];
    }
}
