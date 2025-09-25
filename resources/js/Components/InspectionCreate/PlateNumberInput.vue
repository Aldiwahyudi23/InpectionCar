<template>
  <!-- Form Input Plate Number -->
  <div class="mb-2">
    <label class="block text-sm font-medium text-gray-700 mb-2">
      Nomor Plat Kendaraan
    </label>

    <div class="flex items-center space-x-2">
      <!-- Kode Wilayah -->
      <input
        v-model="plateAreaCode"
        type="text"
        placeholder="D"
        class="w-1/4 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center transition"
        @input="handlePlateInput('area')"
        maxlength="2"
      />

      <!-- Nomor Angka -->
      <input
        v-model="plateNumber"
        type="tel"
        pattern="[0-9]*"
        placeholder="1234"
        class="w-2/4 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center transition"
        @input="handlePlateInput('number')"
        :disabled="isAreaInvalid"
        maxlength="4"
      />

      <!-- Huruf Akhir -->
      <input
        v-model="plateSuffix"
        type="text"
        placeholder="ABC"
        class="w-1/4 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center transition"
        @input="handlePlateInput('suffix')"
        :disabled="isAreaInvalid"
        maxlength="3"
      />
    </div>

    <!-- Pesan Validasi Area -->
    <div v-if="ValidationMessageArea" class="mt-2 text-sm"
         :class="isAreaInvalid ? 'text-red-600' : 'text-green-600'">
      {{ ValidationMessageArea }}
    </div>

    <!-- Pesan Validasi Plat Nomor -->
    <div v-if="inspectionValidationMessage" class="mt-2 text-sm text-red-600">
      {{ inspectionValidationMessage }}
    </div>

    <!-- Pesan Riwayat Inspeksi -->
    <div v-if="inspectionCountMessage" class="mt-2 text-sm text-green-600">
      {{ inspectionCountMessage }}
    </div>

    <!-- Pesan wajib isi -->
    <div v-if="!isPlateValid" class="mt-2 text-sm text-yellow-600">
      ⚠ Kolom kode wilayah dan nomor plat harus diisi
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
  plateNumber: String,
  inspections: Array
});

const emit = defineEmits([
  'update:plateNumber',
  'plateValidityUpdate',
  'carDataUpdate'
]);

// State input
const plateAreaCode = ref('');
const plateNumber = ref('');
const plateSuffix = ref('');

// State validasi & pesan
const inspectionValidationMessage = ref('');
const ValidationMessageArea = ref('');
const inspectionCountMessage = ref('');
const isAreaInvalid = ref(true);

// 🚘 Daftar Kode Plat Nomor Kendaraan di Indonesia
const plateCodes = {
  // 1. Sumatera
  "BL": "Aceh",
  "BB": "Tapanuli (Sumatera Utara)",
  "BK": "Medan, Deli Serdang, Binjai (Sumatera Utara)",
  "BA": "Sumatera Barat",
  "BM": "Riau",
  "BH": "Jambi",
  "BD": "Bengkulu",
  "BG": "Sumatera Selatan",
  "BN": "Bangka Belitung",
  "BE": "Lampung",

  // 2. DKI Jakarta, Banten, Jawa Barat
  "A": "Banten (Serang, Pandeglang, Cilegon, Lebak)",
  "B": "Jakarta, Depok, Tangerang, Bekasi (Jadetabek)",
  "D": "Bandung, Cimahi",
  "E": "Cirebon, Indramayu, Majalengka, Kuningan",
  "F": "Bogor, Sukabumi, Cianjur",
  "T": "Karawang, Purwakarta, Subang, Bekasi Kabupaten, sebagian Cikampek",
  "Z": "Garut, Tasikmalaya, Ciamis, Banjar, Sumedang",

  // 3. Jawa Tengah & DI Yogyakarta
  "G": "Pekalongan, Tegal, Brebes, Pemalang, Batang",
  "H": "Semarang, Kendal, Salatiga, Demak",
  "K": "Pati, Kudus, Rembang, Blora, Jepara",
  "R": "Banyumas, Cilacap, Purbalingga, Banjarnegara",
  "AA": "Magelang, Temanggung, Purworejo, Wonosobo",
  "AB": "DI Yogyakarta (Sleman, Bantul, Gunungkidul, Kulonprogo, Kota Jogja)",
  "AD": "Surakarta (Solo), Sragen, Boyolali, Klaten, Wonogiri, Sukoharjo, Karanganyar",

  // 4. Jawa Timur
  "L": "Surabaya",
  "M": "Madura (Bangkalan, Sampang, Pamekasan, Sumenep)",
  "N": "Malang, Pasuruan, Probolinggo, Lumajang, Batu",
  "P": "Jember, Banyuwangi, Bondowoso, Situbondo",
  "S": "Bojonegoro, Lamongan, Tuban, Mojokerto, Jombang",
  "W": "Sidoarjo, Gresik",
  "AE": "Madiun, Magetan, Ngawi, Ponorogo, Pacitan",
  "AG": "Kediri, Blitar, Tulungagung, Trenggalek",

  // 5. Bali & Nusa Tenggara
  "DK": "Bali",
  "DR": "Lombok (NTB)",
  "EA": "Sumbawa, Dompu, Bima (NTB)",
  "DH": "Nusa Tenggara Timur (Kupang, Timor)",
  "EB": "Flores, Lembata",
  "ED": "Sumba",

  // 6. Kalimantan
  "DA": "Kalimantan Selatan (Banjarmasin, Banjarbaru, dll.)",
  "KB": "Kalimantan Barat (Pontianak, Singkawang, dll.)",
  "KH": "Kalimantan Tengah (Palangkaraya, Sampit, dll.)",
  "KT": "Kalimantan Timur (Balikpapan, Samarinda, Bontang, dll.)",
  "KU": "Kalimantan Utara (Tarakan, Nunukan, Malinau, Tana Tidung, Bulungan)",

  // 7. Sulawesi
  "DB": "Sulawesi Utara (Manado, Bitung, Minahasa, dll.)",
  "DL": "Sangihe, Talaud (Sulut)",
  "DM": "Gorontalo",
  "DN": "Sulawesi Tengah (Palu, Poso, Banggai, dll.)",
  "DT": "Sulawesi Tenggara (Kendari, Bau-bau, Kolaka, dll.)",
  "DD": "Sulawesi Selatan (Makassar, Gowa, Bone, dll.)",
  "DC": "Polewali Mandar, Majene, Mamasa (Sulbar)",
  "DW": "Mamuju, Pasangkayu, Mamuju Tengah (Sulbar)",

  // 8. Maluku & Papua
  "DE": "Maluku (Ambon, Maluku Tengah, dll.)",
  "DG": "Maluku Utara (Ternate, Halmahera, dll.)",
  "PA": "Papua (Jayapura, Merauke, dll.)",
  "PB": "Papua Barat (Manokwari, Sorong, Fakfak, dll.)",
};


