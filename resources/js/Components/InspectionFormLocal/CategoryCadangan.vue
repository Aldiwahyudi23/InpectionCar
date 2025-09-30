<template>
  <div class="category-container">
    <!-- Header untuk mode vertical -->
    <div 
      v-if="props.head === 'vertical'"
      class="category-header vertical-header"
      :class="{ 'header-hidden': !isHeaderVisible }"
    >
      <h4 class="text-base font-semibold text-indigo-700">
        {{ category.name }}
      </h4>

      <button
        v-if="hasHiddenPoints"
        @click="toggleHidden"
        class="toggle-button"
      >
        {{ showHidden ? 'Sembunyikan' : 'Tampilkan Point Lain' }}
      </button>
    </div>

    <!-- Main Content Container -->
    <div 
      class="content-wrapper"
      :class="{ 'vertical-padding': props.head === 'vertical' }"
    >
      <!-- Floating Eye Icon -->
      <button
        v-if="!isHeaderVisible && hasHiddenPoints"
        @click="toggleHidden"
        class="floating-eye-button"
      >
        <svg 
          v-if="showHidden" 
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5" 
          fill="none" 
          viewBox="0 0 24 24" 
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>

        <svg 
          v-else 
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5" 
          fill="none" 
          viewBox="0 0 24 24" 
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.006-3.362M9.88 9.88a3 3 0 104.24 4.24M6.1 6.1l11.8 11.8" />
        </svg>
      </button>

      <!-- Header untuk mode horizontal -->
      <div 
        v-if="props.head === 'horizontal'"
        ref="categoryHeader"
        class="category-header horizontal-header"
      >
        <h4 class="text-base font-semibold text-indigo-700">
          {{ category.name }}
        </h4>

        <button
          v-if="hasHiddenPoints"
          @click="toggleHidden"
          class="toggle-button"
        >
          {{ showHidden ? 'Sembunyikan' : 'Tampilkan Point Lain' }}
        </button>
      </div>
      
      <!-- Points Container - Dioptimalkan untuk scroll -->
      <div class="points-container">
        <div 
          v-for="menuPoint in filteredPoints" 
          :key="menuPoint.id" 
          class="point-item"
        >
          <div class="point-header">
            <label class="point-label">
              {{ menuPoint.inspection_point?.name }}
              <span v-if="menuPoint.settings?.is_required" class="required-star">*</span>
            </label>
            <span 
              v-if="isPointComplete(menuPoint)"
              class="completion-badge"
            >
              ✓
            </span>
          </div>
          
          <!-- Dynamic Input Components menggunakan ComponentWrapper -->
          <ComponentWrapper 
            :menuPoint="menuPoint" 
            @update:modelValue="handleComponentUpdate"
            @update:notes="handleNotesUpdate"
            @update:images="handleImagesUpdate"
            @save="handleSave"
            @hapus="handleHapus"
          />
        </div>

        <!-- Empty State -->
        <div v-if="filteredPoints.length === 0" class="empty-state">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          <p class="empty-text">Tidak ada data untuk ditampilkan</p>
          <p v-if="category.isDamageMenu" class="empty-subtext">
            Tambahkan data melalui tombol "+" di pojok kanan bawah
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, computed, ref, watch, nextTick, h } from 'vue';
import { usePage } from '@inertiajs/vue3';

// Components
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

const emit = defineEmits(['saveResult', 'updateResult', 'removeImage', 'hapusPoint']);

// Refs
const showHidden = ref(false);
const isHeaderVisible = ref(true);
const categoryHeader = ref(null);

// Computed properties dengan caching dan optimasi
const hasHiddenPoints = computed(() => {
  const points = props.category.points || [];
  return points.some(p => p.is_default === false);
});

// Optimasi filteredPoints dengan memoization
const filteredPoints = computed(() => {
  const points = props.category.points || [];
  const isDamageMenu = props.category.isDamageMenu;
  
  if (!isDamageMenu) {
    if (showHidden.value) return points;
    
    return points.filter(point => {
      const pointId = point.inspection_point?.id;
      return point.is_default || hasPointData(pointId);
    });
  }

  // Damage menu logic
  if (showHidden.value) return points;
  
  return points.filter(point => {
    const pointId = point.inspection_point?.id;
    return point.is_default || hasPointData(pointId);
  });
});

