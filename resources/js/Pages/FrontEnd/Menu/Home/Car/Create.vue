
<template>
  <AppLayout title="Tambah Mobil">
    <Head title="Tambah Data Mobil Baru" />
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 relative">
      <div class="space-y-6 bg-white rounded-xl shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl md:text-3xl font-bold text-gray-900 text-center flex-grow">
            Tambah Data Mobil Baru
          </h3>
        </div>

        <div v-if="successMessage" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
          <p class="font-bold">Berhasil!</p>
          <p>{{ successMessage }}</p>
        </div>

        <form @submit.prevent="submitForm" novalidate>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
              <div class="relative">
                <label for="brand" class="block text-sm font-medium text-gray-700">Brand <span class="text-red-500">*</span></label>
                <div class="flex items-end space-x-2">
                  <div class="flex-1">
                    <input
                      id="brand"
                      type="text"
                      v-model="brandSearchQuery"
                      @input="filterBrands"
                      @focus="showBrandSuggestions = true"
                      @blur="handleBlur('brand')"
                      placeholder="Cari atau pilih Brand"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition duration-200 py-2 px-2"
                    />
                    <div v-if="showBrandSuggestions && filteredBrands.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-40 overflow-y-auto">
                      <div
                        v-for="brand in filteredBrands"
                        :key="brand.id"
                        class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
                        @mousedown.prevent="selectBrand(brand)"
                      >
                        {{ brand.name }}
                      </div>
                    </div>
                  </div>
                  <button
                    type="button"
                    class="h-10 w-10 flex items-center justify-center text-white bg-indigo-600 rounded-md shadow-md hover:bg-indigo-700 transition duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                    @click="showAddBrandModal = true"
                    title="Tambah Brand"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                  </button>
                </div>
                <p v-if="errors.brand_id" class="mt-1 text-sm text-red-600">{{ errors.brand_id }}</p>
              </div>

              <div v-if="form.brand_id" class="relative">
                <label for="model" class="block text-sm font-medium text-gray-700">Model <span class="text-red-500">*</span></label>
                <div class="flex items-end space-x-2">
                  <div class="flex-1">
                    <input
                      id="model"
                      type="text"
                      v-model="modelSearchQuery"
                      @input="filterModels"
                      @focus="showModelSuggestions = true"
                      @blur="handleBlur('model')"
                      placeholder="Cari atau pilih Model"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition duration-200 py-2 px-2"
                    />
                    <div v-if="showModelSuggestions && filteredModels.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-40 overflow-y-auto">
                      <div
                        v-for="model in filteredModels"
                        :key="model.id"
                        class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
                        @mousedown.prevent="selectModel(model)"
                      >
                        {{ model.name }}
                      </div>
                    </div>
                  </div>
                  <button
                    type="button"
                    class="h-10 w-10 flex items-center justify-center text-white bg-indigo-600 rounded-md shadow-md hover:bg-indigo-700 transition duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                    @click="showAddModelModal = true"
                    title="Tambah Model"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                  </button>
                </div>
                <p v-if="errors.car_model_id" class="mt-1 text-sm text-red-600">{{ errors.car_model_id }}</p>
              </div>

              <div v-if="form.car_model_id" class="relative">
                <label for="type" class="block text-sm font-medium text-gray-700">Type <span class="text-red-500">*</span></label>
                <div class="flex items-end space-x-2">
                  <div class="flex-1">
                    <input
                      id="type"
                      type="text"
                      v-model="typeSearchQuery"
                      @input="filterTypes"
                      @focus="showTypeSuggestions = true"
                      @blur="handleBlur('type')"
                      placeholder="Cari atau pilih Type"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition duration-200 py-2 px-2"
                    />
                    <div v-if="showTypeSuggestions && filteredTypes.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-40 overflow-y-auto">
                      <div
                        v-for="type in filteredTypes"
                        :key="type.id"
                        class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
                        @mousedown.prevent="selectType(type)"
                      >
                        {{ type.name }}
                      </div>
                    </div>
                  </div>
                  <button
                    type="button"
                    class="h-10 w-10 flex items-center justify-center text-white bg-indigo-600 rounded-md shadow-md hover:bg-indigo-700 transition duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                    @click="showAddTypeModal = true"
                    title="Tambah Type"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                  </button>
                </div>
                <p v-if="errors.car_type_id" class="mt-1 text-sm text-red-600">{{ errors.car_type_id }}</p>
              </div>
            </div>

            <div class="space-y-4">
              <div>
                <label for="year" class="block text-sm font-medium text-gray-700">Year <span class="text-red-500">*</span></label>
                <input
                  id="year"
                  type="number"
                  v-model.number="form.year"
                  min="1900"
                  :max="maxYear"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition duration-200 py-2 px-2"
                  required
                />
                <p v-if="errors.year" class="mt-1 text-sm text-red-600">{{ errors.year }}</p>
              </div>

              <div>
                <label for="cc" class="block text-sm font-medium text-gray-700">CC <span class="text-red-500">*</span></label>
                <input
                  id="cc"
                  type="number"
                  v-model.number="form.cc"
                  min="0"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition duration-200 py-2 px-2"
                />
                <p v-if="errors.cc" class="mt-1 text-sm text-red-600">{{ errors.cc }}</p>
              </div>
              
              <div>
                <label for="transmission" class="block text-sm font-medium text-gray-700">Transmission <span class="text-red-500">*</span></label>
                <div class="mt-1 grid grid-cols-2 gap-2">
                  <label
                    v-for="option in transmissionOptions"
                    :key="option.value"
                    class="relative block cursor-pointer"
                  >
                    <input
                      type="radio"
                      v-model="form.transmission"
                      :value="option.value"
                      class="sr-only"
                      required
                    />
                    <div
                      class="w-full px-2 py-2 border rounded-lg text-center transition-colors whitespace-nowrap text-sm font-medium"
                      :class="{
                        'border-indigo-500 bg-indigo-50 text-indigo-700': form.transmission === option.value,
                        'border-gray-300 text-gray-700 hover:bg-gray-50': form.transmission !== option.value
                      }"
                    >
                      {{ option.label }}
                    </div>
                  </label>
                </div>
                <p v-if="errors.transmission" class="mt-1 text-sm text-red-600">{{ errors.transmission }}</p>
              </div>

              <div>
                <label for="fuel_type" class="block text-sm font-medium text-gray-700">Fuel Type <span class="text-red-500">*</span></label>
                <select
                  id="fuel_type"
                  v-model="form.fuel_type"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition duration-200 py-2 px-2"
                  required
                >
                  <option value="" disabled>Pilih Jenis Bahan Bakar</option>
                  <option v-for="fuel in fuelOptions" :key="fuel.value" :value="fuel.value">{{ fuel.label }}</option>
                </select>
                <p v-if="errors.fuel_type" class="mt-1 text-sm text-red-600">{{ errors.fuel_type }}</p>
              </div>
              
              <div>
                <label for="production_period" class="block text-sm font-medium text-gray-700">Production Period <span class="text-red-500">*</span></label>
                <input
                  id="production_period"
                  type="text"
                  v-model="form.production_period"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition duration-200 py-2 px-2"
                  required
                />
                <p v-if="errors.production_period" class="mt-1 text-sm text-red-600">{{ errors.production_period }}</p>
              </div>
            </div>
          </div>

          <div class="mt-6">
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea
              id="description"
              v-model="form.description"
              rows="4"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition duration-200 py-2 px-2"
            ></textarea>
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
          </div>

          <div v-if="errors.duplicate" class="mt-6 p-3 bg-red-50 border border-red-200 rounded-md text-red-600 font-medium">
            <span class="font-bold">Peringatan:</span> {{ errors.duplicate }}
          </div>

          <div class="mt-8 flex justify-end space-x-4">
            <button
              type="button"
              class="inline-flex justify-center py-2 px-6 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200"
              @click="cancelForm"
            >
              Kembali
            </button>
            <button
              type="submit"
              class="inline-flex justify-center py-2 px-12 border border-transparent shadow-sm text-base font-medium rounded-md bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="submitDisabled"
            >
              <span v-if="form.processing" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Menyimpan...
              </span>
              <span v-else>Simpan</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>

  <div v-if="showAddBrandModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div class="relative top-0 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="flex items-center mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900">Tambah Brand Baru</h3>
      </div>
      <div class="mt-2">
        <label for="newBrandName" class="block text-sm font-medium text-gray-700 mb-1">Nama Brand</label>
        <input
          id="newBrandName"
          type="text"
          v-model="newBrandName"
          placeholder="Masukkan nama brand"
          class="border rounded-md px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
          @input="checkBrandDuplicate"
        />
        <p v-if="brandDuplicateError" class="mt-1 text-sm text-red-600">{{ brandDuplicateError }}</p>
      </div>
      <div class="flex justify-end mt-4 space-x-2">
        <button
          class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white disabled:opacity-50 disabled:cursor-not-allowed transition duration-200"
          :disabled="addBrandLoading || brandDuplicateError || !newBrandName"
          @click="addBrand"
        >
          <span v-if="addBrandLoading" class="flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Menyimpan...
          </span>
          <span v-else>Simpan</span>
        </button>
        <button class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition duration-200" @click="showAddBrandModal = false">
          Batal
        </button>
      </div>
    </div>
  </div>

  <div v-if="showAddModelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div class="relative top-0 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="flex items-center mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900">Tambah Model Baru</h3>
      </div>
      <div class="mt-2">
        <label for="newModelName" class="block text-sm font-medium text-gray-700 mb-1">Nama Model</label>
        <input
          id="newModelName"
          type="text"
          v-model="newModelName"
          placeholder="Masukkan nama model"
          class="border rounded-md px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
          @input="checkModelDuplicate"
        />
        <p v-if="modelDuplicateError" class="mt-1 text-sm text-red-600">{{ modelDuplicateError }}</p>
      </div>
      <div class="flex justify-end mt-4 space-x-2">
        <button
          class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition duration-200"
          :disabled="addModelLoading || modelDuplicateError || !newModelName || !form.brand_id"
          @click="addModel"
        >
          <span v-if="addModelLoading" class="flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Menyimpan...
          </span>
          <span v-else>Simpan</span>
        </button>
        <button class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition duration-200" @click="showAddModelModal = false">
          Batal
        </button>
      </div>
    </div>
  </div>

  <div v-if="showAddTypeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div class="relative top-0 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="flex items-center mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900">Tambah Type Baru</h3>
      </div>
      <div class="mt-2">
        <label for="newTypeName" class="block text-sm font-medium text-gray-700 mb-1">Nama Type</label>
        <input
          id="newTypeName"
          type="text"
          v-model="newTypeName"
          placeholder="Masukkan nama type"
          class="border rounded-md px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
          @input="checkTypeDuplicate"
        />
        <p v-if="typeDuplicateError" class="mt-1 text-sm text-red-600">{{ typeDuplicateError }}</p>
      </div>
      <div class="flex justify-end mt-4 space-x-2">
        <button
          class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white disabled:opacity-50 disabled:cursor-not-allowed transition duration-200"
          :disabled="addTypeLoading || typeDuplicateError || !newTypeName || !form.car_model_id"
          @click="addType"
        >
          <span v-if="addTypeLoading" class="flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Menyimpan...
          </span>
          <span v-else>Simpan</span>
        </button>
        <button class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition duration-200" @click="showAddTypeModal = false">
          Batal
        </button>
      </div>
    </div>
  </div>

  <div v-if="showMessageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div class="relative top-0 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="flex items-center mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900">Notifikasi</h3>
      </div>
      <div class="mt-2">
        <p class="text-gray-800 text-center py-4">{{ message }}</p>
      </div>
      <div class="flex justify-end mt-4">
        <button
          class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white transition duration-200"
          @click="closeMessageModal"
        >
          OK
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, computed, onMounted } from 'vue';
import axios from 'axios';
import { useForm, router, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// Data
const brands = ref([]);
const models = ref([]);
const types = ref([]);
const maxYear = new Date().getFullYear() + 1;

const form = useForm({
  brand_id: '',
  car_model_id: '',
  car_type_id: '',
  year: '',
  cc: '',
  transmission: '',
  fuel_type: '',
  production_period: '',
  description: ''
});

const errors = reactive({
  brand_id: '',
  car_model_id: '',
  car_type_id: '',
  year: '',
  cc: '',
  transmission: '',
  fuel_type: '',
  production_period: '',
  description: '',
  duplicate: ''
});

// Autocomplete state
const brandSearchQuery = ref('');
const modelSearchQuery = ref('');
const typeSearchQuery = ref('');

const showBrandSuggestions = ref(false);
const showModelSuggestions = ref(false);
const showTypeSuggestions = ref(false);

// New state for success message
const successMessage = ref('');

const filteredBrands = computed(() => {
  if (!brandSearchQuery.value) {
    return brands.value;
  }
  return brands.value.filter(brand =>
    brand.name.toLowerCase().includes(brandSearchQuery.value.toLowerCase())
  );
});

const filteredModels = computed(() => {
  if (!modelSearchQuery.value) {
    return models.value;
  }
  return models.value.filter(model =>
    model.name.toLowerCase().includes(modelSearchQuery.value.toLowerCase())
  );
});

const filteredTypes = computed(() => {
  if (!typeSearchQuery.value) {
    return types.value;
  }
  return types.value.filter(type =>
    type.name.toLowerCase().includes(typeSearchQuery.value.toLowerCase())
  );
});

const transmissionOptions = ref([
  { label: 'AT', value: 'AT' },
  { label: 'MT', value: 'MT' }
]);

const fuelOptions = ref([
  { label: 'Bensin', value: 'Bensin' },
  { label: 'Diesel', value: 'Diesel' },
  { label: 'Listrik', value: 'Listrik' },
  { label: 'Hybrid', value: 'Hybrid' },
  { label: 'Gas', value: 'Gas' }
]);

const showAddBrandModal = ref(false);
const showAddModelModal = ref(false);
const showAddTypeModal = ref(false);
const showMessageModal = ref(false);

const newBrandName = ref('');
const newModelName = ref('');
const newTypeName = ref('');

const addBrandLoading = ref(false);
const addModelLoading = ref(false);
const addTypeLoading = ref(false);

const brandDuplicateError = ref('');
const modelDuplicateError = ref('');
const typeDuplicateError = ref('');

const message = ref('');

const submitDisabled = computed(() => {
  const isFormIncomplete = !form.brand_id || !form.car_model_id || !form.car_type_id ||
                            !form.year || !form.transmission || !form.fuel_type ||
                            !form.production_period;
  return isFormIncomplete || !!errors.duplicate || form.processing;
});

// Methods
const fetchBrands = async () => {
  try {
    const response = await axios.get('/api/brands');
    brands.value = response.data;
  } catch (error) {
    console.error('Error fetching brands:', error);
    showMessage('Error fetching brands');
  }
};

const fetchModels = async (brandId) => {
  try {
    const response = await axios.get(`/api/models?brand_id=${brandId}`);
    models.value = response.data;
  } catch (error) {
    console.error('Error fetching models:', error);
    showMessage('Error fetching models');
  }
};

const fetchTypes = async (modelId) => {
  try {
    const response = await axios.get(`/api/types?car_model_id=${modelId}`);
    types.value = response.data;
  } catch (error) {
    console.error('Error fetching types:', error);
    showMessage('Error fetching types');
  }
};

const selectBrand = (brand) => {
  form.brand_id = brand.id;
  brandSearchQuery.value = brand.name;
  showBrandSuggestions.value = false;
  onBrandChange();
};

const selectModel = (model) => {
  form.car_model_id = model.id;
  modelSearchQuery.value = model.name;
  showModelSuggestions.value = false;
  onModelChange();
};

const selectType = (type) => {
  form.car_type_id = type.id;
  typeSearchQuery.value = type.name;
  showTypeSuggestions.value = false;
};

const handleBlur = (field) => {
  setTimeout(() => {
    if (field === 'brand') {
      showBrandSuggestions.value = false;
      if (!form.brand_id || brands.value.find(b => b.id === form.brand_id)?.name !== brandSearchQuery.value) {
        form.brand_id = '';
        brandSearchQuery.value = '';
        models.value = [];
        types.value = [];
        form.car_model_id = '';
        form.car_type_id = '';
      }
    } else if (field === 'model') {
      showModelSuggestions.value = false;
      if (!form.car_model_id || models.value.find(m => m.id === form.car_model_id)?.name !== modelSearchQuery.value) {
        form.car_model_id = '';
        modelSearchQuery.value = '';
        types.value = [];
        form.car_type_id = '';
      }
    } else if (field === 'type') {
      showTypeSuggestions.value = false;
      if (!form.car_type_id || types.value.find(t => t.id === form.car_type_id)?.name !== typeSearchQuery.value) {
        form.car_type_id = '';
        typeSearchQuery.value = '';
      }
    }
  }, 200);
};

const onBrandChange = () => {
  form.car_model_id = '';
  form.car_type_id = '';
  modelSearchQuery.value = '';
  typeSearchQuery.value = '';
  models.value = [];
  types.value = [];
  if (form.brand_id) {
    fetchModels(form.brand_id);
  }
};

const onModelChange = () => {
  form.car_type_id = '';
  typeSearchQuery.value = '';
  types.value = [];
  if (form.car_model_id) {
    fetchTypes(form.car_model_id);
  }
};

const checkDuplicateCar = async () => {
  if (!form.brand_id || !form.car_model_id || !form.car_type_id ||
      !form.year || !form.transmission || !form.fuel_type ||
      !form.production_period) {
    errors.duplicate = '';
    return;
  }
  
  try {
    const response = await axios.post('/api/car-details/check-duplicate', {
      brand_id: form.brand_id,
      car_model_id: form.car_model_id,
      car_type_id: form.car_type_id,
      year: form.year,
      transmission: form.transmission,
      fuel_type: form.fuel_type,
      production_period: form.production_period
    });
    
    if (response.data.exists) {
      errors.duplicate = 'Mobil dengan spesifikasi ini sudah ada di database.';
    } else {
      errors.duplicate = '';
    }
  } catch (error) {
    console.error('Error checking duplicate:', error);
    errors.duplicate = '';
  }
};

const checkBrandDuplicate = async () => {
  if (!newBrandName.value.trim()) {
    brandDuplicateError.value = '';
    return;
  }
  
  try {
    const response = await axios.post('/api/brands/check-duplicate', {
      name: newBrandName.value.trim()
    });
    
    if (response.data.exists) {
      brandDuplicateError.value = 'Brand dengan nama ini sudah ada.';
    } else {
      brandDuplicateError.value = '';
    }
  } catch (error) {
    console.error('Error checking brand duplicate:', error);
    brandDuplicateError.value = '';
  }
};

const checkModelDuplicate = async () => {
  if (!newModelName.value.trim() || !form.brand_id) {
    modelDuplicateError.value = '';
    return;
  }
  
  try {
    const response = await axios.post('/api/models/check-duplicate', {
      name: newModelName.value.trim(),
      brand_id: form.brand_id
    });
    
    if (response.data.exists) {
      modelDuplicateError.value = 'Model dengan nama ini sudah ada untuk brand yang dipilih.';
    } else {
      modelDuplicateError.value = '';
    }
  } catch (error) {
    console.error('Error checking model duplicate:', error);
    modelDuplicateError.value = '';
  }
};

const checkTypeDuplicate = async () => {
  if (!newTypeName.value.trim() || !form.car_model_id) {
    typeDuplicateError.value = '';
    return;
  }
  
  try {
    const response = await axios.post('/api/types/check-duplicate', {
      name: newTypeName.value.trim(),
      car_model_id: form.car_model_id
    });
    
    if (response.data.exists) {
      typeDuplicateError.value = 'Type dengan nama ini sudah ada untuk model yang dipilih.';
    } else {
      typeDuplicateError.value = '';
    }
  } catch (error) {
    console.error('Error checking type duplicate:', error);
    typeDuplicateError.value = '';
  }
};

const addBrand = async () => {
  if (!newBrandName.value.trim() || brandDuplicateError.value) {
    return;
  }
  addBrandLoading.value = true;
  try {
    const response = await axios.post('/api/brands', {
      name: newBrandName.value.trim()
    });
    
    if (response.data.success) {
      brands.value.push(response.data.data);
      selectBrand(response.data.data);
      showAddBrandModal.value = false;
      newBrandName.value = '';
      brandDuplicateError.value = '';
      showMessage('Brand berhasil ditambahkan');
    }
  } catch (error) {
    console.error('Error adding brand:', error);
    if (error.response?.data?.messages) {
      showMessage(Object.values(error.response.data.messages).join(', '));
    } else {
      showMessage('Error adding brand');
    }
  } finally {
    addBrandLoading.value = false;
  }
};

const addModel = async () => {
  if (!newModelName.value.trim() || modelDuplicateError.value || !form.brand_id) {
    return;
  }
  addModelLoading.value = true;
  try {
    const response = await axios.post('/api/models', {
      name: newModelName.value.trim(),
      brand_id: form.brand_id
    });
    
    if (response.data.success) {
      models.value.push(response.data.data);
      selectModel(response.data.data);
      showAddModelModal.value = false;
      newModelName.value = '';
      modelDuplicateError.value = '';
      showMessage('Model berhasil ditambahkan');
    }
  } catch (error) {
    console.error('Error adding model:', error);
    if (error.response?.data?.messages) {
      showMessage(Object.values(error.response.data.messages).join(', '));
    } else {
      showMessage('Error adding model');
    }
  } finally {
    addModelLoading.value = false;
  }
};

const addType = async () => {
  if (!newTypeName.value.trim() || typeDuplicateError.value || !form.car_model_id) {
    return;
  }
  addTypeLoading.value = true;
  try {
    const response = await axios.post('/api/types', {
      name: newTypeName.value.trim(),
      car_model_id: form.car_model_id
    });
    
    if (response.data.success) {
      types.value.push(response.data.data);
      selectType(response.data.data);
      showAddTypeModal.value = false;
      newTypeName.value = '';
      typeDuplicateError.value = '';
      showMessage('Type berhasil ditambahkan');
    }
  } catch (error) {
    console.error('Error adding type:', error);
    if (error.response?.data?.messages) {
      showMessage(Object.values(error.response.data.messages).join(', '));
    } else {
      showMessage('Error adding type');
    }
  } finally {
    addTypeLoading.value = false;
  }
};

const submitForm = () => {
  form.post(route('car-details.store'), {
    onSuccess: () => {
      // Set the success message to be displayed at the top
      successMessage.value = 'Data mobil berhasil disimpan';
      
      // Clear the form after success
      form.reset();
      brandSearchQuery.value = '';
      modelSearchQuery.value = '';
      typeSearchQuery.value = '';
      errors.duplicate = '';

      // Clear the message after a few seconds
      setTimeout(() => {
        successMessage.value = '';
      }, 5000); // Message will disappear after 5 seconds
    },
    onError: (e) => {
      // Clear previous errors and success message
      Object.keys(errors).forEach(key => errors[key] = '');
      successMessage.value = '';

      if (e) {
        Object.keys(e).forEach(key => {
          if (errors.hasOwnProperty(key)) {
            errors[key] = e[key];
          }
        });
      }
    }
  });
};

const cancelForm = () => {
  router.visit(route('cars'));
};

const showMessage = (msg) => {
  message.value = msg;
  showMessageModal.value = true;
};

const closeMessageModal = () => {
  showMessageModal.value = false;
};

// Watchers
watch(() => [form.brand_id, form.car_model_id, form.car_type_id, form.year, form.transmission, form.fuel_type, form.production_period], () => {
  checkDuplicateCar();
}, { deep: true });

onMounted(() => {
  fetchBrands();
});
</script>
```