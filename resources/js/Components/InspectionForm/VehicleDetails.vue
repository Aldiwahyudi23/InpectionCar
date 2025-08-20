<template>
  <div class="bg-white rounded-xl shadow-md p-6">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Detail Kendaraan</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Nomor Polisi -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Polisi</label>
        <input
          v-model="form.plate_number"
          @input="debounceUpdate"
          type="text"
          class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          placeholder="Contoh: B 1234 ABC"
        >
      </div>

      <!-- Merk -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Merk</label>
        <select
          v-model="form.brand_id"
          @change="onBrandChange"
          class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
          <option value="">Pilih Merk</option>
          <option v-for="brand in brands" :key="brand.id" :value="brand.id">
            {{ brand.name }}
          </option>
        </select>
      </div>

      <!-- Model -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Model</label>
        <select
          v-model="form.car_model_id"
          @change="onModelChange"
          :disabled="!form.brand_id"
          class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
          <option value="">Pilih Model</option>
          <option v-for="model in filteredModels" :key="model.id" :value="model.id">
            {{ model.name }} ({{ model.period }})
          </option>
        </select>
      </div>

      <!-- Tipe -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Tipe</label>
        <select
          v-model="form.car_type_id"
          @change="updateForm"
          :disabled="!form.car_model_id"
          class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
          <option value="">Pilih Tipe</option>
          <option v-for="type in filteredTypes" :key="type.id" :value="type.id">
            {{ type.name }}
          </option>
        </select>
      </div>

      <!-- Tahun -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
        <select
          v-model="form.year"
          @change="updateForm"
          class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
          <option value="">Pilih Tahun</option>
          <option v-for="year in years" :key="year" :value="year">
            {{ year }}
          </option>
        </select>
      </div>

      <!-- CC -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Kapasitas Mesin (CC)</label>
        <input
          v-model="form.cc"
          @input="debounceUpdate"
          type="number"
          class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          placeholder="Contoh: 1500"
        >
      </div>

      <!-- Transmisi -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Transmisi</label>
        <select
          v-model="form.transmission"
          @change="updateForm"
          class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
          <option value="">Pilih Transmisi</option>
          <option value="AT">Automatic (AT)</option>
          <option value="MT">Manual (MT)</option>
          <option value="CVT">CVT</option>
        </select>
      </div>

      <!-- Bahan Bakar -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Bahan Bakar</label>
        <select
          v-model="form.fuel_type"
          @change="updateForm"
          class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
          <option value="">Pilih Bahan Bakar</option>
          <option value="Bensin">Bensin</option>
          <option value="Solar">Solar</option>
          <option value="Hybrid">Hybrid</option>
          <option value="Listrik">Listrik</option>
        </select>
      </div>

      <!-- Periode Produksi -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Periode Produksi</label>
        <input
          v-model="form.production_period"
          @input="debounceUpdate"
          type="text"
          class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          placeholder="Contoh: 2018-2022"
        >
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { debounce } from 'lodash';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  inspection: Object,
  car: Object,
  brands: Array,
  carModels: Array,
  carTypes: Array
});

const emit = defineEmits(['update-vehicle']);

// Generate tahun dari 1990 sampai tahun sekarang
const currentYear = new Date().getFullYear();
const years = Array.from({ length: currentYear - 1990 + 1 }, (_, i) => currentYear - i);

// Form data
const form = ref({
  plate_number: props.inspection.plate_number || '',
  brand_id: props.car?.brand_id || '',
  car_model_id: props.car?.car_model_id || '',
  car_type_id: props.car?.car_type_id || '',
  year: props.car?.year || '',
  cc: props.car?.cc || '',
  transmission: props.car?.transmission || '',
  fuel_type: props.car?.fuel_type || '',
  production_period: props.car?.production_period || ''
});

// Computed properties untuk filter
const filteredModels = computed(() => {
  if (!form.value.brand_id) return [];
  return props.carModels.filter(model => model.brand_id == form.value.brand_id);
});

const filteredTypes = computed(() => {
  if (!form.value.car_model_id) return [];
  return props.carTypes.filter(type => type.car_model_id == form.value.car_model_id);
});

// Debounce untuk update otomatis
const debounceUpdate = debounce(() => {
  updateForm();
}, 500);

// Handler untuk perubahan brand
const onBrandChange = () => {
  // Reset model dan type ketika brand berubah
  form.value.car_model_id = '';
  form.value.car_type_id = '';
  updateForm();
};

// Handler untuk perubahan model
const onModelChange = () => {
  // Reset type ketika model berubah
  form.value.car_type_id = '';
  updateForm();
};

// Update form dan kirim ke server
const updateForm = () => {
  emit('update-vehicle', form.value);
  
  // Auto-save ke server
  router.post(route('inspections.update-vehicle-details'), {
    inspection_id: props.inspection.id,
    ...form.value
  }, {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Data kendaraan berhasil diupdate');
    },
    onError: (errors) => {
      console.error('Error update data kendaraan:', errors);
    }
  });
};

// Simpan perubahan manual
const saveChanges = () => {
  updateForm();
};

// Update form ketika props berubah
watch(() => props.inspection, (newInspection) => {
  form.value.plate_number = newInspection.plate_number || '';
}, { deep: true });

watch(() => props.car, (newCar) => {
  if (newCar) {
    form.value.brand_id = newCar.brand_id || '';
    form.value.car_model_id = newCar.car_model_id || '';
    form.value.car_type_id = newCar.car_type_id || '';
    form.value.year = newCar.year || '';
    form.value.cc = newCar.cc || '';
    form.value.transmission = newCar.transmission || '';
    form.value.fuel_type = newCar.fuel_type || '';
    form.value.production_period = newCar.production_period || '';
  }
}, { deep: true });

// Load data saat component mounted
onMounted(() => {
  console.log('VehicleDetails component mounted');
  console.log('Car data:', props.car);
  console.log('Brands:', props.brands);
  console.log('Models:', props.carModels);
  console.log('Types:', props.carTypes);
});
</script>