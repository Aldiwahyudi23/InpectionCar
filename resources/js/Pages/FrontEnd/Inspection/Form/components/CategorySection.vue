<template>
  <div v-if="props.head === 'vertical'"
    class="px-4 sm:px-6 py-2 border-b flex items-center justify-between bg-indigo-200"
    :class=" 'fixed top-0 left-0 right-0 z-20'"
  >
    <h4 class="text-base font-semibold text-indigo-700 break-words flex-1">
      {{ category.name }}
    </h4>

    <button
      v-if="hasHiddenPoints"
      @click="toggleGlobalHidden"
      class="text-sm text-indigo-700 hover:underline focus:outline-none whitespace-nowrap ml-2"
    >
      {{ showGlobalHidden ? 'Sembunyikan Semua' : 'Tampilkan Point Lain' }}
    </button>
  </div>
  
  <div class="bg-gray-50 shadow-lg rounded-xl overflow-hidden border border-gray-100 will-change-transform"
    :class="props.head === 'vertical' ? 'pt-6' : ''"
  >

    <button
      v-if="!isHeaderVisible && hasHiddenPoints"
      @click="toggleGlobalHidden"
      class="fixed top-3 right-2 z-50 bg-indigo-600 text-white p-2 rounded-full shadow-lg touch-manipulation"
    >
      <svg v-if="showGlobalHidden" xmlns="http://www.w3.org/2000/svg"
        class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
      </svg>

      <svg v-else xmlns="http://www.w3.org/2000/svg"
        class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.006-3.362M9.88 9.88a3 3 0 104.24 4.24M6.1 6.1l11.8 11.8" />
      </svg>
    </button>


    <div v-if="props.head === 'horizontal'"
      ref="categoryHeader"
      class="bg-indigo-200 px-4 sm:px-6 py-2 border-b flex items-center justify-between">
      <h4 class="text-base font-semibold text-indigo-700 break-words flex-1">
        {{ category.name }}
      </h4>

      <button
        v-if="hasHiddenPoints"
        @click="toggleGlobalHidden"
        class="text-sm text-indigo-700 hover:underline focus:outline-none whitespace-nowrap ml-2"
      >
        {{ showGlobalHidden ? 'Sembunyikan' : 'Tampilkan Point Lain' }}
      </button>
    </div>
    
    <div class="p-3 sm:p-4 space-y-3 sm:space-y-4"> 
      <div v-for="(item, index) in renderedItems" 
        :key="getItemKey(item, index)" 
        class="point-item space-y-2 transform-gpu"
        :class="item.is_link ? 'pb-0' : 'pb-2 border-b border-gray-100'"
      >
        
        <div v-if="item.is_link" 
          class="flex justify-end py-1">
          <button
            @click="revealHiddenPoints(item.hiddenIds)"
            class="text-sm font-medium text-indigo-600 hover:text-indigo-800 hover:underline focus:outline-none touch-manipulation"
          >
            Tampilkan {{ item.count }} Point Tersembunyi
          </button>
        </div>

        <div v-else class="space-y-2">
          <!-- Header dengan compatibility badge -->
          <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
              <label class="block text-sm font-medium text-gray-700 break-words">
                {{ item.inspection_point?.name }}
                
                <span v-if="item.settings?.is_required" class="text-red-500">*</span>
                <span v-if="!item.is_default && !hasPointData(item.inspection_point?.id)"
                  class="italic text-xs text-gray-400 ml-2"
                >
                  (opsional)
                </span>
              </label>
              
              <!-- Compatibility Info -->
              <div v-if="showCompatibilityInfo(item)" class="mt-1">
                <div v-if="hasCompatibilitySettings(item)"
                  class="inline-flex flex-wrap items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 gap-1">
                      <div v-if="item.settings?.transmission">
                        {{ getTransmissionLabels(item.settings.transmission).join(', ') }}
                      </div>
                      <div v-if="item.settings?.fuel_type">
                      {{ item.settings.fuel_type }}
                      </div>
                      <div v-if="item.settings?.rear_door">
                        Pintu Belakang
                      </div>
                      <div v-if="item.settings?.pick_up">
                        Tipe Pick Up
                      </div>
                      <div v-if="item.settings?.box">
                        Memiliki Box
                      </div>
                </div>
              </div>
            </div>
            
            <span 
              v-if="isPointComplete(item)"
              class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 flex-shrink-0 ml-2"
            >
              ✓
            </span>
          </div>
          
          <!-- Komponen Input dengan optimasi -->
          <input-text
            v-if="item.input_type === 'text' && isFullyCompatible(item)"
            v-model="form.results[item.inspection_point?.id].note"
            :required="item.settings?.is_required"
            :min-length="item.settings?.min_length"
            :max-length="item.settings?.max_length"
            :allowSpace="item.settings?.allow_space"
            :textTransform="item.settings?.text_transform"
            :placeholder="item.settings?.placeholder || 'Masukan text'"
            :error="form.errors[`results.${item.inspection_point?.id}.note`]"
            @update:modelValue="handleUpdateResult(item.inspection_point?.id, $event, 'note')"
            @save="handleSaveResult(item.inspection_point?.id)"
          />
          
          <input-number
            v-if="item.input_type === 'number' && isFullyCompatible(item)"
            v-model="form.results[item.inspection_point?.id].note"
            :required="item.settings?.is_required"
            :min="item.settings?.min"
            :max="item.settings?.max"
            :step="item.settings?.step || 1"
            :placeholder="item.settings?.placeholder || 'Masukan number'"
            :error="form.errors[`results.${item.inspection_point?.id}.note`]"
            @update:modelValue="handleUpdateResult(item.inspection_point?.id, $event, 'note')"
            @save="handleSaveResult(item.inspection_point?.id)"
          />

          <input-account
            v-if="item.input_type === 'account' && isFullyCompatible(item)"
            v-model="form.results[item.inspection_point?.id].note"
            :required="item.settings?.is_required"
            :placeholder="item.settings?.placeholder || 'Masukkan nilai'"
            :error="form.errors[`results.${item.inspection_point?.id}.note`]"
            :point-id="item.inspection_point?.id"
            :settings="item.settings"
            @update:modelValue="handleUpdateResult(item.inspection_point?.id, $event, 'note')"
            @save="handleSaveResult(item.inspection_point?.id)"
          />
          
          <input-date
            v-if="item.input_type === 'date' && isFullyCompatible(item)"
            v-model="form.results[item.inspection_point?.id].note"
            :required="item.settings?.is_required"
            :min-date="item.settings?.min_date"
            :max-date="item.settings?.max_date"
            :error="form.errors[`results.${item.inspection_point?.id}.note`]"
            @update:modelValue="handleUpdateResult(item.inspection_point?.id, $event, 'note')"
            @save="handleSaveResult(item.inspection_point?.id)"
          />
          
          <input-textarea
            v-if="item.input_type === 'textarea' && isFullyCompatible(item)"
            v-model="form.results[item.inspection_point?.id].note"
            :required="item.settings?.is_required"
            :min-length="item.settings?.min_length"
            :max-length="item.settings?.max_length"
            :placeholder="item.settings?.placeholder || 'Masukkan teks di sini'"
            :settings="item.settings"
            :error="form.errors[`results.${item.inspection_point?.id}.note`]"
            @update:modelValue="handleUpdateResult(item.inspection_point?.id, $event, 'note')"
            @save="handleSaveResult(item.inspection_point?.id)"
          />

          <input-radio
            v-if="item.input_type === 'radio' && isFullyCompatible(item)"
            v-model="form.results[item.inspection_point?.id].status"
            :notes="form.results[item.inspection_point?.id].note"
            :images="form.images[item.inspection_point?.id]"
            :required="item.settings?.is_required"
            :point-id="item.inspection_point?.id"
            :point="item.inspection_point"
            :inspection-id="inspectionId" 
            :settings="item.settings"
            :point-name="item.inspection_point?.name"
            :subtitle="item.inspection_point?.description"
            :selected-point="item.inspection_point ?? null"
            :options="item.settings?.radios || defaultRadioOptions"
            :error="form.errors[`results.${item.inspection_point?.id}.status`]"
            @update:modelValue="handleUpdateResult(item.inspection_point?.id, $event, 'status')"
            @update:notes="val => handleUpdateField(item.inspection_point?.id, 'note', val)"
            @update:images="val => handleUpdateImages(item.inspection_point?.id, val)"
            @save="handleSaveResult(item.inspection_point?.id)"
            @hapus="handleHapusPoint(item.inspection_point?.id)"
          />

          <InputImageToRadio
            v-if="item.input_type === 'imageTOradio' && isFullyCompatible(item)"
            v-model="form.results[item.inspection_point?.id].status"
            :notes="form.results[item.inspection_point?.id].note"
            :images="form.images[item.inspection_point?.id]"
            :required="item.settings?.is_required"
            :point-id="item.inspection_point?.id"
            :inspection-id="inspectionId" 
            :settings="item.settings"
            :point-name="item.inspection_point?.name"
            :subtitle="item.inspection_point?.description"
            :point="item"
            :selected-point="item.inspection_point ?? null"
            :options="item.settings?.radios || defaultRadioOptions"
            :error="form.errors[`results.${item.inspection_point?.id}.status`]"
            @update:modelValue="handleUpdateResult(item.inspection_point?.id, $event,'status')"
            @update:notes="val => handleUpdateField(item.inspection_point?.id, 'note', val)"
            @update:images="val => handleUpdateImages(item.inspection_point?.id, val)"
            @save="handleSaveResult(item.inspection_point?.id)"
            @hapus="handleHapusPoint(item.inspection_point?.id)"
          />
          
          <input-image
            v-if="item.input_type === 'image' && isFullyCompatible(item)"
            v-model="form.images[item.inspection_point?.id]"
            :error="form.errors[`images.${item.inspection_point?.id}`]"
            :inspection-id="inspectionId"
            :point-id="item.inspection_point?.id"
            :point="item.inspection_point"
            :point-name="item.inspection_point?.name"
            :settings="item.settings"
            @update:notes="val => handleUpdateField(item.inspection_point?.id, 'note', val)"
            @update:status="val => handleUpdateField(item.inspection_point?.id, 'status', val)"
          />

          <input-select
            v-if="item.input_type === 'select' && isFullyCompatible(item)"
            v-model="form.results[item.inspection_point?.id].status"
            :required="item.settings?.is_required"
            :error="form.errors[`results.${item.inspection_point?.id}.status`]"
            @update:modelValue="handleUpdateResult(item.inspection_point?.id, $event, 'status')"
            @save="handleSaveResult(item.inspection_point?.id)"
          />

          <!-- Not Compatible Message -->
          <div v-if="!isFullyCompatible(item) && hasCompatibilitySettings(item)" 
               class="p-3 bg-gray-100 rounded text-sm text-gray-600">
            <p class="font-medium">Tidak tersedia untuk kendaraan ini</p>
            <p class="text-xs mt-1">Point ini hanya tersedia untuk:</p>
            <ul class="text-xs mt-1 list-disc list-inside">
              <li v-if="item.settings?.transmission">
                Transmisi: {{ getTransmissionLabels(item.settings.transmission).join(', ') }}
              </li>
              <li v-if="item.settings?.fuel_type">
                Bahan bakar: {{ item.settings.fuel_type }}
              </li>
              <li v-if="item.settings?.rear_door">Kendaraan dengan pintu belakang</li>
              <li v-if="item.settings?.pick_up">Tipe pick up</li>
              <li v-if="item.settings?.box">Kendaraan dengan box</li>
            </ul>
          </div>
        </div>
      </div>

      <div v-if="renderedItems.filter(i => !i.is_link).length === 0" class="text-center py-6 sm:py-8 text-gray-500">
        <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p class="mt-2 text-sm">Tidak ada data untuk ditampilkan</p>
        <p v-if="category.isDamageMenu" class="text-xs text-gray-400 mt-1">
          Tambahkan data melalui tombol "+" di pojok kanan bawah
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, computed, ref, watch, inject, provide } from 'vue';
import { usePage } from '@inertiajs/vue3';

