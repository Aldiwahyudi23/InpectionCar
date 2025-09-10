// resources/js/Composables/usePWA.js
import { ref, onMounted } from 'vue';

export const usePWA = () => {
    const deferredPrompt = ref(null);
    const isAppInstalled = ref(false);
    const canInstall = ref(false);
    const isOnline = ref(navigator.onLine);

    const checkInstallStatus = () => {
        // Check multiple ways to detect if app is installed
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches;
        const isInWebApp = window.navigator.standalone === true;
        
        isAppInstalled.value = isStandalone || isInWebApp;
        canInstall.value = !isAppInstalled.value;
    };

    const registerServiceWorker = async () => {
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.register('/sw.js');
                console.log('Service Worker registered successfully');
                
                // Handle updates
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    console.log('New service worker found');
                    
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed') {
                            console.log('New content is available; please refresh.');
                        }
                    });
                });
                
                return true;
            } catch (error) {
                console.error('Service Worker registration failed:', error);
                return false;
            }
        }
        return false;
    };

    const setupEventListeners = () => {
        // Install prompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt.value = e;
            canInstall.value = true;
        });

        // App installed
        window.addEventListener('appinstalled', () => {
            isAppInstalled.value = true;
            canInstall.value = false;
            deferredPrompt.value = null;
        });

        // Online/offline status
        window.addEventListener('online', () => {
            isOnline.value = true;
        });

        window.addEventListener('offline', () => {
            isOnline.value = false;
        });
    };

    const installApp = async () => {
        if (deferredPrompt.value) {
            deferredPrompt.value.prompt();
            const { outcome } = await deferredPrompt.value.userChoice;
            
            if (outcome === 'accepted') {
                console.log('User accepted the install prompt');
            } else {
                console.log('User dismissed the install prompt');
            }
            
            deferredPrompt.value = null;
        }
    };

    const initPWA = async () => {
        checkInstallStatus();
        setupEventListeners();
        await registerServiceWorker();
    };

    onMounted(() => {
        initPWA();
    });

    return {
        deferredPrompt,
        isAppInstalled,
        canInstall,
        isOnline,
        installApp,
        initPWA
    };
};

export default usePWA;