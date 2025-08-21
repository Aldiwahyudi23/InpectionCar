<template>
  <div class="mt-2 space-y-4">
    <!-- Radio Options -->
      <!-- Radio Options - Grid Layout untuk keseragaman -->
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-2 w-full">
      <label
        v-for="(option, index) in options"
        :key="index"
        class="cursor-pointer"
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
          class="w-full px-4 py-3 border rounded-lg text-center transition-colors whitespace-nowrap text-sm font-medium"
          :class="{
            'border-indigo-500 bg-indigo-50 text-indigo-700': modelValue === option.value,
            'border-gray-300 text-gray-700 hover:bg-gray-50': modelValue !== option.value
          }"
        >
          {{ option.label }}
        </div>
      </label>
    </div>

    <!-- Display saved data - Hanya muncul jika radio memiliki settings -->
    <div 
      v-if="modelValue && selectedOption && hasSettings(selectedOption)" 
      class="mt-4 p-4 border rounded-lg bg-gray-50"
    >
      <div class="flex justify-between items-start">
        <h4 class="text-sm font-medium text-gray-700">Detail : </h4>
        <button
          @click="openOptionModal"
          class="text-sm text-indigo-600 hover:text-indigo-800"
        >
          Edit
        </button>
      </div>
      
      <!-- Display images -->

  <div
    v-if="images.length > 0 && selectedOption.settings?.show_image_upload"
    class="mt-3"
  >
    <div class="flex gap-2 overflow-x-auto" style="scrollbar-width: none">
      <div
        v-for="(image, index) in images"
        :key="index"
        class="relative flex-shrink-0"
        style="width: 80px; height: 80px;"
      >
        <img
          :src="getImageSrc(image)"
          class="w-full h-full object-cover rounded"
        />
        <span
          v-if="index === 3 && images.length > 4"
          class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center text-white text-xs"
        >
          +{{ images.length - 4 }}
        </span>
      </div>
    </div>
  </div>

      
      <!-- Display notes -->
      <div v-if="notes && selectedOption.settings?.show_textarea" class="mt-2">
        <p class="text-sm text-gray-600">{{ notes }}</p>
      </div>
    </div>

    <!-- Modal untuk opsi yang dipilih -->
    <BottomSheetModal
      :show="showOptionModal"
      :title="pointName || 'Detail'"
      :subtitle="selectedOption?.description"
      @close="closeOptionModal"
    >
      <div class="space-y-4">
        <!-- Textarea Section -->
        <div v-if="selectedOption?.settings?.show_textarea" class="space-y-2">
          <Textarea
            :model-value="tempNotes"
            :point-id="pointId"
            :inspection-id="inspectionId"
            :settings="selectedOption.settings"
            :required="required"
            :min-length="selectedOption.settings?.min_length"
            :max-length="selectedOption.settings?.max_length"
            :placeholder="selectedOption.settings?.placeholder || 'Tambahkan keterangan...'"
            @update:modelValue="val => { tempNotes = val; }"
            @save="handleTextareaSave"
          />
        </div>

        <!-- Image Upload Section -->
        <div v-if="selectedOption?.settings?.show_image_upload" class="mt-4">
          <h4 class="text-sm font-medium text-gray-700 mb-2">Upload Foto:</h4>
          <InputImage
            :model-value="tempImages"
            :point-id="pointId"
            :inspection-id="inspectionId"
            :settings="selectedOption.settings"
            @update:modelValue="val => { tempImages = val; handleImageUpdate(val); }"
            @save="handleImageSave"
          />
        </div>
      </div>

      <template #footer>
        <div class="flex gap-2">
          <button
            type="button"
            @click="closeOptionModal"
            class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Batal
          </button>
          <button
            type="button"
            @click="saveAllData"
            class="flex-1 px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Simpan
          </button>
        </div>
      </template>
    </BottomSheetModal>

    <p v-if="error" class="mt-2 text-sm text-red-600">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import InputImage from './InputImage.vue';
import Textarea from './InputTextarea.vue';
import BottomSheetModal from './BottomSheetModal.vue';

const props = defineProps({
  modelValue: String,
  required: Boolean,
  error: String,
  pointId: [String, Number],
  pointName: String, // Tambahkan prop untuk nama point
  inspectionId: [String, Number],
  options: {
    type: Array,
    default: () => []
  },
  notes: { type: String, default: '' },
  images: { type: Array, default: () => [] }
});

const emit = defineEmits([
  'update:modelValue',
  'update:notes',
  'update:images',
  'save'
]);

// reactive local state
const notesValue = ref(props.notes);
const imageValues = ref([...props.images]);
const showOptionModal = ref(false);
const tempNotes = ref('');
const tempImages = ref([]);
const originalImages = ref([]);

// computed
const selectedOption = computed(() =>
  props.options.find(opt => opt.value === props.modelValue)
);

// Fungsi untuk mengecek apakah option memiliki settings
const hasSettings = (option) => {
  return option.settings && (
    option.settings.show_textarea || 
    option.settings.show_image_upload
  );
};

// watch for props changes
watch(() => props.notes, (val) => {
  notesValue.value = val;
});
watch(() => props.images, (val) => {
  imageValues.value = [...val];
});

// handler radio change
const handleRadioChange = (option) => {
  emit('update:modelValue', option.value);
  
  // Open modal if option has additional settings
  if (hasSettings(option)) {
    openOptionModal();
  } else {
    // Save immediately if no additional inputs
    emit('save', { 
      pointId: props.pointId, 
      inspectionId: props.inspectionId, 
      value: option.value,
      notes: '',
      images: []
    });
  }
};

// Helper function untuk mendapatkan source gambar
const getImageSrc = (image) => {
  return image.preview || (image.image_path ? `/${image.image_path}` : image);
};

// modal functions
const openOptionModal = () => {
  tempNotes.value = notesValue.value;
  tempImages.value = [...imageValues.value];
  originalImages.value = [...imageValues.value];
  showOptionModal.value = true;
};

const closeOptionModal = () => {
  // Revert to original values if cancelled
  tempNotes.value = notesValue.value;
  tempImages.value = [...imageValues.value];
  showOptionModal.value = false;
};

// Handler untuk update gambar
const handleImageUpdate = (val) => {
  tempImages.value = val;
  // Gambar langsung tersimpan saat diupload
  imageValues.value = [...val];
  emit('update:images', [...val]);
  emit('save', { 
    pointId: props.pointId, 
    inspectionId: props.inspectionId, 
    value: props.modelValue,
    notes: notesValue.value,
    images: [...val]
  });
};

// Handler untuk save gambar
const handleImageSave = (data) => {
  // Gambar sudah langsung tersimpan saat diupload melalui handleImageUpdate
};


// Simpan semua data
const saveAllData = () => {
  // Simpan notes
  notesValue.value = tempNotes.value;
  emit('update:notes', tempNotes.value);
  
  // Gambar sudah tersimpan melalui handleImageUpdate
  
  // Kirim semua data untuk disimpan
  emit('save', { 
    pointId: props.pointId, 
    inspectionId: props.inspectionId, 
    value: props.modelValue,
    notes: tempNotes.value,
    images: tempImages.value
  });
  
  // Tutup modal
  showOptionModal.value = false;
};
</script>