// Import components
import InputText from './InputText.vue';
import InputNumber from './InputNumber.vue';
import InputDate from './InputDate.vue';
import InputAccount from './InputAccount.vue';
import InputTextarea from './InputTextarea.vue';
import InputSelect from './InputSelect.vue';
import InputRadio from './InputRadio.vue';
import InputImage from './InputImage.vue';
import InputImageToRadio from './InputImageToRadio.vue';

const props = defineProps({
  category: Object,
  form: Object,
  head: Object,
  inspectionId: String,
  selectedPoint: Object,
  car: Object,
});

const emit = defineEmits(['updateResult', 'removeImage', 'hapusPoint']);

// Inject dari parent untuk local storage features
const vehicleFeatures = inject('vehicleFeatures', ref(null));

// Setelah data di-load dari localStorage
vehicleFeatures.value = JSON.parse(localStorage.getItem('vehicle_features') || '{}');

provide('vehicleFeatures', vehicleFeatures);

// State untuk tampilan
const showGlobalHidden = ref(false); 
const manuallyShownPoints = ref([]); 
const isHeaderVisible = ref(true);
const categoryHeader = ref(null);

// Debounce untuk mengurangi frekuensi update
let updateTimeout = null;
let saveTimeout = null;

const page = usePage();

// Transmission mapping untuk label
const transmissionLabels = {
  'MT': 'Manual Transmission (MT)',
  'AT': 'Automatic Transmission (AT)', 
  'CVT': 'Continuously Variable Transmission (CVT)',
  'e-CVT': 'Electronic CVT (e-CVT)',
  'DCT': 'Dual Clutch Transmission (DCT)',
  'AMT': 'Automated Manual Transmission (AMT)',
  'IVT': 'Intelligent Variable Transmission (IVT)',
  'SSG': 'Seamless Shift Gearbox (SSG)',
  'AGS': 'Auto Gear Shift (AGS)',
  'DHT': 'Dedicated Hybrid Transmission (DHT)'
};

