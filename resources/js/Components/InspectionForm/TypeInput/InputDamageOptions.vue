<template>
  <div class="border rounded-lg p-4 bg-gray-50">
    <h4 class="text-sm font-medium text-gray-700 mb-2">Tingkat Kerusakan</h4>
    <div class="grid grid-cols-2 gap-2">
      <label
        v-for="(damage, index) in options"
        :key="index"
        class="cursor-pointer flex items-center"
      >
        <input
          type="radio"
          :name="`damage-${pointId}`"
          :value="damage.value"
          :checked="modelValue === damage.value"
          @change="updateValue(damage.value)"
          class="mr-2 text-indigo-600 focus:ring-indigo-500"
        />
        <span class="text-sm text-gray-700">{{ damage.label }}</span>
      </label>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: String,
  options: {
    type: Array,
    default: () => []
  },
  pointId: [String, Number]
});

const emit = defineEmits(['update:modelValue']);

const updateValue = (value) => {
  emit('update:modelValue', value);
};
</script>