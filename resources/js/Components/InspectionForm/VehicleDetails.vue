<template>
  <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
    <div class="flex items-center mb-4">
      <TruckIcon class="h-6 w-6 text-blue-500 mr-2" />
      <h2 class="text-lg font-semibold text-gray-800">Detail Kendaraan</h2>
      <button v-if="editable" @click="toggleEdit" class="ml-auto text-sm text-blue-600">
        {{ editMode ? 'Simpan' : 'Edit' }}
      </button>
    </div>
    
    <div v-if="!editMode && inspection.car_id" class="mb-4">
      <p class="text-sm text-gray-600">Menggunakan data kendaraan yang sudah tersimpan</p>
    </div>

    <div v-if="editMode && !inspection.car_id" class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Kendaraan Existing</label>
      <select v-model="selectedCarDetail" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        <option value="">-- Pilih Kendaraan --</option>
        <option v-for="car in carDetails" :key="car.id" :value="car">
          {{ car.brand }} {{ car.model }} {{ car.type }} ({{ car.year }})
        </option>
      </select>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Brand -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Merek</label>
        <div class="mt-1">
          <select
            v-if="editMode"
            v-model="form.brand_id"
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :disabled="!!selectedCarDetail"
          >
            <option value="">Pilih Merek</option>
            <option v-for="brand in uniqueBrands" :key="brand.id" :value="brand.id">
              {{ brand.name }}
            </option>
          </select>
          <input
            v-else
            type="text"
            :value="currentCarDetail.brand || 'Tidak ada data'"
            readonly
            class="block w-full rounded-lg border-gray-300 shadow-sm bg-gray-50 sm:text-sm"
          >
        </div>
      </div>

      <!-- Model -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
        <div class="mt-1">
          <select
            v-if="editMode"
            v-model="form.car_model_id"
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :disabled="!!selectedCarDetail"
          >
            <option value="">Pilih Model</option>
            <option v-for="model in filteredModels" :key="model.id" :value="model.id">
              {{ model.name }}
            </option>
          </select>
          <input
            v-else
            type="text"
            :value="currentCarDetail.model || 'Tidak ada data'"
            readonly
            class="block w-full rounded-lg border-gray-300 shadow-sm bg-gray-50 sm:text-sm"
          >
        </div>
      </div>

      <!-- Tipe -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
        <div class="mt-1">
          <select
            v-if="editMode"
            v-model="form.car_type_id"
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :disabled="!!selectedCarDetail"
          >
            <option value="">Pilih Tipe</option>
            <option v-for="type in filteredTypes" :key="type.id" :value="type.id">
              {{ type.name }}
            </option>
          </select>
          <input
            v-else
            type="text"
            :value="currentCarDetail.type || 'Tidak ada data'"
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
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :disabled="!!selectedCarDetail"
          >
          <input
            v-else
            type="text"
            :value="currentCarDetail.year || 'Tidak ada data'"
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
            type="number"
            v-model="form.cc"
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :disabled="!!selectedCarDetail"
          >
          <input
            v-else
            type="text"
            :value="currentCarDetail.cc || 'Tidak ada data'"
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
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :disabled="!!selectedCarDetail"
          >
            <option value="AT">Automatic (AT)</option>
            <option value="MT">Manual (MT)</option>
            <option value="CVT">CVT</option>
          </select>
          <input
            v-else
            type="text"
            :value="transmissionLabel"
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
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :disabled="!!selectedCarDetail"
          >
            <option value="Bensin">Bensin</option>
            <option value="Solar">Solar</option>
            <option value="Listrik">Listrik</option>
            <option value="Hybrid">Hybrid</option>
          </select>
          <input
            v-else
            type="text"
            :value="currentCarDetail.fuel_type || 'Tidak ada data'"
            readonly
            class="block w-full rounded-lg border-gray-300 shadow-sm bg-gray-50 sm:text-sm"
          >
        </div>
      </div>

      <!-- Production Period -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Periode Produksi</label>
        <div class="mt-1">
          <input
            v-if="editMode"
            type="text"
            v-model="form.production_period"
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :disabled="!!selectedCarDetail"
          >
          <input
            v-else
            type="text"
            :value="currentCarDetail.production_period || 'Tidak ada data'"
            readonly
            class="block w-full rounded-lg border-gray-300 shadow-sm bg-gray-50 sm:text-sm"
          >
        </div>
      </div>
    </div>

    <div v-if="editMode" class="flex space-x-4 mt-6">
      <button 
        @click="saveCarDetails" 
        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
        :disabled="!isFormValid"
      >
        Simpan Detail Kendaraan
      </button>
      <button 
        @click="cancelEdit" 
        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
      >
        Batal
      </button>
    </div>

    <div v-if="!inspection.car_id && !editMode" class="flex space-x-4 mt-6">
      <button @click="startEdit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        Tambah Detail Kendaraan
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { TruckIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  inspection: {
    type: Object,
    required: true
  },
  carDetails: {
    type: Array,
    default: () => []
  },
  editable: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['update-vehicle', 'save-car-details']);

