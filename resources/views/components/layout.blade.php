@props(['title' => 'Bruno Mars Live'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#1d0811">
        <title>{{ $title }}</title>
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&family=Instrument+Sans:wght@400;500;600;700&display=swap">
        @vite(['resources/css/app.css'])
    </head>
    <body class="flex min-h-screen flex-col font-sans antialiased">
        <header class="mx-auto flex w-full max-w-6xl flex-wrap items-center justify-between gap-x-6 gap-y-3 px-5 py-5 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gold text-wine">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
                </span>
                <span class="font-display text-xl font-extrabold">Bruno <span class="text-gold-soft">Mars</span></span>
            </a>

            <nav aria-label="Main" class="flex flex-wrap items-center gap-1 text-sm font-semibold">
                @foreach (array_filter(['home' => 'Home', 'tickets.index' => auth()->check() ? 'My Tickets' : null]) as $routeName => $label)
                    <a href="{{ route($routeName) }}"
                        @if (request()->routeIs($routeName)) aria-current="page" @endif
                        class="rounded-full px-4 py-2.5 text-cream-muted transition hover:text-cream aria-[current=page]:bg-velvet aria-[current=page]:text-cream">{{ $label }}</a>
                @endforeach

                @auth
                    <span class="hidden max-w-40 truncate border-l border-border pl-4 font-medium text-cream-muted sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-line ml-2 !px-4 !py-2">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-full px-4 py-2.5 text-cream-muted transition hover:text-cream">Log in</a>
                    <a href="{{ route('register') }}" class="btn btn-gold !px-5 !py-2">Sign up</a>
                @endauth
            </nav>
        </header>

        @if (session('success') || session('error') || session('warning'))
            <div class="mx-auto w-full max-w-6xl px-5 lg:px-8">
                <div role="status" class="rounded-2xl border px-5 py-4 text-sm {{ session('success') ? 'border-emerald-300/40 bg-emerald-300/10 text-emerald-100' : (session('warning') ? 'border-gold/50 bg-gold/10 text-gold-soft' : 'border-red-300/40 bg-red-300/10 text-red-100') }}">
                    {{ session('success') ?? session('warning') ?? session('error') }}
                    @if ((session('success') || session('warning')) && auth()->check())
                        <a href="{{ route('tickets.index') }}" class="ml-1 font-bold underline">View My Tickets</a>
                    @endif
                </div>
            </div>
        @endif

        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="border-t border-border/60 px-5 py-8 text-center text-sm text-cream-muted">© {{ date('Y') }} Bruno Mars Live. Official ticket registration.</footer>
    </body>
</html>
