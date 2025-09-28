<template>
  <div class="mt-1">
    <input
      :value="formattedValue"
      @input="updateValue"
      type="text"
      :required="required"
      :minlength="minLength"
      :maxlength="maxLength"
      :allowSpace="allowSpace"
      :placeholder="placeholder"
      class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150"
      :class="{
        'uppercase': textTransform === 'uppercase',
        'lowercase': textTransform === 'lowercase',
        'capitalize': textTransform === 'capitalize',
      }"
    />
    <p v-if="error" class="mt-2 text-sm text-red-600">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { computed } from "vue"

const props = defineProps({
  modelValue: String,
  required: Boolean,
  minLength: [String, Number],
  maxLength: [String, Number],
  pattern: String,
  placeholder: String,
  error: String,
  pointId: [String, Number],
  textTransform: {
    type: String,
    default: 'none', // none, uppercase, lowercase, capitalize
  },
  allowSpace: {
    type: Boolean,
    default: true, // false => hapus semua spasi
  }
});

const emit = defineEmits(['update:modelValue', 'save']);

// Format tampilan
const formattedValue = computed(() => {
  if (!props.modelValue) return ''
  let val = props.modelValue

  if (!props.allowSpace) {
    val = val.replace(/\s+/g, '') // hapus semua spasi
  }

  switch (props.textTransform) {
    case 'uppercase':
      return val.toUpperCase()
    case 'lowercase':
      return val.toLowerCase()
    case 'capitalize':
      return val.replace(/\b\w/g, l => l.toUpperCase())
    default:
      return val
  }
})

const updateValue = (e) => {
  let value = e.target.value

  // Hapus spasi jika tidak diizinkan
  if (!props.allowSpace) {
    value = value.replace(/\s+/g, '')
  }

  // Terapkan transformasi huruf
  switch (props.textTransform) {
    case 'uppercase':
      value = value.toUpperCase()
      break
    case 'lowercase':
      value = value.toLowerCase()
      break
    case 'capitalize':
      value = value.replace(/\b\w/g, l => l.toUpperCase())
      break
  }

  emit('update:modelValue', value)
  emit('save', props.pointId)
}
</script>
