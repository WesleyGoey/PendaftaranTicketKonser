@php($lowestPrice = min(array_column(config('tickets.seats'), 'price')))

<x-layout title="Concerts — Bruno Mars Live">
    <section class="mx-auto max-w-4xl px-5 pb-20 pt-10">
        <h1 class="font-display text-4xl font-extrabold tracking-tight sm:text-5xl">Concerts</h1>
        <p class="mt-4 max-w-xl leading-7 text-cream-muted">Pick a show and secure your tickets before they sell out.</p>

        <ul class="mt-10 space-y-5">
            @forelse ($concerts as $concert)
                <li class="panel p-6 sm:p-8">
                    <h2 class="font-display text-2xl font-extrabold text-gold-soft">{{ $concert->title }}</h2>
                    <p class="mt-2 font-semibold">{{ $concert->concert_date->format('j F Y') }}, 18.00 - 22.00 · {{ $concert->venue }}</p>
                    <p class="mt-3 max-w-2xl leading-7 text-cream-muted">{{ $concert->description }}</p>
                    <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-dashed border-gold/40 pt-5">
                        <p class="text-sm text-cream-muted">Tickets from <strong class="ml-1 text-xl tabular-nums text-cream">Rp {{ number_format($lowestPrice, 0, ',', '.') }}</strong></p>
                        <a href="{{ route('concerts.show', $concert) }}" class="btn btn-gold">View details</a>
                    </div>
                </li>
            @empty
                <li class="panel p-10 text-center text-cream-muted">No concerts are available right now.</li>
            @endforelse
        </ul>
    </section>
</x-layout>
