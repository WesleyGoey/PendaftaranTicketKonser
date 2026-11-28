@php
    $seats = config('tickets.seats');
    $lowestPrice = min(array_column($seats, 'price'));
    $selectedSeat = old('seat_type');
@endphp

<x-layout title="Bruno Mars Live — Tickets">
    <section class="mx-auto max-w-4xl px-5 pb-16 pt-12 text-center lg:pt-20">
        <h1 class="font-display text-5xl font-extrabold leading-[1.02] tracking-tight sm:text-7xl">
            The night starts<br>
            <span class="text-gold-soft">with a little funk.</span>
        </h1>
        <p class="mx-auto mt-6 max-w-xl text-lg leading-8 text-cream-muted">
            Secure your place for an unforgettable Bruno Mars show. Choose your seat category and get ready to dance all night.
        </p>
        <a href="#booking" class="btn btn-gold mt-9 !px-8 !py-4 !text-base">Reserve your tickets</a>

        @if ($concert)
            <dl class="mx-auto mt-14 grid max-w-3xl gap-y-6 border-y border-dashed border-gold/40 py-6 text-center sm:grid-cols-3">
                <div><dt class="text-sm text-cream-muted">Date and time</dt><dd class="mt-1 font-bold">{{ $concert->concert_date->format('j F Y') }}, 18.00 - 22.00</dd></div>
                <div><dt class="text-sm text-cream-muted">Venue</dt><dd class="mt-1 font-bold">{{ $concert->venue }}</dd></div>
                <div><dt class="text-sm text-cream-muted">Tickets from</dt><dd class="mt-1 font-bold tabular-nums">Rp {{ number_format($lowestPrice, 0, ',', '.') }}</dd></div>
            </dl>
        @endif
    </section>

    <section id="booking" class="mx-auto w-full max-w-6xl scroll-mt-6 px-5 pb-20 lg:px-8">
        <h2 class="font-display text-3xl font-extrabold sm:text-4xl">Book your tickets</h2>

        @if (! $concert)
            <p class="panel mt-8 p-8 text-cream-muted">Tickets are not available right now.</p>
        @else
            <p class="mt-3 text-cream-muted">
                {{ $concert->title }} ·
                <a href="{{ route('concerts.show', $concert) }}" class="font-semibold text-gold-soft underline">Concert details</a>
            </p>

            @if ($errors->any())
                <div role="alert" class="mt-6 rounded-2xl border border-red-300/40 bg-red-300/10 px-5 py-4 text-sm text-red-100">
                    @foreach ($errors->all() as $message)
                        <p>{{ $message }}</p>
                    @endforeach
                </div>
            @endif

            <form id="booking-form" method="POST" action="{{ route('concerts.store') }}" class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1.35fr)_minmax(0,1fr)] lg:items-start">
                @csrf
                <input type="hidden" name="concert_id" value="{{ $concert->id }}">

                <div class="space-y-10">
                    <fieldset>
                        <legend class="text-lg font-bold">Seat category</legend>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach ($seats as $type => $seat)
                                <label class="relative block cursor-pointer">
                                    <input type="radio" name="seat_type" value="{{ $type }}" data-label="{{ $seat['label'] }}" data-price="{{ $seat['price'] }}" required @checked($selectedSeat === $type) class="peer sr-only">
                                    <span class="panel flex h-full flex-col p-5 transition hover:border-gold/70 peer-checked:border-gold peer-checked:bg-velvet peer-checked:ring-1 peer-checked:ring-gold peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-gold-soft">
                                        <span class="font-display text-xl font-extrabold text-gold-soft">{{ $seat['label'] }}</span>
                                        <span class="mt-1 text-sm text-cream-muted">{{ $seat['description'] }}</span>
                                        <span class="mt-5 text-lg font-bold tabular-nums">Rp {{ number_format($seat['price'], 0, ',', '.') }} <span class="text-sm font-normal text-cream-muted">/ ticket</span></span>
                                    </span>
                                    <span class="pointer-events-none absolute right-4 top-4 hidden h-6 w-6 items-center justify-center rounded-full bg-gold text-wine peer-checked:flex">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <label for="quantity" class="text-lg font-bold">Number of tickets</label>
                            <p class="mt-1 text-sm text-cream-muted"><span id="tickets-left" class="tabular-nums">{{ $concert->available_tickets }}</span> tickets available</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" data-step="-1" aria-label="Remove one ticket" class="btn btn-line !h-12 !w-12 !p-0 !text-2xl">−</button>
                            <input id="quantity" name="quantity" type="number" inputmode="numeric" min="1" max="{{ $concert->available_tickets }}" value="{{ old('quantity', 1) }}" required class="field !w-24 text-center text-2xl font-bold tabular-nums [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none">
                            <button type="button" data-step="1" aria-label="Add one ticket" class="btn btn-line !h-12 !w-12 !p-0 !text-2xl">+</button>
                        </div>
                    </div>
                </div>

                <aside class="panel p-6">
                    @auth
                        <h3 class="text-lg font-bold">Buyer details</h3>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div><dt class="text-cream-muted">Name</dt><dd class="font-semibold break-words">{{ auth()->user()->name }}</dd></div>
                            <div><dt class="text-cream-muted">Email (your booking code is sent here)</dt><dd class="font-semibold break-all">{{ auth()->user()->email }}</dd></div>
                        </dl>
                        <label for="phone" class="mt-4 block text-sm text-cream-muted">Phone number</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone', auth()->user()->phone) }}" required maxlength="30" autocomplete="tel" class="field mt-1">
                    @else
                        <h3 class="text-lg font-bold">Log in to book</h3>
                        <p class="mt-2 text-sm leading-6 text-cream-muted">You can look around freely. To buy tickets, log in so your booking is saved to your account.</p>
                    @endauth

                    <div class="mt-6 border-t border-dashed border-gold/40 pt-5" aria-live="polite">
                        <p id="order-summary" class="text-sm text-cream-muted">Choose a seat category</p>
                        <p class="mt-1 flex items-baseline justify-between gap-3">
                            <span class="font-semibold">Total</span>
                            <strong id="total-price" class="font-display text-3xl font-extrabold tabular-nums text-gold-soft">Rp 0</strong>
                        </p>
                    </div>

                    @auth
                        <button id="submit-booking" type="submit" class="btn btn-gold mt-5 w-full !py-4 !text-base">Confirm booking</button>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-gold mt-5 w-full !py-4 !text-base">Log in to book</a>
                        <p class="mt-3 text-center text-sm text-cream-muted">New here? <a href="{{ route('register') }}" class="font-semibold text-gold-soft underline">Create an account</a></p>
                    @endauth
                </aside>
            </form>

            <script>
                (() => {
                    const form = document.getElementById('booking-form');
                    const quantity = document.getElementById('quantity');
                    const submit = document.getElementById('submit-booking');
                    const submitLabel = submit?.textContent;
                    const money = (value) => `Rp ${new Intl.NumberFormat('id-ID').format(value)}`;

                    function update(clamp = true) {
                        const max = Number(quantity.max);
                        const count = Math.max(1, Math.min(Math.floor(Number(quantity.value)) || 1, max || 1));
                        if (clamp) quantity.value = count;
                        const seat = form.querySelector('input[name="seat_type"]:checked');
                        document.getElementById('order-summary').textContent = seat ? `${count} × ${seat.dataset.label}` : 'Choose a seat category';
                        document.getElementById('total-price').textContent = money(seat ? Number(seat.dataset.price) * count : 0);
                        if (submit) submit.disabled = !seat || max < 1;
                    }

                    form.querySelectorAll('[data-step]').forEach((button) => button.addEventListener('click', () => {
                        quantity.value = Number(quantity.value) + Number(button.dataset.step);
                        update();
                    }));
                    form.querySelectorAll('input[name="seat_type"]').forEach((radio) => radio.addEventListener('change', () => update()));
                    quantity.addEventListener('input', () => update(false));
                    quantity.addEventListener('change', () => update());
                    // Cegah pesanan ganda saat tombol ditekan berkali-kali.
                    form.addEventListener('submit', () => { if (submit) { submit.disabled = true; submit.textContent = 'Booking…'; } });
                    window.addEventListener('pageshow', () => { if (submit) submit.textContent = submitLabel; update(); });
                    update();
                })();
            </script>
        @endif
    </section>
</x-layout>
