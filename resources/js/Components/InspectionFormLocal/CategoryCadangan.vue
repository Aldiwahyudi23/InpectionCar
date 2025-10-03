<template>
  <div v-if="props.head === 'vertical'"
    class="px-6 py-2 border-b flex items-center justify-between bg-indigo-200"
    :class=" 'fixed top-0 left-0 right-0 z-20'"
  >
    <h4 class="text-base font-semibold text-indigo-700">
      {{ category.name }}
    </h4>

    <button
      v-if="hasHiddenPoints"
      @click="toggleGlobalHidden"
      class="text-sm text-indigo-700 hover:underline focus:outline-none"
    >
      {{ showGlobalHidden ? 'Sembunyikan Semua' : 'Tampilkan Point Lain' }}
    </button>
  </div>
  <div class="bg-gray-50 shadow-lg rounded-xl overflow-hidden border border-gray-100"
    :class="props.head === 'vertical' ? 'pt-6' : ''"
  >

    <button
      v-if="!isHeaderVisible && hasHiddenPoints"
      @click="toggleGlobalHidden"
      class="fixed top-3 right-2 z-50 bg-indigo-600 text-white p-2 rounded-full shadow-lg"
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
      class="bg-indigo-200 px-6 py-2 border-b flex items-center justify-between">
      <h4 class="text-base font-semibold text-indigo-700">
        {{ category.name }}
      </h4>

      <button
        v-if="hasHiddenPoints"
        @click="toggleGlobalHidden"
        class="text-sm text-indigo-700 hover:underline focus:outline-none"
      >
        {{ showGlobalHidden ? 'Sembunyikan' : 'Tampilkan Point Lain' }}
      </button>
    </div>
    
    <div class="p-4 space-y-4"> 
      <div v-for="(item, index) in renderedItems" :key="index" 
        class="space-y-2 last:border-0 last:pb-0"
        :class="item.is_link ? 'pb-0' : 'pb-2 border-b border-gray-100'"
      >
        
        <div v-if="item.is_link" 
          class="flex justify-end py-1"> <button
            @click="revealHiddenPoints(item.hiddenIds)"
            class="text-sm font-medium text-indigo-600 hover:text-indigo-800 hover:underline focus:outline-none"
          >
            Tampilkan {{ item.count }} Point Tersembunyi
          </button>
        </div>

        <div v-else class="space-y-2">
          <div class="flex items-start justify-between">
            <label class="block text-sm font-medium text-gray-700">
              {{ item.inspection_point?.name }}
              
              <span v-if="item.settings?.is_required" class="text-red-500">*</span>
              <span v-if="!item.is_default && !hasPointData(item.inspection_point?.id)"
                class="italic text-xs text-gray-400 ml-2"
              >
                (opsional)
              </span>
            </label>
            <span 
              v-if="isPointComplete(item)"
              class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800"
            >
              ✓
            </span>
          </div>
          
          <input-text
            v-if="item.input_type === 'text'"
            :model-value="form.results[item.inspection_point?.id]?.note"
            :required="item.settings?.is_required"
            :min-length="item.settings?.min_length"
            :max-length="item.settings?.max_length"
            :allowSpace="item.settings?.allow_space"
            :textTransform="item.settings?.text_transform"
            :placeholder="item.settings?.placeholder || 'Masukan text'"
            :error="form.errors[`results.${item.inspection_point?.id}.note`]"
            @update:modelValue="updateResult(item.inspection_point?.id, $event, 'note')"
            @save="saveResult(item.inspection_point?.id)"
          />
          
          <input-number
            v-if="item.input_type === 'number'"
            :model-value="form.results[item.inspection_point?.id]?.note"
            :required="item.settings?.is_required"
            :min="item.settings?.min"
            :max="item.settings?.max"
            :step="item.settings?.step || 1"
            :placeholder="item.settings?.placeholder || 'Masukan number'"
            :error="form.errors[`results.${item.inspection_point?.id}.note`]"
            @update:modelValue="updateResult(item.inspection_point?.id, $event, 'note')"
            @save="saveResult(item.inspection_point?.id)"
          />

          <input-account
            v-if="item.input_type === 'account'"
            :model-value="form.results[item.inspection_point?.id]?.note"
            :required="item.settings?.is_required"
            :placeholder="item.settings?.placeholder || 'Masukkan nilai'"
            :error="form.errors[`results.${item.inspection_point?.id}.note`]"
            :point-id="item.inspection_point?.id"
            :settings="item.settings"
            @update:modelValue="updateResult(item.inspection_point?.id, $event, 'note')"
            @save="saveResult(item.inspection_point?.id)"
          />
          
          <input-date
            v-if="item.input_type === 'date'"
            :model-value="form.results[item.inspection_point?.id]?.note"
            :required="item.settings?.is_required"
            :min-date="item.settings?.min_date"
            :max-date="item.settings?.max_date"
            :error="form.errors[`results.${item.inspection_point?.id}.note`]"
            @update:modelValue="updateResult(item.inspection_point?.id, $event, 'note')"
            @save="saveResult(item.inspection_point?.id)"
          />
          
          <input-textarea
            v-if="item.input_type === 'textarea'"
            :model-value="form.results[item.inspection_point?.id]?.note"
            :required="item.settings?.is_required"
            :min-length="item.settings?.min_length"
            :max-length="item.settings?.max_length"
            :placeholder="item.settings?.placeholder || 'Masukkan teks di sini'"
            :settings="item.settings"
            :error="form.errors[`results.${item.inspection_point?.id}.note`]"
            @update:modelValue="updateResult(item.inspection_point?.id, $event, 'note')"
            @save="saveResult(item.inspection_point?.id)"
          />

          <input-radio
            v-if="item.input_type === 'radio'"
            :model-value="form.results[item.inspection_point?.id]?.status"
            :notes="form.results[item.inspection_point?.id]?.note"
            :images="form.images[item.inspection_point?.id]"
            :required="item.settings?.is_required"
            :point-id="item.inspection_point?.id"
            :point="item.inspection_point"
            :inspection-id="inspectionId" 
            :settings="item.settings"
            :point-name="item.inspection_point?.name"
            :selected-point="item.inspection_point ?? null"
            :options="item.settings?.radios || defaultRadioOptions"
            :error="form.errors[`results.${item.inspection_point?.id}.status`]"
            @update:modelValue="updateResult(item.inspection_point?.id, $event, 'status')"
            @update:notes="val => updateResult(item.inspection_point?.id, val, 'note')"
            @update:images="val => updateImages(item.inspection_point?.id, val)"
            @save="saveResult(item.inspection_point?.id)"
            @hapus="HapusPoint(item.inspection_point?.id)"
          />

          <InputImageToRadio
            v-if="item.input_type === 'imageTOradio'"
            :model-value="form.results[item.inspection_point?.id]?.status"
            :notes="form.results[item.inspection_point?.id]?.note"
            :images="form.images[item.inspection_point?.id]"
            :required="item.settings?.is_required"
            :point-id="item.inspection_point?.id"
            :inspection-id="inspectionId" 
            :settings="item.settings"
            :point-name="item.inspection_point?.name"
            :point="item"
            :selected-point="item.inspection_point ?? null"
            :options="item.settings?.radios || defaultRadioOptions"
            :error="form.errors[`results.${item.inspection_point?.id}.status`]"
            @update:modelValue="updateResult(item.inspection_point?.id, $event,'status')"
            @update:notes="val => updateResult(item.inspection_point?.id, val, 'note')"
            @update:images="val => updateImages(item.inspection_point?.id, val)"
            @save="saveResult(item.inspection_point?.id)"
            @hapus="HapusPoint(item.inspection_point?.id)"
          />
          
          <!-- MODIFIED: InputImage dengan direct upload -->
          <input-image
            v-if="item.input_type === 'image'"
            :model-value="form.images[item.inspection_point?.id]"
            :error="form.errors[`images.${item.inspection_point?.id}`]"
            :inspection-id="inspectionId"
            :point-id="item.inspection_point?.id"
            :point="item.inspection_point"
            :point-name="item.inspection_point?.name"
            :settings="item.settings"
            :direct-upload="true"
            @update:modelValue="updateImages(item.inspection_point?.id, $event)"
            @image-saved="handleDirectImageSave"
          />

          <input-select
            v-if="item.input_type === 'select'"
            :model-value="form.results[item.inspection_point?.id]?.status"
            :required="item.settings?.is_required"
            :error="form.errors[`results.${item.inspection_point?.id}.status`]"
            @update:modelValue="updateResult(item.inspection_point?.id, $event, 'status')"
            @save="saveResult(item.inspection_point?.id)"
          />
        </div>
        
      </div>

      <div v-if="renderedItems.filter(i => !i.is_link).length === 0" class="text-center py-8 text-gray-500">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p class="mt-2 text-sm">Tidak ada data untuk ditampilkan</p>
        <p v-if="category.isDamageMenu" class="text-xs text-gray-400">
          Tambahkan data melalui tombol "+" di pojok kanan bawah
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, computed, ref, watch, inject } from 'vue';
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
});

