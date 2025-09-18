import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import { VitePWA } from 'vite-plugin-pwa';

// Definisikan warna tema di satu tempat agar konsisten
const THEME_COLOR = '#4338ca'; // Warna indigo-700
const BACKGROUND_COLOR = '#ffffff'; // Warna latar belakang

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js'
            ],
            refresh: [
                {
                    paths: [
                        'resources/views/**',
                        'resources/js/Pages/**',
                        'resources/js/Layouts/**',
                        'resources/js/Components/**',
                    ],
                    config: { delay: 300 }
                }
            ],
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        VitePWA({
            registerType: 'autoUpdate',
            includeAssets: ['favicon.ico', 'apple-touch-icon.png', 'masked-icon.svg'],
            injectRegister: 'auto',
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg}']
            },
            manifest: {
                 name: 'cek Mobil',
                short_name: 'Inspek Mobil',
                description: 'Aplikasi Inspek bagi karyawan tersaakiti',
                theme_color: THEME_COLOR, // Gunakan variabel yang sama
                background_color: BACKGROUND_COLOR,
                display: 'standalone', // standalone, minimal-ui, fullscreen
                orientation: 'portrait', // portrait, landscape, atau any
                start_url: '/',
                icons: [
                    {
                        src: '/pwa-192x192.png',
                        sizes: '192x192',
                        type: 'image/png',
                        purpose: 'any maskable'
                    },
                    {
                        src: '/pwa-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'any maskable'
                    },
                    {
                        src: '/pwa-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'maskable' // Untuk ikon yang bisa di-mask
                    }
                ],
                categories: ['productivity', 'utilities'], // Kategori aplikasi
                lang: 'id', // Bahasa default
                dir: 'ltr', // Direction (ltr atau rtl)
                // Untuk iOS khusus
                appleStatusBarStyle: 'black-translucent',
                preferRelatedApplications: false
            }
        }),

    ],
    resolve: {
        alias: {
            '@': '/resources/js',
            'ziggy-js': path.resolve('vendor/tightenco/ziggy/dist/vue.es.js'),
        },
        extensions: ['.js', '.vue', '.json']
    },
    build: {
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['vue', '@inertiajs/vue3'],
                }
            }
        }
    },
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        hmr: {
            host: '127.0.0.1',
            protocol: 'ws'
        }
    },
    optimizeDeps: {
        include: [
            'vue',
            '@inertiajs/vue3',
            'axios'
        ]
    }
});