<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Bruno Mars Live — Ticket Registration</title>
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        <style>
            .seat-card.is-selected {
                border-color: #d7ad62;
                background: linear-gradient(135deg, rgba(215, 173, 98, .16), rgba(91, 23, 38, .45));
                box-shadow: 0 0 0 1px rgba(215, 173, 98, .25), 0 18px 50px rgba(0, 0, 0, .2);
            }

            #quantity::-webkit-inner-spin-button,
            #quantity::-webkit-outer-spin-button {
                appearance: none;
                margin: 0;
            }

            #quantity {
                appearance: textfield;
                box-sizing: border-box;
                width: 7rem !important;
                height: 5rem !important;
                padding: 0 !important;
                font-size: 4.5rem !important;
                font-weight: 900 !important;
                line-height: 5rem !important;
            }

            @media (max-width: 640px) {
                #quantity {
                    width: 7rem !important;
                    font-size: 4.5rem !important;
                }
            }
        </style>
    </head>
    <body class="min-h-screen bg-[#1d0811] font-sans text-[#fff8ed] antialiased">
        <div class="overflow-hidden">
            <header class="relative z-10 mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full border border-[#e7c37b]/60 bg-[#d7ad62] text-xl text-[#3d101b] shadow-lg shadow-[#d7ad62]/20">♪</span>
                    <span class="text-xl font-black tracking-tight">Bruno <span class="text-[#e7c37b]">Mars</span></span>
                </a>
                <nav class="flex items-center gap-2 text-sm">
                    <a href="#booking" class="rounded-full border border-[#e7c37b]/40 px-4 py-2 font-semibold text-[#f3d9a1] transition hover:bg-[#d7ad62] hover:text-[#3d101b]">Get tickets</a>
                </nav>
            </header>

            @if (session('success') || session('error') || session('warning'))
                <div class="relative z-20 mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="rounded-2xl border px-5 py-4 text-sm {{ session('success') ? 'border-emerald-300/30 bg-emerald-300/10 text-emerald-100' : (session('warning') ? 'border-[#d7ad62]/40 bg-[#d7ad62]/10 text-[#f3d9a1]' : 'border-red-300/30 bg-red-300/10 text-red-100') }}">
                        {{ session('success') ?? session('warning') ?? session('error') }}
                    </div>
                </div>
            @endif

            <main>
                <section class="relative mx-auto max-w-7xl px-6 pb-20 pt-16 lg:px-8 lg:pb-24 lg:pt-24">
                    <div class="pointer-events-none absolute -left-24 top-0 h-96 w-96 rounded-full bg-[#8d293e]/30 blur-3xl"></div>
                    <div class="pointer-events-none absolute right-0 top-24 h-80 w-80 rounded-full bg-[#d7ad62]/10 blur-3xl"></div>
                    <div class="relative mx-auto max-w-4xl text-center">
                        <div class="flex flex-col items-center">
                            <span class="inline-flex items-center gap-2 rounded-full border border-[#d7ad62]/40 bg-[#d7ad62]/10 px-4 py-2 text-xs font-bold uppercase tracking-[.2em] text-[#f3d9a1]">
                                <span class="h-2 w-2 rounded-full bg-[#d7ad62]"></span>
                                Bruno Mars live experience
                            </span>
                            <h1 class="mt-7 max-w-3xl text-5xl font-black leading-[1.02] tracking-tight sm:text-6xl lg:text-7xl">
                                The night starts<br>
                                <span class="text-[#e7bd72]">with a little funk.</span>
                            </h1>
                            <p class="mt-6 max-w-2xl text-lg leading-8 text-[#f8e9d2]/65">
                                Secure your place for an unforgettable Bruno Mars show. Choose your seat category and get ready to dance all night.
                            </p>
                            <a href="#booking" class="mt-9 inline-flex items-center rounded-full bg-[#d7ad62] px-7 py-4 font-black text-[#3d101b] shadow-xl shadow-[#d7ad62]/20 transition hover:-translate-y-0.5 hover:bg-[#f0cf8d]">
                                Reserve your tickets <span class="ml-3 text-lg">→</span>
                            </a>
                            <div class="mt-12 flex justify-center gap-8 border-t border-[#f8e9d2]/15 pt-6">
                                <div><p class="text-2xl font-black text-[#f3d9a1]">100K+</p><p class="mt-1 text-xs text-[#f8e9d2]/50">Fans already booked</p></div>
                                <div><p class="text-2xl font-black text-[#f3d9a1]">5/5</p><p class="mt-1 text-xs text-[#f8e9d2]/50">Live experience rating</p></div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="booking" class="mx-auto max-w-7xl px-6 pb-24 lg:px-8">
                    <div class="rounded-[2rem] border border-[#d7ad62]/25 bg-[#5b1726]/45 p-6 shadow-2xl shadow-black/20 md:p-10">
                        <div class="mb-9 max-w-2xl">
                            <p class="text-sm font-bold uppercase tracking-[.2em] text-[#e7bd72]">Ticket registration</p>
                            <h2 class="mt-3 text-3xl font-black sm:text-4xl">Pick your show and make it a night to remember.</h2>
                            <p class="mt-4 leading-7 text-[#f8e9d2]/60">Select a concert date and seat category below. Your total will update instantly as you customize your tickets.</p>
                        </div>

                        <form method="POST" action="{{ route('concerts.store') }}" class="space-y-7">
                            @csrf
                            <div>
                                <p class="mb-3 text-sm font-bold text-[#fff8ed]/80">1. Choose your seat</p>
                                @if ($concert)
                                    <div class="mb-4 flex flex-wrap items-center gap-3 rounded-2xl border border-[#d7ad62]/25 bg-[#3d101b]/60 px-5 py-4 text-sm">
                                        <span class="font-black text-[#f3d9a1]">{{ $concert->title }}</span>
                                        <span class="text-[#f8e9d2]/50">·</span>
                                        <span class="text-[#f8e9d2]/65">9 February 2027 · 18.00 - 22.00</span>
                                        <span class="text-[#f8e9d2]/50">·</span>
                                        <span class="text-[#f8e9d2]/65">{{ $concert->venue }}</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        @foreach (['regular' => ['Regular', 'Standard concert access', 1000000], 'premium' => ['Premium', 'Closer to the stage', 2000000], 'vip' => ['VIP', 'Premium stage access', 3000000], 'diamond' => ['Diamond', 'The ultimate experience', 4000000]] as $type => [$label, $description, $price])
                                            <button type="button" class="seat-card rounded-2xl border border-[#f8e9d2]/15 bg-[#3d101b]/60 p-5 text-left transition hover:-translate-y-0.5 hover:border-[#d7ad62]/60" data-seat="{{ $type }}" data-price="{{ $price }}">
                                                <div class="flex items-start justify-between"><div><span class="text-lg font-black text-[#f3d9a1]">{{ $label }}</span><p class="mt-1 text-sm text-[#f8e9d2]/50">{{ $description }}</p></div><span class="selection-mark flex h-6 w-6 items-center justify-center rounded-full border border-[#f8e9d2]/30 text-xs text-transparent">✓</span></div>
                                                <p class="mt-5 border-t border-[#f8e9d2]/10 pt-4 text-xl font-black text-[#fff8ed]">Rp {{ number_format($price, 0, ',', '.') }} <span class="text-xs font-normal text-[#f8e9d2]/45">/ ticket</span></p>
                                            </button>
                                        @endforeach
                                    </div>
                                    <input id="concert-id" type="hidden" name="concert_id" value="{{ $concert->id }}" required>
                                @else
                                    <p class="rounded-2xl border border-[#f8e9d2]/15 p-6 text-[#f8e9d2]/60">Concert registration is not available yet.</p>
                                @endif
                                <input id="seat-type" type="hidden" name="seat_type" value="{{ old('seat_type') }}" required>
                            </div>

                            <div class="grid gap-5 border-t border-[#f8e9d2]/10 pt-7 md:grid-cols-2">
                                <div>
                                    <p class="mb-3 text-sm font-bold text-[#fff8ed]/80">2. Your details</p>
                                    <div class="space-y-3">
                                        <input name="name" type="text" value="{{ old('name') }}" required maxlength="255" autocomplete="name" placeholder="Full name" class="w-full rounded-xl border border-[#f8e9d2]/15 bg-[#3d101b]/60 px-4 py-3 text-[#fff8ed] placeholder:text-[#f8e9d2]/35 outline-none focus:border-[#d7ad62] focus:ring-2 focus:ring-[#d7ad62]/20">
                                        <input name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" placeholder="Email address" class="w-full rounded-xl border border-[#f8e9d2]/15 bg-[#3d101b]/60 px-4 py-3 text-[#fff8ed] placeholder:text-[#f8e9d2]/35 outline-none focus:border-[#d7ad62] focus:ring-2 focus:ring-[#d7ad62]/20">
                                        <input name="phone" type="tel" value="{{ old('phone') }}" required maxlength="30" autocomplete="tel" placeholder="Phone number" class="w-full rounded-xl border border-[#f8e9d2]/15 bg-[#3d101b]/60 px-4 py-3 text-[#fff8ed] placeholder:text-[#f8e9d2]/35 outline-none focus:border-[#d7ad62] focus:ring-2 focus:ring-[#d7ad62]/20">
                                        <textarea name="notes" rows="3" maxlength="2000" placeholder="Notes (optional)" class="w-full resize-none rounded-xl border border-[#f8e9d2]/15 bg-[#3d101b]/60 px-4 py-3 text-[#fff8ed] placeholder:text-[#f8e9d2]/35 outline-none focus:border-[#d7ad62] focus:ring-2 focus:ring-[#d7ad62]/20">{{ old('notes') }}</textarea>
                                    </div>
                                </div>
                                <div class="flex h-full flex-col">
                                    <p class="mb-3 text-sm font-bold text-[#fff8ed]/80">3. Number of tickets</p>
                                    <div class="flex flex-1 items-center justify-between rounded-xl border border-[#f8e9d2]/15 bg-[#3d101b]/60 px-5">
                                        <button type="button" id="decrease-quantity" aria-label="Decrease number of tickets" class="flex h-14 w-14 items-center justify-center rounded-full border border-[#d7ad62]/50 text-3xl text-[#f3d9a1] transition hover:bg-[#d7ad62] hover:text-[#3d101b]">−</button>
                                        <div class="text-center"><input id="quantity" name="quantity" type="number" min="1" value="1" required disabled class="w-28 border-0 bg-transparent text-center text-7xl font-black leading-none text-[#f3d9a1] outline-none"><p id="ticket-limit" class="mt-4 text-sm text-[#f8e9d2]/45">Choose a concert first</p></div>
                                        <button type="button" id="increase-quantity" aria-label="Increase number of tickets" class="flex h-14 w-14 items-center justify-center rounded-full border border-[#d7ad62]/50 text-3xl text-[#f3d9a1] transition hover:bg-[#d7ad62] hover:text-[#3d101b]">+</button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-5 border-t border-[#f8e9d2]/10 pt-7 sm:flex-row sm:items-center sm:justify-between">
                                <div><p class="text-xs uppercase tracking-widest text-[#f8e9d2]/45">Total amount</p><strong id="total-price" class="text-3xl font-black text-[#f3d9a1]">Rp 0</strong></div>
                                <button id="submit-booking" type="submit" disabled class="rounded-full bg-[#d7ad62] px-8 py-4 font-black text-[#3d101b] transition hover:bg-[#f0cf8d] disabled:cursor-not-allowed disabled:opacity-40">Confirm registration <span class="ml-2">→</span></button>
                            </div>
                        </form>
                    </div>
                </section>
            </main>

            <footer class="border-t border-[#f8e9d2]/10 px-6 py-8 text-center text-sm text-[#f8e9d2]/40">© {{ date('Y') }} Bruno Mars Live. Official ticket registration.</footer>
        </div>
        <script>
            const seatCards = document.querySelectorAll('.seat-card');
            const seatType = document.getElementById('seat-type');
            const quantity = document.getElementById('quantity');
            const totalPrice = document.getElementById('total-price');
            const ticketLimit = document.getElementById('ticket-limit');
            const submitBooking = document.getElementById('submit-booking');
            const decrease = document.getElementById('decrease-quantity');
            const increase = document.getElementById('increase-quantity');
            let selectedSeat = null;
            const availableTickets = {{ $concert?->available_tickets ?? 0 }};
            const formatPrice = (value) => `Rp ${new Intl.NumberFormat('id-ID').format(value)}`;

            function updateBooking() {
                const price = Number(selectedSeat?.dataset.price ?? 0);
                const count = Number(quantity.value) || 1;
                quantity.value = Math.max(1, Math.min(count, availableTickets || 1));
                quantity.disabled = !selectedSeat;
                totalPrice.textContent = formatPrice(price * Number(quantity.value));
                ticketLimit.textContent = `${availableTickets} tickets available`;
                submitBooking.disabled = !selectedSeat || availableTickets < 1;
            }

            seatCards.forEach((card) => card.addEventListener('click', () => {
                seatCards.forEach((item) => {
                    item.classList.remove('is-selected');
                    item.querySelector('.selection-mark').classList.remove('border-[#d7ad62]', 'bg-[#d7ad62]', 'text-[#3d101b]');
                    item.querySelector('.selection-mark').classList.add('text-transparent');
                });
                selectedSeat = card;
                card.classList.add('is-selected');
                const mark = card.querySelector('.selection-mark');
                mark.classList.remove('text-transparent');
                mark.classList.add('border-[#d7ad62]', 'bg-[#d7ad62]', 'text-[#3d101b]');
                seatType.value = card.dataset.seat;
                updateBooking();
            }));

            decrease.addEventListener('click', () => { if (selectedSeat) { quantity.value = Math.max(1, Number(quantity.value) - 1); updateBooking(); } });
            increase.addEventListener('click', () => { if (selectedSeat) { quantity.value = Math.min(availableTickets, Number(quantity.value) + 1); updateBooking(); } });
            quantity.addEventListener('input', updateBooking);
        </script>
    </body>
</html>
