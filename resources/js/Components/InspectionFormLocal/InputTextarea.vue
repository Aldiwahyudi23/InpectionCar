<template>
  <div class="mt-1 space-y-3">
    <!-- Opsi Kerusakan -->
    <div v-if="settings?.enable_damage && settings?.damage_options" class="space-y-2">
      <h4 class="text-sm font-medium text-gray-700">Pilih Kerusakan:</h4>
      <div class="flex flex-wrap gap-2">
        <button
          v-for="(damage, damageIndex) in settings.damage_options"
          :key="damageIndex"
          type="button"
          @click="toggleDamageOption(damage)"
          class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors border"
          :class="{
            'border-indigo-500 bg-indigo-100 text-indigo-700': isDamageSelected(damage.value),
            'border-gray-300 bg-white text-gray-700 hover:bg-gray-50': !isDamageSelected(damage.value)
          }"
        >
          {{ damage.label }}
        </button>
      </div>
    </div>

    <!-- Textarea -->
    <div>
      <textarea
        :value="modelValue"
        @input="updateValue"
        :required="required"
        :minlength="minLength"
        :maxlength="maxLength"
        :placeholder="placeholder"
        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150"
        rows="4"
      ></textarea>
      <div class="flex justify-between items-center mt-1">
        <span class="text-xs text-gray-500">{{ modelValue?.length || 0 }}/{{ maxLength || 400 }}</span>
        <p v-if="error" class="text-xs text-red-600">{{ error }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";

const props = defineProps({
  modelValue: String,
  required: Boolean,
  minLength: [String, Number],
  maxLength: [String, Number],
  placeholder: String,
  error: String,
  pointId: [String, Number],
  inspectionId: [String, Number],
  settings: Object,
});

const emit = defineEmits(["update:modelValue", "save"]);

// daftar damage yang dipilih (berisi object damage)
const selectedDamages = ref([]);
const manualText = ref(""); // untuk menyimpan teks manual

// cek apakah damage sudah dipilih
const isDamageSelected = (value) => {
  return selectedDamages.value.some((d) => d.value === value);
};

// toggle pilihan damage
const toggleDamageOption = (damage) => {
  if (isDamageSelected(damage.value)) {
    // Hapus damage yang dipilih
    selectedDamages.value = selectedDamages.value.filter((d) => d.value !== damage.value);
  } else {
    // Tambah damage yang dipilih
    selectedDamages.value.push(damage);
  }

  // Gabungkan damage values dengan teks manual
  const damageValues = selectedDamages.value.map((d) => d.value);
  const allValues = [...damageValues];
  
  // Tambahkan teks manual jika ada
  if (manualText.value.trim()) {
    allValues.push(manualText.value.trim());
  }
  
  const newValue = allValues.join(", ");
  emit("update:modelValue", newValue);
  emit("save", { 
    pointId: props.pointId, 
    inspectionId: props.inspectionId,
    notes: newValue 
  });
};

// update manual dari textarea (misalnya user edit sendiri)
const updateValue = (e) => {
  const text = e.target.value;
  emit("update:modelValue", text);
  emit("save", { 
    pointId: props.pointId, 
    inspectionId: props.inspectionId,
    notes: text 
  });

  // Ekstrak damage values dan teks manual dari input
  parseTextInput(text);
};

// Fungsi untuk mem-parsing teks input menjadi damage values dan teks manual
const parseTextInput = (text) => {
  if (!text) {
    selectedDamages.value = [];
    manualText.value = "";
    return;
  }

  const values = text.split(",").map(v => v.trim()).filter(v => v);
  const damageValues = props.settings?.damage_options?.map(d => d.value) || [];
  
  // Filter values yang merupakan damage options
  selectedDamages.value = props.settings?.damage_options?.filter(d => 
    values.includes(d.value)
  ) || [];
  
  // Ambil values yang bukan damage options sebagai teks manual
  const manualValues = values.filter(value => 
    !damageValues.includes(value)
  );
  
  manualText.value = manualValues.join(", ");
};

// sinkron saat props.modelValue berubah (misal data lama di-edit)
watch(
  () => props.modelValue,
  (newVal) => {
    if (newVal !== undefined && newVal !== null) {
      parseTextInput(newVal);
    }
  },
  { immediate: true }
);
</script>