// Setup intersection observer untuk header dengan optimasi
onMounted(() => {
  const observer = new IntersectionObserver(([entry]) => {
    isHeaderVisible.value = entry.isIntersecting;
  }, { 
    threshold: 0.1,
    rootMargin: '50px 0px 50px 0px'
  });

  if (categoryHeader.value) {
    observer.observe(categoryHeader.value);
  }

  onUnmounted(() => {
    if (categoryHeader.value) observer.unobserve(categoryHeader.value);
    clearTimeouts();
  });
});

// Optimasi key untuk v-for
const getItemKey = (item, index) => {
  if (item.is_link) {
    return `link-${item.hiddenIds.join('-')}-${index}`;
  }
  return item.inspection_point?.id || `item-${index}`;
};

// Toggle untuk menampilkan/sembunyikan semua point tersembunyi
const toggleGlobalHidden = () => {
  showGlobalHidden.value = !showGlobalHidden.value;
  
  if (!showGlobalHidden.value) {
    manuallyShownPoints.value = [];
  }
};

// Computed properties dengan optimasi
const hasHiddenPoints = computed(() => {
  return (props.category.points || []).some(p => p.is_default === false);
});

// Fungsi untuk mendapatkan transmission labels
const getTransmissionLabels = (transmissionCodes) => {
  if (!transmissionCodes || !Array.isArray(transmissionCodes)) return [];
  return transmissionCodes.map(code => transmissionLabels[code] || code);
};

