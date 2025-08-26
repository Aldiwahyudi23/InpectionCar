<template>
  <div v-if="show" class="fixed inset-0 z-40 p-0 webcam-modal-container">
    <div class="webcam-content-box">
      <!-- Header -->
      <div class="webcam-header">
        <div class="inspection-point-name">{{ point?.name || 'Camera' }}</div>
                <!-- Additional controls
        <div class="advanced-controls"> -->
          <!-- Focus Mode Selector -->
          <!-- <select v-model="focusMode" class="focus-selector" @change="changeFocusMode">
            <option value="continuous">Auto Focus</option>
            <option value="manual">Manual Focus</option>
            <option value="single-shot">Single Focus</option>
          </select> -->
          
          <!-- Quality Selector -->
          <!-- <select v-model="qualityLevel" class="quality-selector" @change="changeQuality">
            <option value="high">High Quality</option>
            <option value="medium">Medium Quality</option>
            <option value="low">Low Quality</option>
          </select>
        </div> -->

        <button @click="closeModal" class="p-2 rounded-full text-white hover:bg-gray-700 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

      </div>

      <!-- Video Container dengan aspect ratio -->
      <div class="webcam-video-container" :style="videoContainerStyle" @click="triggerFocus">
        <video ref="webcamVideo" autoplay playsinline class="webcam-video"></video>
        <canvas ref="webcamCanvas" class="hidden"></canvas>
        <div v-if="showScreenFlash" class="screen-flash-overlay"></div>
        
        <!-- Aspect ratio guide overlay -->
        <div class="aspect-ratio-guide" :style="aspectRatioGuideStyle"></div>

         <!-- Focus indicator -->
        <div v-if="showFocusIndicator" class="focus-indicator" :style="focusIndicatorStyle">
          <div class="focus-ring"></div>
        </div>
        
        <!-- HD Badge -->
        <div class="hd-badge">
          <span class="hd-text">HD</span>
        </div>
        
        <!-- Focus status -->
        <div v-if="focusStatus" class="focus-status">
          {{ focusStatus }}
        </div>
      </div>

      <!-- Footer controls -->
      <div class="webcam-footer">
        <div class="camera-controls">
          <!-- Flash -->
          <button v-if="settings.enable_flash" 
            @click="toggleFlash" 
            :disabled="!isFlashSupported" 
            class="control-button"
            :class="{'active': isFlashOn, 'disabled': !isFlashSupported}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </button>

          <!-- Capture Button dengan icon kamera yang jelas -->
          <button @click="capturePhoto" class="capture-button">
            <div class="camera-icon-container">
              <div class="camera-body">
                <div class="camera-lens"></div>
                <div class="camera-flash"></div>
              </div>
            </div>
          </button>

          <!-- Switch Camera -->
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
import { ref, computed, watch, onBeforeUnmount } from 'vue';

