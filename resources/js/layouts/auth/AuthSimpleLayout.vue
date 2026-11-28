<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 px-5 py-10"
    >
        <Link :href="home()" class="flex items-center gap-3">
            <span
                class="flex h-10 w-10 items-center justify-center rounded-full bg-gold text-wine"
            >
                <AppLogoIcon class="size-5" />
            </span>
            <span class="font-display text-xl font-extrabold"
                >Bruno <span class="text-gold-soft">Mars</span></span
            >
        </Link>

        <main class="panel flex w-full max-w-md flex-col gap-8 p-6 sm:p-8">
            <div class="space-y-2 text-center">
                <h1 class="font-display text-3xl font-extrabold">
                    {{ title }}
                </h1>
                <p class="text-sm text-muted-foreground">{{ description }}</p>
            </div>
            <slot />
        </main>
    </div>
</template>
