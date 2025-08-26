<template>
  <div>
    <input
      ref="galleryInput"
      type="file"
      accept="image/*"
      class="hidden"
      @change="handleImageSelect"
      :multiple="allowMultiple"
      :disabled="isUploading"
    />

    <canvas ref="processingCanvas" class="hidden"></canvas>

    <!-- Upload Trigger -->
    <UploadTrigger 
      v-if="allImages.length === 0"
      @click="openSourceOptions"
      :isUploading="isUploading"
      :maxFiles="settings.max_files"
      :settings="settings"
    />

    <!-- Image Gallery -->
    <ImageGallery
      v-else
      :images="allImages"
      :allowMultiple="allowMultiple"
      :maxFiles="settings.max_files"
      :isUploading="isUploading"
      @openPreview="openPreviewModal"
      @removeImage="removeImage"
      @addMore="openSourceOptions"
    />

    <!-- Error Message -->
    <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>

    <!-- Modals -->
    <ImageSourceOptionsModal
      :show="showSourceOptionsModal"
      @close="closeSourceOptions"
      @open-webcam="openWebcam"
      @trigger-gallery="triggerGallery"
    />

    <WebcamModal
      :show="showWebcamModal"
      :aspect-ratio="aspectRatio"
      :settings="settings"
      @close="closeWebcam"
      @photo-captured="handlePhotoCaptured"
    />

    <PreviewModal
      :show="showPreviewModal"
      :images="allImages"
      :initial-index="currentPreviewIndex"
      :allow-multiple="allowMultiple"
      :max-files="settings.max_files"
      :is-uploading="isUploading"
      :aspect-ratio="aspectRatio"
      @close="closePreviewModal"
      @save-images="triggerUploadAndSave"
      @remove-preview-image="handleRemovePreviewImage"
      @trigger-add-more-photos="openSourceOptions"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import ImageSourceOptionsModal from './Modal-uploader/ImageSourceOptionsModal.vue';
import WebcamModal from './Modal-uploader/WebcamModal.vue';
import PreviewModal from './Modal-uploader/PreviewModal.vue';
import UploadTrigger from './ImageUploader/UploadTrigger.vue';
import ImageGallery from './ImageUploader/ImageGallery.vue';
import axios from 'axios';
import { useImageHandling } from '@/Composables/useImageHandling';
import { useModalHandling } from '@/Composables/useModalHandling';
import { useUploadHandling } from '@/Composables/useUploadHandling';

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  error: String,
  inspectionId: { type: [String, Number], required: true },
  pointId: { type: [String, Number], required: true },
  settings: {
    type: Object,
    default: () => ({
      max_files: 1,
      allowed_types: ['jpg', 'png'],
      camera_aspect_ratio: '3:4',
      enable_flash: true,
      enable_camera_switch: true,
    })
  }
});

const emit = defineEmits(['update:modelValue', 'save', 'removeImage', 'uploaded']);

// Refs
const galleryInput = ref(null);
const processingCanvas = ref(null);
const previewImages = ref([]);
const currentPreviewIndex = ref(0);
const isUploading = ref(false);

// Computed properties
const allowMultiple = computed(() => Number(props.settings.max_files) > 1);
const aspectRatio = computed(() => {
  const parts = props.settings.camera_aspect_ratio.split(':');
  if (parts.length === 2) {
    const width = parseFloat(parts[0]);
    const height = parseFloat(parts[1]);
    if (!isNaN(width) && !isNaN(height) && height !== 0) {
      return width / height;
    }
  }
  return 4 / 3;
});

const allImages = computed(() => {
  const finalImages = [];
  const processedIds = new Set();

  // Add new/rotated images from preview
  for (const pImg of previewImages.value) {
    if (pImg.id) processedIds.add(pImg.id);
    finalImages.push(pImg);
  }

  // Add existing images from modelValue
  for (const mImg of props.modelValue) {
    if (!processedIds.has(mImg.id)) {
      finalImages.push({
        ...mImg,
        rotation: mImg.rotation || 0,
        isNew: false,
        preview: mImg.preview || (mImg.image_path ? `/${mImg.image_path}` : null)
      });
    }
  }

  return finalImages;
});

// Composable functions
const { 
  showSourceOptionsModal, 
  showWebcamModal, 
  showPreviewModal,
  openSourceOptions, 
  closeSourceOptions, 
  openWebcam, 
  closeWebcam, 
  openPreviewModal, 
  closePreviewModal 
} = useModalHandling();

const { 
  loadImageWithDimensions, 
  applyRotationToImage, 
  getImageSrc, 
  getImageContainerStyle 
} = useImageHandling(processingCanvas);

const {
  handleImageSelect,
  handlePhotoCaptured,
  triggerUploadAndSave,
  removeImage,
  handleRemovePreviewImage
} = useUploadHandling({
  props,
  emit,
  galleryInput,
  previewImages,
  allImages,
  currentPreviewIndex,
  isUploading,
  allowMultiple,
  loadImageWithDimensions,
  applyRotationToImage,
  openSourceOptions,
  closeSourceOptions,
  openWebcam,
  closeWebcam,
  openPreviewModal
});

// Expose function to trigger gallery
const triggerGallery = () => {
  showSourceOptionsModal.value = false;
  galleryInput.value.click();
};
</script>