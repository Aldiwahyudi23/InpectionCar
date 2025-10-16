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
        @click="handleTapToFocus"
        @touchstart="handleTapToFocus"
      >
        <video 
          ref="webcamVideo" 
          autoplay 
          playsinline 
          class="webcam-video"
          :class="{'portrait-mode': isPortrait}"
        ></video>
        <canvas ref="webcamCanvas" class="hidden"></canvas>
        
        <!-- Flash Overlay -->
        <div v-if="showScreenFlash" class="screen-flash-overlay"></div>
        
        <!-- Manual Controls Overlay (muncul saat tap) -->
        <div v-if="showManualControls" class="manual-controls-overlay">
          <div class="manual-controls-panel">
            <div class="control-group">
              <label>Focus</label>
              <input
                type="range"
                min="0"
                :max="maxFocusDistance"
                step="0.1"
                v-model="focusDistance"
                @input="setManualFocus(parseFloat($event.target.value))"
                class="control-slider"
              >
            </div>
            <div class="control-group">
              <label>Exposure</label>
              <input
                type="range"
                :min="minExposureCompensation"
                :max="maxExposureCompensation"
                step="0.1"
                v-model="exposureCompensation"
                @input="setExposureCompensation(parseFloat($event.target.value))"
                class="control-slider"
              >
              <span class="control-value">{{ exposureCompensation > 0 ? '+' : '' }}{{ exposureCompensation }}</span>
            </div>
            <button @click="hideManualControls" class="close-controls-btn">
              Tutup
            </button>
          </div>
        </div>

        <div class="aspect-ratio-guide" :style="aspectRatioGuideStyle"></div>

        <div v-if="showFocusIndicator" class="focus-indicator" :style="focusIndicatorStyle">
          <div class="focus-ring"></div>
        </div>

        <!-- Orientation Indicator -->
        <div class="orientation-indicator" :class="orientationClass">
          <div class="orientation-icon">{{ orientationIcon }}</div>
          <span class="orientation-text">{{ orientationText }}</span>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="loading-overlay">
          <div class="loading-spinner"></div>
          <p class="loading-text">Menginisialisasi kamera...</p>
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
              <div v-else class="camera-body">
                <div class="camera-lens"></div>
              </div>
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
const showScreenFlash = ref(false);
const showFocusIndicator = ref(false);
const focusIndicatorStyle = ref({});
const isTakingPhoto = ref(false);
const isLoading = ref(false);
const error = ref(null);

// Manual Controls
const showManualControls = ref(false);
const focusMode = ref('continuous');
const focusDistance = ref(0);
const minFocusDistance = ref(0);
const maxFocusDistance = ref(10);
const isManualFocusSupported = ref(false);

// Exposure Controls (simplified)
const exposureCompensation = ref(0);
const minExposureCompensation = ref(-2);
const maxExposureCompensation = ref(2);
const isExposureSupported = ref(false);

// Orientation Detection
const deviceOrientation = ref('portrait');
const screenOrientation = ref('portrait');

// Computed Properties
const maxSizeKB = computed(() => {
  return props.settings?.max_size || 2048;
});

const isPortrait = computed(() => {
  return deviceOrientation.value === 'portrait' || screenOrientation.value === 'portrait';
});

const videoContainerStyle = computed(() => {
  if (!props.aspectRatio) return {};
  
  // Auto adjust based on orientation
  const aspectRatio = isPortrait.value ? props.aspectRatio : (1 / props.aspectRatio);
  
  return {
    aspectRatio: `${aspectRatio} / 1`,
    maxWidth: '100%',
    maxHeight: '70vh',
    margin: '0 auto'
  };
});

const aspectRatioGuideStyle = computed(() => {
  if (!props.aspectRatio) return {};
  
  const ratio = isPortrait.value ? props.aspectRatio : (1 / props.aspectRatio);
  const width = ratio > 1 ? 80 : 60;
  const height = ratio > 1 ? 80 / ratio : 60 * ratio;
  
  return {
    width: `${width}%`,
    height: `${height}%`
  };
});

const orientationClass = computed(() => ({
  'orientation-portrait': isPortrait.value,
  'orientation-landscape': !isPortrait.value
}));

