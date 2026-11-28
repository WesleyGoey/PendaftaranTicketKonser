<x-layout :title="$concert->title.' — Bruno Mars Live'">
    <article data-live="concert" class="mx-auto max-w-4xl px-5 pb-20 pt-10">
        <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center gap-1.5 text-sm font-semibold text-cream-muted hover:text-cream">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            Back to home
        </a>

        <h1 class="mt-4 font-display text-4xl font-extrabold tracking-tight sm:text-5xl">{{ $concert->title }}</h1>
        <p class="mt-5 max-w-2xl text-lg leading-8 text-cream-muted">{{ $concert->description }}</p>

        <dl class="mt-8 grid gap-6 border-y border-dashed border-gold/40 py-6 sm:grid-cols-2">
            <div><dt class="text-sm text-cream-muted">Artist</dt><dd class="mt-1 font-bold">{{ $concert->artist }}</dd></div>
            <div><dt class="text-sm text-cream-muted">Date and time</dt><dd class="mt-1 font-bold">{{ $concert->concert_date->format('j F Y') }}, 18.00 - 22.00</dd></div>
            <div><dt class="text-sm text-cream-muted">Venue</dt><dd class="mt-1 font-bold">{{ $concert->venue }}</dd></div>
            <div><dt class="text-sm text-cream-muted">Tickets available</dt><dd class="mt-1 font-bold tabular-nums">{{ $concert->available_tickets }} of {{ $concert->total_tickets }}</dd></div>
        </dl>

        <h2 class="mt-10 font-display text-2xl font-extrabold">Seat prices</h2>
        <ul class="panel mt-4 divide-y divide-border">
            @foreach (config('tickets.seats') as $seat)
                <li class="flex flex-wrap items-center justify-between gap-2 px-6 py-4">
                    <div><p class="font-bold text-gold-soft">{{ $seat['label'] }}</p><p class="text-sm text-cream-muted">{{ $seat['description'] }}</p></div>
                    <p class="font-bold tabular-nums">Rp {{ number_format($seat['price'], 0, ',', '.') }}</p>
                </li>
            @endforeach
        </ul>

        @if ($concert->available_tickets > 0)
            <a href="{{ route('home') }}#booking" class="btn btn-gold mt-8 w-full !py-4 !text-base sm:w-auto">Book tickets</a>
        @else
            <p class="panel mt-8 p-6 text-center font-semibold">Sorry, this concert is sold out.</p>
        @endif
    </article>
</x-layout>