// Optimasi hasPointData dengan caching
const pointDataCache = new Map();
const hasPointData = (pointId) => {
  if (!pointId) return false;
  
  // Cek cache dulu
  if (pointDataCache.has(pointId)) {
    return pointDataCache.get(pointId);
  }
  
  const page = usePage();
  
  // Cek data dari server
  const hasServerResult = page.props.existingResults[pointId] !== undefined;
  const hasServerImages = page.props.existingImages[pointId]?.length > 0;
  
  // Cek data lokal
  const hasLocalResult = props.form.results[pointId] && 
                        (props.form.results[pointId].status || props.form.results[pointId].note);
  const hasLocalImages = props.form.images[pointId]?.length > 0;

  const result = hasServerResult || hasServerImages || hasLocalResult || hasLocalImages;
  
  // Cache hasilnya
  pointDataCache.set(pointId, result);
  
  return result;
};

const defaultRadioOptions = [
  { value: 'good', label: 'Good' },
  { value: 'bad', label: 'Bad' },
  { value: 'na', label: 'N/A' }
];

// Optimasi isPointComplete
const isPointComplete = (menuPoint) => {
  const pointId = menuPoint.inspection_point?.id;
  if (!pointId) return false;
  
  const result = props.form.results[pointId];
  const image = props.form.images[pointId];

  if (!result) return false;
  
  const inputType = menuPoint.input_type;
  
  switch(inputType) {
    case 'text':
    case 'number':
    case 'date':
    case 'account':
    case 'textarea':
      return !!result.note?.trim();

    case 'select':
    case 'radio':
      if (!result.status) return false;
      
      const selectedOption = menuPoint.settings?.radios?.find(opt => opt.value === result.status);
      if (selectedOption?.settings) {
        if (selectedOption.settings.show_textarea && !result.note?.trim()) {
          return false;
        }
        if (selectedOption.settings.show_image_upload && !image?.length) {
          return false;
        }
      }
      return true;

    case 'imageTOradio':
      if (!image?.length || !result.status) return false;
      
      const selectedOptionImage = menuPoint.settings?.radios?.find(opt => opt.value === result.status);
      if (selectedOptionImage?.settings?.show_textarea && !result.note?.trim()) {
        return false;
      }
      return true;

    case 'image':
      return image?.length > 0;

    default:
      return !!result.status || !!result.note?.trim();
  }
};

// Methods
const toggleHidden = () => {
  showHidden.value = !showHidden.value;
};

// Handler untuk events dari ComponentWrapper
const handleComponentUpdate = ({ pointId, value, type }) => {
  updateResult(pointId, value, type);
};

const handleNotesUpdate = ({ pointId, value }) => {
  updateResult(pointId, value, 'note');
};

const handleImagesUpdate = ({ pointId, value }) => {
  if (pointId && pointDataCache.has(pointId)) {
    pointDataCache.delete(pointId);
  }
  props.form.images[pointId] = value;
};

const handleSave = (pointId) => {
  saveResult(pointId);
};

const handleHapus = (pointId) => {
  HapusPoint(pointId);
};

const updateResult = (pointId, value, type) => {
  // Clear cache ketika data berubah
  if (pointId && pointDataCache.has(pointId)) {
    pointDataCache.delete(pointId);
  }
  emit('updateResult', { pointId, type, value });
};

const saveResult = (pointId) => {
  emit('saveResult', pointId);
};

const removeImage = (pointId, imageIndex) => {
  if (pointId && pointDataCache.has(pointId)) {
    pointDataCache.delete(pointId);
  }
  emit('removeImage', { pointId, imageIndex });
};

const HapusPoint = (pointId) => {
  if (pointId && pointDataCache.has(pointId)) {
    pointDataCache.delete(pointId);
  }
  emit("hapusPoint", pointId);
};

