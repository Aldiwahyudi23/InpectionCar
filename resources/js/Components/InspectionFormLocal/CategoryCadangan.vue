<template>
  <div class="bg-gray-50 shadow-lg rounded-xl overflow-hidden border border-gray-100">

    <div class="bg-indigo-200 px-6 py-2 border-b flex items-center justify-between">
      <h3 class="text-xl font-semibold text-indigo-700">
        {{ category.name }}
      </h3>

      <!-- Tombol muncul kalau ada hidden points -->
      <button
        v-if="hasHiddenPoints"
        @click="showHidden = !showHidden"
        class="text-sm text-indigo-700 hover:underline focus:outline-none"
      >
        {{ showHidden ? 'Sembunyikan Point Yang Tidak Perlu' : 'Tampilkan Point Tersembunyi' }}
      </button>
    </div>

    
    <div class="p-4 space-y-4"> 
      <!-- PERBAIKAN: Gunakan filteredPoints bukan category.menu_point -->
      <div v-for="menuPoint in filteredPoints" :key="menuPoint.id" 
        class="space-y-2 pb-2 border-b border-gray-100 last:border-0 last:pb-0" >
        <div class="flex items-start justify-between">
          <label class="block text-sm font-medium text-gray-700">
            {{ menuPoint.inspection_point?.name }}
            <span v-if="menuPoint.settings?.is_required" class="text-red-500">*</span>
          </label>
          <span 
            v-if="isPointComplete(menuPoint)"
            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800"
          >
            ✓
          </span>
        </div>
        
        <input-text
          v-if="menuPoint.input_type === 'text'"
          v-model="form.results[menuPoint.inspection_point?.id].note"
          :required="menuPoint.settings?.is_required"
          :min-length="menuPoint.settings?.min_length"
          :max-length="menuPoint.settings?.max_length"
          :allowSpace="menuPoint.settings?.allow_space"
          :textTransform="menuPoint.settings?.text_transform"
          :placeholder="menuPoint.settings?.placeholder || 'Masukan text'"
          :error="form.errors[`results.${menuPoint.inspection_point?.id}.note`]"
          @update:modelValue="updateResult(menuPoint.inspection_point?.id, $event, 'note')"
          @save="saveResult(menuPoint.inspection_point?.id)"
        />
        
        <input-number
          v-if="menuPoint.input_type === 'number'"
          v-model="form.results[menuPoint.inspection_point?.id].note"
          :required="menuPoint.settings?.is_required"
          :min="menuPoint.settings?.min"
          :max="menuPoint.settings?.max"
          :step="menuPoint.settings?.step || 1"
          :placeholder="menuPoint.settings?.placeholder || 'Masukan number'"
          :error="form.errors[`results.${menuPoint.inspection_point?.id}.note`]"
          @update:modelValue="updateResult(menuPoint.inspection_point?.id, $event, 'note')"
          @save="saveResult(menuPoint.inspection_point?.id)"
        />

        <input-account
          v-if="menuPoint.input_type === 'account'"
          v-model="form.results[menuPoint.inspection_point?.id].note"
          :required="menuPoint.settings?.is_required"
          :placeholder="menuPoint.settings?.placeholder || 'Masukkan nilai'"
          :error="form.errors[`results.${menuPoint.inspection_point?.id}.note`]"
          :point-id="menuPoint.inspection_point?.id"
          :settings="menuPoint.settings"
          @update:modelValue="updateResult(menuPoint.inspection_point?.id, $event, 'note')"
          @save="saveResult(menuPoint.inspection_point?.id)"
        />
        
        <input-date
          v-if="menuPoint.input_type === 'date'"
          v-model="form.results[menuPoint.inspection_point?.id].note"
          :required="menuPoint.settings?.is_required"
          :min-date="menuPoint.settings?.min_date"
          :max-date="menuPoint.settings?.max_date"
          :error="form.errors[`results.${menuPoint.inspection_point?.id}.note`]"
          @update:modelValue="updateResult(menuPoint.inspection_point?.id, $event, 'note')"
          @save="saveResult(menuPoint.inspection_point?.id)"
        />
        
        <input-textarea
          v-if="menuPoint.input_type === 'textarea'"
          v-model="form.results[menuPoint.inspection_point?.id].note"
          :required="menuPoint.settings?.is_required"
          :min-length="menuPoint.settings?.min_length"
          :max-length="menuPoint.settings?.max_length"
          :placeholder="menuPoint.settings?.placeholder || 'Masukkan teks di sini'"
          :settings="menuPoint.settings"
          :error="form.errors[`results.${menuPoint.inspection_point?.id}.note`]"
          @update:modelValue="updateResult(menuPoint.inspection_point?.id, $event, 'note')"
          @save="saveResult(menuPoint.inspection_point?.id)"
        />

        <input-radio
          v-if="menuPoint.input_type === 'radio'"
          v-model="form.results[menuPoint.inspection_point?.id].status"
          :notes="form.results[menuPoint.inspection_point?.id].note"
          :images="form.images[menuPoint.inspection_point?.id]"
          :required="menuPoint.settings?.is_required"
          :point-id="menuPoint.inspection_point?.id"
          :point="menuPoint.inspection_point"
          :inspection-id="inspectionId" 
          :settings="menuPoint.settings"
          :point-name="menuPoint.inspection_point?.name"
          :selected-point="menuPoint.inspection_point ?? null"
          :options="menuPoint.settings?.radios || defaultRadioOptions"
          :error="form.errors[`results.${menuPoint.inspection_point?.id}.status`]"
          @update:modelValue="updateResult(menuPoint.inspection_point?.id, $event, 'status')"
          @update:notes="val => form.results[menuPoint.inspection_point?.id].note = val"
          @update:images="val => form.images[menuPoint.inspection_point?.id] = val"
          @save="saveResult(menuPoint.inspection_point?.id)"
          @hapus="HapusPoint(menuPoint.inspection_point?.id)"
        />

        <InputImageToRadio
          v-if="menuPoint.input_type === 'imageTOradio'"
          v-model="form.results[menuPoint.inspection_point?.id].status"
          :notes="form.results[menuPoint.inspection_point?.id].note"
          :images="form.images[menuPoint.inspection_point?.id]"
          :required="menuPoint.settings?.is_required"
          :point-id="menuPoint.inspection_point?.id"
          :inspection-id="inspectionId" 
          :settings="menuPoint.settings"
          :point-name="menuPoint.inspection_point?.name"
          :point="menuPoint"
          :selected-point="menuPoint.inspection_point ?? null"
          :options="menuPoint.settings?.radios || defaultRadioOptions"
          :error="form.errors[`results.${menuPoint.inspection_point?.id}.status`]"
          @update:modelValue="updateResult(menuPoint.inspection_point?.id, $event,'status')"
          @update:notes="val => form.results[menuPoint.inspection_point?.id].note = val"
          @update:images="val => form.images[menuPoint.inspection_point?.id] = val"
          @save="saveResult(menuPoint.inspection_point?.id)"
          @hapus="HapusPoint(menuPoint.inspection_point?.id)"
        />
        
        <input-image
          v-if="menuPoint.input_type === 'image'"
          v-model="form.images[menuPoint.inspection_point?.id]"
          :error="form.errors[`images.${menuPoint.inspection_point?.id}`]"
          :inspection-id="inspectionId"
          :point-id="menuPoint.inspection_point?.id"
          :point="menuPoint.inspection_point"
          :point-name="menuPoint.inspection_point?.name"
          :settings="menuPoint.settings"
          @update:notes="val => form.results[menuPoint.inspection_point?.id].note = val"
          @update:status="val => form.results[menuPoint.inspection_point?.id].status = val"
        />

        <input-select
          v-if="menuPoint.input_type === 'select'"
          v-model="form.results[menuPoint.inspection_point?.id].status"
          :required="menuPoint.settings?.is_required"
          :error="form.errors[`results.${menuPoint.inspection_point?.id}.status`]"
          @update:modelValue="updateResult(menuPoint.inspection_point?.id, $event, 'status')"
          @save="saveResult(menuPoint.inspection_point?.id)"
        />
        
      </div>

      <!-- Tampilkan pesan jika tidak ada points yang visible -->
      <div v-if="filteredPoints.length === 0" class="text-center py-8 text-gray-500">
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
import InputText from './InputText.vue';
import InputNumber from './InputNumber.vue';
import InputDate from './InputDate.vue';
import InputAccount from './InputAccount.vue';
import InputTextarea from './InputTextarea.vue';
import InputSelect from './InputSelect.vue';
import InputRadio from './InputRadio.vue';
import InputImage from './InputImage.vue';
import InputImageToRadio from './InputImageToRadio.vue';
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  category: Object,
  form: Object,
  inspectionId: String,
  selectedPoint: Object,
});

