<template>
  <div v-if="show" class="fixed inset-0 z-40 p-0 webcam-modal-container">
    <div class="webcam-content-box">
      <div class="webcam-header">
        <div class="camera-settings-info">
          <span class="settings-badge" :class="getExposureBadgeClass">{{ exposureModeLabel }}</span>
          <span class="settings-badge" :class="getFlashBadgeClass">{{ flashModeLabel }}</span>
          <span class="settings-badge aspect-ratio">{{ settings.camera_aspect_ratio }}</span>
        </div>
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
        <video ref="webcamVideo" autoplay playsinline class="webcam-video" :class="{'hdr-mode': isHDRActive}"></video>
        <canvas ref="webcamCanvas" class="hidden"></canvas>
        
        <!-- Flash Overlay dengan kontrol intensity -->
        <div v-if="showScreenFlash" class="screen-flash-overlay" :style="flashOverlayStyle"></div>
        
        <!-- Backlight Warning -->
        <div v-if="showBacklightWarning" class="backlight-warning">
          <div class="warning-icon">⚠️</div>
          <p>Backlight terdeteksi! Gunakan fill flash</p>
        </div>

        <div class="aspect-ratio-guide" :style="aspectRatioGuideStyle"></div>

        <div v-if="showFocusIndicator" class="focus-indicator" :style="focusIndicatorStyle">
          <div class="focus-ring"></div>
        </div>
        
        <div class="hd-badge">
          <span class="hd-text" :class="{'hdr-active': isHDRActive}">
            {{ isHDRActive ? 'HDR' : 'HD' }}
          </span>
        </div>

        <!-- Enhanced Zoom Controls -->
        <div v-if="isZoomSupported" class="zoom-controls">
          <span class="zoom-label">{{ zoomLevel.toFixed(1) }}x</span>
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
          <p class="loading-text">Mengoptimalkan kamera...</p>
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
        <!-- Advanced Camera Controls -->
        <div class="advanced-controls">
          <!-- Exposure Compensation -->
          <div v-if="isExposureSupported" class="control-group">
            <label class="control-label">Exposure</label>
            <input
              type="range"
              :min="minExposureCompensation"
              :max="maxExposureCompensation"
              step="0.1"
              v-model="exposureCompensation"
              @input="setExposureCompensation(parseFloat($event.target.value))"
              class="exposure-slider"
            >
            <span class="control-value">{{ exposureCompensation > 0 ? '+' : '' }}{{ exposureCompensation.toFixed(1) }}</span>
          </div>

          <!-- Flash Mode Selector -->
          <div v-if="isFlashSupported" class="control-group">
            <label class="control-label">Flash</label>
            <select v-model="flashMode" @change="setFlashMode" class="flash-selector">
              <option value="off">Off</option>
              <option value="on">On</option>
              <option value="auto">Auto</option>
              <option value="fill">Fill</option>
            </select>
          </div>

          <!-- HDR Toggle -->
          <div v-if="isHDRSupported" class="control-group">
            <label class="control-label">HDR</label>
            <button 
              @click="toggleHDR" 
              class="hdr-toggle"
              :class="{'active': isHDRActive}"
            >
              {{ isHDRActive ? 'ON' : 'OFF' }}
            </button>
          </div>
        </div>

        <!-- Main Camera Controls -->
        <div class="camera-controls">
          <button v-if="settings.enable_flash && isFlashSupported" 
            @click="toggleFlash" 
            class="control-button"
            :class="{'active': isFlashOn, 'fill-mode': flashMode === 'fill'}">
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

        <!-- Manual Focus & Exposure Controls -->
        <div v-if="isManualFocusSupported || isExposureSupported" class="manual-controls">
          <button @click="setFocusMode('continuous')" 
            class="control-btn"
            :class="{'active': focusMode === 'continuous'}">
            Auto Focus
          </button>
          <button @click="setFocusMode('manual')" 
            class="control-btn"
            :class="{'active': focusMode === 'manual'}">
            Manual Focus
          </button>
          
          <button v-if="isExposureSupported" @click="setExposureMode('continuous')" 
            class="control-btn"
            :class="{'active': exposureMode === 'continuous'}">
            Auto Expo
          </button>
          <button v-if="isExposureSupported" @click="setExposureMode('manual')" 
            class="control-btn"
            :class="{'active': exposureMode === 'manual'}">
            Manual Expo
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
const showBacklightWarning = ref(false);
const focusIndicatorStyle = ref({});
const isTakingPhoto = ref(false);
const isLoading = ref(false);
const error = ref(null);

