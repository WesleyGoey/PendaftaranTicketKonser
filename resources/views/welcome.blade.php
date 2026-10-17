<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Pendaftaran Tiket Konser</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.ts'])
    </head>
    <body class="font-sans antialiased bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a]">
        <div class="min-h-screen flex flex-col">
            <header class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl mx-auto">
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

            <main class="flex-1 flex-col items-center py-12 px-6 sm:px-8 lg:py-20">
                <!-- Hero Section -->
                <div class="relative w-full max-w-2xl">
                    <div class="absolute inset-0 bg-gradient-to-r from-[#4f46e5] to-[#7c3aed] opacity-90"></div>
                    <div class="relative z-10 flex flex-col items-center py-12 text-center text-white">
                        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">
                            Selamat Datang di Pendaftaran Tiket Konser
                        </h1>
                        <p class="mt-4 max-w-xl text-lg text-[#e0e7ff]">
                            Platform mudah dan aman untuk memesan tiket konser favorit Anda. Lihat jadwal konser terbaru, pilih tempat duduk, dan beli tiket hanya dalam beberapa klik.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('concerts.index') }}"
                               class="inline-flex items-center px-6 py-3 px-8 text-base font-medium rounded-md shadow-lg border border-transparent bg-white text-[#4f46e5] hover:bg-[#500]">
                                Lihat Konser Tersedia
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Latest Concerts -->
                <div class="mt-16 w-full max-w-2xl">
                    <h2 class="mb-6 text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
                        Konser Terbaru
                    </h2>
                    <div class="space-y-6">
                        @forelse ($latestConcerts as $concert)
                            <div class="bg-white rounded-xl shadow-md p-6 dark:bg-[#161615] dark:text-[#EDEDEC] hover:shadow-xl transition-shadow duration-300">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between">
                                    <div class="flex-1 sm:mr-4">
                                        <h3 class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $concert->title }}</h3>
                                        <p class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                            <strong>Artis:</strong> {{ $concert->artist }} |
                                            <strong>Tanggal:</strong> {{ \Illuminate\Support\Carbon::parse($concert->concert_date)->format('M d, Y') }} |
                                            <strong>Tempat:</strong> {{ $concert->venue }}
                                        </p>
                                        @if ($concert->description)
                                            <p class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ \Illuminate\Support\Str::limit($concert->description, 100) }}</p>
                                        @endif
                                    </div>
                                    <div class="mt-4 sm:mt-0 flex items-center">
                                        @if ($concert->available_tickets > 0)
                                            <span class="px-3 py-1 text-xs font-medium bg-[#10b981] text-white rounded-full">
                                                Tiket Tersedia
                                            </span>
                                        @else
                                            <span class="px-3 py-1 text-xs font-medium bg-[#ef4444] text-white rounded-full">
                                                Sold Out
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="col-span-2 text-center text-[#706f6c] dark:text-[#A1A09A]">
                                Belum ada konser yang tersedia saat ini.
                            </p>
                        @endforelse
                    </div>
                </div>
            </main>

            <footer class="mt-16 flex items-center justify-between px-6 py-4 bg-[#FDFDFC] dark:bg:#[0a0a0a]">
                <span class="text-sm text-[#706f6c] dark:text:#[A1A09A]">
                    &copy; {{ date('Y') }} Pendaftaran Tiket Konser. Hak cipta dilindungi undang-undang.
                </span>
            </footer>
        </div>
    </body>
</html>