// ComponentWrapper yang diperbaiki dengan semua case
const ComponentWrapper = {
  props: ['menuPoint'],
  emits: ['update:modelValue', 'update:notes', 'update:images', 'save', 'hapus'],
  setup(props, { emit }) {
    const page = usePage();
    
    return () => {
      const point = props.menuPoint;
      const pointId = point.inspection_point?.id;
      
      if (!pointId) return null;

      // Helper function untuk mendapatkan modelValue berdasarkan input_type
      const getModelValue = () => {
        if (point.input_type === 'image') {
          return page.props.form?.images[pointId] || [];
        } else if (['text', 'number', 'date', 'account', 'textarea'].includes(point.input_type)) {
          return page.props.form?.results[pointId]?.note || '';
        } else {
          return page.props.form?.results[pointId]?.status || '';
        }
      };

      // Helper function untuk mendapatkan error path
      const getErrorPath = () => {
        if (point.input_type === 'image') {
          return `images.${pointId}`;
        } else if (['text', 'number', 'date', 'account', 'textarea'].includes(point.input_type)) {
          return `results.${pointId}.note`;
        } else {
          return `results.${pointId}.status`;
        }
      };

      const commonProps = {
        modelValue: getModelValue(),
        error: page.props.form?.errors[getErrorPath()],
        required: point.settings?.is_required,
        pointId: pointId,
        inspectionId: page.props.inspectionId,
        settings: point.settings,
        'onUpdate:modelValue': (value) => {
          const type = point.input_type === 'image' ? 'images' : 
                      ['text','number','date','account','textarea'].includes(point.input_type) ? 'note' : 'status';
          emit('update:modelValue', { pointId, value, type });
        }
      };

      // Props khusus untuk setiap component type
      const getComponentSpecificProps = () => {
        const baseProps = {
          placeholder: point.settings?.placeholder || getDefaultPlaceholder(point.input_type),
          minLength: point.settings?.min_length,
          maxLength: point.settings?.max_length,
          min: point.settings?.min,
          max: point.settings?.max,
          step: point.settings?.step,
          allowSpace: point.settings?.allow_space,
          textTransform: point.settings?.text_transform,
          minDate: point.settings?.min_date,
          maxDate: point.settings?.max_date,
          options: point.settings?.radios || defaultRadioOptions,
          pointName: point.inspection_point?.name,
          selectedPoint: point.inspection_point,
          point: point,
          notes: page.props.form?.results[pointId]?.note || '',
          images: page.props.form?.images[pointId] || [],
          'onUpdate:notes': (value) => emit('update:notes', { pointId, value }),
          'onUpdate:images': (value) => emit('update:images', { pointId, value }),
          onSave: () => emit('save', pointId),
          onHapus: () => emit('hapus', pointId)
        };

        return baseProps;
      };

      // Render component berdasarkan input_type
      switch(point.input_type) {
        case 'text':
          return h(InputText, {
            ...commonProps,
            ...getComponentSpecificProps(),
            placeholder: point.settings?.placeholder || 'Masukan text'
          });

        case 'number':
          return h(InputNumber, {
            ...commonProps,
            ...getComponentSpecificProps(),
            placeholder: point.settings?.placeholder || 'Masukan number'
          });

        case 'date':
          return h(InputDate, {
            ...commonProps,
            ...getComponentSpecificProps(),
            placeholder: point.settings?.placeholder || 'Pilih tanggal'
          });

        case 'account':
          return h(InputAccount, {
            ...commonProps,
            ...getComponentSpecificProps(),
            placeholder: point.settings?.placeholder || 'Masukkan nilai'
          });

        case 'textarea':
          return h(InputTextarea, {
            ...commonProps,
            ...getComponentSpecificProps(),
            placeholder: point.settings?.placeholder || 'Masukkan teks di sini'
          });

        case 'select':
          return h(InputSelect, {
            ...commonProps,
            ...getComponentSpecificProps()
          });

        case 'radio':
          return h(InputRadio, {
            ...commonProps,
            ...getComponentSpecificProps()
          });

        case 'imageTOradio':
          return h(InputImageToRadio, {
            ...commonProps,
            ...getComponentSpecificProps()
          });

        case 'image':
          return h(InputImage, {
            ...commonProps,
            ...getComponentSpecificProps()
          });

        default:
          console.warn(`Unknown input type: ${point.input_type} for point ${pointId}`);
          return null;
      }
    };
  }
};