// Enhanced Camera Controls
const zoomLevel = ref(1);
const maxZoom = ref(1);
const isZoomSupported = ref(false);

// Focus Controls
const focusMode = ref('continuous');
const focusDistance = ref(0);
const minFocusDistance = ref(0);
const maxFocusDistance = ref(10);
const isManualFocusSupported = ref(false);

// Exposure Controls
const exposureMode = ref('continuous');
const exposureCompensation = ref(0);
const minExposureCompensation = ref(-3);
const maxExposureCompensation = ref(3);
const isExposureSupported = ref(false);
const isHDRSupported = ref(false);
const isHDRActive = ref(false);

// Flash Controls
const flashMode = ref('off');
const flashIntensity = ref(0.8);

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

const flashOverlayStyle = computed(() => ({
  opacity: flashIntensity.value
}));

const exposureModeLabel = computed(() => {
  return exposureMode.value === 'manual' ? 'M-Expo' : 'A-Expo';
});

const flashModeLabel = computed(() => {
  const modes = {
    'off': 'No Flash',
    'on': 'Flash On',
    'auto': 'Auto Flash',
    'fill': 'Fill Flash'
  };
  return modes[flashMode.value];
});

const getExposureBadgeClass = computed(() => ({
  'badge-auto': exposureMode.value === 'continuous',
  'badge-manual': exposureMode.value === 'manual'
}));

const getFlashBadgeClass = computed(() => ({
  'badge-flash-off': flashMode.value === 'off',
  'badge-flash-on': flashMode.value === 'on',
  'badge-flash-auto': flashMode.value === 'auto',
  'badge-flash-fill': flashMode.value === 'fill'
}));

// Enhanced RTC Camera Initialization dengan Backlight Detection
const initializeWebcam = async () => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }
  
  isLoading.value = true;
  error.value = null;
  
  try {
    await getCameraDevices();
    
    // Advanced constraints untuk handling backlight dan exposure
    const videoConstraints = {
      deviceId: currentDeviceId.value ? { exact: currentDeviceId.value } : undefined,
      facingMode: currentDeviceId.value ? undefined : { ideal: currentFacingMode.value },
      width: { ideal: 3840, min: 1920 },
      height: { ideal: 2160, min: 1080 },
      aspectRatio: { ideal: props.aspectRatio || 4/3 },
      frameRate: { ideal: 60, min: 30 },
      resizeMode: 'crop-and-scale',
      // Advanced settings untuk exposure control
      advanced: [
        { focusMode: 'continuous' },
        { exposureMode: 'continuous' },
        { exposureCompensation: { ideal: 0 } },
        { whiteBalanceMode: 'continuous' },
        { brightness: { ideal: 0 } },
        { contrast: { ideal: 0 } },
        { saturation: { ideal: 0 } },
        { sharpness: { ideal: 0 } },
        // HDR simulation
        { exposureTime: { ideal: 1/60 } },
        { iso: { ideal: 100 } }
      ]
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
    
    // Start backlight detection
    startBacklightDetection();
    
    console.log('Enhanced RTC Camera initialized successfully');
    
  } catch (err) {
    console.error("RTC Error accessing camera: ", err);
    error.value = getErrorMessage(err);
    await initializeWithFallback();
  } finally {
    isLoading.value = false;
  }
};

// Backlight Detection System
const startBacklightDetection = () => {
  if (!webcamVideo.value) return;
  
  const canvas = document.createElement('canvas');
  const ctx = canvas.getContext('2d');
  
  const detectBacklight = () => {
    if (!webcamVideo.value || webcamVideo.value.readyState !== webcamVideo.value.HAVE_ENOUGH_DATA) {
      return;
    }
    
    try {
      const video = webcamVideo.value;
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      
      ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
      const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
      const data = imageData.data;
      
      // Analyze brightness distribution
      let brightPixels = 0;
      let darkPixels = 0;
      let totalBrightness = 0;
      
      for (let i = 0; i < data.length; i += 4) {
        const brightness = (data[i] + data[i + 1] + data[i + 2]) / 3;
        totalBrightness += brightness;
        
        if (brightness > 200) brightPixels++;
        if (brightness < 50) darkPixels++;
      }
      
      const avgBrightness = totalBrightness / (data.length / 4);
      const brightRatio = brightPixels / (data.length / 4);
      const darkRatio = darkPixels / (data.length / 4);
      
      // Backlight detection logic
      const isBacklight = avgBrightness > 180 && brightRatio > 0.3 && darkRatio > 0.4;
      showBacklightWarning.value = isBacklight;
      
      // Auto-adjust exposure for backlight
      if (isBacklight && exposureMode.value === 'continuous') {
        setExposureCompensation(1.0); // Increase exposure for backlight
      }
      
    } catch (error) {
      console.warn('Backlight detection error:', error);
    }
  };
  
  // Run detection every 2 seconds
  setInterval(detectBacklight, 2000);
};