const orientationIcon = computed(() => {
  return isPortrait.value ? '📱' : '🔄';
});

const orientationText = computed(() => {
  return isPortrait.value ? 'Portrait' : 'Landscape';
});

// Orientation Detection
const setupOrientationDetection = () => {
  // Screen orientation
  const updateScreenOrientation = () => {
    screenOrientation.value = window.screen.orientation.type.includes('portrait') ? 'portrait' : 'landscape';
  };

  // Device orientation (accelerometer)
  const handleDeviceOrientation = (event) => {
    if (event.gamma !== null && event.beta !== null) {
      const absGamma = Math.abs(event.gamma);
      const absBeta = Math.abs(event.beta);
      
      if (absBeta > 45 || absGamma < 45) {
        deviceOrientation.value = 'portrait';
      } else {
        deviceOrientation.value = 'landscape';
      }
    }
  };

  // Listeners
  window.addEventListener('orientationchange', updateScreenOrientation);
  window.addEventListener('resize', updateScreenOrientation);
  
  if (window.DeviceOrientationEvent) {
    window.addEventListener('deviceorientation', handleDeviceOrientation);
  }

  // Initial update
  updateScreenOrientation();

  // Cleanup function
  return () => {
    window.removeEventListener('orientationchange', updateScreenOrientation);
    window.removeEventListener('resize', updateScreenOrientation);
    window.removeEventListener('deviceorientation', handleDeviceOrientation);
  };
};

// Enhanced RTC Camera Initialization
const initializeWebcam = async () => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }
  
  isLoading.value = true;
  error.value = null;
  
  try {
    await getCameraDevices();
    
    // Simple constraints that work on most devices
    const videoConstraints = {
      deviceId: currentDeviceId.value ? { exact: currentDeviceId.value } : undefined,
      facingMode: currentDeviceId.value ? undefined : { ideal: currentFacingMode.value },
      width: { ideal: 1920 },
      height: { ideal: 1080 },
      aspectRatio: { ideal: props.aspectRatio || 4/3 },
      frameRate: { ideal: 30 }
    };
    
    mediaStream = await navigator.mediaDevices.getUserMedia({ 
      video: videoConstraints,
      audio: false 
    });
    
    videoTrack = mediaStream.getVideoTracks()[0];
    webcamVideo.value.srcObject = mediaStream;
    
    await new Promise((resolve, reject) => {
      webcamVideo.value.onloadedmetadata = () => resolve();
      webcamVideo.value.onerror = reject;
      setTimeout(resolve, 1000);
    });
    
    await webcamVideo.value.play();
    await checkCameraCapabilities();
    
    console.log('Camera initialized successfully');
    
  } catch (err) {
    console.error("Error accessing camera: ", err);
    error.value = getErrorMessage(err);
  } finally {
    isLoading.value = false;
  }
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
      return 'Tidak ada kamera yang ditemukan. Pastikan kamera terpasang dan tidak digunakan aplikasi lain.';
    case 'NotSupportedError':
      return 'Browser tidak mendukung akses kamera. Gunakan browser modern seperti Chrome, Firefox, atau Safari.';
    case 'NotReadableError':
      return 'Kamera sedang digunakan oleh aplikasi lain. Tutup aplikasi lain yang menggunakan kamera.';
    case 'OverconstrainedError':
    case 'ConstraintNotSatisfiedError':
      return 'Kamera tidak mendukung mode yang diminta. Menggunakan mode alternatif...';
    default:
      return `Error: ${error.message || 'Tidak dapat mengakses kamera'}`;
  }
};

const checkCameraCapabilities = async () => {
  if (!videoTrack) return;
  
  try {
    const capabilities = videoTrack.getCapabilities();
    
    // Flash support
    isFlashSupported.value = !!capabilities.torch;
    
    // Focus support
    if (capabilities.focusMode && capabilities.focusMode.includes('manual')) {
      isManualFocusSupported.value = true;
      if (capabilities.focusDistance) {
        minFocusDistance.value = capabilities.focusDistance.min;
        maxFocusDistance.value = capabilities.focusDistance.max;
        focusDistance.value = capabilities.focusDistance.min;
      }
    }
    
    // Exposure support (simplified)
    isExposureSupported.value = !!capabilities.exposureCompensation;
    
  } catch (error) {
    console.error("Error checking camera capabilities:", error);
  }
};

