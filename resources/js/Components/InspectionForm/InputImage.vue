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

    <!-- Tampilan saat belum ada gambar -->
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

    <!-- Tampilan saat ada gambar - Diubah menjadi horizontal scroll -->
    <div
      v-else
      class="block w-full border-2 border-dashed rounded-lg transition-colors duration-200 border-indigo-300 bg-indigo-50 h-auto p-2"
      aria-label="Image gallery"
    >
      <!-- Container untuk horizontal scroll -->
      <div class="flex space-x-2 overflow-x-auto pb-2 scrollbar-hide">
        <!-- Gambar yang sudah diupload -->
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
          <!-- Indicator untuk gambar baru atau rotated -->
          <div v-if="image.isNew || image.rotation !== 0"
                class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center text-white text-xs font-bold">
              <span v-if="image.isNew">BARU</span>
              <span v-if="image.rotation !== 0" class="ml-1">ROTASI</span>
          </div>

          <!-- Tombol hapus hanya untuk single upload -->
          <button
            @click.stop="removeImage(image)" 
            type="button"
            class="absolute top-1 right-1 bg-red-500 text-white rounded-full h-5 w-5 flex items-center justify-center text-xs shadow-sm hover:bg-red-600 transition-colors z-10"
            aria-label="Remove image"
            :disabled="isUploading"
          >
            ×
          </button>
        </div>

        <!-- Tombol tambah gambar (hanya ditampilkan jika masih bisa menambah) -->
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
      </div>
    </div>

    <!-- Error message -->
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
      @removePreviewImage="handleRemovePreviewImage"
      @trigger-add-more-photos="openSourceOptions"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import ImageSourceOptionsModal from './Modal-uploader/ImageSourceOptionsModal.vue';
import WebcamModal from './Modal-uploader/WebcamModal.vue';
import PreviewModal from './Modal-uploader/PreviewModal.vue';
import axios from 'axios';

// Define props yang diterima komponen
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

// Define events yang bisa dipancarkan komponen
const emit = defineEmits(['update:modelValue', 'save', 'removeImage', 'uploaded']);

// Refs untuk DOM elements dan state management
const galleryInput = ref(null);
const processingCanvas = ref(null);
const showSourceOptionsModal = ref(false);
const showWebcamModal = ref(false);
const showPreviewModal = ref(false);
const previewImages = ref([]);
const currentPreviewIndex = ref(0);
const isUploading = ref(false);

// Computed property untuk menentukan apakah multiple upload diizinkan
const allowMultiple = computed(() => Number(props.settings.max_files) > 1);

// Computed property untuk format allowed types string
const allowedTypesString = computed(() => {
  return props.settings.allowed_types.map(type => `.${type}`).join(', ');
});

// Computed property untuk menghitung aspect ratio camera
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

