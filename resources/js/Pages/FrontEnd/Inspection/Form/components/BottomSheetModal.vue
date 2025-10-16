<!-- BottomSheetModal.vue -->
<template>
  <transition name="bottom-sheet">
    <div v-if="show" class="fixed inset-0 z-50 overflow-hidden">
      <!-- Backdrop - TANPA event click -->
      <div class="absolute inset-0 bg-black bg-opacity-50"></div>
      
      <!-- Modal Content -->
      <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl max-h-[85vh] overflow-hidden flex flex-col">
        <!-- Handle bar -->
        <div class="flex justify-center py-3">
          <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
        </div>
        
        <!-- Header -->
        <div class="px-4 pb-2 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
          <p v-if="subtitle" class="text-sm text-gray-500 mt-1">{{ subtitle }}</p>
        </div>
        
        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-4">
          <slot></slot>
        </div>
        
        <!-- Footer -->
        <div v-if="$slots.footer" class="border-t border-gray-200 p-4 bg-gray-50">
          <slot name="footer"></slot>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
const props = defineProps({
  show: Boolean,
  title: String,
  subtitle: String
});

</script>

<style scoped>
.bottom-sheet-enter-active,
.bottom-sheet-leave-active {
  transition: opacity 0.3s ease;
}

.bottom-sheet-enter-from,
.bottom-sheet-leave-to {
  opacity: 0;
}

.bottom-sheet-enter-active .absolute.bottom-0,
.bottom-sheet-leave-active .absolute.bottom-0 {
  transition: transform 0.3s ease;
}

.bottom-sheet-enter-from .absolute.bottom-0,
.bottom-sheet-leave-to .absolute.bottom-0 {
  transform: translateY(100%);
}
</style>