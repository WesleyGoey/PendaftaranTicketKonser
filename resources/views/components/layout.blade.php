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
        <header class="mx-auto flex w-full max-w-6xl items-center justify-between gap-3 px-5 py-5 lg:px-8">
            <a href="{{ route('home') }}" aria-label="Bruno Mars Live, home" class="flex min-h-11 items-center gap-2.5">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gold text-wine">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
                </span>
                <span class="font-display text-xl font-extrabold {{ auth()->check() ? 'max-[419px]:hidden' : '' }}">Bruno <span class="text-gold-soft">Mars</span></span>
            </a>

            <nav aria-label="Main" class="flex items-center gap-1 text-sm font-semibold">
                @auth
                    <a href="{{ route('tickets.index') }}"
                        @if (request()->routeIs('tickets.index')) aria-current="page" @endif
                        class="flex min-h-11 items-center whitespace-nowrap rounded-full px-4 text-cream transition hover:bg-velvet aria-[current=page]:bg-velvet aria-[current=page]:text-gold-soft">My Tickets</a>
                    <span class="mx-2 hidden max-w-40 truncate border-l border-border pl-4 font-medium text-cream-muted sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-line whitespace-nowrap !px-4 !py-2">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-line !px-6 !py-2">Log in</a>
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
