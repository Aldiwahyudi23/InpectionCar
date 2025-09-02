<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { CalendarDaysIcon, PlusIcon } from '@heroicons/vue/24/outline';
import { CarIcon } from 'lucide-vue-next';

defineProps({
    tasks: Array,
    encryptedIds: Object
});

const getButtonLabel = () => 'Lihat Detail';
</script>

<template>
    <AppLayout>
        <Head title="Riwayat Inspeksi" />
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <h3 class="text-xl md:text-3xl font-bold text-gray-900 mb-6 text-center">
                    Riwayat Inspeksi
                </h3>

                <!-- Jika ada tasks -->
                <div
                    v-if="tasks.length > 0"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2"
                >
                    <div
                        v-for="task in tasks"
                        :key="task.id"
                        class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition"
                    >
                       <!-- Jadwal + Link Log -->
                        <div class="p-4 flex justify-between items-start">
                            <div>
                                <div class="flex items-center mb-1">
                                    <CalendarDaysIcon class="h-5 w-5 text-blue-500 mr-2" />
                                    <span class="text-sm font-medium text-gray-600">Jadwal</span>
                                </div>
                                <p class="text-sm font-semibold text-blue-700 ml-7 -mt-1">
                                    {{ new Date(task.inspection_date).toLocaleDateString('id-ID', {
                                        weekday: 'short', year: 'numeric', month: 'short', day: 'numeric',
                                        hour: '2-digit', minute: '2-digit'
                                    }) }}
                                </p>
                            </div>

                            <!-- Link ke Log -->
                            <Link
                                :href="route('inspection.log', { inspection: encryptedIds[task.id] })"
                                class="text-xs font-semibold text-indigo-600 hover:underline"
                            >
                                Lihat Log
                            </Link>
                        </div>

                        <!-- Mobil -->
                        <div  class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                            <div class="flex items-center">
                                <CarIcon class="h-5 w-5 text-gray-500 mr-2" />
                                <div class="text-sm font-medium text-gray-800">
                                   <div v-if="task.car">
                                        {{ `${task.car.brand.name} ${task.car.model.name} ${task.car.type.name} ${task.car.cc} ${task.car.transmission} ${task.car.year}` }}
                                        <span class="text-gray-600">({{ task.car.fuel_type }})</span>
                                    </div>
                                    <div v-else>
                                        {{ task.car_name }}
                                    </div>
                                </div>
                            </div>
                            <!-- Nomor Plat Mobil -->
                            <div class="flex items-center mt-2">
                                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 mr-2">NO POLISI:</span>
                                <span class="text-sm font-bold text-gray-900">{{ task.plate_number }}</span>
                            </div>
                        </div>

                        <!-- Kategori -->
                        <div v-if="task.category" class="px-4 py-2 bg-white border-t border-gray-100">
                            <p class="text-xs font-medium text-gray-500  tracking-wide">Kategori Inspek</p>
                            <p class="text-sm text-gray-800">{{ task.category.name }}</p>
                        </div>

                        <!-- Tombol detail -->
                        <div class="p-4">
                             <Link
                                :href="route('inspections.review', { id: encryptedIds[task.id] })"
                                class="inline-flex items-center justify-center w-full px-3 py-2 bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white font-medium rounded-md text-sm transition-colors"
                            >
                                {{ getButtonLabel() }}
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Jika tidak ada tasks -->
                <div v-else class="text-center py-8">
                    <p class="text-gray-500">Belum ada riwayat inspeksi.</p>
                </div>

                <!-- Tombol kembali -->
                <div class="text-center mt-6">
                    <Link
                        :href="route('job.index')"
                        class="inline-block px-4 py-2 text-sm font-medium text-indigo-600 border border-indigo-600 rounded-md hover:bg-blue-50"
                    >
                        Kembali ke Tugas
                    </Link>
                </div>
            </div>
    </AppLayout>
</template>

<style scoped>
.animate-bounce {
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-10px);
    }
    60% {
        transform: translateY(-5px);
    }
}
</style>
