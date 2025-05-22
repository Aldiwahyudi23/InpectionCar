<template>
  <div class="mt-1">
    <input
      :value="modelValue"
      @change="updateValue"
      type="date"
      :required="required"
      :min="minDate"
      :max="maxDate"
      class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150"
    />
    <p v-if="error" class="mt-2 text-sm text-red-600">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: String,
  required: Boolean,
  minDate: String,
  maxDate: String,
  error: String,
  pointId: [String, Number]
});

const emit = defineEmits(['update:modelValue', 'save']);

const updateValue = (e) => {
  emit('update:modelValue', e.target.value);
  emit('save', props.pointId);
};
</script>