// Tap to Focus with Manual Controls
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
    // Try to set focus point
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
      
      // Show manual controls
      showManualControls.value = true;
      focusMode.value = 'manual';
    }
  } catch (error) {
    console.warn("Manual focus failed:", error);
    // Fallback to continuous focus
    focusMode.value = 'continuous';
  }
  
  setTimeout(() => {
    showFocusIndicator.value = false;
  }, 2000);
};

const setManualFocus = async (distance) => {
  if (!videoTrack || focusMode.value !== 'manual') return;
  
  focusDistance.value = distance;
  
  try {
    await videoTrack.applyConstraints({
      advanced: [{ focusDistance: distance }]
    });
  } catch (error) {
    console.warn("Manual focus distance adjustment failed:", error);
  }
};

const setExposureCompensation = async (value) => {
  if (!videoTrack || !isExposureSupported.value) return;
  
  exposureCompensation.value = Math.max(minExposureCompensation.value, 
    Math.min(maxExposureCompensation.value, value));
  
  try {
    await videoTrack.applyConstraints({
      advanced: [{ exposureCompensation: exposureCompensation.value }]
    });
  } catch (error) {
    console.error("Failed to set exposure compensation:", error);
  }
};

const hideManualControls = () => {
  showManualControls.value = false;
  // Reset to auto focus
  if (videoTrack) {
    try {
      videoTrack.applyConstraints({
        advanced: [{ focusMode: 'continuous' }]
      });
      focusMode.value = 'continuous';
    } catch (error) {
      console.warn("Failed to reset to auto focus:", error);
    }
  }
};

