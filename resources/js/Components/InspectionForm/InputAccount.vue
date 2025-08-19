<template>
  <div class="mt-1">
    <input
      :value="displayValue"
      @input="handleInput"
      @blur="handleBlur"
      @focus="handleFocus"
      type="text"
      :required="required"
      :placeholder="placeholder"
      :class="[
        'block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150',
        error ? 'border-red-300' : 'border-gray-300'
      ]"
    />
    <p v-if="error" class="mt-2 text-sm text-red-600">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  modelValue: [String, Number],
  required: Boolean,
  placeholder: String,
  error: String,
  pointId: [String, Number],
  settings: {
    type: Object,
    default: () => ({
      currency_symbol: 'Rp',
      thousands_separator: ',',
      min_value: 0,
      max_value: 1000000000
    })
  }
});

const emit = defineEmits(['update:modelValue', 'save']);

const internalValue = ref('');
const isFocused = ref(false);

// Default settings dengan fallback
const currentSettings = computed(() => ({
  currency_symbol: props.settings?.currency_symbol || 'Rp',
  thousands_separator: props.settings?.thousands_separator || ',',
  min_value: props.settings?.min_value !== undefined ? Number(props.settings.min_value) : 0,
  max_value: props.settings?.max_value !== undefined ? Number(props.settings.max_value) : 1000000000
}));

// Format number dengan thousands separator
const formatNumber = (number) => {
  if (number === null || number === undefined || number === '') return '';
  
  const num = Number(number);
  if (isNaN(num)) return '';
  
  // Format number tanpa decimal places (integer only)
  let formatted = num.toLocaleString('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  });
  
  // Replace default separator dengan custom separator
  if (currentSettings.value.thousands_separator !== ',') {
    formatted = formatted.replace(/,/g, currentSettings.value.thousands_separator);
  }
  
  return formatted;
};

// Parse input ke number (hilangkan semua non-digit)
const parseToNumber = (input) => {
  if (!input) return '';
  
  // Hapus semua karakter non-digit
  const digitsOnly = input.replace(/[^\d]/g, '');
  return digitsOnly;
};

// Format nilai untuk display
const displayValue = computed(() => {
  if (isFocused.value) {
    return internalValue.value;
  }
  
  // Format angka dengan currency symbol dan thousands separator
  const formattedNumber = formatNumber(props.modelValue);
  return formattedNumber ? `${currentSettings.value.currency_symbol} ${formattedNumber}` : '';
});

// Handle input event
const handleInput = (event) => {
  const inputValue = event.target.value;
  
  // Simpan raw input untuk editing
  internalValue.value = inputValue;
  
  // Parse ke number (hanya ambil digits)
  const numericValue = parseToNumber(inputValue);
  
  // Emit nilai sebagai string (sama seperti input-number)
  emit('update:modelValue', numericValue);
  emit('save', props.pointId);
};

const handleFocus = () => {
  isFocused.value = true;
  // Tampilkan raw number tanpa formatting saat focus
  internalValue.value = props.modelValue?.toString() || '';
};

const handleBlur = () => {
  isFocused.value = false;
  
  // Validasi min/max saat blur
  const numericValue = parseToNumber(internalValue.value);
  if (numericValue) {
    const num = Number(numericValue);
    let finalValue = numericValue;
    
    if (currentSettings.value.min_value !== undefined && num < currentSettings.value.min_value) {
      finalValue = currentSettings.value.min_value.toString();
    }
    if (currentSettings.value.max_value !== undefined && num > currentSettings.value.max_value) {
      finalValue = currentSettings.value.max_value.toString();
    }
    
    // Update jika ada perubahan setelah validasi
    if (finalValue !== numericValue) {
      emit('update:modelValue', finalValue);
      emit('save', props.pointId);
    }
  }
};

// Watch untuk perubahan modelValue dari luar
watch(() => props.modelValue, (newValue) => {
  if (!isFocused.value && newValue !== undefined && newValue !== null) {
    internalValue.value = newValue.toString();
  }
}, { immediate: true });
</script>