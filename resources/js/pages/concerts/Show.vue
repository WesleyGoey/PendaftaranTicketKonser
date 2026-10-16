<template>
    <div class="min-h-screen bg-[#FDFDFC] p-6 text-[#1b1b18] lg:p-8 dark:bg-[#0a0a0a]">
        <Head title="{{ concert.title }} - Tickets" />

        <header class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl">
            <nav class="flex items-center justify-between gap-4">
                <Link
                    :href="route('concerts.index')"
                    class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                >
                    ← Back to Concerts
                </Link>
                <div class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="route('register')"
                            class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                        >
                            Register
                        </Link>
                    </template>
                </div>
            </nav>
        </header>

        <main class="flex w-full flex-col items-center gap-6">
            <div class="bg-white rounded-lg shadow-md p-6 w-full max-w-2xl dark:bg-[#161615] dark:text-[#EDEDEC]">
                <h1 class="text-2xl font-semibold mb-4">{{ concert.title }}</h1>
                <p class="text-[#706f6c] mb-2 dark:text-[#A1A09A]"><strong>Artist:</strong> {{ concert.artist }}</p>
                <p class="text-[#706f6c] mb-2 dark:text-[#A1A09A]"><strong>Date:</strong> {{ new Date(concert.concert_date).toLocaleDateString() }} at {{ new Date(concert.concert_date).toLocaleTimeString() }}</p>
                <p class="text-[#706f6c] mb-2 dark:text-[#A1A09A]"><strong>Venue:</strong> {{ concert.venue }}</p>
                <p class="text-[#706f6c] mb-2 dark:text-[#A1A09A]"><strong>Price:</strong> ${{ concert.price }} per ticket</p>
                <p class="text-[#706f6c] mb-2 dark:text-[#A1A09A]"><strong>Available Tickets:</strong> {{ concert.available_tickets }} / {{ concert.total_tickets }}</p>

                <div class="mt-6">
                    <h2 class="text-xl font-semibold mb-2">Description</h2>
                    <p class="text-[#706f6c] dark:text-[#A1A09A]">{{ concert.description }}</p>
                </div>

                <div v-if="concert.available_tickets > 0" class="mt-8">
                    <h2 class="text-xl font-semibold mb-4">Purchase Tickets</h2>
                    <form @submit.prevent="purchaseTickets" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Number of Tickets</label>
                            <input
                                type="number"
                                min="1"
                                :max="concert.available_tickets"
                                v-model="quantity"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-[#262623] dark:border-gray-600 dark:text-[#EDEDEC]"
                            />
                        </div>
                        <div class="flex items-center">
                            <span class="mr-2">Total Price: ${{ totalPrice }}</span>
                            <button
                                type="submit"
                                class="ml-auto inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-1.5 text-sm leading-normal text-white hover:border-black hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                            >
                                Purchase Tickets
                            </button>
                        </div>
                    </form>
                </div>

                <div v-else class="mt-8">
                    <p class="text-center text-[#706f6c] py-4 dark:text-[#A1A09A]">Sorry, this concert is sold out.</p>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { route } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    concert: Object
});

const quantity = ref(1);

const totalPrice = computed(() => {
    return (quantity.value * props.concert.price).toFixed(2);
});

function purchaseTickets() {
    if (!quantity.value || quantity.value < 1 || quantity.value > props.concert.available_tickets) {
        alert('Please enter a valid number of tickets');
        return;
    }

    // In a real app, this would submit to the backend
    // For now, we'll simulate with Inertia
    visit(route('concerts.store'), {
        method: 'post',
        data: {
            concert_id: props.concert.id,
            quantity: quantity.value
        },
        onSuccess: () => {
            alert('Ticket purchase successful!');
        },
        onError: (page) => {
            alert(page.props.error || 'An error occurred');
        }
    });
}
</script>