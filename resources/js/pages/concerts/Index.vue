<template>
    <div class="min-h-screen bg-[#FDFDFC] p-6 text-[#1b1b18] lg:p-8 dark:bg-[#0a0a0a]">
        <Head title="Concert Tickets" />

        <header class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl">
            <nav class="flex items-center justify-end gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                    >
                        Log in
                    </Link>
                    <Link
                        :href="register()"
                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                    >
                        Register
                    </Link>
                </template>
            </nav>
        </header>

        <main class="flex w-full flex-col items-center gap-6">
            <h1 class="text-2xl font-bold mb-4">Upcoming Concerts</h1>

            <div class="w-full max-w-4xl space-y-6">
                <template v-for="concert in concerts" :key="concert.id">
                    <div class="bg-white rounded-lg shadow-md p-6 dark:bg-[#161615] dark:text-[#EDEDEC]">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between">
                            <div class="flex-1 md:mr-6">
                                <h2 class="text-xl font-semibold mb-2">{{ concert.title }}</h2>
                                <p class="text-[#706f6c] mb-2 dark:text-[#A1A09A]"><strong>Artist:</strong> {{ concert.artist }}</p>
                                <p class="text-[#706f6c] mb-2 dark:text-[#A1A09A]"><strong>Date:</strong> {{ new Date(concert.concert_date).toLocaleDateString() }}</p>
                                <p class="text-[#706f6c] mb-2 dark:text-[#A1A09A]"><strong>Venue:</strong> {{ concert.venue }}</p>
                                <p class="text-[#706f6c] mb-2 dark:text-[#A1A09A]"><strong>Price:</strong> ${{ concert.price }} per ticket</p>
                                <p class="text-[#706f6c] mb-2 dark:text-[#A1A09A]"><strong>Available Tickets:</strong> {{ concert.available_tickets }} / {{ concert.total_tickets }}</p>

                                <p class="text-[#706f6c] mt-4 dark:text-[#A1A09A]">{{ concert.description }}</p>
                            </div>

                            <div class="md:w-64 mt-6 md:mt-0">
                                <Link
                                    v-if="concert.available_tickets > 0"
                                    :href="`/concerts/${concert.id}`"
                                    class="w-full inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-1.5 text-sm leading-normal text-white hover:border-black hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                                >
                                    View Details & Buy Tickets
                                </Link>
                                <p v-else class="text-center text-[#706f6c] mt-6 dark:text-[#A1A09A]">Sold Out</p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { index } from '@/routes/concerts';
import { dashboard, login, register } from '@/routes';
</script>