// Helper function untuk default placeholder
const getDefaultPlaceholder = (inputType) => {
  const placeholders = {
    text: 'Masukan text',
    number: 'Masukan number',
    date: 'Pilih tanggal',
    account: 'Masukkan nilai',
    textarea: 'Masukkan teks di sini',
    select: 'Pilih opsi',
    radio: 'Pilih opsi',
    imageTOradio: 'Pilih opsi dan upload gambar',
    image: 'Upload gambar'
  };
  return placeholders[inputType] || 'Masukkan data';
};

// Intersection Observer untuk header visibility
let observer = null;

onMounted(() => {
  requestAnimationFrame(() => {
    if (categoryHeader.value) {
      observer = new IntersectionObserver(
        ([entry]) => {
          isHeaderVisible.value = entry.isIntersecting;
        },
        { 
          threshold: 0.1,
          rootMargin: '50px 0px 0px 0px'
        }
      );
      observer.observe(categoryHeader.value);
    }
  });
});

onUnmounted(() => {
  if (observer && categoryHeader.value) {
    observer.unobserve(categoryHeader.value);
  }
  pointDataCache.clear();
});

// Watcher yang lebih efisien
watch(
  () => [props.form.results, props.form.images],
  () => {
    pointDataCache.clear();
  },
  { deep: false }
);
</script>

<style scoped>
/* CSS styles tetap sama seperti sebelumnya */
.category-container {
  transform: translateZ(0);
  backface-visibility: hidden;
  perspective: 1000px;
  will-change: transform;
}

.content-wrapper {
  position: relative;
  background: #f9fafb;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #f3f4f6;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.vertical-padding {
  padding-top: 1.5rem;
}

.category-header {
  padding: 0.75rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  background: #e0e7ff;
  display: flex;
  align-items: center;
  justify-content: between;
  transition: all 0.2s ease;
}

.vertical-header {
  position: sticky;
  top: 0;
  z-index: 10;
  backdrop-filter: blur(8px);
  background: rgba(224, 231, 255, 0.95);
}

.vertical-header.header-hidden {
  transform: translateY(-100%);
  opacity: 0;
}

.horizontal-header {
  background: #e0e7ff;
}

.points-container {
  padding: 1rem;
  max-height: calc(100vh - 200px);
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  scroll-behavior: smooth;
}

.points-container::-webkit-scrollbar {
  width: 4px;
}

.points-container::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 2px;
}

.points-container::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 2px;
}

.points-container::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

.point-item {
  padding: 1rem 0;
  border-bottom: 1px solid #f1f5f9;
  transform: translateZ(0);
  will-change: transform;
}

.point-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.point-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.75rem;
}

.point-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  line-height: 1.4;
}

.required-star {
  color: #ef4444;
  margin-left: 2px;
}

.completion-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.5rem;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  font-weight: 500;
  background: #dcfce7;
  color: #166534;
}

.toggle-button {
  font-size: 0.875rem;
  color: #3730a3;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  border-radius: 0.375rem;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.toggle-button:hover {
  background: rgba(99, 102, 241, 0.1);
  color: #3730a3;
}

.floating-eye-button {
  position: fixed;
  top: 1rem;
  right: 1rem;
  z-index: 50;
  background: #4f46e5;
  color: white;
  padding: 0.75rem;
  border-radius: 50%;
  border: none;
  cursor: pointer;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
  transform: translateZ(0);
}

.floating-eye-button:hover {
  background: #4338ca;
  transform: scale(1.05);
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
  color: #6b7280;
}

.empty-icon {
  height: 3rem;
  width: 3rem;
  margin: 0 auto 1rem;
  opacity: 0.5;
}

.empty-text {
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.empty-subtext {
  font-size: 0.75rem;
  opacity: 0.7;
}

@media (prefers-reduced-motion: reduce) {
  .category-header,
  .floating-eye-button,
  .toggle-button {
    transition: none;
  }
}

@media (max-width: 640px) {
  .content-wrapper {
    margin: 0.5rem;
    border-radius: 8px;
  }
  
  .points-container {
    padding: 0.75rem;
    max-height: calc(100vh - 150px);
  }
  
  .point-item {
    padding: 0.75rem 0;
  }
  
  .floating-eye-button {
    top: 0.5rem;
    right: 0.5rem;
    padding: 0.5rem;
  }
}

@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  .points-container {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }
}
</style>