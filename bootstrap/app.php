<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Di form login/register, tampilkan pesan di form, bukan halaman error dalam kotak.
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            $message = match ($response->getStatusCode()) {
                419 => 'This page expired. Please try again.',
                429 => 'Too many attempts. Please wait a minute and try again.',
                default => null,
            };

            return $message && $request->header('X-Inertia')
                ? back()->withErrors(['email' => $message])
                : $response;
        });
    })->create();
