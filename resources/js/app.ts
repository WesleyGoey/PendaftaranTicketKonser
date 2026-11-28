import { createInertiaApp } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';

const appName = 'Bruno Mars Live';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    // Halaman Inertia yang tersisa hanya halaman auth.
    layout: () => AuthLayout,
    withApp: (app) => {
        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },
    progress: {
        color: '#d7ad62',
    },
});
