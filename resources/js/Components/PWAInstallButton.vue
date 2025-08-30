<template>
  <button
    v-if="showInstallButton && !isInstalled"
    @click="installPWA"
    class="pwa-install-btn"
    id="installButton"
  >
    <svg class="install-icon" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
    </svg>
    Install App
  </button>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const showInstallButton = ref(false)
const isInstalled = ref(false)
let deferredPrompt = null

onMounted(() => {
  // Check if app is already installed (mobile detection)
  isInstalled.value = window.matchMedia('(display-mode: standalone)').matches || 
                     window.navigator.standalone ||
                     document.referrer.includes('android-app://')

  // Listen for before install prompt
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault()
    deferredPrompt = e
    showInstallButton.value = true
  })

  // Listen for app installed event
  window.addEventListener('appinstalled', () => {
    isInstalled.value = true
    showInstallButton.value = false
    deferredPrompt = null
  })
})

const installPWA = async () => {
  if (!deferredPrompt) return

  try {
    deferredPrompt.prompt()
    const { outcome } = await deferredPrompt.userChoice
    
    if (outcome === 'accepted') {
      console.log('PWA installed successfully on mobile')
    }
  } catch (error) {
    console.error('PWA installation failed:', error)
  }
  
  deferredPrompt = null
  showInstallButton.value = false
}
</script>

<style scoped>
.pwa-install-btn {
  position: fixed;
  bottom: 80px; /* Above bottom nav */
  right: 16px;
  background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%);
  color: white;
  padding: 12px 16px;
  border-radius: 50px;
  border: none;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
  z-index: 1000;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  min-height: 44px; /* Mobile touch target */
}

.install-icon {
  width: 20px;
  height: 20px;
}

@media (max-width: 640px) {
  .pwa-install-btn {
    bottom: 70px;
    right: 12px;
    padding: 10px 14px;
    font-size: 14px;
  }
}
</style>