import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
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
            includeAssets: ['favicon.ico', 'apple-touch-icon.jpg', 'masked-icon.svg'],
            manifest: {
                name: 'Car Inspection App',
                short_name: 'CarInspect',
                description: 'Aplikasi Inspeksi Kendaraan',
                theme_color: '#6366f1',
                background_color: '#ffffff',
                display: 'standalone',
                orientation: 'portrait',
                scope: '/',
                start_url: '/',
                icons: [
                    {
                        src: '/icons/icon-72x72.jpg',
                        sizes: '72x72',
                        type: 'image/jpg',
                        purpose: 'maskable any'
                    },
                    {
                        src: '/icons/icon-96x96.jpg',
                        sizes: '96x96',
                        type: 'image/jpg',
                        purpose: 'maskable any'
                    },
                    {
                        src: '/icons/icon-128x128.jpg',
                        sizes: '128x128',
                        type: 'image/jpg',
                        purpose: 'maskable any'
                    },
                    {
                        src: '/icons/icon-144x144.jpg',
                        sizes: '144x144',
                        type: 'image/jpg',
                        purpose: 'maskable any'
                    },
                    {
                        src: '/icons/icon-152x152.jpg',
                        sizes: '152x152',
                        type: 'image/jpg',
                        purpose: 'maskable any'
                    },
                    {
                        src: '/icons/icon-192x192.jpg',
                        sizes: '192x192',
                        type: 'image/jpg',
                        purpose: 'maskable any'
                    },
                    {
                        src: '/icons/icon-384x384.jpg',
                        sizes: '384x384',
                        type: 'image/jpg',
                        purpose: 'maskable any'
                    },
                    {
                        src: '/icons/icon-512x512.jpg',
                        sizes: '512x512',
                        type: 'image/jpg',
                        purpose: 'maskable any'
                    }
                ]
            },
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,jpg,svg,woff2}'],
                runtimeCaching: [
                    {
                        urlPattern: /^https:\/\/fonts\.googleapis\.com\/.*/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'google-fonts-cache',
                            expiration: {
                                maxEntries: 10,
                                maxAgeSeconds: 60 * 60 * 24 * 365 // 1 year
                            },
                            cacheableResponse: {
                                statuses: [0, 200]
                            }
                        }
                    },
                    {
                        urlPattern: /^https:\/\/fonts\.gstatic\.com\/.*/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'gstatic-fonts-cache',
                            expiration: {
                                maxEntries: 10,
                                maxAgeSeconds: 60 * 60 * 24 * 365 // 1 year
                            },
                            cacheableResponse: {
                                statuses: [0, 200]
                            }
                        }
                    }
                ]
            }
        })
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
});