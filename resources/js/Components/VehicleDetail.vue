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
        <input v-if="editMode" type="text" v-model="form.license_plate" 
               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        <input v-else type="text" :value="form.license_plate" 
               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-gray-50" readonly>
      </div>

      <!-- Brand -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Merek</label>
        <input v-if="editMode" type="text" v-model="form.brand" 
               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        <input v-else type="text" :value="form.brand" 
               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-gray-50" readonly>
      </div>
      
      <!-- Model -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
        <input v-if="editMode" type="text" v-model="form.model" 
               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        <input v-else type="text" :value="form.model" 
               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-gray-50" readonly>
      </div>
      
      <!-- Tipe -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
        <input v-if="editMode" type="text" v-model="form.type" 
               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        <input v-else type="text" :value="form.type" 
               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-gray-50" readonly>
      </div>
      
      <!-- CC -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas Mesin (CC)</label>
        <input v-if="editMode" type="text" v-model="form.cc" 
               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        <input v-else type="text" :value="form.cc" 
               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-gray-50" readonly>
      </div>
      
      <!-- Transmisi -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Transmisi</label>
        <select v-if="editMode" v-model="form.transmission" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
          <option value="Manual">Manual</option>
          <option value="Automatic">Automatic</option>
        </select>
        <input v-else type="text" :value="form.transmission" 
               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-gray-50" readonly>
      </div>
      
      <!-- Tahun -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
        <input v-if="editMode" type="number" v-model="form.year" 
               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        <input v-else type="text" :value="form.year" 
               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-gray-50" readonly>
      </div>
      
      <!-- Bahan Bakar -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Bahan Bakar</label>
        <select v-if="editMode" v-model="form.fuel_type" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
          <option value="Bensin">Bensin</option>
          <option value="Solar">Solar</option>
          <option value="Listrik">Listrik</option>
        </select>
        <input v-else type="text" :value="form.fuel_type" 
               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-gray-50" readonly>
      </div>
      
      <div v-if="!inspection.id" class="flex space-x-4 mt-6">
        <button @click="startImmediateInspection" 
                class="px-4 py-2 bg-green-600 text-white rounded-lg">
            Mulai Inspeksi Sekarang
        </button>
        <button @click="scheduleInspection" 
                class="px-4 py-2 bg-blue-600 text-white rounded-lg">
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
  }
});

const emit = defineEmits(['update']);

const editMode = ref(false);
const form = ref({...props.vehicleData});

const toggleEdit = () => {
  if (editMode.value) {
    emit('update', form.value);
  }
  editMode.value = !editMode.value;
};
</script>