const emit = defineEmits(['saveResult', 'updateResult', 'removeImage', 'hapusPoint']);

const showHidden = ref(false); // kontrol link toggle

const page = usePage();

// apakah kategori punya menuPoint dengan is_default false
const hasHiddenPoints = computed(() => {
  return (props.category.points || []).some(p => p.is_default === false);
});

const filteredPoints = computed(() => {
  console.log('CategorySection - Filtering points:', {
    categoryName: props.category.name,
    isDamageMenu: props.category.isDamageMenu,
    allPoints: props.category.points,
    allPointsCount: props.category.points?.length
  });

  // Kalau bukan damage menu
  if (!props.category.isDamageMenu) {
    const filtered = (props.category.points || []).filter(point => {
      const pointId = point.inspection_point?.id
      const hasData = hasPointData(pointId)

      // kalau showHidden aktif → semua tampil
      if (showHidden.value) return true

      // tampilkan kalau default atau ada data
      if (point.is_default) return true
      if (hasData) return true

      console.log(`Non-damage Point ${pointId}: is_default=${point.is_default}, hasData=${hasData}`)
      return false
    })

    console.log('Filtered non-damage points:', filtered)
    return filtered
  }

  // Kalau damage menu (aturan lama, tidak diubah)
  const filtered = (props.category.points || []).filter(point => {
    const pointId = point.inspection_point?.id
    const hasData = hasPointData(pointId)

    if (showHidden.value) return true
    if (point.is_default) return true
    if (!point.is_default && hasData) return true

    console.log(`Damage Point ${pointId}: hasData=${hasData}`)
    return hasData
  })

  console.log('Filtered damage points:', filtered)
  return filtered
})



