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
              <span class="text-sm font-medium text-gray-700">Camera</span>
            </button>
            
            <button @click="triggerGallery" class="flex flex-col items-center justify-center w-24 h-24 rounded-xl bg-purple-50 hover:bg-purple-100 transition-colors">
              <div class="bg-purple-100 p-3 rounded-full mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <span class="text-sm font-medium text-gray-700">Gallery</span>
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
                <div class="flex gap-4">
                    <button @click="toggleFlash" :disabled="!isFlashSupported" class="p-2 rounded-full text-white" :class="{'bg-gray-700': isFlashOn, 'hover:bg-gray-700': isFlashSupported, 'opacity-50 cursor-not-allowed': !isFlashSupported}">
                        <svg v-if="!isFlashSupported" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.102 1.101m-.757 4.898l-4 4L9.828 9.828m4.899-.757l4-4L14.172 14.172" />
                        </svg>
                        <svg v-else-if="isFlashOn" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6" />
                        </svg>
                    </button>
                    <button @click="switchCamera" :disabled="!hasMultipleCameras" class="p-2 rounded-full text-white hover:bg-gray-700" :class="{'opacity-50 cursor-not-allowed': !hasMultipleCameras}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16M6 4h.01M6 20h.01M10 4h.01M10 20h.01M14 4h.01M14 20h.01M18 4h.01M18 20h.01" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="webcam-video-container" @click="triggerFocus">
                <video ref="webcamVideo" autoplay playsinline class="webcam-video"></video>
                <canvas ref="webcamCanvas" class="hidden"></canvas>
                 <div v-if="showScreenFlash" class="screen-flash-overlay"></div>
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
import { ref, computed, onMounted, watch } from 'vue'; // Import watch

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  error: String,
  pointId: [String, Number],
  settings: {
    type: Object,
    default: () => ({
      max_files: 1,
      allowed_types: ['jpg', 'png'],
      camera_aspect_ratio: '4:3',
      // New settings for camera features
      enable_flash: true,
      enable_camera_switch: true,
    })
  }
});

const emit = defineEmits(['update:modelValue', 'save', 'removeImage']);

const galleryInput = ref(null);
const webcamVideo = ref(null);
const webcamCanvas = ref(null);
const processingCanvas = ref(null); // Ref for the new hidden canvas

const previewImages = ref([]);
const showFileOptions = ref(false);
const showWebcamModal = ref(false);
const currentPreviewIndex = ref(0);
const rotationAngle = ref(0);
const touchStartX = ref(0);
const touchEndX = ref(0);

let mediaStream = null;
let videoTrack = null; // To hold the active video track for constraints

// Camera specific states
const currentFacingMode = ref('environment'); // 'environment' (back) or 'user' (front)
const hasMultipleCameras = ref(false);
const isFlashSupported = ref(false); // Does the current camera support torch?
const isFlashOn = ref(false);
const showScreenFlash = ref(false); // For front camera flash simulation

const allowMultiple = computed(() => Number(props.settings.max_files) > 1);
const currentPreviewImage = computed(() => {
  const img = previewImages.value[currentPreviewIndex.value];
  if (img) {
    return {
      ...img,
      rotation: rotationAngle.value // Use rotationAngle that is active for current image
    };
  }
  return null;
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

const openFileOptions = () => {
  showFileOptions.value = true;
};

const triggerGallery = () => {
  showFileOptions.value = false;
  galleryInput.value.click();
};

const initializeWebcam = async () => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }

  try {
    const videoConstraints = {
      facingMode: currentFacingMode.value,
      width: { ideal: 1280 },
      height: { ideal: 960 },
      aspectRatio: { exact: aspectRatio.value }
    };

    // Try to get advanced focus mode if supported
    if (currentFacingMode.value === 'environment') { // Autofocus usually more relevant for back camera
        videoConstraints.advanced = [{ focusMode: "continuous" }];
    }

    mediaStream = await navigator.mediaDevices.getUserMedia({ video: videoConstraints });
    videoTrack = mediaStream.getVideoTracks()[0];
    webcamVideo.value.srcObject = mediaStream;

    webcamVideo.value.onloadedmetadata = async () => {
      webcamVideo.value.style.width = '100%';
      webcamVideo.value.style.height = 'auto';
      webcamVideo.value.style.objectFit = 'cover';

      // Check camera capabilities for flash and switch
      await checkCameraCapabilities();
    };

  } catch (err) {
    console.error("Error accessing camera: ", err);
    alert('Failed to access camera. Please ensure camera permissions are granted and try again.');
    showWebcamModal.value = false;
  }
};

