<template>
  <div v-if="show" class="fixed inset-0 z-40 p-0 webcam-modal-container">
    <div class="webcam-content-box">
      <div class="webcam-header">
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
      >
        <video 
          ref="webcamVideo" 
          autoplay 
          playsinline 
          class="webcam-video"
        ></video>
        <canvas ref="webcamCanvas" class="hidden"></canvas>

        <!-- Focus Indicator -->
        <div v-if="showFocusIndicator" class="focus-indicator" :style="focusIndicatorStyle">
          <div class="focus-ring"></div>
        </div>

        <!-- Auto Focus Indicator -->
        <div v-if="showAutoFocus" class="auto-focus-indicator">
          <div class="auto-focus-ring"></div>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="loading-overlay">
          <div class="loading-spinner"></div>
          <p class="loading-text">Menyiapkan kamera...</p>
        </div>

        <!-- Error State -->
        <div v-if="error" class="error-overlay">
          <div class="error-content">
            <div class="error-icon">⚠️</div>
            <h3 class="error-title">Gagal Mengakses Kamera</h3>
            <p class="error-message">{{ error }}</p>
            <button @click="retryCamera" class="retry-button">
              Coba Lagi
            </button>
          </div>
        </div>
      </div>

      <div class="webcam-footer">
        <div class="camera-controls">
          <button 
            v-if="settings.enable_flash && isFlashSupported" 
            @click="toggleFlash" 
            class="control-button"
            :class="{'active': isFlashOn}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </button>
          
          <button @click="capturePhoto" class="capture-button" :disabled="isTakingPhoto || isLoading">
            <div class="camera-icon-container">
              <div v-if="isTakingPhoto" class="camera-spinner"></div>
              <div v-else class="camera-shutter"></div>
            </div>
          </button>

          <button 
            v-if="settings.enable_camera_switch && hasMultipleCameras" 
            @click="switchCamera" 
            class="control-button"
            :disabled="isLoading">
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
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
  show: Boolean,
  aspectRatio: Number,
  settings: Object,
  point: Object
});
const emit = defineEmits(['close', 'photoCaptured']);

const webcamVideo = ref(null);
const webcamCanvas = ref(null);

// RTC State Management
let mediaStream = null;
let videoTrack = null;

// Camera State
const currentFacingMode = ref('environment');
const currentDeviceId = ref(null);
const cameraDevices = ref([]);
const hasMultipleCameras = ref(false);
const isFlashSupported = ref(false);
const isFlashOn = ref(false);
const isTakingPhoto = ref(false);
const isLoading = ref(false);
const error = ref(null);

// Auto Focus
const showFocusIndicator = ref(false);
const showAutoFocus = ref(true);
const focusIndicatorStyle = ref({});

// Computed Properties
const maxSizeKB = computed(() => {
  return props.settings?.max_size || 2048;
});

const videoContainerStyle = computed(() => {
  if (!props.aspectRatio) return {};
  
  return {
    aspectRatio: `${props.aspectRatio} / 1`,
    maxWidth: '100%',
    maxHeight: '70vh',
    margin: '0 auto'
  };
});

// Optimized Camera Initialization - Full Auto
const initializeWebcam = async () => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }
  
  isLoading.value = true;
  error.value = null;
  
  try {
    await getCameraDevices();
    
    // Simple auto constraints - biarkan browser yang menentukan setting terbaik
    const videoConstraints = {
      deviceId: currentDeviceId.value ? { exact: currentDeviceId.value } : undefined,
      facingMode: currentDeviceId.value ? undefined : { ideal: currentFacingMode.value },
      // Biarkan browser memilih resolusi terbaik secara otomatis
      width: { ideal: 1920 },
      height: { ideal: 1080 },
      // Auto settings untuk exposure dan focus
      exposureMode: 'continuous',
      focusMode: 'continuous',
      whiteBalanceMode: 'continuous'
    };
    
    mediaStream = await navigator.mediaDevices.getUserMedia({ 
      video: videoConstraints,
      audio: false 
    });
    
    videoTrack = mediaStream.getVideoTracks()[0];
    webcamVideo.value.srcObject = mediaStream;
    
    // Fast initialization
    await new Promise((resolve) => {
      if (webcamVideo.value.readyState >= 2) {
        resolve();
      } else {
        webcamVideo.value.onloadeddata = resolve;
        setTimeout(resolve, 300);
      }
    });
    
    await webcamVideo.value.play();
    await checkCameraCapabilities();
    
    // Start auto focus indicator
    startAutoFocusIndicator();
    
  } catch (err) {
    console.error("Error accessing camera: ", err);
    error.value = getErrorMessage(err);
  } finally {
    isLoading.value = false;
  }
};