const props = defineProps({
  show: Boolean,
  aspectRatio: Number,
  settings: Object,
  inspectionPoint: String,
  point: Object,
  pointname: String
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
const currentInspectionPoint = ref(props.inspectionPoint || 'Camera');
const showFocusIndicator = ref(false);
const focusIndicatorStyle = ref({});
const focusStatus = ref('');
const focusMode = ref('continuous');
const qualityLevel = ref('high');

// Computed property untuk resolusi berdasarkan quality level
const resolutionSettings = computed(() => {
  switch (qualityLevel.value) {
    case 'high':
      return { width: 1920, height: 1080 }; // Full HD
    case 'medium':
      return { width: 1280, height: 720 };  // HD
    case 'low':
      return { width: 640, height: 480 };   // VGA
    default:
      return { width: 1920, height: 1080 };
  }
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
  const width = ratio > 1 ? 80 : 60; // Lebar guide berdasarkan ratio
  const height = ratio > 1 ? 80 / ratio : 60 * ratio;
  
  return {
    width: `${width}%`,
    height: `${height}%`
  };
});

// Computed property untuk max size dari settings
const maxSizeKB = computed(() => {
  return props.settings?.max_size || 2048; // Default 2MB jika tidak ada setting
});

const initializeWebcam = async () => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }
  isFlashOn.value = false;
  showFocusIndicator.value = false;
  focusStatus.value = '';
  
  try {
    const videoConstraints = {
      facingMode: currentFacingMode.value,
      width: { ideal: resolutionSettings.value.width },
      height: { ideal: resolutionSettings.value.height },
      aspectRatio: { ideal: props.aspectRatio || 4/3 },
      frameRate: { ideal: 30 }
    };
    
    // Tambahkan constraints untuk kualitas tinggi
    if (qualityLevel.value === 'high') {
      videoConstraints.width = { ideal: 1920, max: 3840 };
      videoConstraints.height = { ideal: 1080, max: 2160 };
      videoConstraints.frameRate = { ideal: 30, max: 60 };
    }
    
    // Set focus mode berdasarkan pilihan
    if (focusMode.value !== 'manual') {
      videoConstraints.advanced = [{ focusMode: focusMode.value }];
    }
    
    if (currentFacingMode.value === 'environment') {
      if (!videoConstraints.advanced) videoConstraints.advanced = [];
      videoConstraints.advanced.push({ focusMode: focusMode.value });
    }
    
    mediaStream = await navigator.mediaDevices.getUserMedia({ 
      video: videoConstraints,
      audio: false 
    });
    
    videoTrack = mediaStream.getVideoTracks()[0];
    webcamVideo.value.srcObject = mediaStream;
    
    webcamVideo.value.onloadedmetadata = async () => {
      await checkCameraCapabilities();
      // Auto focus saat pertama kali
      if (focusMode.value === 'continuous' || focusMode.value === 'single-shot') {
        await triggerAutoFocus();
      }
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
    isFlashSupported.value = capabilities.torch || 
                            (capabilities.fillLightMode && capabilities.fillLightMode.includes('torch'));
    
    // Check focus capabilities
    const focusCapabilities = capabilities.focusDistance || {};
    console.log('Camera capabilities:', capabilities);
    
  } catch (error) {
    console.error("Error checking camera capabilities:", error);
  }
};

const handleTapToFocus = async (event) => {
  if (!videoTrack || focusMode.value === 'continuous') return;
  
  const rect = webcamVideo.value.getBoundingClientRect();
  const x = ((event.clientX - rect.left) / rect.width) * 100;
  const y = ((event.clientY - rect.top) / rect.height) * 100;
  
  // Show focus indicator
  showFocusIndicator.value = true;
  focusIndicatorStyle.value = {
    left: `${x}%`,
    top: `${y}%`,
    transform: 'translate(-50%, -50%)'
  };
  
  focusStatus.value = 'Focusing...';
  
  try {
    if (focusMode.value === 'single-shot') {
      await triggerSingleFocus(x, y);
    } else if (focusMode.value === 'manual') {
      await triggerManualFocus(x, y);
    }
    
    setTimeout(() => {
      showFocusIndicator.value = false;
      focusStatus.value = 'Focused';
      setTimeout(() => { focusStatus.value = ''; }, 2000);
    }, 1000);
    
  } catch (error) {
    console.error("Focus failed:", error);
    focusStatus.value = 'Focus failed';
    setTimeout(() => { focusStatus.value = ''; }, 2000);
  }
};

const triggerAutoFocus = async () => {
  if (!videoTrack) return;
  
  try {
    const capabilities = videoTrack.getCapabilities();
    if (capabilities.focusMode && capabilities.focusMode.includes('continuous')) {
      await videoTrack.applyConstraints({
        advanced: [{ focusMode: 'continuous' }]
      });
      focusStatus.value = 'Auto Focus Enabled';
      setTimeout(() => { focusStatus.value = ''; }, 2000);
    }
  } catch (error) {
    console.warn("Auto focus not supported:", error);
  }
};

const triggerSingleFocus = async (x, y) => {
  if (!videoTrack) return;
  
  try {
    // Untuk single focus, kita gunakan point of interest jika supported
    const capabilities = videoTrack.getCapabilities();
    if (capabilities.pointsOfInterest) {
      await videoTrack.applyConstraints({
        advanced: [{
          pointsOfInterest: [{ x: x/100, y: y/100 }],
          focusMode: 'single-shot'
        }]
      });
    } else if (capabilities.focusMode && capabilities.focusMode.includes('single-shot')) {
      await videoTrack.applyConstraints({
        advanced: [{ focusMode: 'single-shot' }]
      });
    }
  } catch (error) {
    console.warn("Single focus not supported:", error);
  }
};

const triggerManualFocus = async (x, y) => {
  if (!videoTrack) return;
  
  try {
    const capabilities = videoTrack.getCapabilities();
    if (capabilities.pointsOfInterest) {
      await videoTrack.applyConstraints({
        advanced: [{
          pointsOfInterest: [{ x: x/100, y: y/100 }],
          focusMode: 'manual'
        }]
      });
    }
  } catch (error) {
    console.warn("Manual focus not supported:", error);
  }
};

const changeFocusMode = async () => {
  if (props.show) {
    await initializeWebcam();
  }
};

const changeQuality = async () => {
  if (props.show) {
    await initializeWebcam();
  }
};

const toggleFlash = async () => {
  if (!videoTrack || !props.settings.enable_flash) return;
  
  if (currentFacingMode.value === 'user') {
    // Untuk kamera depan, gunakan screen flash
    isFlashOn.value = !isFlashOn.value;
    showScreenFlash.value = isFlashOn.value;
    return;
  }
  
  try {
    if (videoTrack.getCapabilities().torch) {
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
  currentInspectionPoint.value = `${props.inspectionPoint || 'Camera'} (${currentFacingMode.value === 'environment' ? 'Rear' : 'Front'})`;
  
  await initializeWebcam();
};

const triggerFocus = async () => {
  if (!videoTrack) return;
  
  const caps = videoTrack.getCapabilities();
  if (caps.focusMode && caps.focusMode.includes('continuous')) {
    try {
      await videoTrack.applyConstraints({ advanced: [{ focusMode: "continuous" }] });
    } catch (e) {
      console.warn("Focus not supported", e);
    }
  }
};

// Fungsi untuk mengompres gambar sesuai max_size
const compressImage = async (blob) => {
  return new Promise((resolve) => {
    const MAX_SIZE = maxSizeKB.value * 1024; // Convert KB to bytes
    
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
        
        // Pertahankan aspect ratio asli
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
          }, 'image/png', quality);
        };
        
        compressRecursive();
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(blob);
  });
};