// PERBAIKAN: Cek apakah point sudah memiliki data
const hasPointData = (pointId) => {
  if (!pointId) {
    console.log('hasPointData: pointId is null/undefined');
    return false;
  }
  
  // Cek di existingResults (data dari server)
  const hasServerResult = page.props.existingResults[pointId] !== undefined;
  
  // Cek di existingImages (data dari server)
  const hasServerImages = page.props.existingImages[pointId] && page.props.existingImages[pointId].length > 0;
  
  // Cek di form results (data lokal yang belum disimpan)
  const hasLocalResult = props.form.results[pointId] && 
                        (props.form.results[pointId].status || props.form.results[pointId].note);
  
  // Cek di form images (data lokal yang belum disimpan)
  const hasLocalImages = props.form.images[pointId] && props.form.images[pointId].length > 0;

  const result = hasServerResult || hasServerImages || hasLocalResult || hasLocalImages;

  console.log(`Point ${pointId} data check:`, {
    hasServerResult,
    hasServerImages,
    hasLocalResult,
    hasLocalImages,
    finalResult: result
  });
  
  return result;
};

const defaultRadioOptions = [
  { value: 'good', label: 'Good' },
  { value: 'bad', label: 'Bad' },
  { value: 'na', label: 'N/A' }
];

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

// Watcher untuk reset state ketika point dihapus
watch(() => props.form.results, (newResults) => {
  // Cek jika hasil untuk point tertentu dihapus
  Object.keys(newResults).forEach(pointId => {
    if (!newResults[pointId] || (!newResults[pointId].status && !newResults[pointId].note)) {
      // Reset state lokal jika perlu
    }
  });
}, { deep: true });

// Watcher untuk images
watch(() => props.form.images, (newImages) => {
  Object.keys(newImages).forEach(pointId => {
    if (!newImages[pointId] || newImages[pointId].length === 0) {
      // Reset state lokal jika perlu
    }
  });
}, { deep: true });

const updateResult = (pointId, value, type) => {
  emit('updateResult', { pointId, type, value });
};

const saveResult = (pointId) => {
  emit('saveResult', pointId);
};

const removeImage = (pointId, imageIndex) => {
  emit('removeImage', { pointId, imageIndex });
};

const HapusPoint = (pointId) => {
  emit("hapusPoint", pointId);
};
</script>

<style scoped>
/* Mobile-first styles */
@media (min-width: 640px) {
  .point-card {
    padding: 1.25rem;
  }
}
</style>