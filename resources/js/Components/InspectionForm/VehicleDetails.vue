<template>
  <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
    <div class="flex items-center mb-4">
      <TruckIcon class="h-6 w-6 text-blue-500 mr-2" />
      <h2 class="text-lg font-semibold text-gray-800">Detail Kendaraan</h2>
      <button v-if="editable" @click="toggleEdit" class="ml-auto text-sm text-blue-600">
        {{ editMode ? 'Simpan' : 'Edit' }}
      </button>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Nomor Polisi -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Polisi</label>
        <div class="mt-1">
          <input
            v-if="editMode"
            type="text"
            v-model="form.license_plate"
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150"
          >
          <input
            v-else
            type="text"
            :value="form.license_plate"
            readonly
            class="block w-full rounded-lg border-gray-300 shadow-sm bg-gray-50 sm:text-sm"
          >
        </div>
      </div>

      <!-- Brand -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Merek</label>
        <div class="mt-1">
          <input
            v-if="editMode"
            type="text"
            v-model="form.brand"
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150"
          >
          <input
            v-else
            type="text"
            :value="form.brand"
            readonly
            class="block w-full rounded-lg border-gray-300 shadow-sm bg-gray-50 sm:text-sm"
          >
        </div>
      </div>
      
      <!-- Model -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
        <div class="mt-1">
          <input
            v-if="editMode"
            type="text"
            v-model="form.model"
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150"
          >
          <input
            v-else
            type="text"
            :value="form.model"
            readonly
            class="block w-full rounded-lg border-gray-300 shadow-sm bg-gray-50 sm:text-sm"
          >
        </div>
      </div>
      
      <!-- Tipe -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
        <div class="mt-1">
          <input
            v-if="editMode"
            type="text"
            v-model="form.type"
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150"
          >
          <input
            v-else
            type="text"
            :value="form.type"
            readonly
            class="block w-full rounded-lg border-gray-300 shadow-sm bg-gray-50 sm:text-sm"
          >
        </div>
      </div>
      
      <!-- CC -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas Mesin (CC)</label>
        <div class="mt-1">
          <input
            v-if="editMode"
            type="text"
            v-model="form.cc"
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150"
          >
          <input
            v-else
            type="text"
            :value="form.cc"
            readonly
            class="block w-full rounded-lg border-gray-300 shadow-sm bg-gray-50 sm:text-sm"
          >
        </div>
      </div>
      
      <!-- Transmisi -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Transmisi</label>
        <div class="mt-1">
          <select
            v-if="editMode"
            v-model="form.transmission"
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150"
          >
            <option value="Manual">Manual</option>
            <option value="Automatic">Automatic</option>
          </select>
          <input
            v-else
            type="text"
            :value="form.transmission"
            readonly
            class="block w-full rounded-lg border-gray-300 shadow-sm bg-gray-50 sm:text-sm"
          >
        </div>
      </div>
      
      <!-- Tahun -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
        <div class="mt-1">
          <input
            v-if="editMode"
            type="number"
            v-model="form.year"
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150"
          >
          <input
            v-else
            type="text"
            :value="form.year"
            readonly
            class="block w-full rounded-lg border-gray-300 shadow-sm bg-gray-50 sm:text-sm"
          >
        </div>
      </div>
      
      <!-- Bahan Bakar -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Bahan Bakar</label>
        <div class="mt-1">
          <select
            v-if="editMode"
            v-model="form.fuel_type"
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150"
          >
            <option value="Bensin">Bensin</option>
            <option value="Solar">Solar</option>
            <option value="Listrik">Listrik</option>
          </select>
          <input
            v-else
            type="text"
            :value="form.fuel_type"
            readonly
            class="block w-full rounded-lg border-gray-300 shadow-sm bg-gray-50 sm:text-sm"
          >
        </div>
      </div>
      
      <div v-if="!inspection.id" class="flex space-x-4 mt-6 md:col-span-2">
        <button @click="startImmediateInspection" 
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
            Mulai Inspeksi Sekarang
        </button>
        <button @click="scheduleInspection" 
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            Buat Jadwal Inspeksi
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { TruckIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  vehicleData: {
    type: Object,
    required: true
  },
  editable: {
    type: Boolean,
    default: false
  },
  inspection: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['update', 'startImmediateInspection', 'scheduleInspection']);

const editMode = ref(false);
const form = ref({...props.vehicleData});

const toggleEdit = () => {
  if (editMode.value) {
    emit('update', form.value);
  }
  editMode.value = !editMode.value;
};

const startImmediateInspection = () => {
  emit('startImmediateInspection');
};

const scheduleInspection = () => {
  emit('scheduleInspection');
};
</script>