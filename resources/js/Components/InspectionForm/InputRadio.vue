<template>
  <div class="mt-2 space-y-4">
    <div class="flex flex-wrap sm:flex-nowrap overflow-x-auto pb-2 gap-4 w-full justify-start sm:justify-start">
      <label
        v-for="option in options"
        :key="option.value"
        class="cursor-pointer flex-shrink-0"
      >
        <input
          type="radio"
          :name="'radio-' + pointId"
          :value="option.value"
          :checked="modelValue === option.value"
          @change="handleRadioChange(option)"
          class="hidden peer"
          :required="required"
        />
        <div
          class="px-4 py-2 border rounded-lg peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors whitespace-nowrap"
        >
          {{ option.label }}
        </div>
      </label>
    </div>

    <div
      v-if="modelValue === selectedOption?.value && (selectedOption?.settings?.show_textarea || selectedOption?.settings?.show_image_upload)"
      class="mt-4 p-4 border rounded-lg bg-gray-50 shadow-sm"
    >
      <h4 class="text-md font-semibold text-gray-800 mb-2">Detail Pilihan Anda:</h4>
      
      <div v-if="currentResultData.notes && selectedOption?.settings?.show_textarea" class="mb-3">
        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan:</label>
        <p class="text-gray-900 text-sm bg-white p-2 rounded-md border border-gray-200">{{ currentResultData.notes }}</p>
      </div>

      <div v-if="currentResultData.images && currentResultData.images.length > 0 && selectedOption?.settings?.show_image_upload">
        <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Terlampir:</label>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
          <div v-for="image in currentResultData.images" :key="image.id" class="relative group aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-lg bg-gray-200 flex items-center justify-center">
            <img 
              :src="image.url || image.path" 
              :alt="image.name || 'Gambar inspeksi'" 
              class="object-cover w-full h-full"
              @click="openImagePreview(image)"
            />
            </div>
        </div>
      </div>
      
      <p v-else-if="selectedOption?.settings?.show_image_upload && (!currentResultData.images || currentResultData.images.length === 0)" class="text-sm text-gray-500 mt-2">
        Belum ada gambar yang dilampirkan.
      </p>
    </div>


    <div v-if="showModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 space-y-6">
          <div class="flex justify-between items-center border-b pb-4">
            <h3 class="text-lg font-medium text-gray-900">
              {{ selectedOption?.label }} - Detail Inspeksi
            </h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div
            v-if="selectedOption?.settings?.show_image_upload"
            class="border rounded-lg p-4 bg-gray-50"
          >
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Upload Images
            </label>
            <InputImage
              v-model="tempImages"
              :point-id="pointId"
              :inspection-id="inspectionId"
              :settings="{
                max_files: selectedOption?.settings?.image_max_files || 1,
                max_size: selectedOption?.settings?.image_max_size || 2048,
                allowed_types: selectedOption?.settings?.image_allowed_types || ['jpg', 'png'],
                camera_aspect_ratio: selectedOption?.settings?.camera_aspect_ratio || '3:4',
                enable_flash: selectedOption?.settings?.enable_flash !== undefined ? selectedOption.settings.enable_flash : true,
                enable_camera_switch: selectedOption?.settings?.enable_camera_switch !== undefined ? selectedOption.settings.enable_camera_switch : true,
              }"
              @remove-image="handleImageRemovedFromInput"
            />
          </div>

          <div
            v-if="selectedOption?.settings?.show_textarea"
            class="border rounded-lg p-4 bg-gray-50"
          >
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Additional Notes
              <span class="text-xs text-gray-500 float-right">{{ textareaValue.length }}/{{ selectedOption?.settings?.textarea_max_length || 400 }}</span>
            </label>
            <textarea
              v-model="textareaValue"
              :placeholder="selectedOption?.settings?.textarea_placeholder || 'Enter your notes here...'"
              :minlength="selectedOption?.settings?.textarea_min_length"
              :maxlength="selectedOption?.settings?.textarea_max_length"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              rows="4"
            ></textarea>
          </div>

          <div class="flex justify-end space-x-3 pt-4 border-t">
            <button
              @click="closeModal"
              type="button"
              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Batal
            </button>
            <button
              @click="saveInspection"
              type="button"
              class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
            >
              Simpan
            </button>
          </div>
        </div>
      </div>
    </div>

    <p v-if="error" class="mt-2 text-sm text-red-600">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import InputImage from './InputImage.vue'; // PASTIKAN PATH INI BENAR!

const props = defineProps({
  modelValue: String, 
  required: Boolean,
  error: String,
  pointId: [String, Number],
  inspectionId: {
    type: [String, Number],
    required: false
  },
  options: {
    type: Array,
    default: () => [
      { 
        value: 'good', 
        label: 'Baik',
        settings: {
          show_textarea: false,
          show_image_upload: false
        }
      },
      { 
        value: 'bad', 
        label: 'Tidak Normal',
        settings: {
          show_textarea: true,
          show_image_upload: true,
          textarea_placeholder: 'Jelaskan kondisi ini lebih lanjut',
          textarea_min_length: 10,
          textarea_max_length: 400,
          max_files: 3,
          max_size: 2048,
          allowed_types: ['jpg', 'png', 'jpeg'],
          camera_aspect_ratio: '3:4',
          enable_flash: true,
          enable_camera_switch: true
        }
      },
      { 
        value: 'na', 
        label: 'N/A',
        settings: {
          show_textarea: false,
          show_image_upload: false
        }
      }
    ]
  },
  inspectionResult: { // Ini adalah data hasil inspeksi untuk pointId ini
    type: Object,
    default: () => ({ value: null, notes: '', images: [] })
  },
});

