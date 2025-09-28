<template>
  <div v-if="show" class="fixed inset-0 z-40 p-0 webcam-modal-container">
    <div class="webcam-content-box">
      <div class="webcam-header">
         <p class="rounded-full text-white hover:bg-gray-700 transition-colors ">{{ settings.camera_aspect_ratio }}</p>
        <div class="inspection-point-name">{{ point?.name || 'Camera' }}</div>
        <button @click="closeModal" class="p-2 rounded-full text-white hover:bg-gray-700 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div 
        class="webcam-video-container" 
        :style="videoContainerStyle" 
        @click="handleTapToFocus"
        @touchstart="handleTapToFocus"
      >
        <video ref="webcamVideo" autoplay playsinline class="webcam-video"></video>
        <canvas ref="webcamCanvas" class="hidden"></canvas>
        <div v-if="showScreenFlash" class="screen-flash-overlay"></div>
        
        <div class="aspect-ratio-guide" :style="aspectRatioGuideStyle"></div>

        <div v-if="showFocusIndicator" class="focus-indicator" :style="focusIndicatorStyle">
          <div class="focus-ring"></div>
        </div>
        
        <div class="hd-badge">
          <span class="hd-text">HD</span>
        </div>

        <div v-if="isZoomSupported" class="zoom-controls">
          <input
            type="range"
            min="1"
            :max="maxZoom"
            step="0.1"
            v-model="zoomLevel"
            @input="setZoom(parseFloat($event.target.value))"
            class="zoom-slider"
          >
        </div>
      </div>

      <div class="webcam-footer">
        <div class="camera-controls">
          <button v-if="settings.enable_flash && isFlashSupported" 
            @click="toggleFlash" 
            class="control-button"
            :class="{'active': isFlashOn, 'disabled': !isFlashSupported}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </button>
          
          <button @click="capturePhoto" class="capture-button" :disabled="isTakingPhoto">
            <div class="camera-icon-container">
              <div v-if="isTakingPhoto" class="camera-spinner"></div>
              <div v-else class="camera-body">
                <div class="camera-lens"></div>
                <div class="camera-flash"></div>
              </div>
            </div>
          </button>

          <button v-if="settings.enable_camera_switch && hasMultipleCameras" 
            @click="switchCamera" 
            class="control-button">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  show: Boolean,
  aspectRatio: Number,
  settings: Object,
  point: Object
});
const emit = defineEmits(['close', 'photoCaptured']);

const webcamVideo = ref(null);
const webcamCanvas = ref(null);

let mediaStream = null;
let videoTrack = null;

const currentFacingMode = ref('environment');
const hasMultipleCameras = ref(false);
const isFlashSupported = ref(false);
const isFlashOn = ref(false);
const showScreenFlash = ref(false);
const showFocusIndicator = ref(false);
const focusIndicatorStyle = ref({});
const isTakingPhoto = ref(false);
const zoomLevel = ref(1);
const maxZoom = ref(1);
const isZoomSupported = ref(false);

const maxSizeKB = computed(() => {
  return props.settings?.max_size || 2048; // Default 2MB jika tidak ada setting
});

// Computed property untuk style video container berdasarkan aspect ratio
const videoContainerStyle = computed(() => {
  if (!props.aspectRatio) return {};
  
  return {
    aspectRatio: `${props.aspectRatio} / 1`,
    maxWidth: '100%',
    maxHeight: '70vh',
    margin: '0 auto'
  };
});

// Computed property untuk aspect ratio guide
const aspectRatioGuideStyle = computed(() => {
  if (!props.aspectRatio) return {};
  
  const ratio = props.aspectRatio;
  const width = ratio > 1 ? 80 : 60;
  const height = ratio > 1 ? 80 / ratio : 60 * ratio;
  
  return {
    width: `${width}%`,
    height: `${height}%`
  };
});