const openWebcam = async () => {
  showFileOptions.value = false;
  showWebcamModal.value = true;
  await initializeWebcam();
};

const closeWebcam = () => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }
  showWebcamModal.value = false;
  isFlashOn.value = false; // Reset flash state
};

const checkCameraCapabilities = async () => {
  if (!videoTrack) return;

  // Check for multiple cameras
  const devices = await navigator.mediaDevices.enumerateDevices();
  const videoDevices = devices.filter(device => device.kind === 'videoinput');
  hasMultipleCameras.value = videoDevices.length > 1;

  // Check for torch/flash
  const capabilities = videoTrack.getCapabilities();
  isFlashSupported.value = capabilities.torch || (capabilities.fillLightMode && capabilities.fillLightMode.includes('torch'));
};

const toggleFlash = async () => {
  if (!videoTrack || !props.settings.enable_flash) return;

  if (currentFacingMode.value === 'user') {
      // Simulate screen flash for front camera
      isFlashOn.value = !isFlashOn.value; // Toggle icon state
      // Actual screen flash effect happens during capture
      return;
  }

  // For environment camera (back camera)
  try {
      if (videoTrack.getCapabilities().torch) {
          await videoTrack.applyConstraints({
              advanced: [{ torch: !isFlashOn.value }]
          });
          isFlashOn.value = !isFlashOn.value;
      } else {
          // Fallback if torch is not directly supported but some other flash mode is
          console.warn("Torch API not directly supported, checking fillLightMode.");
          const capabilities = videoTrack.getCapabilities();
          if (capabilities.fillLightMode && capabilities.fillLightMode.includes('torch')) {
              await videoTrack.applyConstraints({
                  fillLightMode: isFlashOn.value ? 'none' : 'torch'
              });
              isFlashOn.value = !isFlashOn.value;
          } else {
              alert("Flash/Torch is not supported on this camera.");
              isFlashSupported.value = false; // Disable button
          }
      }
  } catch (err) {
      console.error("Error toggling flash: ", err);
      alert("Could not toggle flash. Feature might not be supported.");
      isFlashSupported.value = false; // Disable button
  }
};

const switchCamera = async () => {
  if (!hasMultipleCameras.value || !props.settings.enable_camera_switch) return;

  currentFacingMode.value = currentFacingMode.value === 'environment' ? 'user' : 'environment';
  await initializeWebcam(); // Re-initialize stream with new facingMode
  isFlashOn.value = false; // Reset flash state when switching camera
};

const triggerFocus = async () => {
    if (!videoTrack) return;
    const capabilities = videoTrack.getCapabilities();
    if (capabilities.focusMode && capabilities.focusMode.includes('continuous')) {
        console.log("Triggering continuous autofocus (if supported).");
        // Re-apply constraints to nudge autofocus
        try {
            await videoTrack.applyConstraints({
                advanced: [{ focusMode: "continuous" }]
            });
        } catch (e) {
            console.warn("Failed to apply continuous focus constraint:", e);
        }
    } else {
        console.log("Autofocus not explicitly controllable or not supported.");
    }
};