const emit = defineEmits([
  'update:modelValue',
  'update:inspectionResult',
  'save'
]);

const showModal = ref(false);
const textareaValue = ref(''); 
const tempImages = ref([]); 
const currentRadioValue = ref(props.modelValue);

// State lokal untuk menyimpan hasil inspeksi yang akan ditampilkan di UI utama
const currentResultData = ref(props.inspectionResult || { value: null, notes: '', images: [] });

const selectedOption = computed(() => {
  return props.options.find(opt => opt.value === currentRadioValue.value);
});

// Sinkronkan currentRadioValue dengan modelValue prop dari parent
watch(() => props.modelValue, (newVal) => {
    currentRadioValue.value = newVal;
});

// Sinkronkan currentResultData dengan inspectionResult prop dari parent
// Ini penting agar tampilan di UI utama selalu up-to-date dengan data dari parent
watch(() => props.inspectionResult, (newVal) => {
    currentResultData.value = newVal || { value: null, notes: '', images: [] };
}, { immediate: true, deep: true }); // deep: true untuk mendeteksi perubahan di dalam objek images/notes

watch(showModal, (newVal) => {
    if (newVal) {
        // Ketika modal dibuka, inisialisasi tempImages dan textareaValue dari currentResultData
        textareaValue.value = currentResultData.value.notes || '';
        tempImages.value = currentResultData.value.images ? [...currentResultData.value.images] : [];
    }
});


const handleRadioChange = (option) => {
  currentRadioValue.value = option.value;
  emit('update:modelValue', option.value); // Emit modelValue segera

  if (!option.settings.show_textarea && !option.settings.show_image_upload) {
    // Langsung simpan jika tidak perlu input tambahan
    const result = {
        value: option.value,
        notes: null,
        images: []
    };
    emit('update:inspectionResult', result);
    currentResultData.value = result; // Update state lokal untuk tampilan
    emit('save', props.pointId);
    showModal.value = false;
  } else {
    // Buka modal jika perlu input tambahan
    showModal.value = true;
    // tempImages dan textareaValue akan diinisialisasi oleh watcher saat modal dibuka
  }
};

const handleImageRemovedFromInput = ({ image }) => {
    console.log("Gambar dihapus dari InputImage (melalui event 'remove-image'):", image);
    // tempImages.value sudah diupdate oleh InputImage melalui v-model.
    // Jika Anda ingin tampilan di bawah radio juga langsung update,
    // Anda bisa memperbarui currentResultData.value.images di sini juga,
    // atau biarkan saveInspection yang melakukannya setelah modal ditutup.
    // Untuk konsistensi, saya sarankan biarkan saveInspection yang mengkonfirmasi.
};

const closeModal = () => {
  showModal.value = false;
  // Saat modal ditutup tanpa menyimpan, kita tidak meng-emit apa pun.
  // currentResultData tetap menunjukkan data yang terakhir disimpan/disinkronkan dari parent.
};

const saveInspection = () => {
  const result = {
    value: currentRadioValue.value,
    notes: null,
    images: []
  };
  
  if (selectedOption.value?.settings?.show_textarea) {
    result.notes = textareaValue.value;
  }
  
  if (selectedOption.value?.settings?.show_image_upload) {
    result.images = tempImages.value;
  }
  
  emit('update:inspectionResult', result); // Emit hasil final ke parent
  currentResultData.value = result; // Update state lokal untuk tampilan di UI utama
  emit('save', props.pointId); // Memicu simpan ke backend
  closeModal();
};

// Opsional: Untuk membuka preview gambar fullscreen saat diklik
// Anda mungkin membutuhkan komponen preview gambar terpisah atau library lightbox
const openImagePreview = (image) => {
  console.log("Membuka preview gambar:", image.url || image.path);
  // Logika untuk menampilkan gambar dalam modal preview/lightbox
  // Misalnya, emit event ke parent untuk menampilkan modal preview global
  // emit('openGlobalImagePreview', image);
};

</script>

<style scoped>
/* Optional: Add some transition effects */
label {
  transition: all 0.2s ease;
}

/* Penyesuaian untuk tampilan responsive radio options */
.flex-wrap {
    flex-wrap: wrap; 
}

.sm\:flex-nowrap {
    flex-wrap: nowrap;
}

.gap-4 {
    gap: 1rem; 
}

.flex-shrink-0 {
    flex-shrink: 0;
    min-width: 100px; 
    text-align: center; 
}

/* Styling untuk modal agar full-height di mobile */
.fixed.inset-0 {
  display: flex;
  align-items: center;
  justify-content: center;
}

.max-h-\[90vh\] {
  max-height: 90vh; 
}

.overflow-y-auto {
  overflow-y: auto; 
}

/* Aspect ratio for image placeholders (Tailwind CSS) */
.aspect-w-1 {
  --tw-aspect-w: 1;
}
.aspect-h-1 {
  --tw-aspect-h: 1;
}
.aspect-w-1\/1 {
  aspect-ratio: var(--tw-aspect-w, 1) / var(--tw-aspect-h, 1);
}
</style>