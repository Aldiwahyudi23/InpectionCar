<template>
  <div v-if="show" class="fixed inset-0 z-40 p-0 webcam-modal-container">
    <div class="webcam-content-box">
      <div class="webcam-header">
        <p class="rounded-full text-white hover:bg-gray-700 transition-colors">{{ settings.camera_aspect_ratio }}</p>
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
          <button v-if="settings.enable_flash && isFlashSupported" 
            @click="toggleFlash" 
            class="control-button"
            :class="{'active': isFlashOn, 'disabled': !isFlashSupported}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </button>
          
          <button @click="capturePhoto" class="capture-button" :disabled="isTakingPhoto || isLoading">
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
            class="control-button"
            :disabled="isLoading">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
          </button>
        </div>

        <!-- Manual Focus Controls -->
        <div v-if="isManualFocusSupported" class="focus-controls">
          <button @click="setFocusMode('continuous')" 
            class="focus-btn"
            :class="{'active': focusMode === 'continuous'}">
            Auto
          </button>
          <button @click="setFocusMode('manual')" 
            class="focus-btn"
            :class="{'active': focusMode === 'manual'}">
            Manual
          </button>
          <input
            v-if="focusMode === 'manual'"
            type="range"
            :min="minFocusDistance"
            :max="maxFocusDistance"
            step="0.1"
            v-model="focusDistance"
            @input="setManualFocus(parseFloat($event.target.value))"
            class="focus-slider"
          >
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

// Zoom & Focus Controls
const zoomLevel = ref(1);
const maxZoom = ref(1);
const isZoomSupported = ref(false);
const focusMode = ref('continuous');
const focusDistance = ref(0);
const minFocusDistance = ref(0);
const maxFocusDistance = ref(10);
const isManualFocusSupported = ref(false);

const maxSizeKB = computed(() => {
  return props.settings?.max_size || 2048;
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

// Enhanced RTC Camera Initialization
const initializeWebcam = async () => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }
  
  isLoading.value = true;
  error.value = null;
  
  try {
    // Get available cameras first
    await getCameraDevices();
    
    // Enhanced video constraints for better RTC performance
    const videoConstraints = {
      deviceId: currentDeviceId.value ? { exact: currentDeviceId.value } : undefined,
      facingMode: currentDeviceId.value ? undefined : { ideal: currentFacingMode.value },
      width: { ideal: 3840, min: 1920 }, // 4K preferred, min 1080p
      height: { ideal: 2160, min: 1080 },
      aspectRatio: { ideal: props.aspectRatio || 4/3 },
      frameRate: { ideal: 60, min: 30 }, // Higher frame rate for smoother video
      resizeMode: 'crop-and-scale',
      advanced: [
        { focusMode: 'continuous' },
        { exposureMode: 'continuous' },
        { whiteBalanceMode: 'continuous' },
        { brightness: { ideal: 0 } },
        { contrast: { ideal: 0 } },
        { saturation: { ideal: 0 } },
        { sharpness: { ideal: 0 } }
      ]
    };
    
    console.log('Initializing RTC camera with constraints:', videoConstraints);
    
    mediaStream = await navigator.mediaDevices.getUserMedia({ 
      video: videoConstraints,
      audio: false 
    });
    
    videoTrack = mediaStream.getVideoTracks()[0];
    webcamVideo.value.srcObject = mediaStream;
    
    // Wait for video to be ready
    await new Promise((resolve, reject) => {
      webcamVideo.value.onloadedmetadata = () => resolve();
      webcamVideo.value.onerror = reject;
      setTimeout(resolve, 1000); // Fallback timeout
    });
    
    await webcamVideo.value.play();
    await checkCameraCapabilities();
    
    console.log('RTC Camera initialized successfully');
    
  } catch (err) {
    console.error("RTC Error accessing camera: ", err);
    error.value = getErrorMessage(err);
    
    // Fallback to simpler constraints
    if (err.name === 'OverconstrainedError' || err.name === 'ConstraintNotSatisfiedError') {
      await initializeWithFallback();
    }
  } finally {
    isLoading.value = false;
  }
};

// Fallback initialization
const initializeWithFallback = async () => {
  try {
    console.log('Trying fallback camera constraints...');
    
    const fallbackConstraints = {
      video: {
        deviceId: currentDeviceId.value ? { exact: currentDeviceId.value } : undefined,
        facingMode: currentDeviceId.value ? undefined : { ideal: currentFacingMode.value },
        width: { ideal: 1920 },
        height: { ideal: 1080 },
        frameRate: { ideal: 30 }
      },
      audio: false
    };
    
    mediaStream = await navigator.mediaDevices.getUserMedia(fallbackConstraints);
    videoTrack = mediaStream.getVideoTracks()[0];
    webcamVideo.value.srcObject = mediaStream;
    
    await webcamVideo.value.play();
    await checkCameraCapabilities();
    
    console.log('Fallback camera initialized successfully');
    error.value = null;
  } catch (fallbackErr) {
    console.error("Fallback camera also failed: ", fallbackErr);
    error.value = getErrorMessage(fallbackErr);
  }
};