// Fungsi utama untuk kompatibilitas kendaraan
const isFullyCompatible = (point) => {
  const vehicleConfig = point.settings;
  
  if (!hasCompatibilitySettings(point)) {
    return true;
  }

  const carTransmission = props.car?.transmission;
  const carFuelType = props.car?.fuel_type;
  
  if (vehicleConfig.transmission && Array.isArray(vehicleConfig.transmission) && vehicleConfig.transmission.length > 0) {
    if (carTransmission && !vehicleConfig.transmission.includes(carTransmission)) {
      return false;
    }
  }

  if (vehicleConfig.fuel_type && vehicleConfig.fuel_type.trim() !== '') {
    if (carFuelType && vehicleConfig.fuel_type !== carFuelType) {
      return false;
    }
  }

  if (vehicleConfig.rear_door) {
    if (!vehicleFeatures.value || !vehicleFeatures.value.rear_door) {
      return false;
    }
  }

  if (vehicleConfig.pick_up) {
    if (!vehicleFeatures.value || !vehicleFeatures.value.pick_up) {
      return false;
    }
  }

  if (vehicleConfig.box) {
    if (!vehicleFeatures.value || !vehicleFeatures.value.box) {
      return false;
    }
  }

  return true;
};

// Cek apakah point memiliki pengaturan kompatibilitas
const hasCompatibilitySettings = (point) => {
  const settings = point.settings;
  return !!(
    (settings?.transmission && Array.isArray(settings.transmission) && settings.transmission.length > 0) ||
    (settings?.fuel_type && settings.fuel_type.trim() !== '') ||
    settings?.rear_door ||
    settings?.pick_up ||
    settings?.box
  );
};

// Tampilkan info kompatibilitas
const showCompatibilityInfo = (point) => {
  return hasCompatibilitySettings(point) && (showGlobalHidden.value || isFullyCompatible(point));
};

/**
 * FUNGSI UTAMA: Gabungkan logika lama dengan filter kompatibilitas
 */
