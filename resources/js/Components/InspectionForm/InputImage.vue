<template>
  <label
    @click="showOptionsModal = true"
    class="block w-full border-2 border-dashed rounded-xl cursor-pointer transition-all duration-200 p-4"
    :class="{
      'border-gray-300 hover:border-indigo-400 bg-gray-50 text-gray-700': modelValue.length === 0,
      'border-indigo-400 bg-indigo-50 text-indigo-800': modelValue.length > 0,
      'min-h-32 flex flex-col items-center justify-center': modelValue.length === 0, // Minimal tinggi untuk area kosong
    }"
  >
    <div v-if="modelValue.length === 0" class="text-center">
      <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
      <p class="text-sm font-medium">Upload Gambar</p>
      <p class="text-xs text-gray-500 mt-1">Klik untuk pilih Kamera atau Galeri</p>
    </div>

    <div v-else class="space-y-3">
      <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
        <div v-for="(image, idx) in modelValue" :key="image.image_path || idx" class="relative aspect-square rounded-lg overflow-hidden shadow-sm">
          <img :src="image.preview || '/storage/' + image.image_path" class="w-full h-full object-cover" />
          <button @click.stop="$emit('removeImage', idx)" type="button"
            class="absolute -top-1 -right-1 bg-red-600 text-white rounded-full h-6 w-6 flex items-center justify-center text-sm font-bold z-10 opacity-90 hover:opacity-100 transition-opacity duration-200">
            &times;
          </button>
        </div>
      </div>

      <div v-if="modelValue.length < settings.max_files" class="text-center pt-2">
        <span @click.stop="showOptionsModal = true"
          class="inline-block px-4 py-2 rounded-full text-sm font-medium bg-indigo-100 text-indigo-700 hover:bg-indigo-200 transition-colors duration-200 cursor-pointer shadow-sm">
          + Tambah Gambar
        </span>
      </div>
    </div>
  </label>

  <div v-if="showOptionsModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl p-6 shadow-2xl w-full max-w-sm text-center transform transition-all scale-95 opacity-0 animate-scale-in">
      <h3 class="text-lg font-bold text-gray-800 mb-4">Pilih Sumber Gambar</h3>
      <div class="flex justify-around gap-6">
        <button @click="openCamera" class="flex flex-col items-center p-3 text-indigo-600 hover:text-indigo-800 transition-colors duration-200">
          <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
          <span class="text-sm font-medium">Kamera</span>
        </button>
        <button @click="openGallery" class="flex flex-col items-center p-3 text-green-600 hover:text-green-800 transition-colors duration-200">
          <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M4 4v16h16V4H4zm4 4h8v8H8V8z" />
          </svg>
          <span class="text-sm font-medium">Galeri</span>
        </button>
      </div>
      <button @click="showOptionsModal = false" class="mt-6 text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors duration-200">Batal</button>
    </div>
  </div>

  <div v-if="showReviewModal" class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 p-4">
    <div class="relative w-full max-w-2xl mx-auto bg-gray-900 rounded-lg shadow-2xl overflow-hidden animate-fade-in-up">
      <div class="relative h-96 sm:h-[450px] flex items-center justify-center p-4">
        <img
          v-if="tempImages.length > 0"
          :src="tempImages[currentReviewIndex].preview"
          :style="{ transform: `rotate(${tempImages[currentReviewIndex].rotation}deg)` }"
          class="max-h-full max-w-full object-contain transition-transform duration-300 ease-in-out"
        />
        <p v-else class="text-white text-lg font-semibold">Tidak ada gambar untuk direview.</p>

        <button v-if="tempImages.length > 1" @click="prevImage"
          class="absolute left-4 top-1/2 -translate-y-1/2 bg-gray-800 bg-opacity-60 text-white p-3 rounded-full hover:bg-opacity-80 focus:outline-none transition-colors duration-200">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
        </button>
        <button v-if="tempImages.length > 1" @click="nextImage"
          class="absolute right-4 top-1/2 -translate-y-1/2 bg-gray-800 bg-opacity-60 text-white p-3 rounded-full hover:bg-opacity-80 focus:outline-none transition-colors duration-200">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </button>

        <div v-if="tempImages.length > 1" class="absolute bottom-4 left-0 right-0 flex justify-center space-x-2">
          <span v-for="(_ , index) in tempImages" :key="'indicator-' + index"
            class="block w-2.5 h-2.5 rounded-full transition-colors duration-300"
            :class="{'bg-white shadow': index === currentReviewIndex, 'bg-gray-500': index !== currentReviewIndex}"
          ></span>
        </div>
      </div>

      <div class="bg-gray-800 p-4 flex justify-around items-center border-t border-gray-700">
        <button
          v-if="tempImages.length > 0"
          @click="removeReviewImage"
          class="flex flex-col items-center text-red-400 hover:text-red-300 transition-colors duration-200 text-sm font-medium py-2 px-3 rounded-md hover:bg-gray-700"
        >
          <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
          Hapus
        </button>

        <button v-if="modelValue.length + tempImages.length < settings.max_files" @click="showOptionsModal = true"
          class="flex flex-col items-center text-blue-400 hover:text-blue-300 transition-colors duration-200 text-sm font-medium py-2 px-3 rounded-md hover:bg-gray-700">
          <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
          Tambah
        </button>

        <button
          v-if="tempImages.length > 0"
          @click="rotateCurrentImage"
          class="flex flex-col items-center text-gray-300 hover:text-white transition-colors duration-200 text-sm font-medium py-2 px-3 rounded-md hover:bg-gray-700"
        >
          <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004 12c0 2.21.896 4.202 2.396 5.604M18 20v-5h-.582m-15.356-2A8.001 8.001 0 0020 12c0-2.21-.896-4.202-2.396-5.604"></path></svg>
          Rotasi
        </button>
      </div>

      <div class="bg-gray-900 p-4 flex justify-end gap-3 border-t border-gray-700">
        <button @click="cancelReview" class="bg-gray-700 text-gray-300 px-5 py-2 rounded-md text-sm font-medium hover:bg-gray-600 transition-colors duration-200 shadow-md">Batal</button>
        <button @click="saveImages" class="bg-indigo-600 text-white px-5 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 transition-colors duration-200 shadow-md">Simpan</button>
      </div>
    </div>
  </div>

  <input ref="fileInput" type="file" :accept="allowedFileTypes" :capture="captureMode" @change="handleFileChange" hidden />
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  modelValue: Array, // Array of { image_path: string, preview: string|null }
  error: String,
  pointId: [String, Number],
  settings: {
    type: Object,
    default: () => ({
      max_files: 1,
      max_size: 2048, // in KB
      allowed_types: ['jpg', 'jpeg', 'png']
    })
  }
});