// Enhanced Flash Management
const setFlashMode = async () => {
  if (!videoTrack) return;
  
  try {
    switch (flashMode.value) {
      case 'off':
        isFlashOn.value = false;
        if (currentFacingMode.value !== 'user') {
          await videoTrack.applyConstraints({ 
            advanced: [{ torch: false }] 
          });
        }
        break;
        
      case 'on':
        isFlashOn.value = true;
        if (currentFacingMode.value !== 'user') {
          await videoTrack.applyConstraints({ 
            advanced: [{ torch: true }] 
          });
        }
        break;
        
      case 'auto':
        // Auto flash logic based on brightness detection
        const shouldFlash = showBacklightWarning.value;
        isFlashOn.value = shouldFlash;
        if (currentFacingMode.value !== 'user' && shouldFlash) {
          await videoTrack.applyConstraints({ 
            advanced: [{ torch: true }] 
          });
        }
        break;
        
      case 'fill':
        // Fill flash for backlight situations
        isFlashOn.value = true;
        flashIntensity.value = 0.4; // Lower intensity for fill
        if (currentFacingMode.value !== 'user') {
          await videoTrack.applyConstraints({ 
            advanced: [{ torch: true }] 
          });
        }
        break;
    }
  } catch (err) {
    console.error("Flash mode change failed:", err);
  }
};

// Enhanced Exposure Control
const setExposureCompensation = async (value) => {
  if (!videoTrack || !isExposureSupported.value) return;
  
  exposureCompensation.value = Math.max(minExposureCompensation.value, 
    Math.min(maxExposureCompensation.value, value));
  
  try {
    await videoTrack.applyConstraints({
      advanced: [{ exposureCompensation: exposureCompensation.value }]
    });
    console.log(`Exposure compensation set to: ${exposureCompensation.value}`);
  } catch (error) {
    console.error("Failed to set exposure compensation:", error);
  }
};

const setExposureMode = async (mode) => {
  if (!videoTrack || !isExposureSupported.value) return;
  
  exposureMode.value = mode;
  
  try {
    if (mode === 'continuous') {
      await videoTrack.applyConstraints({
        advanced: [{ exposureMode: 'continuous' }]
      });
    } else {
      await videoTrack.applyConstraints({
        advanced: [{ exposureMode: 'manual' }]
      });
    }
  } catch (error) {
    console.error("Failed to set exposure mode:", error);
    exposureMode.value = 'continuous';
  }
};

// HDR Simulation
const toggleHDR = async () => {
  if (!isHDRSupported.value) return;
  
  isHDRActive.value = !isHDRActive.value;
  
  try {
    if (isHDRActive.value) {
      // HDR mode - lower contrast, balanced exposure
      await videoTrack.applyConstraints({
        advanced: [
          { contrast: 0 },
          { brightness: 0 },
          { saturation: 0.1 }
        ]
      });
    } else {
      // Standard mode
      await videoTrack.applyConstraints({
        advanced: [
          { contrast: 0 },
          { brightness: 0 },
          { saturation: 0 }
        ]
      });
    }
  } catch (error) {
    console.error("HDR toggle failed:", error);
  }
};

