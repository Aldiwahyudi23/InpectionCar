<template>
  <button
    v-if="showInstallButton && !isInstalled"
    @click="installPWA"
    class="fixed bottom-4 right-4 bg-indigo-600 text-white px-4 py-2 rounded-full shadow-lg hover:bg-indigo-700 z-50"
    id="installButton"
  >
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
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
  // Check if app is already installed
  isInstalled.value = window.matchMedia('(display-mode: standalone)').matches || 
                     window.navigator.standalone

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

  deferredPrompt.prompt()
  const { outcome } = await deferredPrompt.userChoice
  
  if (outcome === 'accepted') {
    console.log('User accepted the install prompt')
  } else {
    console.log('User dismissed the install prompt')
  }
  
  deferredPrompt = null
  showInstallButton.value = false
}
</script>