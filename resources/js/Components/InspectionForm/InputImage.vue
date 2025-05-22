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

    <label
      @click="openFileOptions"
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
              :key="idx"
              class="relative aspect-square"
            >
              <img
                :src="image.preview || '/storage/' + image.image_path"
                class="w-full h-full object-cover rounded-md border border-gray-200"
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

          <div v-if="allowMultiple" class="mt-2 text-center">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 hover:bg-indigo-200 transition-colors">
              + Add More Images
            </span>
          </div>
        </div>
      </template>
    </label>

    <div v-if="showFileOptions" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40 p-4">
      <div class="bg-white w-full max-w-sm rounded-xl shadow-xl overflow-hidden">
        <div class="p-4">
          <h3 class="text-lg font-medium text-center text-gray-800 mb-4">Select Image Source</h3>
          <div class="flex justify-center gap-6">
            <button @click="openWebcam" class="flex flex-col items-center justify-center w-24 h-24 rounded-xl bg-blue-50 hover:bg-blue-100 transition-colors">
              <div class="bg-blue-100 p-3 rounded-full mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <span class="text-sm font-medium text-gray-700">Camera (Web)</span>
            </button>
            
            <button @click="triggerGallery" class="flex flex-col items-center justify-center w-24 h-24 rounded-xl bg-purple-50 hover:bg-purple-100 transition-colors">
              <div class="bg-purple-100 p-3 rounded-full mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <span class="text-sm font-medium text-gray-700">Gallery (HP)</span>
            </button>
          </div>
        </div>
        <button @click="showFileOptions = false" class="w-full py-3 text-center text-gray-500 font-medium border-t border-gray-100 hover:bg-gray-50 transition-colors">Cancel</button>
      </div>
    </div>

    <div v-if="showWebcamModal" class="fixed inset-0 z-40 p-0 webcam-modal-container">
        <div class="webcam-content-box">
            <div class="webcam-header">
                <button @click="closeWebcam" class="p-2 rounded-full text-white hover:bg-gray-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                 <h3 class="text-lg font-medium text-white">Take Photo</h3>
                 <div></div> </div>
            <div class="webcam-video-container">
                <video ref="webcamVideo" autoplay playsinline class="webcam-video"></video>
                <canvas ref="webcamCanvas" class="hidden"></canvas>
            </div>
            <div class="webcam-footer">
                <button @click="capturePhoto" class="capture-button">
                    </button>
            </div>
        </div>
    </div>


    <div v-if="previewImages.length" class="fixed inset-0 bg-black bg-opacity-90 z-50 flex flex-col items-center justify-center">
      <div class="w-full h-full flex flex-col">
        <div class="flex justify-between items-center p-4 bg-black bg-opacity-50 text-white">
          <button @click="cancelPreview" class="p-2 rounded-full hover:bg-white hover:bg-opacity-10 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          <div class="text-lg font-medium">Preview ({{ currentPreviewIndex + 1 }}/{{ previewImages.length }})</div>
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
              :src="currentPreviewImage.preview" 
              class="max-w-full max-h-full object-contain"
              :style="{ transform: `rotate(${rotationAngle}deg)` }"
            />
          </div>
          
          <button 
            v-if="previewImages.length > 1"
            @click="prevImage"
            class="absolute left-4 p-2 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-70 transition-colors"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <button 
            v-if="previewImages.length > 1"
            @click="nextImage"
            class="absolute right-4 p-2 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-70 transition-colors"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
        
        <div class="flex justify-between p-4 bg-black bg-opacity-50">
          <button @click="removePreviewImage(currentPreviewIndex)" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
            Delete
          </button>
          <button @click="savePreviewImages" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
            Save {{ previewImages.length }} Image(s)
          </button>
        </div>
      </div>
    </div>

    <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  error: String,
  pointId: [String, Number],
  settings: {
    type: Object,
    default: () => ({
      max_files: 1,
      allowed_types: ['jpg', 'png'],
      // Tambahkan default aspect ratio
      camera_aspect_ratio: '4:3' // Atau '16:9', '1:1'
    })
  }
});

const emit = defineEmits(['update:modelValue', 'save', 'removeImage']);

const galleryInput = ref(null);
const webcamVideo = ref(null);
const webcamCanvas = ref(null);

const previewImages = ref([]);
const showFileOptions = ref(false);
const showWebcamModal = ref(false);
const currentPreviewIndex = ref(0);
const rotationAngle = ref(0);
const touchStartX = ref(0);
const touchEndX = ref(0);

let mediaStream = null;

const allowMultiple = computed(() => Number(props.settings.max_files) > 1);
const currentPreviewImage = computed(() => previewImages.value[currentPreviewIndex.value]);

// Computed property for aspect ratio
const aspectRatio = computed(() => {
  const parts = props.settings.camera_aspect_ratio.split(':');
  if (parts.length === 2) {
    const width = parseFloat(parts[0]);
    const height = parseFloat(parts[1]);
    if (!isNaN(width) && !isNaN(height) && height !== 0) {
      return width / height;
    }
  }
  return 4 / 3; // Default to 4:3 if invalid
});