// Enhanced Camera Capabilities Check
const checkCameraCapabilities = async () => {
  if (!videoTrack) return;
  
  try {
    const capabilities = videoTrack.getCapabilities();
    const settings = videoTrack.getSettings();
    
    console.log('Enhanced Camera Capabilities:', capabilities);
    
    // Zoom
    if (capabilities.zoom) {
      isZoomSupported.value = true;
      maxZoom.value = capabilities.zoom.max;
      zoomLevel.value = settings.zoom || 1;
    }
    
    // Flash
    isFlashSupported.value = !!capabilities.torch;
    
    // Focus
    if (capabilities.focusMode && capabilities.focusMode.includes('manual')) {
      isManualFocusSupported.value = true;
      if (capabilities.focusDistance) {
        minFocusDistance.value = capabilities.focusDistance.min;
        maxFocusDistance.value = capabilities.focusDistance.max;
        focusDistance.value = settings.focusDistance || minFocusDistance.value;
      }
    }
    
    // Exposure
    if (capabilities.exposureCompensation) {
      isExposureSupported.value = true;
      minExposureCompensation.value = capabilities.exposureCompensation.min;
      maxExposureCompensation.value = capabilities.exposureCompensation.max;
      exposureCompensation.value = settings.exposureCompensation || 0;
    }
    
    // HDR detection (simulated)
    isHDRSupported.value = capabilities.whiteBalanceMode && 
                          capabilities.exposureMode && 
                          capabilities.focusMode;
    
  } catch (error) {
    console.error("Error checking camera capabilities:", error);
  }
};