// Auto focus indicator animation
const startAutoFocusIndicator = () => {
  let focusState = true;
  
  const animateFocus = () => {
    if (!mediaStream) return;
    
    showAutoFocus.value = focusState;
    focusState = !focusState;
    
    setTimeout(animateFocus, focusState ? 2000 : 500);
  };
  
  animateFocus();
};

const getCameraDevices = async () => {
  try {
    const tempStream = await navigator.mediaDevices.getUserMedia({ video: true });
    tempStream.getTracks().forEach(track => track.stop());
    
    const devices = await navigator.mediaDevices.enumerateDevices();
    cameraDevices.value = devices.filter(d => d.kind === 'videoinput');
    hasMultipleCameras.value = cameraDevices.value.length > 1;
    
    if (!currentDeviceId.value && cameraDevices.value.length > 0) {
      const rearCamera = cameraDevices.value.find(device => 
        device.label.toLowerCase().includes('back') || 
        device.label.toLowerCase().includes('rear') ||
        device.label.toLowerCase().includes('environment')
      );
      currentDeviceId.value = rearCamera ? rearCamera.deviceId : cameraDevices.value[0].deviceId;
    }
  } catch (err) {
    console.error('Error getting camera devices:', err);
  }
};

const getErrorMessage = (error) => {
  switch(error.name) {
    case 'NotAllowedError':
      return 'Izin akses kamera ditolak. Silakan izinkan akses kamera di pengaturan browser.';
    case 'NotFoundError':
      return 'Tidak ada kamera yang ditemukan.';
    case 'NotSupportedError':
      return 'Browser tidak mendukung akses kamera.';
    case 'NotReadableError':
      return 'Kamera sedang digunakan oleh aplikasi lain.';
    default:
      return `Tidak dapat mengakses kamera: ${error.message}`;
  }
};

const checkCameraCapabilities = async () => {
  if (!videoTrack) return;
  
  try {
    const capabilities = videoTrack.getCapabilities();
    isFlashSupported.value = !!capabilities.torch;
  } catch (error) {
    console.error("Error checking camera capabilities:", error);
  }
};

const toggleFlash = async () => {
  if (!videoTrack || !isFlashSupported.value) return;
  
  try {
    if (currentFacingMode.value === 'user') {
      isFlashOn.value = !isFlashOn.value;
    } else {
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
  if (!hasMultipleCameras.value || isLoading.value) return;
  
  isLoading.value = true;
  
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }
  
  const currentIndex = cameraDevices.value.findIndex(d => d.deviceId === currentDeviceId.value);
  const nextIndex = (currentIndex + 1) % cameraDevices.value.length;
  currentDeviceId.value = cameraDevices.value[nextIndex].deviceId;
  
  const nextCamera = cameraDevices.value[nextIndex];
  currentFacingMode.value = nextCamera.label.toLowerCase().includes('front') ? 'user' : 'environment';
  
  await initializeWebcam();
};

// Ultra Fast Photo Capture - Full Auto
const capturePhoto = async () => {
  if (isTakingPhoto.value || !webcamVideo.value || !webcamCanvas.value) return;
  
  isTakingPhoto.value = true;
  
  try {
    const video = webcamVideo.value;
    const canvas = webcamCanvas.value;
    
    const vw = video.videoWidth;
    const vh = video.videoHeight;
    
    // Fast crop calculation based on aspect ratio
    let sw, sh, sx, sy;
    
    if (vw / vh > props.aspectRatio) {
      // Video lebih lebar dari aspect ratio yang diinginkan
      sh = vh;
      sw = vh * props.aspectRatio;
      sx = (vw - sw) / 2;
      sy = 0;
    } else {
      // Video lebih tinggi dari aspect ratio yang diinginkan
      sw = vw;
      sh = vw / props.aspectRatio;
      sx = 0;
      sy = (vh - sh) / 2;
    }
    
    canvas.width = sw;
    canvas.height = sh;
    
    const ctx = canvas.getContext('2d');
    
    // Ultra fast capture - langsung ambil frame
    ctx.drawImage(video, sx, sy, sw, sh, 0, 0, sw, sh);
    
    // Fast compression dengan quality optimal
    const blob = await new Promise(resolve => {
      canvas.toBlob(resolve, 'image/jpeg', 0.92);
    });
    
    if (blob) {
      const fileName = `inspeksi_${props.point?.name || 'foto'}_${Date.now()}.jpg`;
      const file = new File([blob], fileName, { 
        type: 'image/jpeg',
        lastModified: Date.now()
      });
      
      emit('photoCaptured', file);
    }
    
  } catch (error) {
    console.error("Error capturing photo:", error);
  } finally {
    isTakingPhoto.value = false;
  }
};

const closeModal = () => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }
  emit('close');
};

