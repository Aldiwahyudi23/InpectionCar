// resources/js/composables/useDraggableButton.js
import { ref, onMounted } from 'vue';

export function useDraggableButton(storageKey, defaultPos) {
  const pos = ref({ x: defaultPos.x, y: defaultPos.y });
  const dragging = ref(false);
  const longPressTimer = ref(null);
  const isLongPressing = ref(false);
  const startPos = ref({ x: 0, y: 0 });

  onMounted(() => {
    const saved = localStorage.getItem(storageKey);
    if (saved) {
      try {
        pos.value = JSON.parse(saved);
      } catch (e) {
        console.error('Error parsing saved position:', e);
      }
    }
  });

  const startLongPress = (e) => {
    // Simpan posisi awal
    if (e.type.includes('touch')) {
      startPos.value.x = e.touches[0].clientX;
      startPos.value.y = e.touches[0].clientY;
    } else {
      startPos.value.x = e.clientX;
      startPos.value.y = e.clientY;
    }

    // Start timer untuk long press
    longPressTimer.value = setTimeout(() => {
      isLongPressing.value = true;
      dragging.value = true;
      console.log('Long press activated - drag mode ON');
    }, 900); // ✅ PERPANJANG jadi 800ms agar tidak accidental
  };

  const cancelLongPress = () => {
    if (longPressTimer.value) {
      clearTimeout(longPressTimer.value);
      longPressTimer.value = null;
    }
    isLongPressing.value = false;
  };

  const onDrag = (e) => {
    // Hanya drag jika dalam mode long press
    if (!dragging.value || !isLongPressing.value) return;

    e.preventDefault();

    let clientX, clientY;
    
    if (e.type.includes('touch')) {
      clientX = e.touches[0].clientX;
      clientY = e.touches[0].clientY;
    } else {
      clientX = e.clientX;
      clientY = e.clientY;
    }

    // Update posisi
    pos.value.x = clientX - 24; // 24 = half of button size
    pos.value.y = clientY - 24;
  };

  const stopDrag = () => {
    if (dragging.value && isLongPressing.value) {
      console.log('Drag stopped - saving position');
      dragging.value = false;
      isLongPressing.value = false;
      
      try {
        localStorage.setItem(storageKey, JSON.stringify(pos.value));
      } catch (e) {
        console.error('Error saving position:', e);
      }
    }
    
    // Always clear timer
    cancelLongPress();
  };

  const handleClick = (e) => {
    // Jika sedang tidak dragging, ini adalah click biasa
    if (!dragging.value && !isLongPressing.value) {
      console.log('Regular click detected');
      return true; // Return true untuk indicate ini click biasa
    }
    
    // Jika sedang dragging, ini adalah bagian dari drag operation
    console.log('Click ignored - part of drag operation');
    return false;
  };

  return {
    pos,
    dragging,
    startLongPress,
    cancelLongPress,
    onDrag,
    stopDrag,
    handleClick,
  };
}