// Emit 'update:modelValue' when the list of images changes (e.g., after saving or removing)
// Emit 'handleImageUpload' when a new file is ready to be uploaded to the server
// Emit 'removeImage' when an image is to be removed from the server (handled by parent)
const emit = defineEmits(['update:modelValue', 'handleImageUpload', 'removeImage']);

const showOptionsModal = ref(false);
const showReviewModal = ref(false);
const tempImages = ref([]); // Temporary array for images being reviewed
const currentReviewIndex = ref(0); // Index of the currently displayed image in review modal
const fileInput = ref(null); // Ref for the hidden file input
const captureMode = ref(''); // 'environment' for camera, '' for gallery

// Computed property for allowed file types for the input accept attribute
const allowedFileTypes = computed(() => {
  return props.settings.allowed_types.map(type => `image/${type}`).join(',');
});

const openCamera = () => {
  showOptionsModal.value = false;
  captureMode.value = 'environment'; // 'environment' for rear camera, 'user' for front
  fileInput.value.click();
};

const openGallery = () => {
  showOptionsModal.value = false;
  captureMode.value = ''; // No capture attribute for gallery
  fileInput.value.click();
};

// Handle file change from the hidden input
const handleFileChange = (event) => {
  const files = Array.from(event.target.files);
  if (files.length === 0) return;

  const newFiles = files.filter(file => {
    const fileType = file.type.split('/').pop().toLowerCase(); // Mendapatkan ekstensi file
    const isAllowedType = props.settings.allowed_types.includes(fileType);
    const isWithinSize = file.size / 1024 <= props.settings.max_size; // size in KB

    if (!isAllowedType) {
      alert(`Tipe file tidak diizinkan: ${file.name}. Hanya ${props.settings.allowed_types.join(', ')} yang diizinkan.`);
      return false;
    }
    if (!isWithinSize) {
      alert(`Ukuran file terlalu besar: ${file.name}. Maksimal ${props.settings.max_size}KB.`);
      return false;
    }
    return true;
  });

  if (newFiles.length === 0) {
    event.target.value = '';
    return;
  }

  // Combine new files with existing temp images, respecting max_files limit
  // First, check how many slots are available considering already saved images
  const totalExistingImages = props.modelValue.length;
  const totalTempImages = tempImages.value.length;
  const combinedCurrentImages = totalExistingImages + totalTempImages;
  
  const filesToAdd = newFiles.slice(0, props.settings.max_files - combinedCurrentImages);

  if (filesToAdd.length === 0 && newFiles.length > 0) {
    alert(`Anda telah mencapai batas maksimum ${props.settings.max_files} gambar.`);
  }

  tempImages.value = [
    ...tempImages.value,
    ...filesToAdd.map(file => ({
      file,
      preview: URL.createObjectURL(file),
      rotation: 0 // Initialize rotation for each new image
    }))
  ];

  if (tempImages.value.length > 0) {
    currentReviewIndex.value = tempImages.value.length - 1; // Show the last added image
    showReviewModal.value = true;
  }
  event.target.value = ''; // Clear the input value to allow re-selection
};

