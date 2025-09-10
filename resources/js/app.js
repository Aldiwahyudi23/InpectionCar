import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// Import PWA setup
import { usePWA } from './Composables/usePWA';

const appName = import.meta.env.VITE_APP_NAME || 'Car Inspection';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .mount(el);
          // Buat instance PWA
        const pwa = usePWA();
        
        const vueApp = createApp({ 
            render: () => h(App, props) 
        })
           // Provide PWA functionality globally
        vueApp.provide('pwa', pwa);
        
        vueApp.mount(el);
        
         // Initialize PWA setelah app mounted
        pwa.initPWA();

        return vueApp;
    },
    progress: {
        color: '#4B5563',
    },
});