const editMode = ref(false);
const selectedCarDetail = ref(null);
const form = ref({
  brand_id: '',
  car_model_id: '',
  car_type_id: '',
  year: '',
  cc: '',
  transmission: 'AT',
  fuel_type: 'Bensin',
  production_period: ''
});

// Computed properties
const currentCarDetail = computed(() => {
  if (props.inspection.car_id) {
    const car = props.carDetails.find(c => c.id === props.inspection.car_id);
    return car || {};
  }
  return {};
});

const transmissionLabel = computed(() => {
  const labels = {
    'AT': 'Automatic (AT)',
    'MT': 'Manual (MT)', 
    'CVT': 'CVT'
  };
  return labels[currentCarDetail.value.transmission] || 'Tidak ada data';
});

const uniqueBrands = computed(() => {
  const brands = props.carDetails.map(car => ({
    id: car.brand_id,
    name: car.brand
  }));
  return [...new Map(brands.map(item => [item.id, item])).values()];
});

const filteredModels = computed(() => {
  if (!form.value.brand_id) return [];
  return props.carDetails
    .filter(car => car.brand_id == form.value.brand_id)
    .map(car => ({
      id: car.car_model_id,
      name: car.model
    }));
});

const filteredTypes = computed(() => {
  if (!form.value.car_model_id) return [];
  return props.carDetails
    .filter(car => car.car_model_id == form.value.car_model_id)
    .map(car => ({
      id: car.car_type_id,
      name: car.type
    }));
});

const isFormValid = computed(() => {
  return form.value.brand_id && form.value.car_model_id && form.value.car_type_id && form.value.year;
});

// Watchers
watch(selectedCarDetail, (newCar) => {
  if (newCar) {
    form.value = {
      brand_id: newCar.brand_id,
      car_model_id: newCar.car_model_id,
      car_type_id: newCar.car_type_id,
      year: newCar.year,
      cc: newCar.cc,
      transmission: newCar.transmission,
      fuel_type: newCar.fuel_type,
      production_period: newCar.production_period
    };
  }
});

// Methods
const startEdit = () => {
  editMode.value = true;
  if (props.inspection.car_id) {
    const car = props.carDetails.find(c => c.id === props.inspection.car_id);
    if (car) {
      form.value = {
        brand_id: car.brand_id,
        car_model_id: car.car_model_id,
        car_type_id: car.car_type_id,
        year: car.year,
        cc: car.cc,
        transmission: car.transmission,
        fuel_type: car.fuel_type,
        production_period: car.production_period
      };
    }
  }
};

const cancelEdit = () => {
  editMode.value = false;
  selectedCarDetail.value = null;
  form.value = {
    brand_id: '',
    car_model_id: '',
    car_type_id: '',
    year: '',
    cc: '',
    transmission: 'AT',
    fuel_type: 'Bensin',
    production_period: ''
  };
};

const saveCarDetails = () => {
  if (selectedCarDetail.value) {
    // Gunakan car detail yang dipilih
    emit('update-vehicle', {
      car_id: selectedCarDetail.value.id,
      ...form.value
    });
  } else {
    // Buat car detail baru
    emit('save-car-details', form.value);
  }
  editMode.value = false;
};

// Initialize form jika sudah ada car_id
onMounted(() => {
  if (props.inspection.car_id) {
    const car = props.carDetails.find(c => c.id === props.inspection.car_id);
    if (car) {
      form.value = {
        brand_id: car.brand_id,
        car_model_id: car.car_model_id,
        car_type_id: car.car_type_id,
        year: car.year,
        cc: car.cc,
        transmission: car.transmission,
        fuel_type: car.fuel_type,
        production_period: car.production_period
      };
    }
  }
});
</script>