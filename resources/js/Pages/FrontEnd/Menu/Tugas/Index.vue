<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { CalendarDaysIcon, ArrowRightIcon, PlusIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { CarIcon } from 'lucide-vue-next';
import { ref , computed} from 'vue';

const props = defineProps({
    tasks: Array,
    encryptedIds: Object
});

const showModal = ref(false);
const showCancelModal = ref(false);
const selectedTask = ref(null);
const cancelReason = ref('');

// Form untuk memulai inspeksi, mirip dengan form pembatalan
const startForm = useForm({});

const openModal = (task) => {
    selectedTask.value = task;
    showModal.value = true;
};

const openCancelModal = (task) => {
    selectedTask.value = task;
    showCancelModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    showCancelModal.value = false;
    cancelReason.value = '';
};

const cancelForm = useForm({
    reason: ''
});

const submitCancel = () => {
    if (!selectedTask.value) return;

    cancelForm.reason = cancelReason.value;
    cancelForm.post(route('inspections.cancel', {
        inspection: props.encryptedIds[selectedTask.value.id]
    }), {
        onSuccess: () => {
            closeModal();
            cancelReason.value = '';
        }
    });
};

// Fungsi baru untuk memulai inspeksi dengan status loading
const submitStartInspection = () => {
    if (!selectedTask.value) return;

    startForm.get(route('inspections.start', {
        inspection: props.encryptedIds[selectedTask.value.id]
    }), {
        onFinish: () => {
            // Menutup modal setelah selesai, baik sukses maupun gagal
            closeModal();
        }
    });
};

// Computed property untuk memeriksa jika ada tugas 'in_progress' atau 'revision'
const isAnyTaskActive = computed(() => {
    return props.tasks.some(task => task.status === 'in_progress' || task.status === 'revision');
});

// mapping button text berdasarkan status
const getButtonLabel = (status) => {
    switch (status) {
        case 'draft':
            return 'Mulai Inspeksi';
        case 'in_progress':
            return 'Lanjutkan Inspeksi';
        case 'pending_review':
            return 'Periksa Laporan';
        case 'revision':
            return 'Lanjutkan Revisi';
        case 'pending':
            return 'Ditunda => Lanjutkan Inspeksi';
        default:
            return 'Detail';
    }
};
const getButtonProses = (status) => {
    switch (status) {
        case 'draft':
            return 'Memuat Halaman Inspeksi...';
        case 'in_progress':
            return 'Membuka Halaman Inspeksi...';
        case 'pending_review':
            return 'Memuat Halaman Review...';
        case 'revision':
            return 'Memual Halaman Revisi...';
        case 'pending':
            return 'Lanjutkan Inspeksi...';
        default:
            return 'Detail';
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Tugas" />
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
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

                        <!-- Buttons Container -->
                        <div class="p-4 flex space-x-2">
                            <!-- Tombol Batal -->
                            <button
                                @click="openCancelModal(task)"
                                class="flex-6 px-3 py-2 bg-gray-200 text-gray-700 font-medium rounded-md text-sm hover:bg-gray-300 transition-colors"
                            >
                                Batal
                            </button>


                              <!-- Tombol Mulai/Lanjutkan -->
                            <button
                                @click="openModal(task)"
                                :disabled="isAnyTaskActive && task.status !== 'in_progress' && task.status !== 'revision'"
                                class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white font-medium rounded-md text-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
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
                        class="inline-block px-4 py-2 text-sm font-medium text-indigo-600 border border-indigo-600 rounded-md hover:bg-blue-50"
                    >
                        Lihat Inspeksi Lainnya
                    </Link>
                </div>

            </div>

        <!-- Tombol Mengambang untuk Membuat Inspeksi Baru -->
        <Link
            :href="route('inspections.create.new')"
            class="fixed bottom-24 right-6 z-40 p-4 bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white rounded-full transition-colors duration-200 animate-bounce"
            title="Buat Inspeksi Baru"
        >
            <PlusIcon class="h-6 w-6" />
        </Link>

        <!-- Modal Konfirmasi Mulai Inspeksi -->
        <div
            v-if="showModal"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center p-4 z-50"
        >
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Konfirmasi</h3>

                    <!-- Info Mobil -->
                    <div  class="flex items-start mb-4">
                        <CarIcon class="h-8 w-8 text-blue-500 mr-3 mt-1" />
                        <div>
                            <div v-if="selectedTask?.car">
                                <p class="font-medium text-gray-800">
                                    {{ `${selectedTask.car.brand.name} ${selectedTask.car.model.name} ${selectedTask.car.type.name}` }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ selectedTask.car.cc }} • {{ selectedTask.car.transmission }} •
                                    {{ selectedTask.car.year }}
                                    <span class="text-gray-500">({{ selectedTask.car.fuel_type }})</span>
                                </p>
                            </div>
                            <div v-else class="text-sm font-medium text-gray-800">
                                {{ selectedTask?.car_name }}
                            </div>
                            <!-- Nomor Plat Mobil di Modal -->
                             <div class="flex items-center mt-2">
                                <span class="text-sm font-semibold uppercase tracking-wide text-gray-500 mr-2">no polisi:</span>
                                <span class="text-base font-bold text-gray-900">{{ selectedTask?.plate_number }}</span>
                            </div>
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
                        <!-- Tombol "Mulai Inspeksi" yang diperbarui -->
                        <button
                            @click="submitStartInspection"
                            type="button"
                            :disabled="startForm.processing || isAnyTaskActive && selectedTask.status !== 'in_progress' && selectedTask.status !== 'revision'"
                            class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white border border-transparent rounded-md text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="startForm.processing">{{ getButtonProses(selectedTask?.status) }}</span>
                            <span v-else>{{ getButtonLabel(selectedTask?.status) }}</span>
                            <!-- <ArrowRightIcon v-if="!startForm.processing" class="ml-2 h-4 w-4" /> -->
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Batalkan Inspeksi -->
        <div
            v-if="showCancelModal"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center p-4 z-50"
        >
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Batalkan Inspeksi</h3>
                        <button
                            @click="closeModal"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <XMarkIcon class="h-6 w-6" />
                        </button>
                    </div>

                    <!-- Info Mobil -->
                    <div class="flex items-start mb-4">
                        <CarIcon class="h-8 w-8 text-red-500 mr-3 mt-1" />
                        <div>
                            <div v-if="selectedTask?.car">
                                <p class="font-medium text-gray-800">
                                    {{ `${selectedTask.car.brand.name} ${selectedTask.car.model.name} ${selectedTask.car.type.name}` }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ selectedTask.car.cc }} • {{ selectedTask.car.transmission }} •
                                    {{ selectedTask.car.year }}
                                    <span class="text-gray-500">({{ selectedTask.car.fuel_type }})</span>
                                </p>
                            </div>
                            <div v-else class="text-sm font-medium text-gray-800">
                                {{ selectedTask?.car_name }}
                            </div>
                            <!-- Nomor Plat Mobil -->
                            <div class="flex items-center mt-2">
                                <span class="text-sm font-semibold uppercase tracking-wide text-gray-500 mr-2">no polisi:</span>
                                <span class="text-base font-bold text-gray-900">{{ selectedTask?.plate_number }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Form Alasan -->
                    <div class="mb-6">
                        <label for="cancelReason" class="block text-sm font-medium text-gray-700 mb-2">
                            Alasan Pembatalan *
                        </label>
                        <textarea
                            id="cancelReason"
                            v-model="cancelReason"
                            rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Masukkan alasan pembatalan inspeksi..."
                            required
                        ></textarea>
                        <p v-if="cancelForm.errors.reason" class="text-red-500 text-sm mt-1">
                            {{ cancelForm.errors.reason }}
                        </p>
                    </div>

                    <!-- Tombol -->
                    <div class="flex justify-end space-x-3">
                        <button
                            @click="closeModal"
                            type="button"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Tutup
                        </button>
                        <button
                            @click="submitCancel"
                            :disabled="!cancelReason.trim() || cancelForm.processing"
                            class="px-4 py-2 bg-red-600 text-white border border-transparent rounded-md text-sm font-medium hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="cancelForm.processing">Memproses...</span>
                            <span v-else>Batalkan Inspeksi</span>
                        </button>
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
