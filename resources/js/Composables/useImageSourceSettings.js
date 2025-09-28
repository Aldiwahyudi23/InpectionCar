import { ref, computed } from 'vue';

export function useImageSourceSettings() {
    const IMAGE_SOURCE_KEY = 'image_source_preference';
    
    // Default preference
    const defaultPreference = {
        source: 'ask', // 'camera', 'gallery', 'ask'
        rememberForAll: false
    };
    
    // Get current preference
    const getPreference = () => {
        try {
            const saved = localStorage.getItem(IMAGE_SOURCE_KEY);
            return saved ? JSON.parse(saved) : defaultPreference;
        } catch (error) {
            console.error('Error reading image source preference:', error);
            return defaultPreference;
        }
    };
    
    // Save preference
    const savePreference = (preference) => {
        try {
            localStorage.setItem(IMAGE_SOURCE_KEY, JSON.stringify(preference));
            return true;
        } catch (error) {
            console.error('Error saving image source preference:', error);
            return false;
        }
    };
    
    // Clear preference
    const clearPreference = () => {
        try {
            localStorage.removeItem(IMAGE_SOURCE_KEY);
        } catch (error) {
            console.error('Error clearing image source preference:', error);
        }
    };
    
    // Current preference (reactive)
    const currentPreference = ref(getPreference());
    
    // Update preference
    const updatePreference = (newPreference) => {
        currentPreference.value = { ...currentPreference.value, ...newPreference };
        savePreference(currentPreference.value);
    };
    
    // Set specific source
    const setSource = (source) => {
        updatePreference({ source });
    };
    
    // Check if should ask or auto-select
    const shouldAutoSelect = computed(() => {
        return currentPreference.value.source !== 'ask';
    });
    
    const autoSelectSource = computed(() => {
        return currentPreference.value.source;
    });
    
    return {
        currentPreference,
        getPreference,
        updatePreference,
        setSource,
        clearPreference,
        shouldAutoSelect,
        autoSelectSource
    };
}