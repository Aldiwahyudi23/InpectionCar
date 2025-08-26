<template>
  <div class="bg-white rounded-xl shadow-md p-6">
    <h2 class="text-xl font-semibold text-gray-800 mb-6">Detail Kendaraan</h2>

    <div v-if="inspection">
      <!-- Input Plat -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Nomor Plat Kendaraan
        </label>
        <input
          v-model="form.plate_number"
          type="text"
          placeholder="Contoh: B 1234 ABC"
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          @input="updateVehicleData"
        >
      </div>

      <!-- Input Nama Mobil -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Nama Mobil
        </label>
        <div class="relative">
          <input
            v-model="carSearchQuery"
            type="text"
            placeholder="Cari atau ketik nama mobil..."
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
            @input="handleCarInput"
            @focus="showSuggestions = true"
            @blur="handleInputBlur"
          >

          <!-- Loader -->
          <div v-if="isSearching" class="absolute right-3 top-3">
            <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>

          <!-- Suggestion Dropdown -->
          <div 
            v-if="showSuggestions && filteredCars.length > 0" 
            class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto"
          >
            <div 
              v-for="car in filteredCars" 
              :key="car.id"
              class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
              @mousedown="selectCar(car)"
            >
              <div class="font-medium text-gray-900">
                {{ formatCarName(car) }}
              </div>
              <div class="text-sm text-gray-500">
                {{ car.year }} • {{ car.cc }}cc • {{ car.transmission }} • {{ car.fuel_type }}
              </div>
            </div>
          </div>
        </div>
        <input type="hidden" v-model="form.car_id">
      </div>

      <!-- Detail Mobil -->
      <div v-if="selectedCar" class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
        <div v-if="selectedCar.description" class="mb-4">
          <h3 class="text-sm font-medium text-gray-700 mb-2">Deskripsi:</h3>
          <p class="text-gray-600 text-sm">{{ selectedCar.description }}</p>
        </div>

        <div v-if="carImages.length > 0">
          <h3 class="text-sm font-medium text-gray-700 mb-3">Gambar Mobil:</h3>
          <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <div 
              v-for="image in carImages" 
              :key="image.id"
              class="relative group"
            >
              <img 
                :src="image.file_path" 
                :alt="image.name || 'Car Image'"
                class="w-full h-24 object-cover rounded-lg border border-gray-200"
              >
              <div 
                v-if="image.note"
                class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center p-2"
              >
                <p class="text-white text-xs text-center">{{ image.note }}</p>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-4 text-gray-500 text-sm">
          Tidak ada gambar tersedia untuk mobil ini
        </div>
      </div>

      <!-- Tombol Update -->
      <button
        type="button"
        @click="updateVehicleDetails"
        class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg hover:bg-indigo-700 transition-colors font-medium"
      >
        Perbarui Detail Kendaraan
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';

const props = defineProps({
  inspection: {
    type: Object,
    default: null
  },
  CarDetail: Array
});

const emit = defineEmits(['update-vehicle']);

// Form
const form = useForm({
  plate_number: props.inspection?.plate_number || '',
  car_id: props.inspection?.car_id || null,
  car_name: props.inspection?.car_name || ''
});

// State
const carSearchQuery = ref(form.car_name || '');
const showSuggestions = ref(false);
const isSearching = ref(false);
const filteredCars = ref([]);
const selectedCar = ref(null);
const carImages = ref([]);

// Initial
onMounted(() => {
  if (props.inspection?.car_id && props.CarDetail?.length > 0) {
    const car = props.CarDetail.find(c => c.id === props.inspection.car_id);
    if (car) {
      selectedCar.value = car;
      carSearchQuery.value = formatCarName(car);
      form.car_id = car.id;
      loadCarImages(car.id);
    }
  }
});

// Format nama mobil
const formatCarName = (car) => {
  if (!car) return '';
  const parts = [];
  if (car.brand?.name) parts.push(car.brand.name);
  if (car.model?.name) parts.push(car.model.name);
  if (car.type?.name) parts.push(car.type.name);
  if (car.cc) parts.push(`${car.cc}cc`);
  if (car.transmission) parts.push(car.transmission);
  if (car.fuel_type) parts.push(car.fuel_type);
  if (car.year) parts.push(car.year.toString());
  if (car.production_period) parts.push(`(${car.production_period})`);
  return parts.join(' ');
};

// Cari mobil
const searchCars = debounce(() => {
  if (!carSearchQuery.value.trim()) {
    filteredCars.value = [];
    showSuggestions.value = false;
    return;
  }
  isSearching.value = true;
  try {
    const query = carSearchQuery.value.toLowerCase().trim();
    filteredCars.value = props.CarDetail.filter(car => {
      const carName = formatCarName(car).toLowerCase();
      return carName.includes(query);
    });
    showSuggestions.value = true;
  } finally {
    isSearching.value = false;
  }
}, 300);

// Input manual / search
const handleCarInput = () => {
  searchCars();
  // Jika diketik manual (belum selectCar), reset car_id tapi simpan car_name
  form.car_id = null;
  form.car_name = carSearchQuery.value.trim();
  selectedCar.value = null;
};

// Blur
const handleInputBlur = () => {
  setTimeout(() => {
    showSuggestions.value = false;
  }, 200);
};

// Pilih mobil dari hasil pencarian
const selectCar = (car) => {
  selectedCar.value = car;
  carSearchQuery.value = formatCarName(car);
  form.car_id = car.id;
  form.car_name = null; // pakai car_id, jadi tidak perlu simpan car_name manual
  showSuggestions.value = false;
  loadCarImages(car.id);
  updateVehicleData();
};

// Load gambar
const loadCarImages = async (carId) => {
  try {
    const response = await fetch(`/api/cars/${carId}/images`);
    carImages.value = response.ok ? await response.json() : [];
  } catch (error) {
    carImages.value = [];
  }
};

// Emit data ke parent
const updateVehicleData = () => {
  const vehicleData = {
    plate_number: form.plate_number,
    car_id: form.car_id,
    car_name: form.car_id ? null : carSearchQuery.value.trim()
  };
  emit('update-vehicle', vehicleData);
};

// Submit update
const updateVehicleDetails = () => {
  if (!props.inspection) return;
  form.post(`/inspections/${props.inspection.id}/vehicle-details`, {
    preserveScroll: true
  });
};

// Watch perubahan inspection
watch(() => props.inspection, (newInspection) => {
  if (newInspection) {
    form.plate_number = newInspection.plate_number || '';
    form.car_id = newInspection.car_id || null;
    form.car_name = newInspection.car_name || '';
    carSearchQuery.value = form.car_id
      ? formatCarName(props.CarDetail.find(c => c.id === form.car_id))
      : form.car_name;
  }
}, { deep: true });
</script>


<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>