// Get available camera devices
const getCameraDevices = async () => {
  try {
    // First get permission by accessing any camera
    const tempStream = await navigator.mediaDevices.getUserMedia({ video: true });
    tempStream.getTracks().forEach(track => track.stop());
    
    const devices = await navigator.mediaDevices.enumerateDevices();
    cameraDevices.value = devices.filter(d => d.kind === 'videoinput');
    hasMultipleCameras.value = cameraDevices.value.length > 1;
    
    // Set current device if not set
    if (!currentDeviceId.value && cameraDevices.value.length > 0) {
      // Prefer rear camera
      const rearCamera = cameraDevices.value.find(device => 
        device.label.toLowerCase().includes('back') || 
        device.label.toLowerCase().includes('rear') ||
        device.label.toLowerCase().includes('environment')
      );
      currentDeviceId.value = rearCamera ? rearCamera.deviceId : cameraDevices.value[0].deviceId;
    }
    
    console.log(`Found ${cameraDevices.value.length} cameras:`, cameraDevices.value);
  } catch (err) {
    console.error('Error getting camera devices:', err);
  }
};

// Enhanced error messaging
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

const closeModal = () => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }
  emit('close');
};

// Enhanced camera capabilities check
const checkCameraCapabilities = async () => {
  if (!videoTrack) return;
  
  try {
    const capabilities = videoTrack.getCapabilities();
    const settings = videoTrack.getSettings();
    
    console.log('Camera Capabilities:', capabilities);
    console.log('Camera Settings:', settings);
    
    // Enhanced zoom detection
    if (capabilities.zoom) {
      isZoomSupported.value = true;
      maxZoom.value = capabilities.zoom.max;
      zoomLevel.value = settings.zoom || 1;
      console.log(`Zoom supported. Range: ${capabilities.zoom.min} - ${capabilities.zoom.max}`);
    }
    
    // Enhanced flash detection
    isFlashSupported.value = !!capabilities.torch;
    
    // Enhanced focus capabilities
    if (capabilities.focusMode && capabilities.focusMode.includes('manual')) {
      isManualFocusSupported.value = true;
      if (capabilities.focusDistance) {
        minFocusDistance.value = capabilities.focusDistance.min;
        maxFocusDistance.value = capabilities.focusDistance.max;
        focusDistance.value = settings.focusDistance || minFocusDistance.value;
      }
    }
    
    // Check for advanced features
    if (capabilities.exposureMode) {
      console.log('Exposure modes:', capabilities.exposureMode);
    }
    
    if (capabilities.whiteBalanceMode) {
      console.log('White balance modes:', capabilities.whiteBalanceMode);
    }
    
  } catch (error) {
    console.error("Error checking camera capabilities:", error);
  }
};

// Enhanced tap to focus with RTC optimizations
const handleTapToFocus = async (event) => {
  if (!videoTrack || !isManualFocusSupported.value) return;
  
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
      
      console.log(`Manual focus set at: ${focusX.toFixed(2)}, ${focusY.toFixed(2)}`);
    }
  } catch (error) {
    console.warn("Manual focus failed:", error);
  }
  
  setTimeout(() => {
    showFocusIndicator.value = false;
  }, 2000);
};

// Enhanced zoom with RTC constraints
const setZoom = async (level) => {
  if (!videoTrack || !isZoomSupported.value) return;

  const newZoom = Math.min(Math.max(level, 1), maxZoom.value);
  zoomLevel.value = newZoom;
  
  try {
    await videoTrack.applyConstraints({
      advanced: [{ zoom: newZoom }]
    });
    console.log(`Zoom set to: ${newZoom}`);
  } catch (error) {
    console.error("Failed to set zoom:", error);
  }
};

// Enhanced focus mode control
const setFocusMode = async (mode) => {
  if (!videoTrack || !isManualFocusSupported.value) return;
  
  focusMode.value = mode;
  
  try {
    if (mode === 'continuous') {
      await videoTrack.applyConstraints({
        advanced: [{ focusMode: 'continuous' }]
      });
    } else {
      await videoTrack.applyConstraints({
        advanced: [{ focusMode: 'manual' }]
      });
    }
    console.log(`Focus mode set to: ${mode}`);
  } catch (error) {
    console.error("Failed to set focus mode:", error);
    focusMode.value = 'continuous'; // Revert on error
  }
};