const capturePhoto = async () => {
  if (!webcamVideo.value || !webcamCanvas.value) return;

  // --- Screen Flash for Front Camera Simulation ---
  if (currentFacingMode.value === 'user' && isFlashOn.value) {
      showScreenFlash.value = true;
      await new Promise(resolve => setTimeout(resolve, 100)); // Flash for 100ms
      showScreenFlash.value = false;
  }
  // --- End Screen Flash ---

  const video = webcamVideo.value;
  const canvas = webcamCanvas.value;
  const context = canvas.getContext('2d');

  const videoWidth = video.videoWidth;
  const videoHeight = video.videoHeight;
  
  let drawWidth = videoWidth;
  let drawHeight = videoHeight;
  let offsetX = 0;
  let offsetY = 0;

  if (videoWidth / videoHeight > aspectRatio.value) {
    drawWidth = videoHeight * aspectRatio.value;
    offsetX = (videoWidth - drawWidth) / 2;
  } else if (videoWidth / videoHeight < aspectRatio.value) {
    drawHeight = videoWidth / aspectRatio.value;
    offsetY = (videoHeight - drawHeight) / 2;
  }
  
  canvas.width = drawWidth;
  canvas.height = drawHeight;

  context.drawImage(
    video,
    offsetX, offsetY,
    drawWidth, drawHeight,
    0, 0,
    canvas.width, canvas.height
  );

  canvas.toBlob((blob) => {
    if (blob) {
      const file = new File([blob], `captured_image_${Date.now()}.png`, { type: 'image/png' });
      
      const newImage = {
        file: file,
        preview: URL.createObjectURL(file),
        rotation: 0
      };

      if (allowMultiple.value && props.modelValue.length < props.settings.max_files) {
          previewImages.value.push(newImage);
      } else {
          if (previewImages.value.length > 0) {
              URL.revokeObjectURL(previewImages.value[0].preview);
          }
          previewImages.value = [newImage];
      }

      currentPreviewIndex.value = previewImages.value.length - 1;
      rotationAngle.value = 0;
      
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

  if (!allowMultiple.value || (allowedCount === 0 && selectedFiles.length > 0)) {
     previewImages.value.forEach(img => URL.revokeObjectURL(img.preview));
     previewImages.value = [];
  }

  const newPreviews = selectedFiles.map(file => ({
    file,
    preview: URL.createObjectURL(file),
    rotation: 0
  }));
  
  previewImages.value.push(...newPreviews);

  currentPreviewIndex.value = 0;
  rotationAngle.value = 0;
  event.target.value = '';
};

const applyRotationToImage = (imageObject) => {
    return new Promise((resolve) => {
        if (imageObject.rotation === 0 || !processingCanvas.value) {
            resolve(imageObject.file);
            return;
        }

        const img = new Image();
        img.onload = () => {
            const canvas = processingCanvas.value;
            const context = canvas.getContext('2d');

            const width = img.width;
            const height = img.height;

            if (imageObject.rotation === 90 || imageObject.rotation === 270) {
                canvas.width = height;
                canvas.height = width;
            } else {
                canvas.width = width;
                canvas.height = height;
            }

            context.clearRect(0, 0, canvas.width, canvas.height);
            context.translate(canvas.width / 2, canvas.height / 2);
            context.rotate(imageObject.rotation * Math.PI / 180);
            context.drawImage(img, -width / 2, -height / 2, width, height);
            context.setTransform(1, 0, 0, 1, 0, 0);

            canvas.toBlob((blob) => {
                const newFile = new File([blob], imageObject.file.name, { type: imageObject.file.type });
                resolve(newFile);
            }, imageObject.file.type);
        };
        img.src = imageObject.preview;
    });
};

const savePreviewImages = async () => {
    const processedImages = [];
    for (const img of previewImages.value) {
        const rotatedFile = await applyRotationToImage(img);
        processedImages.push({
            file: rotatedFile,
            preview: URL.createObjectURL(rotatedFile)
        });
        if (img.preview) URL.revokeObjectURL(img.preview);
    }

    const newImages = [...props.modelValue, ...processedImages];
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
    const currentImage = previewImages.value[currentPreviewIndex.value];
    if (currentImage) {
        currentImage.rotation = (currentImage.rotation + 90) % 360;
        rotationAngle.value = currentImage.rotation;
    }
};

const nextImage = () => {
  if (currentPreviewIndex.value < previewImages.value.length - 1) {
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

onMounted(() => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }
});

watch(currentPreviewIndex, (newIndex) => {
  if (previewImages.value[newIndex]) {
    rotationAngle.value = previewImages.value[newIndex].rotation;
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
  padding: 0;
  background-color: rgba(0, 0, 0, 0.9); /* Sedikit transparansi untuk efek modal */
}

/* The inner content box of the webcam modal */
.webcam-content-box {
  background-color: black;
  width: 100%;
  height: 100%;
  max-width: none;
  border-radius: 0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Header for webcam modal */
.webcam-header {
  background-color: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
  z-index: 10;
  min-height: 4rem;
}

/* Video container */
.webcam-video-container {
  flex-grow: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  background-color: black;
  padding: 0;
  width: 100%;
  padding-bottom: calc(100% * 3 / 4); /* For 4:3 aspect ratio (height is 3/4 of width) */
  overflow: hidden;
  cursor: pointer; /* Indicate it's clickable for focus */
}

/* Actual video element */
.webcam-video {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  /* transform: scaleX(-1); /* Mirror effect for selfie camera, remove if only environment camera */
}

/* Footer for webcam controls */
.webcam-footer {
  background-color: rgba(0, 0, 0, 0.7);
  padding: 1rem;
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
  z-index: 10;
  min-height: 6rem;
}

/* Capture button styling */
.capture-button {
  background-color: white;
  border: 4px solid rgba(255, 255, 255, 0.5);
  width: 70px;
  height: 70px;
  border-radius: 50%;
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

/* Screen Flash Overlay */
.screen-flash-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: white;
    opacity: 1;
    z-index: 20; /* Above video but below header/footer */
    transition: opacity 0.05s ease-out; /* Very quick fade */
}
</style>