const initializeWebcam = async () => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }
  
  try {
    const videoConstraints = {
      facingMode: currentFacingMode.value,
      width: { ideal: 4096 }, // Resolusi 4K untuk kualitas terbaik
      height: { ideal: 2160 }, // Resolusi 4K untuk kualitas terbaik
      aspectRatio: { ideal: props.aspectRatio || 4/3 },
      frameRate: { ideal: 30 },
      advanced: [
        { focusMode: 'continuous' },
        { exposureMode: 'continuous' },
        { whiteBalanceMode: 'continuous' }
      ]
    };
    
    mediaStream = await navigator.mediaDevices.getUserMedia({ 
      video: videoConstraints,
      audio: false 
    });
    
    videoTrack = mediaStream.getVideoTracks()[0];
    webcamVideo.value.srcObject = mediaStream;
    
    webcamVideo.value.onloadedmetadata = async () => {
      await checkCameraCapabilities();
      // Auto-play the video
      await webcamVideo.value.play();
    };
    
  } catch (err) {
    console.error("Error accessing camera: ", err);
    alert('Failed to access camera. Please allow camera permission.');
    emit('close');
  }
};

const closeModal = () => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }
  emit('close');
};

const checkCameraCapabilities = async () => {
  if (!videoTrack) return;
  
  try {
    const devices = await navigator.mediaDevices.enumerateDevices();
    hasMultipleCameras.value = devices.filter(d => d.kind === 'videoinput').length > 1;
    
    const capabilities = videoTrack.getCapabilities();
    
    // Deteksi kemampuan zoom
    if (capabilities.zoom) {
      isZoomSupported.value = true;
      maxZoom.value = capabilities.zoom.max;
      console.log(`Zoom supported. Max zoom: ${maxZoom.value}`);
    } else {
      isZoomSupported.value = false;
      console.warn("Zoom is not supported by this camera.");
    }

    isFlashSupported.value = capabilities.torch || 
      (capabilities.fillLightMode && capabilities.fillLightMode.includes('torch'));
  } catch (error) {
    console.error("Error checking camera capabilities:", error);
  }
};

const handleTapToFocus = async (event) => {
  if (!videoTrack) return;
  
  const rect = webcamVideo.value.getBoundingClientRect();
  const x = event.clientX - rect.left;
  const y = event.clientY - rect.top;
  
  // Show focus indicator
  showFocusIndicator.value = true;
  focusIndicatorStyle.value = {
    left: `${x}px`,
    top: `${y}px`,
    transform: 'translate(-50%, -50%)'
  };

  try {
    const capabilities = videoTrack.getCapabilities();
    if (capabilities.pointsOfInterest) {
      const focusX = x / rect.width;
      const focusY = y / rect.height;
      
      await videoTrack.applyConstraints({
        advanced: [{
          focusMode: 'manual',
          pointsOfInterest: [{ x: focusX, y: focusY }]
        }]
      });
    } else {
      console.warn("Manual focus with point of interest is not supported.");
    }
  } catch (error) {
    console.warn("Manual focus failed:", error);
  }
  
  setTimeout(() => {
    showFocusIndicator.value = false;
    // Kembali ke fokus otomatis setelah 1 detik
    if (videoTrack.getCapabilities().focusMode.includes('continuous')) {
        videoTrack.applyConstraints({
            advanced: [{ focusMode: 'continuous' }]
        });
    }
  }, 1000);
};

// Fungsi baru untuk mengatur zoom
const setZoom = async (level) => {
  if (!videoTrack || !isZoomSupported.value) return;

  const newZoom = Math.min(Math.max(level, 1), maxZoom.value);
  zoomLevel.value = newZoom;
  
  try {
    await videoTrack.applyConstraints({
      advanced: [{ zoom: newZoom }]
    });
  } catch (error) {
    console.error("Failed to set zoom:", error);
  }
};