// Gabungkan plate untuk emit
const combinePlateNumber = () => {
  const combined = `${plateAreaCode.value}${plateNumber.value}${plateSuffix.value}`.toUpperCase();
  emit('update:plateNumber', combined);
};

// Input handler
const handlePlateInput = (type) => {
  if (type === 'area') {
    plateAreaCode.value = plateAreaCode.value.toUpperCase().replace(/[^A-Z]/g, '');
  } else if (type === 'number') {
    plateNumber.value = plateNumber.value.replace(/[^0-9]/g, '');
  } else if (type === 'suffix') {
    plateSuffix.value = plateSuffix.value.toUpperCase().replace(/[^A-Z]/g, '');
  }
  combinePlateNumber();
  updateValidationMessage();
};

// Validasi area
const updateValidationMessage = () => {
  if (plateAreaCode.value && plateCodes[plateAreaCode.value]) {
    ValidationMessageArea.value = `✅ ${plateAreaCode.value} = ${plateCodes[plateAreaCode.value]}`;
    isAreaInvalid.value = false;
  } else if (plateAreaCode.value) {
    ValidationMessageArea.value = `🚫 Kode wilayah "${plateAreaCode.value}" tidak valid`;
    isAreaInvalid.value = true;
  } else {
    ValidationMessageArea.value = '⚠ Masukkan kode wilayah (contoh: D, B, DK)';
    isAreaInvalid.value = true;
  }
};

// Cek validasi minimal area & number terisi
const isPlateValid = computed(() => {
  return (
    plateAreaCode.value.trim().length >= 1 &&
    plateNumber.value.trim().length >= 1 &&
    !isAreaInvalid.value
  );
});

// Emit setiap kali isPlateValid berubah
watch(isPlateValid, (newVal) => {
  emit('plateValidityUpdate', newVal);
});

// Watch setiap kali plate number berubah → reset pesan
watch([plateAreaCode, plateNumber, plateSuffix], () => {
  inspectionValidationMessage.value = '';
  inspectionCountMessage.value = '';
});

// Watch props plateNumber → cek existing inspection
watch(() => props.plateNumber, (newPlateNumber) => {
  if (newPlateNumber.length >= 3) {
    validatePlateNumber(newPlateNumber);
  }
});

// Validasi apakah plat sudah pernah diperiksa
const validatePlateNumber = (plateNumber) => {
  const existing = props.inspections.filter(i => i.plate_number === plateNumber);

  if (existing.length > 0) {
    const blockingStatuses = ['draft', 'in_progress', 'pending', 'pending_review', 'revision'];
    const blockingInspection = existing.find(i => blockingStatuses.includes(i.status));

    if (blockingInspection) {
      inspectionValidationMessage.value =
        `Nomor plat ini sedang dalam proses inspeksi dengan status: ${blockingInspection.status.replace(/_/g, ' ').toUpperCase()}. Selesaikan inspeksi tersebut terlebih dahulu.`;
      emit('plateValidityUpdate', false);
      return;
    }

    const completedInspections = existing.filter(i =>
      ['approved', 'rejected', 'completed', 'cancelled'].includes(i.status)
    );

    if (completedInspections.length > 0) {
      completedInspections.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      const latest = completedInspections[0];

      emit('carDataUpdate', {
        id: latest.car_id,
        name: latest.car_name
      });

      inspectionCountMessage.value =
        `Nomor plat ini sudah pernah diperiksa ${completedInspections.length} kali sebelumnya.`;
    }
  }
};
</script>
