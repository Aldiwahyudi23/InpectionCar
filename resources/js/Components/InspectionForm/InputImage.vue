<template>
  <div>
    <input
      ref="galleryInput"
      type="file"
      accept="image/*"
      class="hidden"
      @change="handleImageSelect"
      :multiple="allowMultiple"
    />

    <canvas ref="processingCanvas" class="hidden"></canvas>

    <label
      @click="triggerAction"
      class="block w-full border-2 border-dashed rounded-lg cursor-pointer transition-colors duration-200"
      :class="{
        'border-gray-300 hover:border-indigo-400 bg-gray-50': modelValue.length === 0,
        'border-indigo-300 bg-indigo-50': modelValue.length > 0,
        'h-28': modelValue.length === 0,
        'h-auto': modelValue.length > 0
      }"
    >
      <template v-if="modelValue.length === 0">
        <div class="h-full flex flex-col items-center justify-center p-4 text-center">
          <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <p class="text-sm text-gray-600 font-medium">Upload Image</p>
          <p class="text-xs text-gray-500">Click to open options</p>
        </div>
      </template>

      <template v-else>
        <div class="p-2">
          <div class="grid grid-cols-3 gap-2">
            <div
              v-for="(image, idx) in modelValue"
              :key="image.id || idx"
              class="relative w-full overflow-hidden rounded-md border border-gray-200"
              :style="getImageContainerStyle(image)"
              @click.stop="openPreviewModal(idx)"
            >
              <img
                :src="getImageSrc(image)"
                class="absolute top-0 left-0 w-full h-full object-contain"
              >
              <button
                @click.stop="removeImage(idx)"
                type="button"
                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full h-5 w-5 flex items-center justify-center text-xs shadow-sm hover:bg-red-600 transition-colors"
              >
                ×
              </button>
            </div>
          </div>

          <div v-if="allowMultiple && modelValue.length < settings.max_files" class="mt-2 text-center">
            <span @click.stop="openSourceOptions" class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 hover:bg-indigo-200 transition-colors">
              + Add More Images
            </span>
          </div>
        </div>
      </template>
    </label>

    <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>

    <ImageSourceOptionsModal
      :show="showSourceOptionsModal"
      @close="showSourceOptionsModal = false"
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

    <ImagePreviewModal
      :show="showPreviewModal"
      :images="previewImages"
      :initial-index="currentPreviewIndex"
      :allow-multiple="allowMultiple"
      :max-files="settings.max_files"
      :is-uploading="isUploading"
      @close="closePreviewModal"
      @save-images="triggerUploadAndSave"
      @remove-preview-image="handleRemovePreviewImage"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
// Sesuaikan path import modal Anda
import ImageSourceOptionsModal from './image-uploader/ImageSourceOptionsModal.vue';
import WebcamModal from './image-uploader/WebcamModal.vue';
import ImagePreviewModal from './image-uploader/ImagePreviewModal.vue';
import axios from 'axios'; 
// Pastikan `route` tersedia secara global (dari Ziggy)

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  error: String,

  pointId: {
    type: [String, Number],
    required: true // Tandai sebagai required untuk membantu debugging
  }, 
  settings: {
    type: Object,
    default: () => ({
      max_files: 1,
      allowed_types: ['jpg', 'png'],
      camera_aspect_ratio: '4:3',
      enable_flash: true,
      enable_camera_switch: true,
    })
  }
});

const emit = defineEmits(['update:modelValue', 'save', 'removeImage', 'uploaded']);

const galleryInput = ref(null);
const processingCanvas = ref(null);

const showSourceOptionsModal = ref(false);
const showWebcamModal = ref(false);
const showPreviewModal = ref(false);

const previewImages = ref([]);
const currentPreviewIndex = ref(0);
const isUploading = ref(false);

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

const getImageSrc = (image) => {
  // Sudah diperbaiki agar tidak double /storage/
  return image.preview || (image.image_path ? `/${image.image_path}` : '');
};

const getImageContainerStyle = (image) => {
  if (!image.width || !image.height || image.width === 0 || image.height === 0) {
    return {
      paddingBottom: '100%',
      position: 'relative'
    };
  }
  const aspectRatioPercentage = (image.height / image.width) * 100;
  return {
    paddingBottom: `${aspectRatioPercentage}%`,
    position: 'relative'
  };
};

