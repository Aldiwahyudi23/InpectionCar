<template>
  <div class="mt-1">
    <select
      :value="modelValue"
      @change="updateValue"
      :required="required"
      class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150"
    >
      <option value="" disabled>Select status</option>
      <option value="good">Good</option>
      <option value="bad">Bad</option>
      <option value="na">N/A</option>
    </select>
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
  pointId: [String, Number]
});

const emit = defineEmits(['update:modelValue', 'save']);

const updateValue = (e) => {
  emit('update:modelValue', e.target.value);
  emit('save', props.pointId);
};
</script>

<style scoped>
select {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: 2.5rem;
  -webkit-print-color-adjust: exact;
  print-color-adjust: exact;
}
</style>