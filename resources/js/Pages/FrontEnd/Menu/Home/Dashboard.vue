<template>
    <AppLayout title="Dashboard">
        <Head title="Dashboard" />
        <!-- Kontainer utama dengan latar belakang abu-abu yang lembut -->
        <div class="bg-slate-100 min-h-screen">
            <!-- <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6"> -->
                <div class="w-full px-4 sm:px-6 lg:px-6 py-6">
                <!-- Card Jumlah Inspeksi (Menggunakan gradasi warna tema) -->
                <div class="bg-gradient-to-br from-indigo-700 to-sky-500 rounded-xl shadow-lg p-2 mb-4 text-center text-white">
                    <h2 class="font-semibold text-sm opacity-80">Hasil Inspection Bulan Ini</h2>
                    <p class="text-4xl font-bold mt-2">{{ monthlyApprovedCount }}</p>
                    <p class="text-sm opacity-90 mt-1">Inspeksi yang disetujui</p>
                </div>

                <!-- Menu Fitur Utama -->
                <div class="bg-white rounded-xl shadow-lg p-4 mb-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Fitur Utama</h3>
                    <div class="grid grid-cols-4 md:grid-cols-4 lg:grid-cols-5 gap-y-6">
                        <Link v-for="menu in filteredNavMenus" 
                            :key="menu.name" 
                            :href="menu.route.startsWith('/') ? menu.route : route(menu.route)" 
                            class="flex flex-col items-center text-gray-600 hover:text-indigo-800 transition-all duration-200 group">
                            
                            <div :class="['p-3 rounded-xl mb-1 transition-all duration-200 group-hover:bg-indigo-100', menu.color]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="menu.icon" />
                                </svg>
                            </div>
                            
                            <span class="text-xs font-medium text-center mt-1">{{ menu.name }}</span>
                        </Link>
                    </div>
                </div>


                <!-- Inspeksi Terakhir -->
                <div class="bg-white rounded-xl shadow-lg p-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Inspeksi Terakhir</h3>
                    
                    <div class="space-y-4" v-if="recentInspections.length > 0">
                        <Link v-for="inspection in recentInspections" :key="inspection.id" 
                                :href="route('job.index', inspection.id)" 
                                class="block bg-slate-50 p-4 rounded-lg border border-slate-200 hover:bg-slate-100 transition-colors duration-200">
                            <div class="flex justify-between items-center">
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-gray-800 truncate">{{ inspection.vehicle_name }}</p>
                                    <p class="text-sm text-gray-500 truncate">{{ inspection.license_plate }}</p>
                                </div>
                                <div class="text-right">
                                    <span :class="['text-xs font-medium px-2.5 py-0.5 rounded-full', getStatusBadgeClass(inspection.status)]">
                                        {{ getStatusText(inspection.status) }}
                                    </span>
                                    <p class="text-xs text-gray-400 mt-1">{{ inspection.created_at }}</p>
                                </div>
                            </div>
                        </Link>
                    </div>
                    
                    <div v-else class="text-center py-8 text-gray-500">
                        <p>Belum ada inspeksi</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, usePage} from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';


// Props dari controller
const props = defineProps({
    monthlyApprovedCount: Number,
    recentInspections: Array,
});

// Mengambil data global dari Inertia, termasuk roles pengguna
const page = usePage();
const userRoles = page.props.global.has_roles;

// Definisi menu dengan ikon dan warna
const navMenus = [
    { 
        name: 'Inspeksi Baru', 
        icon: 'M12 4v16m8-8H4', 
        route: 'inspections.create.new',
        color: 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200'
    },
    { 
        name: 'Daftar Inspeksi', 
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M15 12a3 3 0 11-6 0 3 3 0 016 0z', 
        route: 'coordinator.inspections.index',
        color: 'bg-sky-100 text-sky-700 hover:bg-sky-200',
        restricted: true // Penanda bahwa menu ini dibatasi
    },
    { 
        name: 'Arsip', 
        icon: 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4', 
        route: 'inspections.history',
        color: 'bg-blue-100 text-blue-700 hover:bg-blue-200'
    },
    { 
        name: 'Pelanggan', 
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 
        route: 'job.index',
        color: 'bg-cyan-100 text-cyan-700 hover:bg-cyan-200'
    },
    { 
        name: 'Kendaraan', 
        icon: 'M5 13.5a1.5 1.5 0 01-1.5-1.5V7a1.5 1.5 0 011.5-1.5h1a1.5 1.5 0 011.5 1.5v5a1.5 1.5 0 01-1.5 1.5H5zM13 13.5a1.5 1.5 0 01-1.5-1.5V7a1.5 1.5 0 011.5-1.5h1a1.5 1.5 0 011.5 1.5v5a1.5 1.5 0 01-1.5 1.5h-1zM5 19a2 2 0 01-2-2v-1a1 1 0 011-1h14a1 1 0 011 1v1a2 2 0 01-2 2H5z', 
        route: 'cars',
        color: 'bg-teal-100 text-teal-700 hover:bg-teal-200'
    },
    { 
        name: 'Laporan', 
        icon: 'M9 19V6l12-3v14H9zM9 19a2 2 0 002 2h2a2 2 0 002-2M9 19c-.333 0-.667 0-1 0a2 2 0 01-2-2v-3.5a2 2 0 012-2a2 2 0 002-2c0-.333 0-.667 0-1', 
        route: 'report.index',
        color: 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200',
    },
    { 
        name: 'Admin Panel', 
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 002.572-1.065z', 
        route: '/admin',
        color: 'bg-violet-100 text-violet-700 hover:bg-violet-200',
        restricted: true // Penanda bahwa menu ini dibatasi
    },
    { 
        name: 'Bantuan', 
        icon: 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.79 4 4s-1.79 4-4 4c-.733 0-1.424-.23-1.992-.622l-.462.924a7.99 7.99 0 002.454.898c2.93 0 5.295-2.365 5.295-5.295S15.158 3.5 12.228 3.5c-2.93 0-5.295 2.365-5.295 5.295z', 
        route: 'bantuan.index',
        color: 'bg-purple-100 text-purple-700 hover:bg-purple-200'
    },
];

// Menghitung menu yang difilter berdasarkan peran
const filteredNavMenus = computed(() => {
    return navMenus.filter(menu => {
        // Jika menu tidak dibatasi, selalu tampilkan
        if (!menu.restricted) {
            return true;
        }

        // Jika menu dibatasi, periksa apakah pengguna memiliki peran yang diizinkan
        return userRoles.includes('Admin') || userRoles.includes('coordinator');
    });
});

// Function untuk menentukan class badge berdasarkan status
const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'draft':
            return 'bg-gray-100 text-gray-800';
        case 'in_progress':
            return 'bg-blue-100 text-blue-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'pending_review':
            return 'bg-orange-100 text-orange-800';
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        case 'revision':
            return 'bg-purple-100 text-purple-800';
        case 'completed':
            return 'bg-teal-100 text-teal-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

// Function untuk teks status yang lebih user-friendly dalam Bahasa Indonesia
const getStatusText = (status) => {
    switch (status) {
        case 'draft':
            return 'Dibuat';
        case 'in_progress':
            return 'Dalam Proses';
        case 'pending':
            return 'Menunggu';
        case 'pending_review':
            return 'Menunggu Review';
        case 'approved':
            return 'Disetujui';
        case 'rejected':
            return 'Ditolak';
        case 'revision':
            return 'Revisi';
        case 'completed':
            return 'Selesai';
        case 'cancelled':
            return 'Dibatalkan';
        default:
            return status;
    }
};
</script>