const emit = defineEmits(['updateResult', 'removeImage', 'hapusPoint', 'imageSaved']);

// Inject dari parent component untuk background upload system
const addToUploadQueue = inject('addToUploadQueue');
const globalUploadQueue = inject('globalUploadQueue');
const retryFailedUpload = inject('retryFailedUpload');
const uploadStats = inject('uploadStats');
const isUploading = inject('isUploading');

// State untuk tampilan
const showGlobalHidden = ref(false); 
const manuallyShownPoints = ref([]); 
const isHeaderVisible = ref(true);
const categoryHeader = ref(null);

const page = usePage();

// Setup intersection observer untuk header
onMounted(() => {
  const observer = new IntersectionObserver(([entry]) => {
    isHeaderVisible.value = entry.isIntersecting;
  }, { threshold: 0.1 });

  if (categoryHeader.value) {
    observer.observe(categoryHeader.value);
  }

  onUnmounted(() => {
    if (categoryHeader.value) observer.unobserve(categoryHeader.value);
  });
});

// Toggle untuk menampilkan/sembunyikan semua point tersembunyi
const toggleGlobalHidden = () => {
  showGlobalHidden.value = !showGlobalHidden.value;
  
  if (!showGlobalHidden.value) {
    manuallyShownPoints.value = [];
  }
};