const openFileOptions = () => {
  showFileOptions.value = true;
};

const triggerGallery = () => {
  showFileOptions.value = false;
  galleryInput.value.click();
};

const openWebcam = async () => {
  showFileOptions.value = false;
  showWebcamModal.value = true;
  try {
    const videoConstraints = {
      facingMode: 'environment', // Prioritaskan kamera belakang
      width: { ideal: 1280 }, // Coba resolusi ideal 1280x720 (atau sesuai rasio)
      height: { ideal: 960 },
      aspectRatio: { exact: aspectRatio.value } // Gunakan aspect ratio yang dihitung
    };

    mediaStream = await navigator.mediaDevices.getUserMedia({
      video: videoConstraints
    });

    webcamVideo.value.srcObject = mediaStream;

    // Tambahkan event listener saat video dimuat metadata
    webcamVideo.value.onloadedmetadata = () => {
        // Atur ukuran video agar sesuai dengan kontainer dan mempertahankan rasio
        webcamVideo.value.style.width = '100%';
        webcamVideo.value.style.height = 'auto'; // Agar tinggi menyesuaikan lebar
        webcamVideo.value.style.objectFit = 'cover'; // Memastikan video mengisi ruang
    };

  } catch (err) {
    console.error("Error accessing camera: ", err);
    alert('Failed to access camera. Please ensure camera permissions are granted and try again.');
    showWebcamModal.value = false;
  }
};

const closeWebcam = () => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }
  showWebcamModal.value = false;
};

const capturePhoto = () => {
  if (!webcamVideo.value || !webcamCanvas.value) return;

  const video = webcamVideo.value;
  const canvas = webcamCanvas.value;
  const context = canvas.getContext('2d');

  // Calculate dimensions to maintain 4:3 aspect ratio
  const videoWidth = video.videoWidth;
  const videoHeight = video.videoHeight;
  
  let drawWidth = videoWidth;
  let drawHeight = videoHeight;
  let offsetX = 0;
  let offsetY = 0;

  // Adjust to fit 4:3 ratio based on the larger dimension
  if (videoWidth / videoHeight > aspectRatio.value) { // video is wider than 4:3
    drawWidth = videoHeight * aspectRatio.value;
    offsetX = (videoWidth - drawWidth) / 2;
  } else if (videoWidth / videoHeight < aspectRatio.value) { // video is taller than 4:3
    drawHeight = videoWidth / aspectRatio.value;
    offsetY = (videoHeight - drawHeight) / 2;
  }
  
  canvas.width = drawWidth;
  canvas.height = drawHeight;

  context.drawImage(
    video,
    offsetX, offsetY, // Source X, Y
    drawWidth, drawHeight, // Source Width, Height (portion of video to capture)
    0, 0, // Destination X, Y
    canvas.width, canvas.height // Destination Width, Height (canvas size)
  );

  // Get image data from canvas
  canvas.toBlob((blob) => {
    if (blob) {
      const file = new File([blob], `captured_image_${Date.now()}.png`, { type: 'image/png' });
      
      const newImage = {
        file: file,
        preview: URL.createObjectURL(file)
      };

      // Handle multiple captures if allowed
      if (allowMultiple.value && props.modelValue.length < props.settings.max_files) {
          previewImages.value.push(newImage); // Add to current preview batch
      } else {
          // If not multiple or max files reached, replace existing preview
          if (previewImages.value.length > 0) {
              URL.revokeObjectURL(previewImages.value[0].preview);
          }
          previewImages.value = [newImage];
      }

      currentPreviewIndex.value = previewImages.value.length - 1; // Show the new image
      rotationAngle.value = 0;
      
      // Keep webcam open for multiple captures, close if single capture
      if (!allowMultiple.value || previewImages.value.length >= props.settings.max_files) {
        closeWebcam();
      }

    } else {
      console.error("Failed to convert canvas to blob.");
    }
  }, 'image/png');
};


const handleImageSelect = (event) => {
  const files = Array.from(event.target.files);
  if (!files.length) return;

  const currentCount = props.modelValue.length;
  const allowedCount = props.settings.max_files - currentCount;
  const selectedFiles = files.slice(0, allowedCount);

  // Clear existing previews if not allowing multiple or max files reached
  if (!allowMultiple.value || (allowedCount === 0 && selectedFiles.length > 0)) {
     previewImages.value.forEach(img => URL.revokeObjectURL(img.preview));
     previewImages.value = [];
  }

  const newPreviews = selectedFiles.map(file => ({
    file,
    preview: URL.createObjectURL(file)
  }));
  
  previewImages.value.push(...newPreviews);

  currentPreviewIndex.value = 0;
  rotationAngle.value = 0;
  event.target.value = ''; // reset input
};

const savePreviewImages = () => {
  const newImages = [...props.modelValue, ...previewImages.value];
  emit('update:modelValue', newImages.slice(0, props.settings.max_files));
  emit('save', props.pointId);
  previewImages.value = [];
};