// Enhanced Photo Capture dengan Exposure Compensation
const capturePhoto = async () => {
  if (isTakingPhoto.value || !webcamVideo.value || !webcamCanvas.value || isLoading.value) return;
  
  isTakingPhoto.value = true;
  
  try {
    // Pre-capture adjustments
    if (flashMode.value === 'on' || flashMode.value === 'fill') {
      showScreenFlash.value = true;
      await new Promise(resolve => setTimeout(resolve, 50));
    }
    
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
    
    // Apply exposure compensation in post-processing
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(video, sx, sy, sw, sh, 0, 0, sw, sh);
    
    // Apply brightness/contrast adjustment based on exposure compensation
    if (exposureCompensation.value !== 0) {
      const imageData = ctx.getImageData(0, 0, sw, sh);
      const data = imageData.data;
      const adjustment = 1 + (exposureCompensation.value * 0.1);
      
      for (let i = 0; i < data.length; i += 4) {
        data[i] = Math.min(255, data[i] * adjustment);     // Red
        data[i + 1] = Math.min(255, data[i + 1] * adjustment); // Green
        data[i + 2] = Math.min(255, data[i + 2] * adjustment); // Blue
      }
      
      ctx.putImageData(imageData, 0, 0);
    }
    
    const originalBlob = await new Promise(resolve => {
      canvas.toBlob(resolve, 'image/jpeg', 0.95); // Higher quality
    });
    
    const compressedBlob = await compressImage(originalBlob);
    
    if (compressedBlob) {
      const file = new File([compressedBlob], `capture_${Date.now()}.jpeg`, { 
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

// Existing functions (getCameraDevices, getErrorMessage, initializeWithFallback, etc.)
// ... (tetap sama seperti sebelumnya, tapi dengan enhancements)

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

const initializeWithFallback = async () => {
  try {
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
    error.value = null;
  } catch (fallbackErr) {
    console.error("Fallback camera also failed: ", fallbackErr);
    error.value = getErrorMessage(fallbackErr);
  }
};

// Existing functions (toggleFlash, switchCamera, compressImage, etc.)
// ... (tetap sama seperti sebelumnya)

const toggleFlash = async () => {
  // Cycle through flash modes
  const modes = ['off', 'on', 'auto', 'fill'];
  const currentIndex = modes.indexOf(flashMode.value);
  const nextIndex = (currentIndex + 1) % modes.length;
  flashMode.value = modes[nextIndex];
  await setFlashMode();
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
        
        if (targetWidth < 800) {
          targetWidth = 800;
          targetHeight = Math.floor((800 / img.width) * img.height);
          scaleFactor = 800 / img.width;
        }
        
        canvas.width = targetWidth;
        canvas.height = targetHeight;
        
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        ctx.drawImage(img, 0, 0, targetWidth, targetHeight);
        
        let quality = 0.92;
        
        const compressRecursive = () => {
          canvas.toBlob((compressedBlob) => {
            if (compressedBlob.size > MAX_SIZE && quality > 0.4) {
              quality -= 0.08;
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

// Focus functions (handleTapToFocus, setFocusMode, setManualFocus, setZoom)
// ... (tetap sama seperti sebelumnya)

const handleTapToFocus = async (event) => {
  if (!videoTrack || !isManualFocusSupported.value) return;
  
  const rect = webcamVideo.value.getBoundingClientRect();
  const x = event.clientX - rect.left;
  const y = event.clientY - rect.top;
  
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
    }
  } catch (error) {
    console.warn("Manual focus failed:", error);
  }
  
  setTimeout(() => {
    showFocusIndicator.value = false;
  }, 2000);
};

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
  } catch (error) {
    console.error("Failed to set focus mode:", error);
    focusMode.value = 'continuous';
  }
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
/* Enhanced CSS dengan styling untuk kontrol eksposur dan flash */
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

.camera-settings-info {
  display: flex;
  gap: 0.5rem;
}

.settings-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: 600;
}

.badge-auto { background: #4CAF50; color: white; }
.badge-manual { background: #FF9800; color: white; }
.badge-flash-off { background: #666; color: white; }
.badge-flash-on { background: #FFC107; color: black; }
.badge-flash-auto { background: #2196F3; color: white; }
.badge-flash-fill { background: #FF5722; color: white; }
.aspect-ratio { background: #9C27B0; color: white; }

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

.webcam-video.hdr-mode {
  filter: brightness(1.1) contrast(1.1) saturate(1.1);
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

/* Enhanced Flash Overlay */
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

/* Backlight Warning */
.backlight-warning {
  position: absolute;
  top: 10px;
  left: 10px;
  background: rgba(255, 152, 0, 0.9);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  z-index: 25;
  animation: pulse 2s infinite;
}

.warning-icon {
  font-size: 1.2rem;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

.webcam-footer {
  background: rgba(0, 0, 0, 0.8);
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  justify-content: center;
  align-items: center;
  flex-shrink: 0;
}

/* Advanced Controls */
.advanced-controls {
  display: flex;
  gap: 1rem;
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
  width: 100%;
  max-width: 500px;
}

.control-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(255, 255, 255, 0.1);
  padding: 0.5rem;
  border-radius: 20px;
}

.control-label {
  color: white;
  font-size: 0.8rem;
  font-weight: 600;
  min-width: 60px;
}

.exposure-slider {
  width: 80px;
  -webkit-appearance: none;
  height: 6px;
  background: linear-gradient(to right, #666, #fff);
  border-radius: 3px;
  outline: none;
}

.exposure-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 16px;
  height: 16px;
  background: #FF9800;
  border-radius: 50%;
  cursor: pointer;
}

.control-value {
  color: white;
  font-size: 0.8rem;
  min-width: 40px;
  text-align: center;
}

.flash-selector {
  background: rgba(255, 255, 255, 0.9);
  border: none;
  border-radius: 15px;
  padding: 0.25rem 0.5rem;
  font-size: 0.8rem;
  color: #333;
}

.hdr-toggle {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 15px;
  padding: 0.5rem 1rem;
  color: white;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 0.8rem;
}

.hdr-toggle.active {
  background: #4CAF50;
  color: white;
}

.hdr-toggle:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* Main Camera Controls */
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

.control-button.fill-mode {
  background: #FF5722;
  animation: pulse 2s infinite;
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
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.capture-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Manual Controls */
.manual-controls {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  justify-content: center;
}

.control-btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 15px;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 0.8rem;
}

.control-btn.active {
  background: #007bff;
  color: white;
}

.control-btn:hover:not(.active) {
  background: rgba(255, 255, 255, 0.3);
}

/* Enhanced HD Badge */
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

.hd-text.hdr-active {
  color: #FF9800;
  animation: glow 2s infinite;
}

@keyframes glow {
  0%, 100% { text-shadow: 0 0 5px #FF9800; }
  50% { text-shadow: 0 0 15px #FF9800, 0 0 20px #FF9800; }
}

/* Enhanced Zoom Controls */
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
  align-items: center;
  gap: 10px;
}

.zoom-label {
  color: white;
  font-size: 0.8rem;
  font-weight: bold;
  min-width: 30px;
}

.zoom-slider {
  flex: 1;
  -webkit-appearance: none;
  height: 8px;
  background: rgba(255, 255, 255, 0.4);
  border-radius: 5px;
  outline: none;
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

/* Loading and Error States (tetap sama) */
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

/* Focus Indicator (tetap sama) */
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

@keyframes spin {
  to { transform: rotate(360deg); }
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
</style>