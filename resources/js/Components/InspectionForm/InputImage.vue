<template>
  <div>
    <input
      ref="galleryInput"
      type="file"
      :accept="allowedTypesString"
      class="hidden"
      @change="handleImageSelect"
      :multiple="allowMultiple"
      :disabled="isUploading"
    />

    <canvas ref="processingCanvas" class="hidden"></canvas>

    <label
      v-if="allImages.length === 0"
      @click="openSourceOptions"
      class="block w-full border-2 border-dashed rounded-lg cursor-pointer transition-colors duration-200 h-28"
      :class="{
        'border-gray-300 hover:border-indigo-400 bg-gray-50': true,
      }"
      :disabled="isUploading"
    >
      <div class="h-full flex flex-col items-center justify-center p-4 text-center">
        <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p class="text-sm text-gray-600 font-medium">Upload Image</p>
        <p class="text-xs text-gray-500">Click to open options</p>
        <p class="text-xs text-gray-400 mt-1">Allowed types: {{ allowedTypesString }}</p>
      </div>
    </label>

    <div
      v-else
      class="block w-full rounded-lg transition-colors duration-200 p-2"
      :class="{
        'border-2 border-dashed border-indigo-200 bg-indigo-50 h-auto p-2': settings.max_files == 1,
        'bg-transparent': settings.max_files > 1,
      }"
      aria-label="Image gallery"
    >
      <div v-if="settings.max_files === 1" class="flex items-center gap-4">
        <div 
          class="relative flex-shrink-0 w-24 h-24 overflow-hidden rounded-md border border-gray-200 cursor-pointer"
          @click="openPreviewModal(0)"
        >
          <img
            :src="getImageSrc(allImages[0])"
            class="w-full h-full object-cover"
          />
        </div>
        <button
          @click.stop="removeImage(allImages[0])"
          type="button"
          class="flex items-center gap-1 text-red-600 font-medium hover:text-red-800 transition-colors"
          :disabled="isUploading"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.095 21H7.905a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
          Hapus Foto
        </button>
      </div>

      <div v-else class="flex space-x-2 overflow-x-auto pb-2 scrollbar-hide">
        <div
          v-if="allowMultiple && allImages.length < settings.max_files"
          class="flex-shrink-0 flex flex-col items-center justify-center p-2 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-indigo-500 transition-colors w-24 h-24"
          @click="openSourceOptions"
          :disabled="isUploading"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <p class="mt-1 text-xs text-gray-600">Tambah</p>
        </div>

        <div
          v-for="(image, idx) in allImages"
          :key="image.id || image.preview" 
          class="relative flex-shrink-0 w-24 h-24 overflow-hidden rounded-md border border-gray-200 cursor-pointer"
          @click="openPreviewModal(idx)"
        >
          <img
            :src="getImageSrc(image)"
            class="w-full h-full object-cover"
          >
          <div v-if="image.isNew || image.rotation !== 0"
            class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center text-white text-xs font-bold">
            <span v-if="image.isNew">BARU</span>
            <span v-if="image.rotation !== 0" class="ml-1">ROTASI</span>
          </div>
        </div>
      </div>
    </div>

    <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>

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
      :point="point"
      @close="closeWebcam"
      @photo-captured="handlePhotoCaptured"
    />

    <PreviewModal
      :show="showPreviewModal"
      :images="allImages" 
      :point="point"
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
import { ref, computed, watch } from 'vue';
import ImageSourceOptionsModal from './Modal-uploader/ImageSourceOptionsModal.vue';
import WebcamModal from './Modal-uploader/WebcamModal.vue';
import PreviewModal from './Modal-uploader/PreviewModal.vue';
import axios from 'axios';

