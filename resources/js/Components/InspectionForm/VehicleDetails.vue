<script setup>
import { ref, computed, watch, onMounted } from "vue";

const props = defineProps({
  inspection: Object,   // data inspeksi
  CarDetail: Array,     // daftar car_details (sudah dengan brand, model, type)
});

const emit = defineEmits(["update-vehicle", "save-car-details"]);

// --- FORM STATE ---
const form = ref({
  plate_number: props.inspection?.plate_number || "",
  brand_id: "",
  car_model_id: "",
  car_type_id: "",
  year: "",
  cc: "",
  transmission: "AT",
  fuel_type: "",
  production_period: "",
  car_id: props.inspection?.car_id || null,
});

// --- kalau inspection.car_id ada, isi form otomatis ---
onMounted(() => {
  if (props.inspection?.car_id) {
    const car = props.CarDetail.find(c => c.id === props.inspection.car_id);
    if (car) {
      form.value = {
        plate_number: props.inspection.plate_number,
        brand_id: car.brand_id,
        car_model_id: car.car_model_id,
        car_type_id: car.car_type_id,
        year: car.year,
        cc: car.cc,
        transmission: car.transmission,
        fuel_type: car.fuel_type,
        production_period: car.production_period,
        car_id: car.id,
      };
    }
  }
});

// --- DROPDOWN OPTIONS ---
// unique brand
const brands = computed(() => {
  const unique = {};
  props.CarDetail.foreach(c => {
    if (!unique[c.brand_id]) unique[c.brand_id] = c.brand.name;
  });
  return Object.entries(unique).map(([id, name]) => ({ id, name }));
});

// models filter by brand
const models = computed(() => {
  if (!form.value.brand_id) return [];
  const filtered = props.CarDetail.filter(c => c.brand_id == form.value.brand_id);
  const unique = {};
  filtered.forEach(c => { if (!unique[c.car_model_id]) unique[c.car_model_id] = c.model.name; });
  return Object.entries(unique).map(([id, name]) => ({ id, name }));
});

// types filter by model
const types = computed(() => {
  if (!form.value.car_model_id) return [];
  const filtered = props.CarDetail.filter(c => c.car_model_id == form.value.car_model_id);
  const unique = {};
  filtered.forEach(c => { if (!unique[c.car_type_id]) unique[c.car_type_id] = c.type.name; });
  return Object.entries(unique).map(([id, name]) => ({ id, name }));
});

// years filter by type
const years = computed(() => {
  if (!form.value.car_type_id) return [];
  return [...new Set(
    props.CarDetail.filter(c => c.car_type_id == form.value.car_type_id).map(c => c.year)
  )];
});

// periods filter by year
const periods = computed(() => {
  if (!form.value.year) return [];
  return [...new Set(
    props.CarDetail.filter(c => c.year == form.value.year).map(c => c.production_period)
  )];
});

// --- cek apakah kombinasi form cocok dengan CarDetail di DB ---
watch(form, (val) => {
  const matched = props.CarDetail.find(c =>
    c.brand_id == val.brand_id &&
    c.car_model_id == val.car_model_id &&
    c.car_type_id == val.car_type_id &&
    c.year == val.year &&
    c.cc == val.cc &&
    c.transmission == val.transmission &&
    c.fuel_type == val.fuel_type &&
    c.production_period == val.production_period
  );
  form.value.car_id = matched ? matched.id : null;
  emit("update-vehicle", form.value);
}, { deep: true });

const submitForm = () => {
  emit("save-car-details", form.value);
};
</script>

<template>
  <div class="p-4 bg-white rounded-lg shadow">
    <h2 class="text-lg font-semibold mb-4">Detail Kendaraan</h2>
    <form @submit.prevent="submitForm">

      <!-- Plate Number -->
      <div class="mb-4">
        <label class="block text-sm font-medium">Plate Number</label>
        <input v-model="form.plate_number" class="mt-1 block w-full border rounded p-2" readonly />
      </div>

      <!-- Brand -->
      <div class="mb-4">
        <label class="block text-sm font-medium">Brand</label>
        <select v-model="form.brand_id" class="mt-1 block w-full border rounded p-2">
          <option value="">-- Pilih Brand --</option>
          <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
        </select>
      </div>

      <!-- Model -->
      <div class="mb-4" v-if="form.brand_id">
        <label class="block text-sm font-medium">Model</label>
        <select v-model="form.car_model_id" class="mt-1 block w-full border rounded p-2">
          <option value="">-- Pilih Model --</option>
          <option v-for="m in models" :key="m.id" :value="m.id">{{ m.name }}</option>
        </select>
      </div>

      <!-- Type -->
      <div class="mb-4" v-if="form.car_model_id">
        <label class="block text-sm font-medium">Type</label>
        <select v-model="form.car_type_id" class="mt-1 block w-full border rounded p-2">
          <option value="">-- Pilih Type --</option>
          <option v-for="t in types" :key="t.id" :value="t.id">{{ t.name }}</option>
        </select>
      </div>

      <!-- Year -->
      <div class="mb-4" v-if="form.car_type_id">
        <label class="block text-sm font-medium">Year</label>
        <select v-model="form.year" class="mt-1 block w-full border rounded p-2">
          <option value="">-- Pilih Tahun --</option>
          <option v-for="y in years" :key="y">{{ y }}</option>
        </select>
      </div>

      <!-- CC -->
      <div class="mb-4">
        <label class="block text-sm font-medium">CC</label>
        <input type="number" v-model="form.cc" class="mt-1 block w-full border rounded p-2" />
      </div>

      <!-- Transmission -->
      <div class="mb-4">
        <label class="block text-sm font-medium">Transmission</label>
        <select v-model="form.transmission" class="mt-1 block w-full border rounded p-2">
          <option value="AT">Automatic</option>
          <option value="MT">Manual</option>
          <option value="CVT">CVT</option>
        </select>
      </div>

      <!-- Fuel -->
      <div class="mb-4">
        <label class="block text-sm font-medium">Fuel Type</label>
        <input type="text" v-model="form.fuel_type" class="mt-1 block w-full border rounded p-2" />
      </div>

      <!-- Production Period -->
      <div class="mb-4" v-if="form.year">
        <label class="block text-sm font-medium">Production Period</label>
        <select v-model="form.production_period" class="mt-1 block w-full border rounded p-2">
          <option value="">-- Pilih Periode --</option>
          <option v-for="p in periods" :key="p">{{ p }}</option>
        </select>
      </div>

      <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded">
        Save Vehicle Details
      </button>
    </form>
  </div>
</template>
