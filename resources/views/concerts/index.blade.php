<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Bruno Mars Live — Ticket Registration</title>
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
        @vite(['resources/css/app.css', 'resources/js/app.ts'])
    </head>
    <body class="min-h-screen bg-[#0b0914] font-sans text-white antialiased">
        <header class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-full border border-[#e7c37b]/60 bg-[#d7ad62] text-xl text-[#3d101b]">♪</span><span class="text-xl font-bold">Bruno <span class="text-[#e7bd72]">Mars</span></span></a>
            <nav class="flex items-center gap-3 text-sm">
                <a href="{{ route('home') }}" class="rounded-full px-4 py-2 text-white/60 hover:text-white">Home</a>
                @auth <a href="{{ route('dashboard') }}" class="rounded-full border border-white/15 px-4 py-2">Dashboard</a>
                @else <a href="{{ route('login') }}" class="rounded-full px-4 py-2 text-white/70">Log in</a><a href="{{ route('register') }}" class="rounded-full bg-white px-4 py-2 font-semibold text-[#171223]">Sign up</a> @endauth
            </nav>
        </header>
        <main class="mx-auto max-w-7xl px-6 pb-24 pt-12 lg:px-8">
            <div class="mb-12 max-w-2xl"><p class="text-sm font-semibold uppercase tracking-[0.2em] text-fuchsia-400">Find your next moment</p><h1 class="mt-3 text-5xl font-black tracking-tight sm:text-6xl">Choose your concert.<br><span class="text-white/45">Make your story.</span></h1><p class="mt-5 text-lg leading-8 text-white/55">Your favorite stages are here. Secure your tickets before they sell out.</p></div>
            <div class="grid gap-6 md:grid-cols-2">
                @forelse ($concerts as $concert)
                    <article class="group overflow-hidden rounded-3xl border border-white/10 bg-white/[0.06] transition hover:border-fuchsia-400/40">
                        <div class="relative flex h-28 items-center overflow-hidden bg-gradient-to-br from-[#7b2336] to-[#3d101b] px-6"><div class="absolute -right-6 -top-16 h-40 w-40 rounded-full border border-[#d7ad62]/25"></div><div class="absolute -bottom-24 -left-8 h-40 w-40 rounded-full border border-[#d7ad62]/20"></div><span class="relative rounded-full border border-[#f3d9a1]/40 px-3 py-1 text-xs font-bold tracking-widest text-[#f3d9a1]">BRUNO MARS LIVE</span></div>
                        <div class="p-6"><p class="text-sm font-semibold text-fuchsia-300">{{ $concert->artist }}</p><h2 class="mt-1 text-2xl font-bold">{{ $concert->title }}</h2><p class="mt-4 text-sm text-white/55">{{ $concert->description }}</p><div class="mt-5 grid grid-cols-2 gap-3 text-sm text-white/60"><span>◷ 9 Feb 2027 · 18.00 - 22.00</span><span>⌖ {{ $concert->venue }}</span></div><div class="mt-6 flex items-center justify-between border-t border-white/10 pt-5"><div><p class="text-xs text-white/40">Starting from</p><p class="text-xl font-bold">Rp {{ number_format($concert->price, 0, ',', '.') }}</p></div><a href="{{ route('concerts.show', $concert) }}" class="rounded-full bg-white px-5 py-3 text-sm font-bold text-[#171223] transition hover:bg-fuchsia-100">View details →</a></div></div>
                    </article>
                @empty
                    <div class="col-span-full rounded-3xl border border-dashed border-white/15 p-12 text-center text-white/50">No concerts are available right now.</div>
                @endforelse
            </div>
        </main>
    </body>
</html>