const toggleFlash = async () => {
  if (!videoTrack || !isFlashSupported.value) return;
  
  try {
    if (currentFacingMode.value === 'user') {
      // Front camera - use screen flash
      isFlashOn.value = !isFlashOn.value;
    } else {
      // Rear camera - toggle torch
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
  if (nextCamera.label.toLowerCase().includes('front') || nextCamera.label.toLowerCase().includes('user')) {
    currentFacingMode.value = 'user';
  } else {
    currentFacingMode.value = 'environment';
  }
  
  await initializeWebcam();
};

// Enhanced Photo Capture with Auto-Rotation
const capturePhoto = async () => {
  if (isTakingPhoto.value || !webcamVideo.value || !webcamCanvas.value || isLoading.value) return;
  
  isTakingPhoto.value = true;
  
  try {
    // Flash effect
    if (isFlashOn.value) {
      showScreenFlash.value = true;
      await new Promise(resolve => setTimeout(resolve, 50));
    }
    
    const video = webcamVideo.value;
    const canvas = webcamCanvas.value;
    
    const vw = video.videoWidth;
    const vh = video.videoHeight;
    
    // Determine crop area based on aspect ratio and orientation
    let sw, sh, sx, sy;
    let outputWidth, outputHeight;
    
    if (isPortrait.value) {
      // Portrait mode - maintain portrait aspect ratio
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
      outputWidth = sw;
      outputHeight = sh;
    } else {
      // Landscape mode - maintain landscape aspect ratio
      const landscapeAspectRatio = 1 / props.aspectRatio;
      if (vw / vh > landscapeAspectRatio) {
        sh = vh;
        sw = vh * landscapeAspectRatio;
        sx = (vw - sw) / 2;
        sy = 0;
      } else {
        sw = vw;
        sh = vw / landscapeAspectRatio;
        sx = 0;
        sy = (vh - sh) / 2;
      }
      outputWidth = sw;
      outputHeight = sh;
    }
    
    canvas.width = outputWidth;
    canvas.height = outputHeight;
    
    const ctx = canvas.getContext('2d');
    
    // High quality rendering
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(video, sx, sy, sw, sh, 0, 0, outputWidth, outputHeight);
    
    // Apply exposure compensation in post-processing if needed
    if (exposureCompensation.value !== 0 && isExposureSupported.value) {
      const imageData = ctx.getImageData(0, 0, outputWidth, outputHeight);
      const data = imageData.data;
      const adjustment = 1 + (exposureCompensation.value * 0.15);
      
      for (let i = 0; i < data.length; i += 4) {
        data[i] = Math.min(255, data[i] * adjustment);     // Red
        data[i + 1] = Math.min(255, data[i + 1] * adjustment); // Green
        data[i + 2] = Math.min(255, data[i + 2] * adjustment); // Blue
      }
      
      ctx.putImageData(imageData, 0, 0);
    }
    
    const originalBlob = await new Promise(resolve => {
      canvas.toBlob(resolve, 'image/jpeg', 0.92);
    });
    
    const compressedBlob = await compressImage(originalBlob);
    
    if (compressedBlob) {
      const fileName = `capture_${Date.now()}_${isPortrait.value ? 'portrait' : 'landscape'}.jpeg`;
      const file = new File([compressedBlob], fileName, { 
        type: 'image/jpeg',
        lastModified: Date.now()
      });
      
      emit('photoCaptured', file);
    }
    
    // Reset flash
    setTimeout(() => {
      showScreenFlash.value = false;
    }, 100);
    
  } catch (error) {
    console.error("Error capturing photo:", error);
  } finally {
    isTakingPhoto.value = false;
  }
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
        
        let scaleFactor = Math.sqrt(MAX_SIZE / blob.size);
        let targetWidth = Math.floor(img.width * scaleFactor);
        let targetHeight = Math.floor(img.height * scaleFactor);
        
        // Ensure minimum quality
        if (targetWidth < 1200) {
          targetWidth = 1200;
          targetHeight = Math.floor((1200 / img.width) * img.height);
        }
        
        canvas.width = targetWidth;
        canvas.height = targetHeight;
        
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        ctx.drawImage(img, 0, 0, targetWidth, targetHeight);
        
        let quality = 0.9;
        
        const compressRecursive = () => {
          canvas.toBlob((compressedBlob) => {
            if (compressedBlob.size > MAX_SIZE && quality > 0.5) {
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
let orientationCleanup = null;

watch(() => props.show, async (v) => { 
  if (v) {
    await nextTick();
    // Setup orientation detection
    orientationCleanup = setupOrientationDetection();
    await initializeWebcam();
  } else {
    if (orientationCleanup) {
      orientationCleanup();
    }
    closeModal();
  }
});

onUnmounted(() => { 
  if (mediaStream) {
    mediaStream.getTracks().forEach(t => t.stop());
  }
  if (orientationCleanup) {
    orientationCleanup();
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

.webcam-video.portrait-mode {
  transform: rotate(0deg);
}

.aspect-ratio-guide {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  border: 2px dashed rgba(255, 255, 255, 0.3);
  pointer-events: none;
  z-index: 10;
}

/* Manual Controls Overlay */
.manual-controls-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 25;
}

.manual-controls-panel {
  background: rgba(255, 255, 255, 0.95);
  padding: 1.5rem;
  border-radius: 15px;
  min-width: 250px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.control-group {
  margin-bottom: 1rem;
}

.control-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #333;
}

.control-slider {
  width: 100%;
  height: 6px;
  border-radius: 3px;
  background: #ddd;
  outline: none;
  -webkit-appearance: none;
}

.control-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #007bff;
  cursor: pointer;
}

.control-value {
  margin-left: 0.5rem;
  font-weight: 600;
  color: #333;
  min-width: 40px;
  display: inline-block;
}

.close-controls-btn {
  width: 100%;
  padding: 0.75rem;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: background 0.2s ease;
}

.close-controls-btn:hover {
  background: #0056b3;
}

/* Orientation Indicator */
.orientation-indicator {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8rem;
  z-index: 15;
}

.orientation-portrait {
  background: rgba(76, 175, 80, 0.8);
}

.orientation-landscape {
  background: rgba(255, 152, 0, 0.8);
}

.orientation-icon {
  font-size: 1rem;
}

.orientation-text {
  font-weight: 600;
}

/* Flash Overlay */
.screen-flash-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.9);
  z-index: 20;
  animation: flash 0.2s ease-out;
}

@keyframes flash {
  0% { opacity: 1; }
  100% { opacity: 0; }
}

/* Focus Indicator */
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
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}

.capture-button:hover:not(:disabled) {
  transform: scale(1.05);
  background: #f0f0f0;
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