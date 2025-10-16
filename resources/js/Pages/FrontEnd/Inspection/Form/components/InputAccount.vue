<template>
  <div class="mt-1">
    <input
      :value="displayValue"
      @input="handleInput"
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

// Default settings
const currentSettings = computed(() => ({
  currency_symbol: props.settings?.currency_symbol || 'Rp',
  thousands_separator: props.settings?.thousands_separator || ',',
  min_value: props.settings?.min_value !== undefined ? Number(props.settings.min_value) : 0,
  max_value: props.settings?.max_value !== undefined ? Number(props.settings.max_value) : 1000000000
}));

// Format number ke ribuan
const formatNumber = (number) => {
  if (number === null || number === undefined || number === '') return '';

  const num = Number(number);
  if (isNaN(num)) return '';

  let formatted = num.toLocaleString('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  });

  if (currentSettings.value.thousands_separator !== ',') {
    formatted = formatted.replace(/,/g, currentSettings.value.thousands_separator);
  }

  return formatted;
};

// Ambil angka dari string (hapus non-digit)
const parseToNumber = (input) => {
  if (!input) return '';
  return input.replace(/[^\d]/g, '');
};

// Tampilkan selalu terformat
const displayValue = computed(() => {
  if (!internalValue.value) return '';
  return `${currentSettings.value.currency_symbol} ${formatNumber(internalValue.value)}`;
});

// Input handler
const handleInput = (event) => {
  let raw = parseToNumber(event.target.value);

  // validasi batas
  let num = Number(raw);
  if (currentSettings.value.min_value !== undefined && num < currentSettings.value.min_value) {
    raw = currentSettings.value.min_value.toString();
  }
  if (currentSettings.value.max_value !== undefined && num > currentSettings.value.max_value) {
    raw = currentSettings.value.max_value.toString();
  }

  internalValue.value = raw;

  // Emit angka mentah ke parent
  emit('update:modelValue', raw);
  emit('save', props.pointId);
};

// Sinkronisasi jika value dari luar berubah
watch(() => props.modelValue, (newValue) => {
  if (newValue !== undefined && newValue !== null) {
    internalValue.value = newValue.toString();
  }
}, { immediate: true });
</script>
