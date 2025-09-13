import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import PWAInstallButton from './Components/PWAInstallButton.vue';

// Import service worker
import './registerSW';

const appName = import.meta.env.VITE_APP_NAME || 'Car Inspection';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .component('PWAInstallButton', PWAInstallButton)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },

     // Ini bagian pentingnya: Penanganan Error
    onError: (error) => {
        // Log error untuk debugging
        console.error(error);
        
        // Cek jika statusnya 403 (Forbidden)
        if (error.status === 403) {
            // Arahkan ke rute halaman error 403 yang sebenarnya
            // Pastikan Anda sudah membuat rute '/403-forbidden' di routes/web.php
            window.location.href = '/error/403';
        }

        // Jika statusnya 419 (CSRF token expired), muat ulang halaman
        if (error.status === 419) {
            window.location.reload();
        }

        // tambahkan di app.js atau file utama
        if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(function(registrations) {
            for(let registration of registrations) {
            registration.unregister();
            }
        });
        }
    },
});