// Define props dan emits
const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  error: String,
  inspectionId: { type: [String, Number], required: true },
  pointId: { type: [String, Number], required: true },
  settings: {
    type: Object,
    default: () => ({
      max_files: 1,
      allowed_types: ['jpg', 'png', 'jpeg'],
      camera_aspect_ratio: '3:4',
      enable_flash: true,
      enable_camera_switch: true,
      max_size: 2048 // Default 2MB dalam KB
    })
  },
  point: Object
});

const emit = defineEmits(['update:modelValue', 'save', 'removeImage', 'uploaded']);

// Refs for DOM elements and state management
const galleryInput = ref(null);
const processingCanvas = ref(null);
const showSourceOptionsModal = ref(false);
const showWebcamModal = ref(false);
const showPreviewModal = ref(false);
const previewImages = ref([]);
const currentPreviewIndex = ref(0);
const isUploading = ref(false);

// Computed properties
const allowMultiple = computed(() => Number(props.settings.max_files) > 1);
const allowedTypesString = computed(() => {
  return props.settings.allowed_types.map(type => `.${type}`).join(', ');
});
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
  
  for (const pImg of previewImages.value) {
    if (pImg.id) processedIds.add(pImg.id);
    finalImages.push(pImg);
  }

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

// Functions
const getImageSrc = (image) => {
  return image.preview || (image.image_path ? `/${image.image_path}` : '');
};
const openSourceOptions = () => {
  if (showPreviewModal.value) showPreviewModal.value = false;
  showSourceOptionsModal.value = true;
};
const triggerGallery = () => {
  showSourceOptionsModal.value = false;
  galleryInput.value.click();
};
const openWebcam = () => {
  showSourceOptionsModal.value = false;
  showWebcamModal.value = true;
};
const closeSourceOptions = () => {
  showSourceOptionsModal.value = false;
  if (allImages.value.length > 0) showPreviewModal.value = true;
};
const closeWebcam = () => {
  showWebcamModal.value = false;
  if (allImages.value.length > 0) showPreviewModal.value = true;
};
const closePreviewModal = () => {
  showPreviewModal.value = false;
  previewImages.value.forEach(img => {
    if (img.preview && img.preview.startsWith('blob:')) {
      URL.revokeObjectURL(img.preview);
    }
  });
  previewImages.value = [];
};
const loadImageWithDimensions = (file) => {
  return new Promise((resolve) => {
    const reader = new FileReader();
    reader.onload = (e) => {
      const img = new Image();
      img.onload = () => {
        resolve({
          file,
          preview: URL.createObjectURL(file),
          rotation: 0,
          width: img.naturalWidth,
          height: img.naturalHeight,
          isNew: true
        });
      };
      img.onerror = () => {
        console.error("Failed to load image for dimensions:", file.name);
        resolve({ file, preview: URL.createObjectURL(file), rotation: 0, width: 0, height: 0, isNew: true });
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  });
};
const validateFileType = (file) => {
  const extension = file.name.split('.').pop().toLowerCase();
  return props.settings.allowed_types.includes(extension);
};
const compressAndSquareImage = async (file) => {
  const MAX_SIZE_KB = props.settings.max_size || 2048;
  const MAX_SIZE = MAX_SIZE_KB * 1024;
  
  return new Promise((resolve) => {
    const img = new Image();
    const reader = new FileReader();
    
    reader.onload = (e) => {
      img.onload = () => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const size = Math.max(img.width, img.height);
        canvas.width = size;
        canvas.height = size;
        
        ctx.fillStyle = 'white';
        ctx.fillRect(0, 0, size, size);
        
        const x = (size - img.width) / 2;
        const y = (size - img.height) / 2;
        
        ctx.drawImage(img, x, y, img.width, img.height);
        
        let quality = 0.9;
        
        const compress = () => {
          canvas.toBlob((blob) => {
            if (blob.size > MAX_SIZE && quality > 0.1) {
              quality -= 0.1;
              compress();
            } else {
              const compressedFile = new File(
                [blob], 
                file.name, 
                { 
                  type: 'image/jpeg', 
                  lastModified: Date.now() 
                }
              );
              resolve(compressedFile);
            }
          }, 'image/jpeg', quality);
        };
        
        compress();
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  });
};
const handleImageSelect = async (event) => {
  const files = Array.from(event.target.files);
  if (!files.length) {
    showSourceOptionsModal.value = false;
    if (allImages.value.length > 0) showPreviewModal.value = true;
    return;
  }

  try {
    const invalidFiles = files.filter(file => !validateFileType(file));
    if (invalidFiles.length > 0) {
      const invalidTypes = invalidFiles.map(f => f.name.split('.').pop()).join(', ');
      alert(`File type not allowed: ${invalidTypes}. Allowed types: ${props.settings.allowed_types.join(', ')}`);
      event.target.value = '';
      return;
    }

    const currentTotalImages = allImages.value.length;
    const allowedToAdd = props.settings.max_files - currentTotalImages;
    
    const compressedFiles = await Promise.all(
      files.slice(0, allowedToAdd).map(async (file) => {
        return await compressAndSquareImage(file);
      })
    );

    const newImages = await Promise.all(compressedFiles.map(loadImageWithDimensions));
    const imagesToProcess = allowMultiple.value ? newImages : newImages.slice(0, 1);

    if (!allowMultiple.value) {
      previewImages.value.forEach(img => {
        if (img.preview && img.preview.startsWith('blob:')) {
          URL.revokeObjectURL(img.preview);
        }
      });
      previewImages.value = imagesToProcess;
    } else {
      previewImages.value.push(...imagesToProcess);
    }
    
    showSourceOptionsModal.value = false;

    if (imagesToProcess.length > 0) {
      currentPreviewIndex.value = Math.max(0, allImages.value.length - imagesToProcess.length);
      showPreviewModal.value = true;
    } else if (allImages.value.length > 0) {
      showPreviewModal.value = true;
    }
    
    event.target.value = '';
  } catch (error) {
    console.error("Error processing selected images:", error);
    alert("Failed to process selected images. Please try again.");
    showSourceOptionsModal.value = false;
    if (allImages.value.length > 0) showPreviewModal.value = true;
  }
};
const handlePhotoCaptured = async (newImageFile) => {
  const currentTotalImages = allImages.value.length;
  if (!allowMultiple.value || currentTotalImages < props.settings.max_files) {
    try {
      const compressedFile = await compressAndSquareImage(newImageFile);
      
      const img = new Image();
      img.onload = () => {
        const newImage = {
          file: compressedFile,
          preview: URL.createObjectURL(compressedFile),
          rotation: 0,
          width: img.naturalWidth,
          height: img.naturalHeight,
          isNew: true
        };

        if (!allowMultiple.value) {
          previewImages.value.forEach(img => {
            if (img.preview && img.preview.startsWith('blob:')) {
              URL.revokeObjectURL(img.preview);
            }
          });
          previewImages.value = [newImage];
        } else {
          previewImages.value.push(newImage);
        }
        
        showWebcamModal.value = false;
        showSourceOptionsModal.value = false;
        currentPreviewIndex.value = allImages.value.length - 1;
        showPreviewModal.value = true;
      };
      img.src = URL.createObjectURL(compressedFile);
    } catch (error) {
      console.error("Error processing captured image:", error);
      alert("Failed to process captured image. Please try again.");
    }
  } else {
    alert(`Maximum ${props.settings.max_files} files allowed.`);
    showWebcamModal.value = false;
    showSourceOptionsModal.value = false;
    if (allImages.value.length > 0) showPreviewModal.value = true;
  }
};
const openPreviewModal = (initialIdx = 0) => {
  currentPreviewIndex.value = initialIdx;
  showPreviewModal.value = true;
};
const applyRotationToImage = (imageObject) => {
  return new Promise((resolve) => {
    if (imageObject.rotation === 0 || !processingCanvas.value) {
      if (!imageObject.isNew && !imageObject.file) {
        resolve({ file: null, width: imageObject.width, height: imageObject.height, isOriginal: true });
        return;
      }
      resolve({ file: imageObject.file, width: imageObject.width, height: imageObject.height });
      return;
    }

    const img = new Image();
    img.onload = async () => {
      try {
        const canvas = processingCanvas.value;
        const context = canvas.getContext('2d');
        const originalWidth = img.width;
        const originalHeight = img.height;

        let newCanvasWidth, newCanvasHeight;
        if (imageObject.rotation === 90 || imageObject.rotation === 270) {
          newCanvasWidth = originalHeight;
          newCanvasHeight = originalWidth;
        } else {
          newCanvasWidth = originalWidth;
          newCanvasHeight = originalHeight;
        }

        canvas.width = newCanvasWidth;
        canvas.height = newCanvasHeight;

        context.clearRect(0, 0, canvas.width, canvas.height);
        context.translate(canvas.width / 2, canvas.height / 2);
        context.rotate(imageObject.rotation * Math.PI / 180);
        context.drawImage(img, -originalWidth / 2, -originalHeight / 2, originalWidth, originalHeight);
        context.setTransform(1, 0, 0, 1, 0, 0);

        const blob = await new Promise(resolve => {
          canvas.toBlob(resolve, 'image/jpeg', 0.9);
        });

        const rotatedFile = new File(
          [blob], 
          imageObject.file ? imageObject.file.name : `rotated_image_${Date.now()}.jpg`, 
          { type: 'image/jpeg' }
        );

        const finalFile = await compressAndSquareImage(rotatedFile);
        resolve({ file: finalFile, width: newCanvasWidth, height: newCanvasHeight });
        
      } catch (error) {
        console.error("Error processing rotated image:", error);
        resolve({ file: imageObject.file, width: imageObject.width, height: imageObject.height });
      }
    };
    
    img.onerror = () => {
      console.error("Failed to load image for rotation processing:", imageObject.preview);
      resolve({ file: imageObject.file, width: imageObject.width, height: imageObject.height });
    };
    
    img.src = imageObject.preview;
  });
};

const triggerUploadAndSave = async (imagesToSaveFromPreview) => {
    isUploading.value = true;
    const finalUploadedImages = [];
    const newImagesToUpload = [];

    if (!props.pointId) {
        alert('Error: Point ID is missing. Cannot upload image.');
        isUploading.value = false;
        return;
    }

    try {
        for (const img of imagesToSaveFromPreview) {
            if (!img.isNew && img.rotation === 0 && img.id && img.image_path) {
                finalUploadedImages.push({
                    id: img.id,
                    image_path: img.image_path,
                    width: img.width,
                    height: img.height,
                    rotation: 0,
                    preview: img.preview,
                });
                continue;
            }

            const { file: processedFile, width: newWidth, height: newHeight, isOriginal } = await applyRotationToImage(img);

            if (isOriginal && !img.isNew) {
                 finalUploadedImages.push({
                    id: img.id,
                    image_path: img.image_path,
                    width: img.width,
                    height: img.height,
                    rotation: 0,
                    preview: img.preview,
                });
                continue;
            }

            if (processedFile) {
                newImagesToUpload.push({ file: processedFile, width: newWidth, height: newHeight, rotation: 0 });
            }
        }

        if (newImagesToUpload.length > 0) {
            const formData = new FormData();
            formData.append('inspection_id', props.inspectionId);
            formData.append('point_id', props.pointId);

            newImagesToUpload.forEach(img => {
                formData.append('images[]', img.file);
            });

            const response = await axios.post(route('inspections.upload-image'), formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            const serverImages = response.data.images;
            for (const imgData of serverImages) {
                 finalUploadedImages.push({
                    id: imgData.image_id,
                    image_path: imgData.path,
                    // Note: You might need to adjust your backend to return these
                    // or set them to default values if not available.
                    width: imgData.width || 0,
                    height: imgData.height || 0,
                    rotation: imgData.rotation || 0, 
                    preview: imgData.public_url,
                });
            }
            
            newImagesToUpload.forEach(img => {
                 if (img.file && img.file.preview && img.file.preview.startsWith('blob:')) {
                     URL.revokeObjectURL(img.file.preview);
                 }
            });
        }
        
        emit('update:modelValue', finalUploadedImages);
        emit('save', props.pointId);
        emit('uploaded', { pointId: props.pointId, images: finalUploadedImages });
        closePreviewModal();

    } catch (error) {
        console.error("Error during image processing/upload:", error);
        if (error.response?.data?.message) {
            alert(`Failed to save images: ${error.response.data.message}`);
        } else if (error.response?.data?.errors) {
            const errors = Object.values(error.response.data.errors).flat().join('\n');
            alert(`Failed to save images:\n${errors}`);
        } else {
            alert(`Failed to save images: ${error.message}`);
        }
    } finally {
        isUploading.value = false;
    }
};

const handleRemovePreviewImage = (imageToRemove) => {
  if (!imageToRemove) return;

  if (imageToRemove.id && !imageToRemove.isNew) {
    removeImage(imageToRemove);
  } else {
    const idx = previewImages.value.findIndex(
      img =>
        (img.preview && img.preview === imageToRemove.preview && img.isNew) ||
        (img.id && img.id === imageToRemove.id)
    );

    if (idx !== -1) {
      const removed = previewImages.value.splice(idx, 1)[0];
      if (removed.preview && removed.preview.startsWith("blob:")) {
        URL.revokeObjectURL(removed.preview);
      }
    }

    emit(
      "update:modelValue",
      props.modelValue.filter(
        (img) =>
          !(img.id && img.id === imageToRemove.id) &&
          !(img.preview && img.preview === imageToRemove.preview)
      )
    );
  }

  if (currentPreviewIndex.value >= allImages.value.length) {
    currentPreviewIndex.value = Math.max(0, allImages.value.length - 1);
  }

  if (allImages.value.length === 0) {
    closePreviewModal();
  }
};
const removeImage = async (imageObject) => {
  if (imageObject.id || imageObject.image_path) {
    try {
      await axios.delete(route('inspections.delete-image'), {
        data: { image_id: imageObject.id, image_path: imageObject.image_path }
      });
    } catch (error) {
      console.error("Error deleting image from server:", error);
      if (error.response?.data?.message) {
        alert(`Failed to delete image from server: ${error.response.data.message}`);
      } else {
        alert(`Failed to delete image from server: ${error.message}`);
      }
      return;
    }
  }

  if (imageObject.preview && imageObject.preview.startsWith('blob:')) {
    URL.revokeObjectURL(imageObject.preview);
  }

  const updatedModelValue = props.modelValue.filter(img => img.id !== imageObject.id);
  emit('update:modelValue', updatedModelValue);

  previewImages.value = previewImages.value.filter(img => {
    if (img.isNew && imageObject.isNew) return img.preview !== imageObject.preview;
    return img.id !== imageObject.id;
  });

  emit('removeImage', { image: imageObject });

  if (showPreviewModal.value && allImages.value.length === 0) closePreviewModal();
  else if (showPreviewModal.value && currentPreviewIndex.value >= allImages.value.length) {
    currentPreviewIndex.value = Math.max(0, allImages.value.length - 1);
  }
};
</script>

<style scoped>
/* Scrollbar hiding untuk horizontal scroll */
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

/* Z-index untuk modals */
.image-source-options-modal {
  z-index: 60;
}
.webcam-modal {
  z-index: 70;
}
.image-preview-modal {
  z-index: 50;
}

/* Smooth transition untuk gambar */
img {
  transition: transform 0.2s ease;
}

img:hover {
  transform: scale(1.05);
}
</style>