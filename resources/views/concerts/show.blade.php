<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $concert->title }} - Bruno Mars Live</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.ts'])
    </head>
    <body class="font-sans antialiased bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a]">
        <div class="flex min-h-screen flex-col items-center p-6 lg:justify-center lg:p-8">
            <header class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl">
                <nav class="flex items-center justify-between gap-4">
                    <a href="{{ route('concerts.index') }}"
                       class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]">
                        ← Back to Concerts
                    </a>
                    <div class="flex items-center gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}"
                               class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]">
                                Log in
                            </a>
                            <a href="{{ route('register') }}"
                               class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]">
                                Register
                            </a>
                        @endauth
                    </div>
                </nav>
            </header>

            <main class="flex w-full flex-col items-center gap-6">
                <div class="bg-white rounded-lg shadow-md p-6 w-full max-w-2xl dark:bg-[#161615] dark:text-[#EDEDEC]">
                    <h1 class="text-2xl font-semibold mb-4">{{ $concert->title }}</h1>
                    <p class="text-[#706f6c] mb-2 dark:text:#[A1A09A]"><strong>Artist:</strong> {{ $concert->artist }}</p>
                    <p class="text:#[706f6c] mb-2 dark:text:#[A1A09A]"><strong>Date:</strong> 9 February 2027</p>
                    <p class="text:#[706f6c] mb-2 dark:text:#[A1A09A]"><strong>Time:</strong> 18.00 - 22.00</p>
                    <p class="text:#[706f6c] mb-2 dark:text:#[A1A09A]"><strong>Venue:</strong> {{ $concert->venue }}</p>
                    <p class="text:#[706f6c] mb-2 dark:text:#[A1A09A]"><strong>Starting price:</strong> Rp {{ number_format($concert->price, 0, ',', '.') }} per ticket</p>
                    <p class="text:#[706f6c] mb-2 dark:text:#[A1A09A]"><strong>Available Tickets:</strong> {{ $concert->available_tickets }} / {{ $concert->total_tickets }}</p>

                    <div class="mt-6">
                        <h2 class="text-xl font-semibold mb-2">Description</h2>
                        <p class="text:#[706f6c] dark:text:#[A1A09A]">{{ $concert->description }}</p>
                    </div>

                    @if ($concert->available_tickets > 0)
                        <div class="mt-8">
                            <h2 class="text-xl font-semibold mb-4">Purchase Tickets</h2>
                            <form action="{{ route('concerts.store') }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium mb-1">Number of Tickets</label>
                                    <input type="number"
                                           name="quantity"
                                           min="1"
                                           max="{{ $concert->available_tickets }}"
                                           value="1"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-[#262623] dark:border-gray-600 dark:text-[#EDEDEC]">
                                </div>
                                <div>
                                    <label for="seat-type" class="block text-sm font-medium mb-1">Seat category</label>
                                    <select id="seat-type" name="seat_type" required class="w-full rounded-md border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-[#262623] dark:text-[#EDEDEC]">
                                        <option value="regular">Regular — base price</option>
                                        <option value="premium">Premium — 1.5x base price</option>
                                        <option value="vip">VIP — 2x base price</option>
                                    </select>
                                </div>
                                <div class="flex items-center">
                                    <span class="mr-2" id="total-price">Total Price: Rp {{ number_format($concert->price, 0, ',', '.') }}</span>
                                    <button type="submit"
                                            class="ml-auto inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-1.5 text-sm leading-normal text-white hover:border-black hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white">
                                        Purchase Tickets
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="mt-8">
                            <p class="text-center text-[#706f6c] py-4 dark:text:#[A1A09A]">Sorry, this concert is sold out.</p>
                        </div>
                    @endif
                </div>
            </main>
        </body>
</html>