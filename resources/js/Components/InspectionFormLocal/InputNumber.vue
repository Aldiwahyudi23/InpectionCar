<template>
  <div class="mt-1">
    <input
      :value="modelValue"
      @input="updateValue"
      type="number"
      :required="required"
      :min="min"
      :max="max"
      :step="step"
      :placeholder="placeholder"
      class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150"
    />
    <p v-if="error" class="mt-2 text-sm text-red-600">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: [String, Number],
  required: Boolean,
  min: [String, Number],
  max: [String, Number],
  step: [String, Number],
  placeholder: String,
  error: String,
  pointId: [String, Number]
});

const emit = defineEmits(['update:modelValue', 'save']);

const updateValue = (e) => {
  const value = e.target.value; // Kirim sebagai string
  emit('update:modelValue', value);
  emit('save', props.pointId);
};
</script>