const renderedItems = computed(() => {
  const points = props.category.points || [];
  const itemsToRender = [];
  let hiddenGroup = []; 

  if (showGlobalHidden.value) {
    if (props.category.isDamageMenu) {
      return points.filter(p => p.is_default || hasPointData(p.inspection_point?.id) || showGlobalHidden.value);
    }
    return points; 
  }
  
  if (props.category.isDamageMenu) {
    return points.filter(point => {
      const pointId = point.inspection_point?.id;
      return hasPointData(pointId) || point.is_default || manuallyShownPoints.value.includes(pointId);
    });
  }

  points.forEach((point) => {
    const pointId = point.inspection_point?.id;
    const hasData = hasPointData(pointId);
    const isShownManually = manuallyShownPoints.value.includes(pointId);
    
    const shouldCheckCompatibility = hasCompatibilitySettings(point);
    const isCompatible = !shouldCheckCompatibility || isFullyCompatible(point);

    const isVisible = (point.is_default || hasData || isShownManually) && isCompatible;

    if (isVisible) {
      if (hiddenGroup.length > 0) {
        itemsToRender.push({
          is_link: true,
          count: hiddenGroup.length,
          hiddenIds: hiddenGroup.map(p => p.inspection_point.id), 
        });
        
        hiddenGroup.forEach(hiddenPoint => {
          if (manuallyShownPoints.value.includes(hiddenPoint.inspection_point.id)) {
            itemsToRender.push(hiddenPoint);
          }
        });
        
        hiddenGroup = []; 
      }
      itemsToRender.push(point);
    } else {
      if (!hasCompatibilitySettings(point) || isFullyCompatible(point)) {
        hiddenGroup.push(point);
      }
    }
  });

  if (hiddenGroup.length > 0) {
    itemsToRender.push({
      is_link: true,
      count: hiddenGroup.length,
      hiddenIds: hiddenGroup.map(p => p.inspection_point.id),
    });
    
    hiddenGroup.forEach(hiddenPoint => {
      if (manuallyShownPoints.value.includes(hiddenPoint.inspection_point.id)) {
        itemsToRender.push(hiddenPoint);
      }
    });
  }
  
  return itemsToRender.filter(item => 
    item.is_link || 
    item.is_default || 
    hasPointData(item.inspection_point?.id) || 
    manuallyShownPoints.value.includes(item.inspection_point?.id)
  );
});

const filteredPoints = computed(() => {
  return renderedItems.value.filter(item => !item.is_link);
});

// Fungsi untuk menampilkan point tersembunyi
const revealHiddenPoints = (hiddenIds) => {
  showGlobalHidden.value = false; 

  hiddenIds.forEach(id => {
    if (!manuallyShownPoints.value.includes(id)) {
      manuallyShownPoints.value.push(id);
    }
  });
};

// Cek apakah point sudah memiliki data
const hasPointData = (pointId) => {
  if (!pointId) {
    return false;
  }
  
  const hasServerResult = page.props.existingResults[pointId] !== undefined;
  const hasServerImages = page.props.existingImages[pointId] && page.props.existingImages[pointId].length > 0;
  
  const hasLocalResult = props.form.results[pointId] && 
                       (props.form.results[pointId].status || props.form.results[pointId].note);
  
  const hasLocalImages = props.form.images[pointId] && props.form.images[pointId].length > 0;

  return hasServerResult || hasServerImages || hasLocalResult || hasLocalImages;
};

// Default radio options
const defaultRadioOptions = [
  { value: 'good', label: 'Good' },
  { value: 'bad', label: 'Bad' },
  { value: 'na', label: 'N/A' }
];

// Cek apakah point sudah lengkap
const isPointComplete = (menuPoint) => {
  const result = props.form.results[menuPoint.inspection_point?.id];
  const image = props.form.images[menuPoint.inspection_point?.id];

  if (!result) return false;
  
  switch(menuPoint.input_type) {
    case 'text':
    case 'number':
    case 'date':
    case 'account':
    case 'textarea':
      return !!result.note;

    case 'select':
    case 'radio':
      if (!result.status) return false;
      
      const selectedOption = menuPoint.settings?.radios?.find(opt => opt.value === result.status);
      if (selectedOption?.settings) {
        if (selectedOption.settings.show_textarea && !result.note?.trim()) {
          return false;
        }
        if (selectedOption.settings.show_image_upload && image?.length === 0) {
          return false;
        }
      }
      return true;

    case 'imageTOradio':
      if (image?.length === 0 || !result.status) return false;
      
      const selectedOptionImage = menuPoint.settings?.radios?.find(opt => opt.value === result.status);
      if (selectedOptionImage?.settings) {
        if (selectedOptionImage.settings.show_textarea && !result.note?.trim()) {
          return false;
        }
      }
      return true;

    case 'image':
      return image?.length > 0;

    default:
      return !!result.status || !!result.note?.trim();
  }
};