const triggerAction = () => {
  if (props.modelValue.length === 0) {
    openSourceOptions();
  } else {
    openPreviewModal(0);
  }
};

const openSourceOptions = () => {
  showSourceOptionsModal.value = true;
};

const triggerGallery = () => {
  showSourceOptionsModal.value = false;
  galleryInput.value.click();
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
                    isNew: true // Tandai sebagai gambar baru
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

const handleImageSelect = (event) => {
  const files = Array.from(event.target.files);
  if (!files.length) return;

  if (!allowMultiple.value) {
      previewImages.value.forEach(img => img.preview && img.preview.startsWith('blob:') && URL.revokeObjectURL(img.preview));
      previewImages.value = [];
  }

  Promise.all(files.map(loadImageWithDimensions)).then(newImages => {
      const currentTotalImages = props.modelValue.length + previewImages.value.length;
      const allowedToAdd = props.settings.max_files - currentTotalImages;
      const imagesToAdd = allowMultiple.value ? newImages.slice(0, allowedToAdd) : newImages.slice(0, 1);

      previewImages.value.push(...imagesToAdd);
      currentPreviewIndex.value = previewImages.value.length - 1;
      showPreviewModal.value = true;
      event.target.value = '';
  }).catch(error => {
      console.error("Error processing selected images:", error);
      alert("Failed to process selected images. Please try again.");
  });
};

const openWebcam = () => {
  showSourceOptionsModal.value = false;
  showWebcamModal.value = true;
};

const closeWebcam = () => {
  showWebcamModal.value = false;
};

const handlePhotoCaptured = (newImageFile) => {
  const currentTotalImages = props.modelValue.length + previewImages.value.length;
  if (!allowMultiple.value || currentTotalImages < props.settings.max_files) {
    const img = new Image();
    img.onload = () => {
      const newImage = {
          file: newImageFile,
          preview: URL.createObjectURL(newImageFile),
          rotation: 0,
          width: img.naturalWidth,
          height: img.naturalHeight,
          isNew: true // Tandai sebagai gambar baru
      };

      if (!allowMultiple.value) {
          previewImages.value.forEach(img => img.preview && img.preview.startsWith('blob:') && URL.revokeObjectURL(img.preview));
          previewImages.value = [newImage];
      } else {
          previewImages.value.push(newImage);
      }
      currentPreviewIndex.value = previewImages.value.length - 1;
      showWebcamModal.value = false;
      showPreviewModal.value = true;
    };
    img.onerror = () => {
      console.error("Failed to load captured image for dimensions.");
      const newImage = { file: newImageFile, preview: URL.createObjectURL(newImageFile), rotation: 0, width: 0, height: 0, isNew: true };
      if (!allowMultiple.value) {
          previewImages.value.forEach(img => img.preview && img.preview.startsWith('blob:') && URL.revokeObjectURL(img.preview));
          previewImages.value = [newImage];
      } else {
          previewImages.value.push(newImage);
      }
      currentPreviewIndex.value = previewImages.value.length - 1;
      showWebcamModal.value = false;
      showPreviewModal.value = true;
    };
    img.src = URL.createObjectURL(newImageFile);
  } else {
    alert(`Maximum ${props.settings.max_files} files allowed.`);
    showWebcamModal.value = false;
  }
};

const openPreviewModal = async (initialIdx = 0) => {
  const existingImagesProcessed = await Promise.all(props.modelValue.map(async (img) => {
      if (img.width && img.height && img.image_path) {
          return { ...img, rotation: img.rotation || 0, isNew: false };
      } else {
          const tempImg = new Image();
          return new Promise(resolve => {
            tempImg.onload = () => resolve({ ...img, rotation: img.rotation || 0, preview: img.image_path ? `/${img.image_path}` : null, width: tempImg.naturalWidth, height: tempImg.naturalHeight, isNew: false });
            tempImg.onerror = () => resolve({ ...img, rotation: img.rotation || 0, preview: img.image_path ? `/${img.image_path}` : null, width: 0, height: 0, isNew: false });
            tempImg.src = img.image_path ? `/${img.image_path}` : '';
          });
      }
  }));

  const currentTemporaryNewImages = previewImages.value.filter(img => img.isNew || img.rotation !== 0);
  previewImages.value = [...existingImagesProcessed, ...currentTemporaryNewImages];

  currentPreviewIndex.value = initialIdx; 
  showPreviewModal.value = true;
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

const applyRotationToImage = (imageObject) => {
    return new Promise((resolve) => {
        if (imageObject.rotation === 0 || !processingCanvas.value) {
            resolve({ file: imageObject.file, width: imageObject.width, height: imageObject.height });
            return;
        }

        const img = new Image();
        img.onload = () => {
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

            canvas.toBlob((blob) => {
                const newFile = new File([blob], imageObject.file.name, { type: imageObject.file.type });
                resolve({ file: newFile, width: newCanvasWidth, height: newCanvasHeight });
            }, imageObject.file.type);
        };
        img.onerror = () => {
            console.error("Failed to load image for rotation processing:", imageObject.preview);
            resolve({ file: imageObject.file, width: imageObject.width, height: imageObject.height });
        };
        img.src = imageObject.preview;
    });
};

const triggerUploadAndSave = async (imagesToSave) => {
    isUploading.value = true;
    const uploadedImagesData = [];

    // Debugging: Pastikan props.inspectionId dan props.pointId ada
    // Ini akan menampilkan alert jika props tidak ada, sangat membantu saat pengembangan
    if (!props.pointId) {
        alert('Error: Point ID is missing. Cannot upload image. (Check parent component)');
        isUploading.value = false;
        return;
    }
    console.log('Uploading for Inspection ID:', props.inspectionId, 'Point ID:', props.pointId);


    try {
        for (const img of imagesToSave) {
            if (img.isNew || img.rotation !== 0) {
                const { file: processedFile, width: newWidth, height: newHeight } = await applyRotationToImage(img);

                const formData = new FormData();
                formData.append('inspection_id', props.inspectionId); 
                formData.append('point_id', props.pointId); 
                formData.append('image', processedFile);

                const response = await axios.post(route('inspections.upload-image'), formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data' 
                    }
                });

                const serverImage = response.data.image; 
                const imagePath = response.data.path; 

                uploadedImagesData.push({
                    id: serverImage.id,
                    image_path: imagePath, 
                    width: newWidth, 
                    height: newHeight,
                    rotation: 0,
                    preview: `/${imagePath}` 
                });

                if (img.preview && img.preview.startsWith('blob:')) {
                    URL.revokeObjectURL(img.preview);
                }

            } else {
                uploadedImagesData.push(img);
            }
        }

        emit('update:modelValue', uploadedImagesData.slice(0, props.settings.max_files));
        emit('save', props.pointId);
        emit('uploaded', { pointId: props.pointId, images: uploadedImagesData });

        closePreviewModal();
        alert('All images processed and saved successfully!');

    } catch (error) {
        console.error("Error during image processing/upload:", error);
        if (error.response && error.response.data && error.response.data.message) {
            alert(`Failed to save images: ${error.response.data.message}`);
        } else if (error.response && error.response.data && error.response.data.errors) {
            // Tangani error validasi dari Laravel
            const errors = Object.values(error.response.data.errors).flat().join('\n');
            alert(`Failed to save images:\n${errors}`);
        }
        else {
            alert(`Failed to save images: ${error.message}`);
        }
    } finally {
        isUploading.value = false;
    }
};

const removeImage = async (index) => {
    const updated = [...props.modelValue];
    const removed = updated.splice(index, 1)[0];

    if (removed.id || removed.image_path) {
        try {
            await axios.delete(route('inspections.delete-image'), {
                data: { 
                    image_path: removed.image_path
                }
            });
            alert('Image deleted from server successfully!');
        } catch (error) {
            console.error("Error deleting image from server:", error);
            if (error.response && error.response.data && error.response.data.message) {
                alert(`Failed to delete image from server: ${error.response.data.message}`);
            } else if (error.response && error.response.data && error.response.data.errors) {
                const errors = Object.values(error.response.data.errors).flat().join('\n');
                alert(`Failed to delete image:\n${errors}`);
            }
            else {
                alert(`Failed to delete image from server: ${error.message}`);
            }
        }
    }

    if (removed.preview && removed.preview.startsWith('blob:')) {
        URL.revokeObjectURL(removed.preview);
    }
    emit('update:modelValue', updated);
    emit('removeImage', { index, pointId: props.pointId, image: removed });
};
</script>