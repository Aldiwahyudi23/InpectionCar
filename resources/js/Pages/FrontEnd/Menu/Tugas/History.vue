<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { CalendarDaysIcon } from '@heroicons/vue/24/outline';
import { Car } from 'lucide-vue-next';

defineProps({
    tasks: Array,
});
</script>

<template>
    <AppLayout>
        <Head title="Riwayat Inspeksi" />

        <div class="py-6 md:py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6 text-center">
                    Riwayat Inspeksi
                </h1>

                <!-- Jika ada data -->
                <div
                    v-if="tasks.length > 0"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
                >
                    <div
                        v-for="task in tasks"
                        :key="task.id"
                        class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition"
                    >
                        <!-- Jadwal -->
                        <div class="p-4">
                            <div class="flex items-center mb-1">
                                <CalendarDaysIcon class="h-5 w-5 text-blue-500 mr-2" />
                                <span class="text-sm font-medium text-gray-600">Tanggal</span>
                            </div>
                            <p class="text-sm font-semibold text-blue-700 ml-7 -mt-1">
                                {{ new Date(task.inspection_date).toLocaleDateString('id-ID', { 
                                    weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', 
                                    hour: '2-digit', minute: '2-digit' 
                                }) }}
                            </p>
                        </div>

                        <!-- Mobil -->
                        <div v-if="task.car" class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                            <div class="flex items-center">
                                <Car class="h-5 w-5 text-gray-500 mr-2" />
                                <div class="text-sm font-medium text-gray-800">
                                    {{ `${task.car.brand.name} ${task.car.model.name} ${task.car.type.name} ${task.car.cc} ${task.car.transmission} ${task.car.year}` }}
                                    <span class="text-gray-600">({{ task.car.fuel_type }})</span>
                                </div>
                            </div>
                        </div>

                        <!-- Kategori -->
                        <div v-if="task.category" class="px-4 py-2 bg-white border-t border-gray-100">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Kategori</p>
                            <p class="text-sm text-gray-800">{{ task.category.name }}</p>
                        </div>

                        <!-- Status -->
                        <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Status</p>
                            <p class="text-sm font-semibold text-green-600 capitalize">
                                {{ task.status.replace('_', ' ') }}
                            </p>
                        </div>

                        <!-- Tombol detail -->
                        <div class="p-4">
                            <Link
                                :href="`/inspections/${task.id}`"
                                class="inline-flex items-center justify-center w-full px-3 py-2 bg-gray-700 hover:bg-gray-800 text-white font-medium rounded-md text-sm transition-colors"
                            >
                                Lihat Detail
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Jika tidak ada data -->
                <div v-else class="text-center py-8">
                    <p class="text-gray-500">Belum ada riwayat inspeksi.</p>
                </div>

                <!-- Tombol kembali -->
                <div class="text-center mt-6">
                    <Link
                         :href="route('job.index')"
                        class="inline-block px-4 py-2 text-sm font-medium text-blue-600 border border-blue-600 rounded-md hover:bg-blue-50"
                    >
                        Kembali ke Tugas
                    </Link>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
