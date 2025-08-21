<template>
  <div class="mt-1 space-y-1">
    <label class="block text-sm font-medium text-gray-700">
      Masukan Keterangan:
      <span class="text-xs text-gray-500 float-right">{{ modelValue.length }}/{{ settings.textarea_max_length || 400 }}</span>
    </label>
    <textarea
      :value="modelValue"
      @input="updateValue($event)"
      :placeholder="settings.textarea_placeholder || 'Tambahkan keterangan di sini...'"
      :minlength="settings.textarea_min_length"
      :maxlength="settings.textarea_max_length"
      :required="required"
      class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150 resize-none"
      rows="3"
    ></textarea>
    <p v-if="error" class="mt-1 text-sm text-red-600">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: String,
  required: Boolean,
  settings: {
    type: Object,
    default: () => ({})
  },
  error: String,
  pointId: [String, Number]
});

const emit = defineEmits(['update:modelValue', 'save']);

const updateValue = (e) => {
  emit('update:modelValue', e.target.value);
  emit('save', props.pointId);
};
</script>