<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { CalendarDaysIcon, ArrowRightIcon, TruckIcon } from '@heroicons/vue/24/outline';
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
</script>

<template>
    <AppLayout>
        <Head title="Tugas" />

        <div class="py-8 md:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6 text-center">Inspeksi yang Harus Dilakukan</h1>

                <div v-if="tasks.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-5">
                    <div v-for="task in tasks" :key="task.id" class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="p-4 pb-2">
                            <div class="flex items-center mb-1">
                                <CalendarDaysIcon class="h-5 w-5 text-blue-500 mr-2" />
                                <span class="text-sm font-medium text-gray-600">Jadwal Inspeksi</span>
                            </div>
                            <p class="text-sm font-medium text-blue-700 ml-7 -mt-1">
                                {{ new Date(task.inspection_date).toLocaleDateString('id-ID', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' }) }}
                            </p>
                        </div>

                        <div v-if="task.car" class="px-4 py-2 bg-gray-50 border-t border-gray-100">
                            <div class="flex items-center">
                                <TruckIcon class="h-5 w-5 text-gray-500 mr-2" />
                                <span class="text-sm font-medium text-gray-800">
                                    {{ 
                                        `${task.car.brand.name} ${task.car.model.name} ${task.car.type.name} ${task.car.cc} ${task.car.transmission} ${task.car.year}` 
                                    }}
                                    <span class="text-gray-600">({{ task.car.fuel_type }})</span>
                                </span>
                            </div>
                        </div>

                        <div class="p-4 pt-2">
                            <button 
                                @click="openModal(task)"
                                class="inline-flex items-center justify-center w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md text-sm transition-colors"
                            >
                                Mulai Inspeksi
                                <ArrowRightIcon class="ml-2 h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>
                
                <div v-else class="text-center py-8">
                    <p class="text-gray-500">Tidak ada tugas inspeksi yang tersedia.</p>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Konfirmasi Inspeksi</h3>
                    
                    <div v-if="selectedTask?.car" class="flex items-center mb-4">
                        <TruckIcon class="h-8 w-8 text-blue-500 mr-3" />
                        <div>
                            <p class="font-medium text-gray-800">
                                {{ 
                                    `${selectedTask.car.brand.name} ${selectedTask.car.model.name} ${selectedTask.car.type.name}`
                                }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ selectedTask.car.cc }} • {{ selectedTask.car.transmission }} • {{ selectedTask.car.year }}
                                <span class="text-gray-500">({{ selectedTask.car.fuel_type }})</span>
                            </p>
                        </div>
                    </div>

                    <p class="text-gray-600 mb-6">Anda yakin ingin memulai inspeksi kendaraan ini?</p>

                    <div class="flex justify-end space-x-3">
                        <button
                            @click="closeModal"
                            type="button"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Batal
                        </button>
                        <!-- /inspections/{inspection}/create -->
                        <Link
                            :href="`/inspections/${selectedTask?.id}/create`"
                            method="get"
                            class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700"
                        >
                            Mulai Inspeksi
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>