const retryCamera = async () => {
  error.value = null;
  await initializeWebcam();
};

// Watchers and lifecycle
watch(() => props.show, async (v) => { 
  if (v) {
    await nextTick();
    await initializeWebcam();
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
}

/* Auto Focus Indicator */
.auto-focus-indicator {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 120px;
  height: 120px;
  pointer-events: none;
  z-index: 10;
}

.auto-focus-ring {
  width: 100%;
  height: 100%;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  animation: autoFocusPulse 2s ease-in-out infinite;
}

@keyframes autoFocusPulse {
  0%, 100% { 
    transform: scale(0.8);
    opacity: 0.3;
    border-color: rgba(255, 255, 255, 0.3);
  }
  50% { 
    transform: scale(1.1);
    opacity: 0.6;
    border-color: rgba(0, 255, 0, 0.5);
  }
}

/* Focus Indicator */
.focus-indicator {
  position: absolute;
  width: 80px;
  height: 80px;
  z-index: 15;
  pointer-events: none;
}

.focus-ring {
  width: 100%;
  height: 100%;
  border: 3px solid #00ff00;
  border-radius: 50%;
  background: rgba(0, 255, 0, 0.1);
  animation: focusPulse 0.8s ease-out;
}

@keyframes focusPulse {
  0% { transform: scale(0.5); opacity: 0; }
  50% { transform: scale(1.1); opacity: 1; }
  100% { transform: scale(1); opacity: 1; }
}

/* Footer Controls */
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

.control-button.active {
  background: #FFC107;
  animation: flashActive 1s ease-in-out infinite;
}

@keyframes flashActive {
  0%, 100% { background: #FFC107; }
  50% { background: #FFA000; }
}

.control-button:hover:not(:disabled) {
  transform: scale(1.08);
  background: rgba(255, 255, 255, 1);
}

.control-button:disabled {
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
  transition: all 0.1s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}

.capture-button:hover:not(:disabled) {
  transform: scale(1.05);
  background: #f8f9fa;
}

.capture-button:active:not(:disabled) {
  transform: scale(0.95);
}

.capture-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.camera-icon-container {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  height: 100%;
}

.camera-shutter {
  width: 60px;
  height: 60px;
  background: #dc3545;
  border-radius: 50%;
  border: 4px solid white;
  transition: all 0.1s ease;
}

.capture-button:active:not(:disabled) .camera-shutter {
  background: #c82333;
  transform: scale(0.9);
}

.camera-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Loading and Error States */
.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  z-index: 30;
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

.loading-text {
  color: white;
  font-size: 1rem;
}

.error-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 30;
}

.error-content {
  text-align: center;
  color: white;
  padding: 2rem;
  max-width: 80%;
}

.error-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.error-title {
  font-size: 1.5rem;
  margin-bottom: 1rem;
  font-weight: bold;
}

.error-message {
  margin-bottom: 2rem;
  line-height: 1.5;
}

.retry-button {
  padding: 0.75rem 2rem;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 25px;
  cursor: pointer;
  font-size: 1rem;
  transition: background 0.2s ease;
}

.retry-button:hover {
  background: #0056b3;
}
</style>