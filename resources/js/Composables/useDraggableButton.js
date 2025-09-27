// resources/js/composables/useDraggableButton.js
import { ref, onMounted } from 'vue';

export function useDraggableButton(storageKey, defaultPos) {
  const pos = ref({ x: defaultPos.x, y: defaultPos.y });
  const dragging = ref(false);
  const longPressTimer = ref(null);

  onMounted(() => {
    const saved = localStorage.getItem(storageKey);
    if (saved) pos.value = JSON.parse(saved);
  });

  const startLongPress = () => {
    longPressTimer.value = setTimeout(() => {
      dragging.value = true;
    }, 500); // 500ms tahan → bisa drag
  };

  const cancelLongPress = () => {
    clearTimeout(longPressTimer.value);
  };

  const onDrag = (e) => {
    if (!dragging.value) return;

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
      localStorage.setItem(storageKey, JSON.stringify(pos.value));
    }
  };

  return {
    pos,
    startLongPress,
    cancelLongPress,
    onDrag,
    stopDrag,
    dragging,
  };
}