const cancelPreview = () => {
  previewImages.value.forEach(img => URL.revokeObjectURL(img.preview));
  previewImages.value = [];
};

const removeImage = (index) => {
  const updated = [...props.modelValue];
  const removed = updated.splice(index, 1)[0];
  if (removed.preview) URL.revokeObjectURL(removed.preview);
  emit('update:modelValue', updated);
  emit('removeImage', { index, pointId: props.pointId });
};

const removePreviewImage = (index) => {
  const removed = previewImages.value.splice(index, 1)[0];
  if (removed.preview) URL.revokeObjectURL(removed.preview);
  
  if (previewImages.value.length === 0) {
    cancelPreview();
  } else if (currentPreviewIndex.value >= previewImages.value.length) {
    currentPreviewIndex.value = previewImages.value.length - 1;
  }
};

const rotateImage = () => {
  rotationAngle.value = (rotationAngle.value + 90) % 360;
};

const nextImage = () => {
  if (currentPreviewIndex.value < previewImages.value.length - 1) {
    currentPreviewIndex.value++;
    rotationAngle.value = 0;
  }
};

const prevImage = () => {
  if (currentPreviewIndex.value > 0) {
    currentPreviewIndex.value--;
    rotationAngle.value = 0;
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
    // Swipe left
    nextImage();
  } else if (touchEndX.value - touchStartX.value > 50) {
    // Swipe right
    prevImage();
  }
};

// Pastikan untuk menghentikan stream ketika komponen di-unmount
onMounted(() => {
  // Pastikan tidak ada stream yang tersisa dari instance sebelumnya jika di-hot-reload
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }
});

</script>

<style scoped>
/* Smooth transitions for modal */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

/* Image transition for preview */
.image-slide-enter-active,
.image-slide-leave-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.image-slide-enter-from {
  transform: translateX(100%);
  opacity: 0;
}

.image-slide-leave-to {
  transform: translateX(-100%);
  opacity: 0;
}

/* Rotation transition */
.rotate-transition {
  transition: transform 0.3s ease;
}

/* Prevent scrolling when modal is open */
body.modal-open {
  overflow: hidden;
}

/* --- Webcam Modal Styling --- */

/* Parent container for webcam modal to fill screen and center content */
.webcam-modal-container {
  /* This is already handled by fixed inset-0 from Tailwind. */
  /* Ensure no padding if you want true full screen */
  padding: 0;
}

/* The inner content box of the webcam modal */
.webcam-content-box {
  background-color: black; /* Dark background like camera apps */
  width: 100%;
  height: 100%; /* Fill available height */
  max-width: none; /* Override max-w-lg from Tailwind for true full screen */
  border-radius: 0; /* No rounded corners for full screen */
  display: flex;
  flex-direction: column;
  overflow: hidden; /* Ensure no scrollbars */
}

/* Header for webcam modal */
.webcam-header {
  background-color: rgba(0, 0, 0, 0.7); /* Slightly transparent dark header */
  color: white;
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
  z-index: 10;
  /* Tambahan untuk menyesuaikan tinggi agar tombol tidak tertutup */
  min-height: 4rem; /* Atur tinggi minimum agar tidak terlalu kecil di HP */
}

/* Video container */
.webcam-video-container {
  flex-grow: 1; /* Make video container take all available space */
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  background-color: black;
  padding: 0;
  /* Atur aspek rasio untuk kontainer video */
  width: 100%;
  padding-bottom: calc(100% * 3 / 4); /* For 4:3 aspect ratio (height is 3/4 of width) */
  /* Remove flex-grow and use absolute positioning for video if you want strict aspect ratio container */
  /* For simpler approach, we let flex-grow determine height and object-fit handle video */
  overflow: hidden; /* Ensure video doesn't overflow its calculated aspect ratio container */
}

/* Actual video element */
.webcam-video {
  position: absolute; /* Position absolutely inside its parent */
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover; /* Important: This will make the video fill the container, cropping if aspect ratios differ */
  /* object-fit: contain; Uncomment this if you prefer to see the full video frame, even with black bars */
  /* transform: scaleX(-1); /* Mirror effect for selfie camera, remove if only environment camera */
}

/* Footer for webcam controls */
.webcam-footer {
  background-color: rgba(0, 0, 0, 0.7); /* Slightly transparent dark footer */
  padding: 1rem;
  display: flex;
  justify-content: center; /* Center the capture button */
  align-items: center;
  position: relative;
  z-index: 10;
  /* Tambahan untuk menyesuaikan tinggi agar tombol tidak tertutup */
  min-height: 6rem; /* Atur tinggi minimum agar ada ruang untuk tombol */
}

/* Capture button styling */
.capture-button {
  background-color: white; /* White circle for the capture button */
  border: 4px solid rgba(255, 255, 255, 0.5); /* Outer ring */
  width: 70px; /* Large circle */
  height: 70px;
  border-radius: 50%; /* Make it a circle */
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.capture-button:hover {
  background-color: #f0f0f0;
  border-color: rgba(255, 255, 255, 0.7);
  transform: scale(1.05);
}

</style>