<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { CalendarDaysIcon, ArrowRightIcon, PlusIcon } from '@heroicons/vue/24/outline';
import { CarIcon } from 'lucide-vue-next'; // citycar icon
import { ref } from 'vue';

defineProps({
    tasks: Array,
});

const showModal = ref(false);
const selectedTask = ref(null);

const openModal = (task) => {
    selectedTask.value = task;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

// mapping button text berdasarkan status
const getButtonLabel = (status) => {
    switch (status) {
        case 'draft':
            return 'Mulai Inspeksi';
        case 'in_progress':
            return 'Lanjutkan Inspeksi';
        case 'pending_review':
            return 'Periksa Laporan';
        default:
            return 'Detail';
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Tugas" />

        <div class="py-6 md:py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h3 class="text-xl md:text-3xl font-bold text-gray-900 mb-6 text-center">
                    Inspeksi yang Harus Diselesaikan
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
                        <!-- Jadwal -->
                        <div class="p-4">
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

                        <!-- Mobil -->
                        <div v-if="task.car" class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                            <div class="flex items-center">
                                <CarIcon class="h-5 w-5 text-gray-500 mr-2" />
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

                        <!-- Button -->
                        <div class="p-4">
                            <button
                                @click="openModal(task)"
                                class="inline-flex items-center justify-center w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md text-sm transition-colors"
                            >
                                {{ getButtonLabel(task.status) }}
                                <ArrowRightIcon class="ml-2 h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Jika tidak ada tasks -->
                <div v-else class="text-center py-8">
                    <p class="text-gray-500">Tidak ada tugas inspeksi yang tersedia.</p>
                </div>

                <!-- Link tambahan untuk melihat inspeksi lainnya -->
                <div class="text-center mt-6">
                    <Link
                        :href="route('inspections.history')"
                        class="inline-block px-4 py-2 text-sm font-medium text-blue-600 border border-blue-600 rounded-md hover:bg-blue-50"
                    >
                        Lihat Inspeksi Lainnya
                    </Link>
                </div>

            </div>
        </div>

        <!-- Tombol Mengambang untuk Membuat Inspeksi Baru -->
        <Link
            :href="route('inspections.create.new')"
            class="fixed bottom-16 right-6 z-40 p-4 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 transition-colors duration-200 animate-bounce"
            title="Buat Inspeksi Baru"
        >
            <PlusIcon class="h-6 w-6" />
        </Link>


        <!-- Confirmation Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center p-4 z-50"
        >
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Konfirmasi</h3>

                    <!-- Info Mobil -->
                    <div v-if="selectedTask?.car" class="flex items-center mb-4">
                        <CarIcon class="h-8 w-8 text-blue-500 mr-3" />
                        <div>
                            <p class="font-medium text-gray-800">
                                {{ `${selectedTask.car.brand.name} ${selectedTask.car.model.name} ${selectedTask.car.type.name}` }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ selectedTask.car.cc }} • {{ selectedTask.car.transmission }} •
                                {{ selectedTask.car.year }}
                                <span class="text-gray-500">({{ selectedTask.car.fuel_type }})</span>
                            </p>
                        </div>
                    </div>

                    <!-- Pesan konfirmasi -->
                    <p class="text-gray-600 mb-6">
                        Anda yakin ingin {{ getButtonLabel(selectedTask?.status).toLowerCase() }}
                        inspeksi ini
                        <span v-if="selectedTask?.category" class="font-semibold text-gray-800">
                            berdasarkan Type Inspek: {{ selectedTask.category.name }}
                        </span>?
                    </p>

                    <!-- Tombol -->
                    <div class="flex justify-end space-x-3">
                        <button
                            @click="closeModal"
                            type="button"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Batal
                        </button>
                        <Link
                            :href="`/inspections/${selectedTask?.id}/start`"
                            method="get"
                            class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700"
                        >
                            {{ getButtonLabel(selectedTask?.status) }}
                        </Link>
                    </div>
                </div>
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