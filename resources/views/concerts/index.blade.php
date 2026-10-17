<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Concert Tickets</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.ts'])
    </head>
    <body class="font-sans antialiased bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a]">
        <div class="flex min-h-screen flex-col items-center p-6 lg:justify-center lg:p-8">
            <header class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl">
                <nav class="flex items-center justify-end gap-4">
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
                </nav>
            </header>

            <main class="flex w-full flex-col items-center gap-6">
                <h1 class="text-2xl font-bold mb-4">Upcoming Concerts</h1>

                <div class="w-full max-w-4xl space-y-6">
                    @forelse ($concerts as $concert)
                        <div class="bg-white rounded-lg shadow-md p-6 dark:bg-[#161615] dark:text-[#EDEDEC]">
                            <div class="flex flex-col md:flex-row md:items-start md:justify-between">
                                <div class="flex-1 md:mr-6">
                                    <h2 class="text-xl font-semibold mb-2">{{ $concert->title }}</h2>
                                    <p class="text-[#706f6c] mb-2 dark:text-[#A1A09A]"><strong>Artist:</strong> {{ $concert->artist }}</p>
                                    <p class="text-[#706f6c] mb-2 dark:text:#[A1A09A]"><strong>Date:</strong> {{ \Illuminate\Support\Carbon::parse($concert->concert_date)->format('F j, Y') }}</p>
                                    <p class="text-[#706f6c] mb-2 dark:text:#[A1A09A]"><strong>Venue:</strong> {{ $concert->venue }}</p>
                                    <p class="text:#[706f6c] mb-2 dark:text:#[A1A09A]"><strong>Price:</strong> ${{ $concert->price }} per ticket</p>
                                    <p class="text:#[706f6c] mb-2 dark:text:#[A1A09A]"><strong>Available Tickets:</strong> {{ $concert->available_tickets }} / {{ $concert->total_tickets }}</p>

                                    <p class="text:#[706f6c] mt-4 dark:text:#[A1A09A]">{{ $concert->description }}</p>
                                </div>

                                <div class="md:w-64 mt-6 md:mt-0">
                                    @if ($concert->available_tickets > 0)
                                        <a href="{{ route('concerts.show', $concert) }}"
                                           class="w-full inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-1.5 text-sm leading-normal text-white hover:border-black hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white">
                                            View Details & Buy Tickets
                                        </a>
                                    @else
                                        <p class="text-center text-[#706f6c] mt-6 dark:text:#[A1A09A]">Sold Out</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-[#706f6c]">No concerts available.</p>
                    @endforelse
                </div>
            </main>
        </div>
    </body>
</html>