// =========================================================================
// PERBAIKAN UTAMA: Filter gambar yang ada berdasarkan inspectionId
// =========================================================================
const allImages = computed(() => {
  const finalImages = [];
  const processedIds = new Set();
  
  // Tambahkan gambar baru dari previewImages
  for (const pImg of previewImages.value) {
    if (pImg.id) processedIds.add(pImg.id);
    finalImages.push(pImg);
  }

  // Tambahkan gambar existing dari modelValue yang *belum* ada di finalImages dan
  // pastikan inspection_id cocok dengan prop
  for (const mImg of props.modelValue) {
    // Pastikan gambar memiliki inspection_id yang cocok sebelum menambahkannya
    if (mImg.inspection_id && String(mImg.inspection_id) !== String(props.inspectionId)) {
        continue;
    }
    
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

/**
 * Mengembalikan URL sumber gambar
 */
const getImageSrc = (image) => {
  return image.preview || (image.image_path ? `/${image.image_path}` : '');
};

/**
 * Membuka modal pilihan sumber gambar
 */
const openSourceOptions = () => {
  if (showPreviewModal.value) showPreviewModal.value = false;
  showSourceOptionsModal.value = true;
};

/**
 * Memicu input file gallery
 */
const triggerGallery = () => {
  showSourceOptionsModal.value = false;
  galleryInput.value.click();
};

/**
 * Membuka modal webcam
 */
const openWebcam = () => {
  showSourceOptionsModal.value = false;
  showWebcamModal.value = true;
};

/**
 * Menutup modal pilihan sumber
 */
const closeSourceOptions = () => {
  showSourceOptionsModal.value = false;
  if (allImages.value.length > 0) showPreviewModal.value = true;
};

/**
 * Menutup modal webcam
 */
const closeWebcam = () => {
  showWebcamModal.value = false;
  if (allImages.value.length > 0) showPreviewModal.value = true;
};

/**
 * Menutup modal preview dan membersihkan blob URLs
 */
const closePreviewModal = () => {
  showPreviewModal.value = false;
  previewImages.value.forEach(img => {
    if (img.preview && img.preview.startsWith('blob:')) {
      URL.revokeObjectURL(img.preview);
    }
  });
  previewImages.value = [];
};

/**
 * Memuat gambar dan mendapatkan dimensinya
 */
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

/**
 * Validasi tipe file berdasarkan settings
 */
const validateFileType = (file) => {
  const extension = file.name.split('.').pop().toLowerCase();
  return props.settings.allowed_types.includes(extension);
};

/**
 * Mengompres gambar menjadi format persegi (1:1) dengan padding putih
 */
const compressAndSquareImage = async (file) => {
  const MAX_SIZE_KB = props.settings.max_size || 2048; // Ambil dari settings
  const MAX_SIZE = MAX_SIZE_KB * 1024; // Convert KB to bytes
  
  return new Promise((resolve) => {
    const img = new Image();
    const reader = new FileReader();
    
    reader.onload = (e) => {
      img.onload = () => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        // Buat canvas persegi dengan ukuran terbesar dari width/height
        const size = Math.max(img.width, img.height);
        canvas.width = size;
        canvas.height = size;
        
        // Isi background putih
        ctx.fillStyle = 'white';
        ctx.fillRect(0, 0, size, size);
        
        // Hitung posisi untuk menempatkan gambar di tengah
        const x = (size - img.width) / 2;
        const y = (size - img.height) / 2;
        
        // Gambar gambar di tengah canvas
        ctx.drawImage(img, x, y, img.width, img.height);
        
        let quality = 0.9;
        
        const compress = () => {
          canvas.toBlob((blob) => {
            if (blob.size > MAX_SIZE && quality > 0.1) {
              // Kurangi kualitas dan coba lagi
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

/**
 * Menangani pemilihan file dari input
 */
const handleImageSelect = async (event) => {
  const files = Array.from(event.target.files);
  if (!files.length) {
    showSourceOptionsModal.value = false;
    if (allImages.value.length > 0) showPreviewModal.value = true;
    return;
  }

  try {
    // Validasi tipe file
    const invalidFiles = files.filter(file => !validateFileType(file));
    if (invalidFiles.length > 0) {
      const invalidTypes = invalidFiles.map(f => f.name.split('.').pop()).join(', ');
      alert(`File type not allowed: ${invalidTypes}. Allowed types: ${props.settings.allowed_types.join(', ')}`);
      event.target.value = '';
      return;
    }

    const currentTotalImages = allImages.value.length;
    const allowedToAdd = props.settings.max_files - currentTotalImages;
    
    // Kompres gambar yang dipilih
    const compressedFiles = await Promise.all(
      files.slice(0, allowedToAdd).map(async (file) => {
        return await compressAndSquareImage(file);
      })
    );

    // Load gambar dengan dimensi
    const newImages = await Promise.all(compressedFiles.map(loadImageWithDimensions));
    const imagesToProcess = allowMultiple.value ? newImages : newImages.slice(0, 1);

    if (!allowMultiple.value) {
      // Hapus gambar lama jika single upload
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

/**
 * Menangani foto yang diambil dari webcam
 */
const handlePhotoCaptured = async (newImageFile) => {
  const currentTotalImages = allImages.value.length;
  if (!allowMultiple.value || currentTotalImages < props.settings.max_files) {
    try {
      // Kompres gambar dari webcam
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

/**
 * Membuka modal preview gambar
 */
const openPreviewModal = (initialIdx = 0) => {
  currentPreviewIndex.value = initialIdx;
  showPreviewModal.value = true;
};

/**
 * Memutar gambar dan mengembalikan file yang sudah diputar
 */
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
        // Kompres ulang gambar yang dirotasi untuk memastikan ukuran sesuai
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

        // Convert ke blob dan kompres
        const blob = await new Promise(resolve => {
          canvas.toBlob(resolve, 'image/jpeg', 0.9);
        });

        const rotatedFile = new File(
          [blob], 
          imageObject.file ? imageObject.file.name : `rotated_image_${Date.now()}.jpg`, 
          { type: 'image/jpeg' }
        );

        // Kompres ulang untuk memastikan ukuran sesuai settings
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

/**
 * Mengupload dan menyimpan gambar
 */
const triggerUploadAndSave = async (imagesToSaveFromPreview) => {
  isUploading.value = true;
  const finalUploadedImages = [];

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
          preview: img.preview
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
          preview: img.preview
        });
        continue;
      }

      const formData = new FormData();
      formData.append('inspection_id', props.inspectionId);
      formData.append('point_id', props.pointId);
      formData.append('image', processedFile);
      formData.append('width', newWidth);
      formData.append('height', newHeight);
      formData.append('rotation', 0);

      if (img.id && !img.isNew) {
        formData.append('image_id', img.id);
      }

      const response = await axios.post(route('inspections.upload-image'), formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });

      const serverImage = response.data.image;
      finalUploadedImages.push({
        id: serverImage.id,
        image_path: serverImage.image_path,
        width: serverImage.width,
        height: serverImage.height,
        rotation: serverImage.rotation,
        preview: serverImage.public_url
      });

      if (img.preview && img.preview.startsWith('blob:')) {
        URL.revokeObjectURL(img.preview);
      }
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

/**
 * Menangani penghapusan gambar dari preview
 */
const handleRemovePreviewImage = (imageToRemove) => {
  if (!imageToRemove) return;

  if (imageToRemove.id && !imageToRemove.isNew) {
    // gambar lama → hapus dari server
    removeImage(imageToRemove);
  } else {
    // gambar baru (belum tersimpan ke server) → hapus lokal
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

    // emit biar parent juga update modelValue
    emit(
      "update:modelValue",
      props.modelValue.filter(
        (img) =>
          !(img.id && img.id === imageToRemove.id) &&
          !(img.preview && img.preview === imageToRemove.preview)
      )
    );
  }

  // Adjust index setelah hapus
  if (currentPreviewIndex.value >= allImages.value.length) {
    currentPreviewIndex.value = Math.max(0, allImages.value.length - 1);
  }

  // Tutup modal kalau tidak ada gambar
  if (allImages.value.length === 0) {
    closePreviewModal();
  }
};



/**
 * Menghapus gambar dari server dan state
 */
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