// Debounced event handlers untuk smooth performance
const handleUpdateResult = (pointId, value, type) => {
  if (updateTimeout) {
    clearTimeout(updateTimeout);
  }
  
  updateTimeout = setTimeout(() => {
    emit('updateResult', { pointId, type, value });
  }, 16); // 60fps
};

const handleUpdateField = (pointId, field, value) => {
  if (updateTimeout) {
    clearTimeout(updateTimeout);
  }
  
  updateTimeout = setTimeout(() => {
    if (field === 'note' || field === 'status') {
      emit('updateResult', { pointId, type: field, value });
    }
  }, 16);
};

const handleUpdateImages = (pointId, images) => {
  if (updateTimeout) {
    clearTimeout(updateTimeout);
  }
  
  updateTimeout = setTimeout(() => {
    // Update local state langsung untuk responsiveness
    if (props.form.images) {
      props.form.images[pointId] = images;
    }
    // Emit untuk parent/saving
    // emit('updateImages', { pointId, images });
  }, 16);
};

const handleSaveResult = (pointId) => {
  if (saveTimeout) {
    clearTimeout(saveTimeout);
  }
  
  saveTimeout = setTimeout(() => {
    console.log('Save result for point:', pointId);
  }, 100);
};

const handleHapusPoint = (pointId) => {
  emit("hapusPoint", pointId);
};

// Cleanup timeouts
const clearTimeouts = () => {
  if (updateTimeout) clearTimeout(updateTimeout);
  if (saveTimeout) clearTimeout(saveTimeout);
};

// Watchers dengan optimasi
watch(() => props.form.results, (newResults) => {
  // Handle perubahan results jika diperlukan
}, { deep: true, flush: 'post' }); // flush: 'post' untuk mengurangi blocking

watch(() => props.form.images, (newImages) => {
  // Handle perubahan images jika diperlukan
}, { deep: true, flush: 'post' });

watch(() => props.car, (newCar) => {
  console.log('Car data updated:', newCar);
}, { deep: true });

watch(vehicleFeatures, (newFeatures) => {
  console.log('Vehicle features updated:', newFeatures);
}, { deep: true });
</script>

<style scoped>
/* Optimasi CSS untuk smooth scrolling di mobile */
.will-change-transform {
  will-change: transform;
  transform: translateZ(0);
  backface-visibility: hidden;
  perspective: 1000px;
}

.point-item {
  content-visibility: auto;
  contain-intrinsic-size: 100px 200px;
  transform: translateZ(0);
  isolation: isolate;
}

.touch-manipulation {
  touch-action: manipulation;
}

/* Mobile-first responsive design */
@media (max-width: 640px) {
  .point-item {
    padding: 0.5rem 0;
  }
  
  /* Improve touch targets */
  button {
    min-height: 44px;
    min-width: 44px;
  }
  
  /* Prevent zoom on iOS */
  input, 
  select, 
  textarea {
    font-size: 16px;
  }
  
  /* Optimize spacing for mobile */
  .space-y-2 > * + * {
    margin-top: 0.5rem;
  }
  
  .space-y-3 > * + * {
    margin-top: 0.75rem;
  }
}

/* Optimasi animasi */
.animate-spin {
  transform: translateZ(0);
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg) translateZ(0);
  }
  to {
    transform: rotate(360deg) translateZ(0);
  }
}

/* Reduce paint area */
.border-gray-100 {
  border-color: rgba(243, 244, 246, 0.8);
}

/* Better text rendering */
.text-gray-700 {
  text-rendering: optimizeLegibility;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* Optimize shadow performance */
.shadow-lg {
  box-shadow: 
    0 4px 6px -1px rgba(0, 0, 0, 0.05),
    0 2px 4px -1px rgba(0, 0, 0, 0.03);
}

/* Hardware acceleration for fixed elements */
.fixed {
  transform: translateZ(0);
}
</style>