const toggleFlash = async () => {
  if (!videoTrack || !isFlashSupported.value) return;
  
  try {
    // For front camera, use screen flash effect
    if (currentFacingMode.value === 'user') {
      isFlashOn.value = !isFlashOn.value;
      showScreenFlash.value = isFlashOn.value;
    } else {
      // For rear camera, toggle torch mode
      await videoTrack.applyConstraints({ 
        advanced: [{ torch: !isFlashOn.value }] 
      });
      isFlashOn.value = !isFlashOn.value;
    }
  } catch (err) {
    console.error("Flash toggle failed:", err);
  }
};

const switchCamera = async () => {
  if (!hasMultipleCameras.value) return;
  
  currentFacingMode.value = currentFacingMode.value === 'environment' ? 'user' : 'environment';
  
  // Stop existing stream
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }
  
  // Restart webcam with new facing mode
  await initializeWebcam();
};

const compressImage = async (blob) => {
  return new Promise((resolve) => {
    const MAX_SIZE = maxSizeKB.value * 1024;
    
    if (blob.size <= MAX_SIZE) {
      resolve(blob);
      return;
    }

    const img = new Image();
    const reader = new FileReader();
    
    reader.onload = (e) => {
      img.onload = () => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        const scaleFactor = Math.sqrt(MAX_SIZE / blob.size);
        canvas.width = img.width * scaleFactor;
        canvas.height = img.height * scaleFactor;
        
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        
        let quality = 0.9;
        
        const compressRecursive = () => {
          canvas.toBlob((compressedBlob) => {
            if (compressedBlob.size > MAX_SIZE && quality > 0.1) {
              quality -= 0.1;
              compressRecursive();
            } else {
              resolve(compressedBlob);
            }
          }, 'image/jpeg', quality);
        };
        
        compressRecursive();
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(blob);
  });
};

const capturePhoto = async () => {
  if (isTakingPhoto.value || !webcamVideo.value || !webcamCanvas.value) return;
  
  isTakingPhoto.value = true;
  
  try {
    const video = webcamVideo.value;
    const canvas = webcamCanvas.value;
    
    const vw = video.videoWidth;
    const vh = video.videoHeight;
    
    let sw, sh, sx, sy;
    
    if (vw / vh > props.aspectRatio) {
      sh = vh;
      sw = vh * props.aspectRatio;
      sx = (vw - sw) / 2;
      sy = 0;
    } else {
      sw = vw;
      sh = vw / props.aspectRatio;
      sx = 0;
      sy = (vh - sh) / 2;
    }
    
    canvas.width = sw;
    canvas.height = sh;
    
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, sx, sy, sw, sh, 0, 0, sw, sh);
    
    const originalBlob = await new Promise(resolve => {
      canvas.toBlob(resolve, 'image/jpeg', 1.0);
    });
    
    const compressedBlob = await compressImage(originalBlob);
    
    if (compressedBlob) {
      const file = new File([compressedBlob], `capture_${Date.now()}.jpeg`, { 
        type: 'image/jpeg',
        lastModified: Date.now()
      });
      
      emit('photoCaptured', file);
      
      // Screen flash effect
      showScreenFlash.value = true;
      setTimeout(() => {
        showScreenFlash.value = false;
      }, 100);
    }
    
  } catch (error) {
    console.error("Error capturing photo:", error);
    alert("Failed to capture photo. Please try again.");
  } finally {
    isTakingPhoto.value = false;
  }
};

// Watch for prop changes
watch(() => props.show, (v) => { 
  if (v) {
    initializeWebcam();
  } else {
    closeModal();
  }
});

onUnmounted(() => { 
  if (mediaStream) {
    mediaStream.getTracks().forEach(t => t.stop());
  }
});
</script>

---

<style scoped>
.webcam-modal-container {
  background-color: rgba(0, 0, 0, 0.95);
  display: flex;
  align-items: center;
  justify-content: center;
}

.webcam-content-box {
  background: #000;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  max-width: 100%;
  max-height: 100vh;
}