// Enhanced manual focus control
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

// Enhanced flash control
const toggleFlash = async () => {
  if (!videoTrack || !isFlashSupported.value) return;
  
  try {
    if (currentFacingMode.value === 'user') {
      // Front camera - use screen flash
      isFlashOn.value = !isFlashOn.value;
      showScreenFlash.value = isFlashOn.value;
    } else {
      // Rear camera - toggle torch
      await videoTrack.applyConstraints({ 
        advanced: [{ torch: !isFlashOn.value }] 
      });
      isFlashOn.value = !isFlashOn.value;
    }
    console.log(`Flash ${isFlashOn.value ? 'ON' : 'OFF'}`);
  } catch (err) {
    console.error("Flash toggle failed:", err);
  }
};

// Enhanced camera switching
const switchCamera = async () => {
  if (!hasMultipleCameras.value || isLoading.value) return;
  
  isLoading.value = true;
  
  // Stop existing stream
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }
  
  // Find next camera
  const currentIndex = cameraDevices.value.findIndex(d => d.deviceId === currentDeviceId.value);
  const nextIndex = (currentIndex + 1) % cameraDevices.value.length;
  currentDeviceId.value = cameraDevices.value[nextIndex].deviceId;
  
  // Update facing mode based on camera label (heuristic)
  const nextCamera = cameraDevices.value[nextIndex];
  if (nextCamera.label.toLowerCase().includes('front') || nextCamera.label.toLowerCase().includes('user')) {
    currentFacingMode.value = 'user';
  } else {
    currentFacingMode.value = 'environment';
  }
  
  console.log(`Switching to camera: ${nextCamera.label}`);
  
  // Restart webcam with new device
  await initializeWebcam();
};

// Retry camera initialization
const retryCamera = async () => {
  error.value = null;
  await initializeWebcam();
};

// Enhanced image compression with RTC optimizations
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
        
        // Calculate optimal dimensions while maintaining quality
        let scaleFactor = Math.sqrt(MAX_SIZE / blob.size);
        let targetWidth = Math.floor(img.width * scaleFactor);
        let targetHeight = Math.floor(img.height * scaleFactor);
        
        // Ensure minimum dimensions for quality
        if (targetWidth < 800) {
          targetWidth = 800;
          targetHeight = Math.floor((800 / img.width) * img.height);
          scaleFactor = 800 / img.width;
        }
        
        canvas.width = targetWidth;
        canvas.height = targetHeight;
        
        // Use high-quality image rendering
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        ctx.drawImage(img, 0, 0, targetWidth, targetHeight);
        
        let quality = 0.92; // Start with high quality
        
        const compressRecursive = () => {
          canvas.toBlob((compressedBlob) => {
            if (compressedBlob.size > MAX_SIZE && quality > 0.4) {
              quality -= 0.08;
              compressRecursive();
            } else {
              console.log(`Image compressed: ${blob.size} -> ${compressedBlob.size} bytes, quality: ${quality}`);
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

// Enhanced photo capture with RTC optimizations
const capturePhoto = async () => {
  if (isTakingPhoto.value || !webcamVideo.value || !webcamCanvas.value || isLoading.value) return;
  
  isTakingPhoto.value = true;
  
  try {
    const video = webcamVideo.value;
    const canvas = webcamCanvas.value;
    
    const vw = video.videoWidth;
    const vh = video.videoHeight;
    
    let sw, sh, sx, sy;
    
    // Calculate crop area based on aspect ratio
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
    
    // Use high-quality image rendering
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';
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
  } finally {
    isTakingPhoto.value = false;
  }
};

// Watch for prop changes
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
  flex-direction: column;
  gap: 1rem;
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

.control-button:hover:not(:disabled) {
  transform: scale(1.08);
  background: rgba(255, 255, 255, 1);
}

.control-button:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.control-button.active {
  background: #ffeb3b;
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
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
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

/* Enhanced zoom controls */
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

/* Focus controls */
.focus-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 25px;
}

.focus-btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 15px;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 0.8rem;
}

.focus-btn.active {
  background: #007bff;
  color: white;
}

.focus-btn:hover:not(.active) {
  background: rgba(255, 255, 255, 0.3);
}

.focus-slider {
  width: 100px;
  -webkit-appearance: none;
  height: 6px;
  background: rgba(255, 255, 255, 0.3);
  border-radius: 3px;
  outline: none;
}

.focus-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 16px;
  height: 16px;
  background: white;
  border-radius: 50%;
  cursor: pointer;
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