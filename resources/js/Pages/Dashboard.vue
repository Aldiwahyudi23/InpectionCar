<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// Data menu untuk mempermudah penambahan atau penghapusan item di masa depan
const navMenus = [
    { name: 'Inspeksi Baru', icon: 'M12 4v16m8-8H4', route: 'inspections.create.new' },
    { name: 'Daftar Inspeksi', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M15 12a3 3 0 11-6 0 3 3 0 016 0z', route: 'welcome' },
    { name: 'Arsip', icon: 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4', route: 'inspections.history' },
    { name: 'Pelanggan', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', route: 'welcome' },
    { name: 'Kendaraan', icon: 'M3 5a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-4 4v-4H5a2 2 0 01-2-2V5z', route: 'welcome' },
    { name: 'Laporan', icon: 'M9 19V6l12-3v14H9zM9 19a2 2 0 002 2h2a2 2 0 002-2M9 19c-.333 0-.667 0-1 0a2 2 0 01-2-2v-3.5a2 2 0 012-2a2 2 0 002-2c0-.333 0-.667 0-1', route: 'welcome' },
    { name: 'Pengaturan', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 002.572-1.065z', route: 'welcome' },
    { name: 'Bantuan', icon: 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.79 4 4s-1.79 4-4 4c-.733 0-1.424-.23-1.992-.622l-.462.924a7.99 7.99 0 002.454.898c2.93 0 5.295-2.365 5.295-5.295S15.158 3.5 12.228 3.5c-2.93 0-5.295 2.365-5.295 5.295z', route: 'welcome' },
];
</script>

<template>
    <AppLayout title="Dashboard">
         <!-- <Head title="Riwayat Inspeksi" /> -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6 text-center">
                    <h2 class="text-gray-500 font-semibold text-sm">Hasil Inspection</h2>
                    <p class="text-4xl font-bold text-gray-800 mt-2">12</p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                    <h3 class="text-md font-bold text-gray-800 mb-4">Fitur Utama</h3>
                    <div class="grid grid-cols-4 sm:grid-cols-5 gap-y-6">
                        <Link v-for="menu in navMenus" :key="menu.name" :href="route(menu.route)" 
                              class="flex flex-col items-center text-gray-600 hover:text-gray-800 transition-colors duration-200">
                            <div class="bg-gray-100 p-3 rounded-xl mb-1 hover:bg-gray-200 transition-colors duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="menu.icon" />
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-center">{{ menu.name }}</span>
                        </Link>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Inspeksi Terakhir</h3>
                    
                    <div class="space-y-4">
                        <Link :href="route('welcome')" class="block bg-gray-50 p-3 rounded-md border border-gray-200 hover:bg-gray-100 transition-colors duration-200">
                            <div class="flex justify-between items-center">
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-gray-800 truncate">Toyota Avanza 2020</p>
                                    <p class="text-sm text-gray-500 truncate">B 1234 ABC</p>
                                </div>
                                <div class="text-right">
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                        Dalam Proses
                                    </span>
                                    <p class="text-xs text-gray-400 mt-1">2 jam lalu</p>
                                </div>
                            </div>
                        </Link>
                        <Link :href="route('welcome')" class="block bg-gray-50 p-3 rounded-md border border-gray-200 hover:bg-gray-100 transition-colors duration-200">
                            <div class="flex justify-between items-center">
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-gray-800 truncate">Honda CR-V 2018</p>
                                    <p class="text-sm text-gray-500 truncate">D 5678 EFG</p>
                                </div>
                                <div class="text-right">
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                        Selesai
                                    </span>
                                    <p class="text-xs text-gray-400 mt-1">Kemarin</p>
                                </div>
                            </div>
                        </Link>
                        <Link :href="route('welcome')" class="block bg-gray-50 p-3 rounded-md border border-gray-200 hover:bg-gray-100 transition-colors duration-200">
                            <div class="flex justify-between items-center">
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-gray-800 truncate">Mitsubishi Pajero Sport 2019</p>
                                    <p class="text-sm text-gray-500 truncate">F 9012 HIJ</p>
                                </div>
                                <div class="text-right">
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                        Dalam Proses
                                    </span>
                                    <p class="text-xs text-gray-400 mt-1">3 hari lalu</p>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
    </AppLayout>
</template>