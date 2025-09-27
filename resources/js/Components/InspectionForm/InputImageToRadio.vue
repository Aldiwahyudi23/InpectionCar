<template>
  <div class="mt-2 space-y-2">
    <!-- Jika belum ada data tampil tombol tambah -->
    <div v-if="!modelValue && imageValues.length === 0 && !notesValue" class="flex flex-wrap gap-3">
      <div 
        class="relative w-28 h-28 border-2 border-dashed rounded-lg flex items-center justify-center cursor-pointer overflow-hidden"
        @click="openOptionModal"
      >
        <div class="flex flex-col items-center text-gray-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span class="text-xs font-medium">Tambah</span>
        </div>
      </div>
    </div>

    <!-- Jika sudah ada data tampil display -->
    <div v-else class="p-2 ">
      <div class="flex justify-between items-start">
        <h4 class="text-sm font-medium text-gray-700">Detail :</h4>
        <button
          @click="openOptionModal"
          class="text-sm text-indigo-600 hover:text-indigo-800"
        >
          Edit
        </button>
      </div>

      <!-- Display Images -->
      <div v-if="imageValues.length > 0" class="mt-2">
        <div class="flex gap-2 overflow-x-auto scrollbar-hide">
          <div
            v-for="(image, index) in imageValues"
            :key="index"
            class="relative flex-shrink-0"
            style="width: 130px; height: 130px;"
          >
            <img :src="getImageSrc(image)" class="w-full h-full object-cover rounded" />
            <span
              v-if="index === 3 && imageValues.length > 4"
              class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center text-white text-xs"
            >
              +{{ imageValues.length - 4 }}
            </span>
          </div>
        </div>
      </div>

      <!-- Display Radio (non-editable) -->

    <div 
   class="grid gap-2 w-full mt-2"
  :class="`grid-cols-${Math.min(options.length, 3)}`"
    >
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
          :disabled="true"
          class="hidden peer"
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

      <!-- Display Notes -->
      <div v-if="notesValue && selectedOption.settings?.show_textarea" class="mt-2">
        <p class="text-sm text-gray-600">{{ notesValue }}</p>
      </div>
    </div>

    <!-- Modal -->
    <RadioOptionModal
      v-if="showOptionModal"
      :key="selectedPoint?.id"
      :show="showOptionModal"
      :title="pointName || 'Detail'"
      :subtitle="selectedOption?.description"
      :name="'modal-radio-' + pointId"
      :options="options"
      :selected-value="tempRadioValue"
      :notes-value="tempNotes"
      :images-value="tempImages"
      :point-id="pointId"
      :point="point"
      :inspection-id="inspectionId"
      :selected-point="selectedPoint"
      @update:selectedValue="tempRadioValue = $event"
      @update:notesValue="tempNotes = $event"
      @update:imagesValue="handleImageUpdate($event)"
      @close="closeOptionModal"
      @save="saveAllData"
      @hapus="HapusPoint(pointId)"
    />

    <p v-if="error" class="mt-2 text-sm text-red-600">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import RadioOptionModal from './RadioOptionModal.vue';

const props = defineProps({
  modelValue: String,
  required: Boolean,
  error: String,
  point: Object,
  pointId: [String, Number],
  pointName: String,
  inspectionId: [String, Number],
  options: { type: Array, default: () => [] },
  selectedPoint: Object,
  notes: { type: String, default: '' },
  images: { type: Array, default: () => [] }
});

const emit = defineEmits([
  'update:modelValue',
  'update:notes',
  'update:images',
  'save',
  'hapus'
]);

const notesValue = ref(props.notes);
const imageValues = ref([...props.images]);
const showOptionModal = ref(false);
const tempNotes = ref('');
const tempImages = ref([]);
const tempRadioValue = ref('');
const originalImages = ref([]);

const selectedOption = computed(() =>
  props.options.find(opt => opt.value === props.modelValue)
);

const getImageSrc = (image) => {
  return image.preview || (image.image_path ? `/${image.image_path}` : image);
};

// watch for props changes
watch(() => props.notes, (val) => {
  notesValue.value = val;
});

const openOptionModal = () => {
  tempRadioValue.value = props.modelValue || '';
  tempNotes.value = notesValue.value;
  tempImages.value = [...imageValues.value];
  originalImages.value = [...imageValues.value];
  showOptionModal.value = true;
};

const closeOptionModal = () => {
  tempNotes.value = '';
  tempImages.value = [];
  tempRadioValue.value = '';
  showOptionModal.value = false;
};

const handleImageUpdate = (val) => {
  tempImages.value = val;
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

const saveAllData = () => {
  if (tempRadioValue.value !== props.modelValue) {
    emit('update:modelValue', tempRadioValue.value);
  }
  notesValue.value = tempNotes.value;
  emit('update:notes', tempNotes.value);
  imageValues.value = [...tempImages.value];
  emit('update:images', [...tempImages.value]);
  emit('save', {
    pointId: props.pointId,
    inspectionId: props.inspectionId,
    value: tempRadioValue.value,
    notes: tempNotes.value,
    images: tempImages.value
  });
  showOptionModal.value = false;
};

const HapusPoint = (pointId) => {
  // Reset semua state lokal
  tempRadioValue.value = '';
  tempNotes.value = '';
  tempImages.value = [];
  notesValue.value = '';
  imageValues.value = [];
  
  // Emit ke parent untuk reset state dan hapus data
  emit('update:modelValue', '');
  emit('update:notes', '');
  emit('update:images', []);
  emit('hapus', pointId); // Ini yang memanggil hapusData di parent
  
  showOptionModal.value = false;
};
</script>

<style>
/* hide scrollbar horizontal */
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
