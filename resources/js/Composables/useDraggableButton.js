// resources/js/composables/useDraggableButton.js
import { ref, onMounted } from 'vue';

export function useDraggableButton(storageKey, defaultPos) {
  const pos = ref({ x: defaultPos.x, y: defaultPos.y });
  const dragging = ref(false);
  const longPressTimer = ref(null);
  const isLongPressing = ref(false);

  onMounted(() => {
    const saved = localStorage.getItem(storageKey);
    if (saved) pos.value = JSON.parse(saved);
  });

  const startLongPress = (e) => {
    // Prevent default untuk menghindari behavior tidak diinginkan
    e.preventDefault();
    
    longPressTimer.value = setTimeout(() => {
      isLongPressing.value = true;
      dragging.value = true;
      
      // Set posisi awal drag berdasarkan titik sentuh
      if (e.type.includes('touch')) {
        pos.value.x = e.touches[0].clientX - 24;
        pos.value.y = e.touches[0].clientY - 24;
      } else {
        pos.value.x = e.clientX - 24;
        pos.value.y = e.clientY - 24;
      }
    }, 500); // Harus tahan 500ms baru bisa drag
  };

  const cancelLongPress = () => {
    clearTimeout(longPressTimer.value);
    isLongPressing.value = false;
    
    // Jika sedang tidak dragging, reset timer saja
    if (!dragging.value) {
      longPressTimer.value = null;
    }
  };

  const onDrag = (e) => {
    // Hanya drag jika benar-benar dalam mode dragging (setelah long press)
    if (!dragging.value || !isLongPressing.value) return;

    // Prevent default untuk mobile scrolling
    e.preventDefault();

    if (e.type.includes('touch')) {
      pos.value.x = e.touches[0].clientX - 24;
      pos.value.y = e.touches[0].clientY - 24;
    } else {
      pos.value.x = e.clientX - 24;
      pos.value.y = e.clientY - 24;
    }
  };

  const stopDrag = () => {
    if (dragging.value) {
      dragging.value = false;
      isLongPressing.value = false;
      clearTimeout(longPressTimer.value);
      longPressTimer.value = null;
      localStorage.setItem(storageKey, JSON.stringify(pos.value));
    }
  };

  // Fungsi untuk handle click biasa (bukan drag)
  const handleClick = (e) => {
    // Jika sedang tidak dragging, biarkan click biasa
    if (!dragging.value && !isLongPressing.value) {
      // Click handler biasa bisa ditambahkan di sini
      return true; // Return true untuk indicate ini click biasa
    }
    return false; // Return false untuk indicate ini bagian dari drag
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