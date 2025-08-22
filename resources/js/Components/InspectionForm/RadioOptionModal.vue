<template>
  <BottomSheetModal
    :show="show"
    :title="title"
    :subtitle="subtitle"
    @close="$emit('close')"
  >
    <div class="space-y-4">
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
            @change="$emit('update:selectedValue', option.value)"
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
  }
});

const emit = defineEmits([
  'update:selectedValue',
  'update:notesValue',
  'update:imagesValue',
  'close',
  'save',
  'saveTextarea',
  'saveImage'
]);

const selectedOption = computed(() =>
  props.options.find(opt => opt.value === props.selectedValue)
);

// Validasi form
const isFormValid = computed(() => {
  if (!props.selectedValue) return false;
  
  const option = selectedOption.value;
  if (!option || !option.settings) return true;
  
  // Validasi textarea jika required
  if (option.settings.show_textarea && option.settings.required && !props.notesValue) {
    return false;
  }
  
  // Validasi image jika required
  if (option.settings.show_image_upload && option.settings.required && props.imagesValue.length === 0) {
    return false;
  }
  
  return true;
});
</script>