const capturePhoto = async () => {
  const video = webcamVideo.value;
  const canvas = webcamCanvas.value;
  
  if (!video || !canvas || video.readyState !== 4) return;
  
  try {
    const ctx = canvas.getContext('2d');
    const vw = video.videoWidth;
    const vh = video.videoHeight;
    
    // Hitung crop area berdasarkan aspect ratio yang diinginkan
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
    
    // Set canvas size sesuai aspect ratio yang diinginkan
    canvas.width = sw;
    canvas.height = sh;
    
    // Draw image dengan crop yang tepat
    ctx.drawImage(video, sx, sy, sw, sh, 0, 0, sw, sh);
    
    // Dapatkan blob asli
    const originalBlob = await new Promise(resolve => {
      canvas.toBlob(resolve, 'image/png', 1.0);
    });
    
    // Kompres gambar jika diperlukan
    const compressedBlob = await compressImage(originalBlob);
    
    if (compressedBlob) {
      const file = new File([compressedBlob], `capture_${Date.now()}.png`, { 
        type: 'image/png',
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
  }
};

// Watch untuk perubahan props
watch(() => props.show, (v) => { 
  if (v) {
    initializeWebcam();
  } else {
    closeModal();
  }
});

watch(() => props.inspectionPoint, (v) => { 
  currentInspectionPoint.value = v || 'Camera'; 
});

watch(() => props.aspectRatio, () => {
  if (props.show) {
    initializeWebcam();
  }
});

onBeforeUnmount(() => { 
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

/* Responsive design */
@media (max-width: 640px) {
  .control-button {
    width: 45px;
    height: 45px;
  }
  
  .capture-button {
    width: 65px;
    height: 65px;
  }
  
  .control-button svg {
    width: 20px;
    height: 20px;
  }
  
  .camera-body {
    width: 28px;
    height: 28px;
  }
  
  .camera-lens {
    width: 18px;
    height: 18px;
  }
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

.focus-status {
  position: absolute;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 14px;
  z-index: 15;
}

.advanced-controls {
  display: flex;
  gap: 10px;
  margin-top: 15px;
  justify-content: center;
}

.focus-selector,
.quality-selector {
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid #ccc;
  border-radius: 20px;
  padding: 8px 16px;
  font-size: 12px;
  color: #333;
  cursor: pointer;
}

.focus-selector:hover,
.quality-selector:hover {
  background: rgba(255, 255, 255, 1);
}

/* Improved video quality */
.webcam-video {
  width: 100%;
  height: 100%;
  object-fit: cover;
  filter: brightness(1.05) contrast(1.05);
}

/* Enhanced capture button */
.capture-button {
  background: linear-gradient(145deg, #ffffff, #e6e6e6);
  border: 2px solid rgba(255, 255, 255, 0.8);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.capture-button:active {
  transform: scale(0.95);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}
</style>