const prevImage = () => {
  if (tempImages.value.length === 0) return;
  currentReviewIndex.value = (currentReviewIndex.value - 1 + tempImages.value.length) % tempImages.value.length;
};

const nextImage = () => {
  if (tempImages.value.length === 0) return;
  currentReviewIndex.value = (currentReviewIndex.value + 1) % tempImages.value.length;
};

const rotateCurrentImage = () => {
  if (tempImages.value.length === 0) return;
  const currentImage = tempImages.value[currentReviewIndex.value];
  currentImage.rotation = (currentImage.rotation + 90) % 360;
};

const removeReviewImage = () => {
  if (tempImages.value.length === 0) return;
  const removedImage = tempImages.value.splice(currentReviewIndex.value, 1)[0];
  URL.revokeObjectURL(removedImage.preview); // Clean up the URL object

  // Adjust index if the removed image was the last one
  if (tempImages.value.length === 0) {
    showReviewModal.value = false; // If no images left, close the modal
    currentReviewIndex.value = 0;
  } else if (currentReviewIndex.value >= tempImages.value.length) {
    currentReviewIndex.value = tempImages.value.length - 1;
  }
};

const cancelReview = () => {
  tempImages.value.forEach(img => URL.revokeObjectURL(img.preview));
  tempImages.value = [];
  currentReviewIndex.value = 0;
  showReviewModal.value = false;
};

const saveImages = () => {
  // Emit each new image to the parent component for upload
  tempImages.value.forEach(img => {
    emit('handleImageUpload', { 
        file: img.file, 
        pointId: props.pointId,
        rotation: img.rotation // Kirim informasi rotasi ke parent
    });
  });

  // Clear temp images and close modal
  tempImages.value.forEach(img => URL.revokeObjectURL(img.preview));
  tempImages.value = [];
  currentReviewIndex.value = 0;
  showReviewModal.value = false;
};
</script>

<style scoped>
/* Animasi untuk Modal Pilihan Kamera/Galeri */
@keyframes scaleIn {
  from {
    transform: scale(0.95);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}
.animate-scale-in {
  animation: scaleIn 0.2s ease-out forwards;
}

/* Animasi untuk Modal Review Gambar */
@keyframes fadeInUp {
  from {
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
.animate-fade-in-up {
  animation: fadeInUp 0.3s ease-out forwards;
}
</style>