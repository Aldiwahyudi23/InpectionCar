<template>
  <div class="mt-2">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
      <label
        v-for="option in options"
        :key="option.value"
        class="inline-flex items-center space-x-2"
      >
        <input
          type="radio"
          :name="'radio-' + pointId"
          :value="option.value"
          :checked="modelValue === option.value"
          @change="updateValue(option.value)"
          :required="required"
          class="text-indigo-600 focus:ring-indigo-500 border-gray-300"
        />
        <span class="text-sm text-gray-700">{{ option.label }}</span>
      </label>
    </div>
    <p v-if="error" class="mt-2 text-sm text-red-600">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: String,
  required: Boolean,
  error: String,
  pointId: [String, Number],
  options: {
    type: Array,
    default: () => [
      { value: 'good', label: 'Good' },
      { value: 'bad', label: 'Bad' },
      { value: 'na', label: 'N/A' }
    ]
  }
});

const emit = defineEmits(['update:modelValue', 'save']);

const updateValue = (value) => {
  emit('update:modelValue', value);
  emit('save', props.pointId);
};
</script>