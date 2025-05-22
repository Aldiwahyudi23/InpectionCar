<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-90 z-50 flex flex-col items-center justify-center">
    <div class="w-full h-full flex flex-col">
      <div class="flex justify-between items-center p-4 bg-black bg-opacity-50 text-white">
        <button @click="cancelPreview" class="p-2 rounded-full hover:bg-white hover:bg-opacity-10 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <div class="text-lg font-medium">Preview ({{ currentPreviewIndex + 1 }}/{{ images.length }})</div>
        <button @click="rotateImage" class="p-2 rounded-full hover:bg-white hover:bg-opacity-10 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
        </button>
      </div>

      <div class="flex-1 flex items-center justify-center overflow-hidden relative">
        <div
          class="w-full h-full flex items-center justify-center"
          @touchstart="handleTouchStart"
          @touchmove="handleTouchMove"
          @touchend="handleTouchEnd"
        >
          <img
            v-if="currentImage"
            :src="currentImage.preview || (currentImage.image_path ? `/storage/${currentImage.image_path}` : '')"
            class="max-w-full max-h-full object-contain"
            :style="{ transform: `rotate(${currentImage.rotation}deg)` }"
          />
        </div>

        <button
          v-if="images.length > 1"
          @click="prevImage"
          class="absolute left-4 p-2 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-70 transition-colors"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <button
          v-if="images.length > 1"
          @click="nextImage"
          class="absolute right-4 p-2 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-70 transition-colors"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>

      <div class="flex flex-col gap-2 p-4 bg-black bg-opacity-50">
        <div class="flex justify-between">
          <button
            @click="removeCurrentImage"
            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors"
            :disabled="isUploading" >
            Hapus
          </button>

          <button
            v-if="!isUploading"
            @click="saveImages"
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
          >
            Simpan {{ images.length }}
          </button>
          <button
            v-else
            class="px-4 py-2 bg-indigo-400 text-white rounded-lg cursor-not-allowed flex items-center justify-center"
            disabled
          >
            Menyimpan<span class="loading-dots ml-1"><span>.</span><span>.</span><span>.</span></span>
          </button>
        </div>

        <div v-if="allowMultiple && images.length < maxFiles" class="text-center mt-2">
          <button
            @click="addMorePhotos"
            class="inline-block px-4 py-2 rounded-lg text-sm font-medium bg-indigo-700 text-white hover:bg-indigo-800 transition-colors cursor-pointer"
            :disabled="isUploading"
          >
            + Tambah Foto
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, defineProps, defineEmits } from 'vue';

const props = defineProps({
  show: Boolean,
  images: {
    type: Array,
    default: () => []
  },
  initialIndex: {
    type: Number,
    default: 0
  },
  allowMultiple: Boolean,
  maxFiles: Number,
  isUploading: Boolean,
});

// PENTING: Tambahkan 'triggerAddMorePhotos' ke daftar emit
const emit = defineEmits(['close', 'saveImages', 'removePreviewImage', 'triggerAddMorePhotos']);

const currentPreviewIndex = ref(props.initialIndex);
const touchStartX = ref(0);
const touchEndX = ref(0);

const currentImage = computed(() => {
  if (props.images.length > 0) {
    return props.images[currentPreviewIndex.value];
  }
  return null;
});

// Update currentPreviewIndex if initialIndex changes
watch(() => props.initialIndex, (newIndex) => {
  currentPreviewIndex.value = newIndex;
}, { immediate: true });

const rotateImage = () => {
  if (currentImage.value) {
    currentImage.value.rotation = (currentImage.value.rotation + 90) % 360;
  }
};

const nextImage = () => {
  if (currentPreviewIndex.value < props.images.length - 1) {
    currentPreviewIndex.value++;
  }
};

const prevImage = () => {
  if (currentPreviewIndex.value > 0) {
    currentPreviewIndex.value--;
  }
};

const handleTouchStart = (e) => {
  touchStartX.value = e.touches[0].clientX;
};

const handleTouchMove = (e) => {
  touchEndX.value = e.touches[0].clientX;
};

const handleTouchEnd = () => {
  if (touchStartX.value - touchEndX.value > 50) {
    nextImage();
  } else if (touchEndX.value - touchStartX.value > 50) {
    prevImage();
  }
};

const removeCurrentImage = () => {
  if (props.images.length === 0) return;

  emit('removePreviewImage', currentPreviewIndex.value); // Emit index to parent for removal

  // Sesuaikan indeks setelah penghapusan. Ingat, `props.images` akan diperbarui oleh komponen induk.
  // Logika ini mengasumsikan `props.images` akan segera mencerminkan penghapusan.
  if (currentPreviewIndex.value >= props.images.length -1 && props.images.length > 1) {
      currentPreviewIndex.value = Math.max(0, props.images.length - 2); // Pindah ke gambar sebelumnya
  } else if (props.images.length === 1) { // Jika setelah dihapus hanya tersisa 1 gambar (atau 0 jika ini adalah gambar terakhir)
      cancelPreview(); // Tutup modal jika tidak ada gambar tersisa
  }
};

const saveImages = () => {
  emit('saveImages', props.images);
};

const cancelPreview = () => {
  emit('close');
};

// FUNGSI BARU: Untuk memicu permintaan penambahan foto di komponen induk
const addMorePhotos = () => {
  emit('triggerAddMorePhotos');
};

// Ensure body scroll is managed
watch(() => props.show, (newVal) => {
  if (newVal) {
    document.body.classList.add('modal-open');
  } else {
    document.body.classList.remove('modal-open');
  }
}, { immediate: true });
</script>

<style scoped>
/* Basic modal styles for overlay */
.fixed.inset-0 {
  background-color: rgba(0, 0, 0, 0.9);
}

body.modal-open {
  overflow: hidden;
}

/* Loading dots animation */
.loading-dots span {
  animation: blink 1.4s infinite both;
}

.loading-dots span:nth-child(2) {
  animation-delay: 0.2s;
}

.loading-dots span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes blink {
  0%, 80%, 100% { opacity: 0; }
  40% { opacity: 1; }
}
</style>