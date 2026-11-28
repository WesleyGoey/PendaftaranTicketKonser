<x-layout title="My Tickets — Bruno Mars Live">
    <section class="mx-auto max-w-4xl px-5 pb-20 pt-10">
        <h1 class="font-display text-4xl font-extrabold tracking-tight sm:text-5xl">My Tickets</h1>
        <p class="mt-4 text-cream-muted">Your booking history, newest first.</p>

        @if ($purchases->isEmpty())
            <div class="panel mt-10 p-10 text-center">
                <p class="text-lg font-bold">No bookings yet</p>
                <p class="mt-2 text-cream-muted">Tickets you book will appear here with their booking code.</p>
                <a href="{{ route('home') }}#booking" class="btn btn-gold mt-6">Book tickets</a>
            </div>
        @else
            <ul class="mt-10 space-y-5">
                @foreach ($purchases as $purchase)
                    <li class="panel grid sm:grid-cols-[minmax(0,1fr)_15rem]">
                        <div class="p-6">
                            <h2 class="font-display text-xl font-extrabold">{{ $purchase->concert->title }}</h2>
                            <p class="mt-1 text-sm text-cream-muted">{{ $purchase->concert->concert_date->format('j F Y') }}, 18.00 - 22.00 · {{ $purchase->concert->venue }}</p>
                            <dl class="mt-5 flex flex-wrap gap-x-10 gap-y-3 whitespace-nowrap text-sm">
                                <div><dt class="text-cream-muted">Seat</dt><dd class="mt-1 font-bold">{{ config("tickets.seats.{$purchase->seat_type}.label", ucfirst($purchase->seat_type)) }}</dd></div>
                                <div><dt class="text-cream-muted">Tickets</dt><dd class="mt-1 font-bold tabular-nums">{{ $purchase->quantity }}</dd></div>
                                <div><dt class="text-cream-muted">Total</dt><dd class="mt-1 font-bold tabular-nums">Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</dd></div>
                            </dl>
                        </div>
                        <div class="flex flex-col justify-center border-t-2 border-dashed border-gold/40 p-6 sm:border-l-2 sm:border-t-0">
                            <p class="text-sm text-cream-muted">Booking code</p>
                            <p class="mt-1 font-mono text-2xl font-bold tracking-widest text-gold-soft">{{ $purchase->confirmation_code ?? '—' }}</p>
                            <p class="mt-3 text-sm text-cream-muted">Booked <time datetime="{{ $purchase->created_at->toIso8601String() }}">{{ $purchase->created_at->timezone('Asia/Jakarta')->format('j M Y, H.i') }} WIB</time></p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layout>
