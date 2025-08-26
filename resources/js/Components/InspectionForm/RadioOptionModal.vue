<template>
  <BottomSheetModal
    :show="show"
    :title="title"
    :subtitle="subtitle"
    @close="$emit('close')"
  >
    <div class="space-y-4">
      <div v-if="point.input_type === 'imageTOradio'" class="mt-2">
        <h4 class="text-sm font-medium text-gray-700 mb-2">
          <span v-if="point.settings?.required" class="text-red-500">*</span>
        </h4>
        <InputImage
          :model-value="imagesValue"
          :point-id="pointId"
          :inspection-id="inspectionId"
          :settings="point.settings"
          @update:modelValue="$emit('update:imagesValue', $event)"
          @save="$emit('saveImage', $event)"
        />
        <p v-if="point.settings?.required && imagesValue.length === 0" class="text-xs text-red-500">
          Foto wajib diupload
        </p>
      </div>
      
      <!-- Radio Options di dalam Modal -->
      <div class="grid grid-cols-2 gap-2">
        <label
          v-for="(option, index) in options"
          :key="index"
          class="cursor-pointer"
        >
          <input
            type="radio"
            :name="name"
            :value="option.value"
            :checked="selectedValue === option.value"
            @change="$emit('update:selectedValue', $event.target.value)"
            class="hidden peer"
          />
          <div
            class="w-full px-4 py-3 border rounded-lg text-center transition-colors whitespace-nowrap text-sm font-medium"
            :class="{
              'border-indigo-500 bg-indigo-50 text-indigo-700': selectedValue === option.value,
              'border-gray-300 text-gray-700 hover:bg-gray-50': selectedValue !== option.value
            }"
          >
            {{ option.label }}
          </div>
        </label>
      </div>

      <!-- Textarea Section -->
      <div v-if="showTextarea && selectedOption?.settings?.show_textarea" class="space-y-2">
        <span v-if="selectedOption.settings?.required" class="text-red-500">*</span>
        <Textarea
          :model-value="notesValue"
          :point-id="pointId"
          :inspection-id="inspectionId"
          :settings="selectedOption.settings"
          :required="selectedOption.settings?.required"
          :min-length="selectedOption.settings?.min_length"
          :max-length="selectedOption.settings?.max_length"
          :placeholder="selectedOption.settings?.placeholder || 'Tambahkan keterangan...'"
          @update:modelValue="$emit('update:notesValue', $event)"
          @save="$emit('saveTextarea', $event)"
        />
        <p v-if="selectedOption.settings?.required && !notesValue" class="text-xs text-red-500">
          Keterangan wajib diisi
        </p>
      </div>

      <!-- Image Upload Section -->
      <div v-if="showImageUpload && selectedOption?.settings?.show_image_upload" class="mt-4">
        <h4 class="text-sm font-medium text-gray-700 mb-2">
          Upload Foto
          <span v-if="selectedOption.settings?.required" class="text-red-500">*</span>
        </h4>
        <InputImage
          :model-value="imagesValue"
          :point-id="pointId"
          :inspection-id="inspectionId"
          :settings="selectedOption.settings"
          @update:modelValue="$emit('update:imagesValue', $event)"
          @save="$emit('saveImage', $event)"
        />
        <p v-if="selectedOption.settings?.required && imagesValue.length === 0" class="text-xs text-red-500">
          Foto wajib diupload
        </p>
      </div>
    </div>
    
    <template #footer>
      <!-- Tombol Hapus Data (hanya muncul jika ada selectedPoint) -->
      <button 
        v-if="selectedPoint" 
        @click="hapusPoint(pointId)"
        class="flex items-center justify-center text-red-600 hover:text-red-800 mb-3"
      >
        <svg xmlns="http://www.w3.org/2000/svg" 
             class="h-5 w-5 mr-1" 
             fill="none" 
             viewBox="0 0 24 24" 
             stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2-3H7m5 0v3" />
        </svg>
        Hapus Data
      </button>

      <!-- Tombol Batal & Simpan -->
      <div class="flex gap-2">
        <button
          type="button"
          @click="$emit('close')"
          class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Batal
        </button>
        <button
          type="button"
          @click="$emit('save')"
          :disabled="!isFormValid"
          class="flex-1 px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-gray-300 disabled:cursor-not-allowed"
        >
          Simpan
        </button>
      </div>
    </template>
  </BottomSheetModal>
</template>

<script setup>
import { computed } from 'vue';
import InputImage from './InputImage.vue';
import Textarea from './InputTextarea.vue';
import BottomSheetModal from './BottomSheetModal.vue';

const props = defineProps({
  show: Boolean,
  title: String,
  subtitle: String,
  name: String,
  options: {
    type: Array,
    default: () => []
  },
  selectedValue: String,
  notesValue: String,
  imagesValue: {
    type: Array,
    default: () => []
  },
  pointId: [String, Number],
  inspectionId: [String, Number],
  showTextarea: {
    type: Boolean,
    default: true
  },
  showImageUpload: {
    type: Boolean,
    default: true
  },
  selectedPoint: Object,
  point: Object
});

const emit = defineEmits([
  'update:selectedValue',
  'update:notesValue',
  'update:imagesValue',
  'close',
  'save',
  'hapus',
  'saveTextarea',
  'saveImage'
]);

const hapusPoint = (pointId) => {
  emit("hapus", pointId);
};

const selectedOption = computed(() =>
  props.options.find(opt => opt.value === props.selectedValue)
);

// Validasi form yang dinamis berdasarkan kondisi
const isFormValid = computed(() => {
  // Validasi dasar: harus memilih opsi radio
  if (!props.selectedValue) return false;
  
  const option = selectedOption.value;
  if (!option || !option.settings) return true;
  
  // Validasi untuk input_type 'imageTOradio'
  if (props.point.input_type === 'imageTOradio') {
    if (props.point.settings?.required && props.imagesValue.length === 0) {
      return false;
    }
    return true;
  }
  
  // Validasi textarea jika diaktifkan dan required
  if (option.settings.show_textarea ) {
    if (!props.notesValue || props.notesValue.trim() === '') {
      return false;
    }
    
    // Validasi min length jika diatur
    if (option.settings.min_length && props.notesValue.length < option.settings.min_length) {
      return false;
    }
    
    // Validasi max length jika diatur
    if (option.settings.max_length && props.notesValue.length > option.settings.max_length) {
      return false;
    }
  }
  
  // Validasi image upload jika diaktifkan dan required
  if (option.settings.show_image_upload ) {
    if (props.imagesValue.length === 0) {
      return false;
    }
    
    // Validasi max files jika diatur
    if (option.settings.max_files && props.imagesValue.length > option.settings.max_files) {
      return false;
    }
  }
  
  return true;
});
</script>