.webcam-header {
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0;
}

.inspection-point-name {
  flex-grow: 1;
  text-align: center;
  font-weight: 600;
  font-size: 1.1rem;
}

.webcam-video-container {
  position: relative;
  flex-grow: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #000;
  overflow: hidden;
  margin: 0 auto;
}

.webcam-video {
  width: 100%;
  height: 100%;
  object-fit: cover;
  filter: brightness(1.05) contrast(1.05);
}

.aspect-ratio-guide {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  border: 2px dashed rgba(255, 255, 255, 0.5);
  pointer-events: none;
  z-index: 10;
}

.webcam-footer {
  background: rgba(0, 0, 0, 0.8);
  padding: 1.5rem 1rem;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-shrink: 0;
}

.camera-controls {
  display: flex;
  justify-content: space-around;
  align-items: center;
  width: 100%;
  max-width: 320px;
}

.control-button {
  background: rgba(255, 255, 255, 0.9);
  border: none;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.control-button:hover {
  transform: scale(1.08);
  background: rgba(255, 255, 255, 1);
}

.control-button.active {
  background: #ffeb3b;
}

.control-button.disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.control-button svg {
  stroke: #000;
  width: 24px;
  height: 24px;
}

.capture-button {
  background: #fff;
  border: none;
  width: 70px;
  height: 70px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}

.capture-button:hover {
  transform: scale(1.05);
  background: #f0f0f0;
}

.capture-button:active {
  transform: scale(0.95);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.camera-icon-container {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  height: 100%;
}

.camera-body {
  position: relative;
  width: 32px;
  height: 32px;
  background: #333;
  border-radius: 8px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.camera-lens {
  width: 20px;
  height: 20px;
  background: #666;
  border-radius: 50%;
  border: 2px solid #999;
}

.camera-flash {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 6px;
  height: 6px;
  background: #ccc;
  border-radius: 50%;
}

.camera-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.screen-flash-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.8);
  z-index: 20;
  animation: flash 0.3s ease-out;
}

@keyframes flash {
  0% { opacity: 1; }
  100% { opacity: 0; }
}

.focus-indicator {
  position: absolute;
  width: 60px;
  height: 60px;
  z-index: 15;
  pointer-events: none;
}

.focus-ring {
  width: 100%;
  height: 100%;
  border: 2px solid #00ff00;
  border-radius: 50%;
  animation: focusPulse 1s ease-in-out;
}

@keyframes focusPulse {
  0% { transform: scale(0.8); opacity: 0; }
  50% { transform: scale(1.2); opacity: 1; }
  100% { transform: scale(1); opacity: 1; }
}

.hd-badge {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(0, 0, 0, 0.7);
  padding: 4px 8px;
  border-radius: 4px;
  z-index: 15;
}

.hd-text {
  color: #00ff00;
  font-size: 12px;
  font-weight: bold;
}

/* Tambahan CSS untuk kontrol zoom */
.zoom-controls {
  position: absolute;
  bottom: 10px;
  width: 80%;
  left: 50%;
  transform: translateX(-50%);
  z-index: 25;
  padding: 10px;
  background: rgba(0, 0, 0, 0.5);
  border-radius: 20px;
  display: flex;
  justify-content: center;
}

.zoom-slider {
  width: 90%;
  -webkit-appearance: none;
  height: 8px;
  background: rgba(255, 255, 255, 0.4);
  border-radius: 5px;
  outline: none;
  opacity: 0.7;
  transition: opacity .2s;
}

.zoom-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 20px;
  height: 20px;
  background: white;
  border-radius: 50%;
  cursor: pointer;
  box-shadow: 0 0 5px rgba(0,0,0,0.3);
}

.zoom-slider::-moz-range-thumb {
  width: 20px;
  height: 20px;
  background: white;
  border-radius: 50%;
  cursor: pointer;
}
</style>