// Computed properties
const hasHiddenPoints = computed(() => {
  return (props.category.points || []).some(p => p.is_default === false);
});

/**
 * FUNGSI UTAMA: Memproses daftar poin untuk menyisipkan tombol "Tampilkan"
 */
const renderedItems = computed(() => {
  const points = props.category.points || [];
  const itemsToRender = [];
  let hiddenGroup = []; 

  // KONDISI 1: Jika toggle global aktif (Tampilkan Semua), HANYA tampilkan poin
  if (showGlobalHidden.value) {
    if (props.category.isDamageMenu) {
        return points.filter(p => p.is_default || hasPointData(p.inspection_point?.id) || showGlobalHidden.value);
    }
    return points; 
  }
  
  // KONDISI 2: Jika damage menu non-global (gunakan logika lama)
  if (props.category.isDamageMenu) {
    return points.filter(point => {
        const pointId = point.inspection_point?.id;
        return hasPointData(pointId) || point.is_default || manuallyShownPoints.value.includes(pointId);
    });
  }

  // KONDISI 3: Logika inline untuk non-damage menu
  points.forEach((point) => {
    const pointId = point.inspection_point?.id;
    const hasData = hasPointData(pointId);
    const isShownManually = manuallyShownPoints.value.includes(pointId);

    // Kriteria tampil: is_default TRUE, ATAU sudah ada data, ATAU sudah dibuka manual
    const isVisible = point.is_default || hasData || isShownManually;

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
      hiddenGroup.push(point);
    }
  });

  // Setelah loop selesai, jika masih ada hiddenGroup yang tersisa di akhir
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

// Event handlers
const updateResult = (pointId, value, type) => {
  emit('updateResult', { pointId, type, value });
};

const updateImages = (pointId, images) => {
  // Update images di form
  props.form.images[pointId] = images;
  
  // Emit event untuk parent component
  emit('updateResult', { 
    pointId, 
    type: 'images', 
    value: images 
  });
};

const saveResult = (pointId) => {
  // Untuk sekarang, kita hanya update state lokal
  // Simpan otomatis sudah ditangani oleh watcher di parent
  console.log('Save result for point:', pointId);
};

// Handle direct image save dari InputImage component
const handleDirectImageSave = (imageData) => {
  console.log('Direct image save in CategorySection:', imageData);
  
  // Emit ke parent component untuk diproses
  emit('imageSaved', imageData);
};

const removeImage = (pointId, imageIndex) => {
  emit('removeImage', { pointId, imageIndex });
};

const HapusPoint = (pointId) => {
  emit("hapusPoint", pointId);
};

// Watchers untuk perubahan data
watch(() => props.form.results, (newResults) => {
  // Handle perubahan results jika diperlukan
}, { deep: true });

watch(() => props.form.images, (newImages) => {
  // Handle perubahan images jika diperlukan
}, { deep: true });

// Watch untuk global upload queue changes untuk update status gambar
watch(globalUploadQueue, (queue) => {
  // Update status gambar berdasarkan queue
  queue.forEach(task => {
    if (task.pointId && props.form.images[task.pointId]) {
      const imageIndex = props.form.images[task.pointId].findIndex(img => img.id === task.imageId);
      if (imageIndex !== -1) {
        // Update status gambar berdasarkan task status
        props.form.images[task.pointId][imageIndex].isUploading = task.status === 'uploading';
        props.form.images[task.pointId][imageIndex].isFailed = task.status === 'failed';
        props.form.images[task.pointId][imageIndex].isUploaded = task.status === 'completed';
        
        if (task.status === 'completed' && task.uploadedData) {
          props.form.images[task.pointId][imageIndex].id = task.uploadedData.image_id;
          props.form.images[task.pointId][imageIndex].image_path = task.uploadedData.path;
          props.form.images[task.pointId][imageIndex].preview = task.uploadedData.public_url;
          props.form.images[task.pointId][imageIndex].isNew = false;
          props.form.images[task.pointId][imageIndex].isPendingUpload = false;
        }
      }
    }
  });
}, { deep: true });
</script>

<style scoped>
/* Mobile-first styles */
@media (min-width: 640px) {
  .point-card {
    padding: 1.25rem;
  }
}
</style>