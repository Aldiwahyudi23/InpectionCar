<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router, useForm, usePage  } from '@inertiajs/vue3';
import { CalendarDaysIcon, ClipboardDocumentListIcon } from '@heroicons/vue/24/outline';
import { CarIcon } from 'lucide-vue-next';
import { ref, computed ,watch} from 'vue';

// props
const props = defineProps({
  inspection: {
    type: Object,
    required: true
  },
  encryptedIds: Object,
  transaction: Object
});

const page = usePage();

// Ambil roles user dari global
const roles = page.props.global?.roles || [];

// Cek apakah user adalah admin atau coordinator
const isAdminOrCoordinator = computed(() => {
    return roles.includes('Admin') || roles.includes('coordinator');
});
const isCoordinator = computed(() => {
    return roles.includes('coordinator');
});
const isAdmin = computed(() => {
    return roles.includes('Admin');
});

// state untuk modal dan loading
const showRevisionModal = ref(false);
const showEmailModal = ref(false);
const showCustomerModal = ref(false);
const emailAddress = ref('');
const isLoading = ref(false);
const currentAction = ref(null);

// Form terpadu untuk customer dan transaksi
const unifiedForm = useForm({
  // Data customer
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  customer_address: '',
  
  // Data transaksi
  amount: '',
  payment_method: '',
  status: 'paid'
});

const formatCc = (cc) => {
  return (cc / 1000).toFixed(1) + "L";
}

// Cek apakah inspection sudah memiliki customer
const hasCustomer = computed(() => props.inspection.customer_id !== null);

// Cek apakah sudah ada transaksi
const hasTransaction = computed(() => props.transaction !== null);

// Cek status transaksi
const isPaid = computed(() => props.transaction?.status === 'paid');
const isPending = computed(() => props.transaction?.status === 'pending');
const isRejected = computed(() => props.inspection?.status === 'rejected');
const isCompleted = computed(() => props.inspection?.status === 'completed');
const isCcancelled = computed(() => props.inspection?.status === 'cancelled');
const Pending = computed(() => props.inspection?.status === 'pending');

const isStatus = computed(() => 
  isRejected.value || isCcancelled.value || Pending.value
);



// Cek apakah amount sudah diisi
const hasAmount = computed(() => props.transaction?.amount > 0);

// Cek apakah tombol PDF bisa ditampilkan
const canShowPdf = computed(() => {
  return hasTransaction.value && isPaid.value && hasAmount.value;
});

// Cek apakah tombol submit harus disembunyikan
const hasAllData = computed(() => hasCustomer.value && hasTransaction.value && isPaid.value);


// Mapping status ke label bahasa Indonesia
const statusLabel = (status) => {
  switch (status) {
    case 'draft':
      return 'Dibuat';
    case 'in_progress':
      return 'Dalam Proses';
    case 'pending':
      return 'Menunggu';
    case 'pending_review':
      return 'Menunggu Review';
    case 'approved':
      return 'Disetujui';
    case 'rejected':
      return 'Ditolak';
    case 'revision':
      return 'Revisi';
    case 'completed':
      return 'Selesai';
    case 'cancelled':
      return 'Dibatalkan';
    default:
      return status;
  }
};

// Menerjemahkan status
const statusTranslations = {
    'pending': 'Menunggu Pembayaran',
    'paid': 'Sudah di bayar',
    'failed': 'Gagal',
    'refunded': 'Dikembalikan',
    'expired': 'Kadaluarsa'
};



// Computed property untuk teks tombol submit
const submitButtonText = computed(() => {
  if (unifiedForm.processing) {
    return 'Menyimpan...';
  }
  if (!hasCustomer.value && !hasTransaction.value) {
    return 'Tambah Data';
  }
  if (hasCustomer.value && !hasTransaction.value) {
    return 'Tambah Pembayaran';
  }
  if (!hasCustomer.value && hasTransaction.value) {
    return 'Tambahkan Data Customer';
  }
  return 'Perbaharui Data'; // Default case
});


// Format currency
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount);
};

// ==========================================================
// Logika untuk input nominal otomatis format mata uang

const formattedAmount = ref('');

// Fungsi untuk memformat angka menjadi format mata uang di input
const formatAmountForInput = (value) => {
  if (!value) return '';
  const cleanNumber = String(value).replace(/\D/g, '');
  const parsedValue = parseInt(cleanNumber, 10);
  if (isNaN(parsedValue)) return '';
  
  const formatter = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  });

  return formatter.format(parsedValue);
};

// Update unifiedForm.amount saat input berubah
const handleAmountInput = (event) => {
  const cleanValue = event.target.value.replace(/\D/g, '');
  unifiedForm.amount = cleanValue;
  formattedAmount.value = formatAmountForInput(cleanValue);
};

// Watcher untuk sinkronisasi amount
watch(() => unifiedForm.amount, (newAmount) => {
    if (newAmount || newAmount === 0) {
        // Hapus karakter non-digit dari input untuk mencegah error
        const cleanNumber = String(newAmount).replace(/\D/g, '');
        // Format ulang nominal saat unifiedForm.amount berubah
        formattedAmount.value = formatAmountForInput(cleanNumber);
    } else {
        formattedAmount.value = '';
    }
}, { immediate: true });


// Fungsi untuk membuka modal customer
const openCustomerModal = () => {
  // Isi form dengan data yang sudah ada jika ada
  if (props.inspection.customer) {
    unifiedForm.customer_name = props.inspection.customer.name;
    unifiedForm.customer_phone = props.inspection.customer.phone;
    unifiedForm.customer_email = props.inspection.customer.email;
    unifiedForm.customer_address = props.inspection.customer.address;
  }
  
if (props.transaction) {
    // Simpan angka murni ke form
    unifiedForm.amount = Number(props.transaction.amount); // 300000
    // Format untuk tampilkan di input
    formattedAmount.value = formatAmountForInput(unifiedForm.amount); // "Rp300.000"
    unifiedForm.payment_method = props.transaction.payment_method;
    unifiedForm.status = props.transaction.status;
}

  
  showCustomerModal.value = true;
};

// Handle submit form terpadu
const submitUnifiedForm = () => {
      // Pastikan nominal yang dikirim adalah angka
    const numericAmount = parseInt(unifiedForm.amount, 10);
    if (isNaN(numericAmount)) {
        alert('Nominal pembayaran tidak valid.');
        return;
    }
    
    // Atur status langsung menjadi 'paid' sebelum dikirim
    unifiedForm.status = 'paid';
    
    // Kirim data dengan nominal dan status yang sudah bersih
    unifiedForm.amount = numericAmount;

  unifiedForm.post(route('inspections.updateUnified', props.encryptedIds), {
    preserveScroll: true,
    onSuccess: () => {
      showCustomerModal.value = false;
      router.reload();
    }
  });
};

// Fungsi untuk menangani aksi dengan status loading
const handleAction = (route, actionType) => {
  isLoading.value = true;
  currentAction.value = actionType;

  router.visit(route, {
    onFinish: () => {
      isLoading.value = false;
      currentAction.value = null;
    }
  });
};

// Fungsi untuk menangani download
const handleDownload = (route) => {
  isLoading.value = true;
  currentAction.value = 'download';
  // Gunakan setTimeout untuk menampilkan loading sebentar
  setTimeout(() => {
    isLoading.value = false;
    currentAction.value = null;
  }, 1500); // 1.5 detik
};

</script>

<template>
  <AppLayout>
    <Head title="Review Inspeksi" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <h3 class="text-xl md:text-3xl font-bold text-gray-900 mb-6 text-center">
        Inspeksi yang Harus Diselesaikan
      </h3>

      <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200">
        <!-- Jadwal + Link Log -->
        <div class="p-4 flex justify-between items-start">
          <div>
            <div class="flex items-center mb-1">
              <CalendarDaysIcon class="h-5 w-5 text-blue-500 mr-2" />
              <span class="text-sm font-medium text-gray-600">Jadwal</span>
            </div>
            <p class="text-sm font-semibold text-blue-700 ml-7 -mt-1">
              {{ new Date(inspection.inspection_date).toLocaleDateString('id-ID', {
                weekday: 'short', year: 'numeric', month: 'short', day: 'numeric',
                hour: '2-digit', minute: '2-digit'
              }) }}
            </p>
          </div>

          <!-- Link ke Log -->
          <Link
            :href="route('inspection.log', encryptedIds)"
            class="text-xs font-semibold text-indigo-600 hover:underline"
          >
            Lihat Log
          </Link>
        </div>

        <!-- Mobil -->
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
          <div class="flex items-center">
            <CarIcon class="h-5 w-5 text-gray-500 mr-2" />
            <div class="text-sm font-medium text-gray-800">
              <div v-if="inspection.car">
                {{ `${inspection.car.brand.name} ${inspection.car.model.name} ${inspection.car.type.name} ${(inspection.car.cc / 1000).toFixed(1)} ${inspection.car.transmission} ${inspection.car.year}` }}
                <span class="text-gray-600">({{ inspection.car.fuel_type }})</span>
              </div>
              <div v-else>
                {{ inspection.car_name }}
              </div>
            </div>
          </div>
          <!-- Nomor Plat Mobil + Link -->
        <div class="flex items-center justify-between mt-2">
            <!-- Plate Number -->
            <div class="flex items-center">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 mr-2">NO POLISI:</span>
                <span class="text-sm font-bold text-gray-900">{{ inspection.plate_number }}</span>
            </div>

            <!-- Link ke Detail -->
            <Link
               v-if="inspection.status === 'draft' && isAdminOrCoordinator "
              :href="route('coordinator.inspections.show', encryptedIds)"
              class="text-xs font-semibold text-indigo-600 hover:underline"
            >
              Alihkan Tugas
            </Link>
        </div>

        </div>

<!-- Informasi Customer & Transaksi -->
<div class="px-4 py-3 bg-white border-t border-gray-100">
  <div class="flex justify-between items-center mb-3">
    <h4 class="text-sm font-medium text-gray-700">Informasi Customer & Pembayaran</h4>
    <button 
    v-if="!hasCustomer && !hasTransaction && !isStatus"
      @click="openCustomerModal"
      class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded hover:bg-blue-200"
    >
      {{ submitButtonText }}
    </button>
  </div>

  <!-- Data Customer -->
  <div 
    v-if="hasCustomer" 
    class="mb-3 p-2 rounded"
    :class="{
      'bg-green-50 border border-green-200': hasCustomer,
      'bg-yellow-50 border border-yellow-200': !hasCustomer
    }"
  >
    <p class="text-xs font-medium text-gray-500">Customer</p>
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm font-semibold text-green-800">{{ inspection.customer.name }}</p>
        <p class="text-xs text-green-700">{{ inspection.customer.phone }}</p>
        <p v-if="inspection.customer.email" class="text-xs text-green-700">{{ inspection.customer.email }}</p>
      </div>
     <!-- Icon WhatsApp -->
        <a
            v-if="inspection.customer.phone"
            :href="`https://wa.me/62${inspection.customer.phone.replace(/^0/, '')}`"
            target="_blank"
            class="text-green-600 hover:text-green-800 ml-2"
            title="Hubungi via WhatsApp"
        >
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.864 3.488"/>
            </svg>
        </a>
    </div>
  </div>
  <div 
    v-else 
    class="mb-3 p-2 bg-yellow-50 border border-yellow-200 rounded"
  >
    <p class="text-xs text-yellow-700">Data customer belum tersedia</p>
  </div>

  <!-- Data Transaksi -->
  <div 
    v-if="hasTransaction" 
    class="p-2 rounded"
    :class="{
      'bg-green-50 border border-green-200': isPaid,
      'bg-red-50 border border-red-200': !isPaid,
      'bg-yellow-50 border border-yellow-200': isPending
    }"
  >
    <p class="text-xs font-medium text-gray-500">Pembayaran</p>
    <div class="grid grid-cols-2 gap-1 text-sm">
      <span>Status:</span>
      <span :class="{
        'text-green-600 font-semibold': isPaid,
        'text-yellow-600 font-semibold': isPending,
        'text-red-600 font-semibold': !isPaid && !isPending
      }">
        {{ statusTranslations[transaction.status] }}
      </span>
      <span>Nominal:</span>
      <span :class="{
        'text-green-700': isPaid,
        'text-yellow-700': isPending,
        'text-red-700': !isPaid && !isPending
      }">
        {{ formatCurrency(transaction.amount) }}
      </span>
      <span v-if="transaction.payment_method">Metode:</span>
      <span 
        v-if="transaction.payment_method"
        :class="{
          'text-green-700': isPaid,
          'text-yellow-700': isPending,
          'text-red-700': !isPaid && !isPending
        }"
      >
        {{ transaction.payment_method }}
      </span>
    </div>
  </div>
  <div 
    v-else 
    class="p-2 bg-yellow-50 border border-yellow-200 rounded"
  >
    <p class="text-xs text-yellow-700">Data transaksi belum tersedia</p>
  </div>
</div>

        <!-- Kategori -->
        <div v-if="inspection.category" class="px-4 py-2 bg-white border-t border-gray-100">
          <p class="text-xs font-medium text-gray-500 tracking-wide">
            Kategori Inspeksi
          </p>
          <p class="text-sm text-gray-800">{{ inspection.category.name }}</p>
        </div>

        <!-- Status & Catatan -->
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
          <div class="flex items-center mb-1">
            <ClipboardDocumentListIcon class="h-5 w-5 text-green-500 mr-2" />
            <span class="text-sm font-medium text-gray-600">Status</span>
          </div>
          <p class="text-sm font-semibold text-gray-800 ml-7 -mt-1">
            {{ statusLabel(inspection.status) }}
          </p>
          <div class="mt-3">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
              Catatan
            </p>
            <p class="text-sm text-gray-800">
              <div v-html="inspection.notes || '-'"></div>
            </p>
          </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="p-4 flex gap-2">
          <!-- Tampilan tombol untuk status 'pending_review' -->
          <template v-if="inspection.status === 'pending_review' || isAdminOrCoordinator">
            <!-- Tombol Revisi (lebih pendek) -->
            <button
              @click="showRevisionModal = true"
              class="flex-2 px-3 py-2 bg-yellow-500 text-white font-medium rounded-md text-sm transition-colors hover:bg-yellow-600"
              :disabled="isLoading"
            >
              Revisi
            </button>
            <!-- Tombol Lihat Laporan PDF -->
            <Link
              :href="route('inspections.review.pdf', encryptedIds)"
              :disabled="isLoading"
              @click="handleAction(route('inspections.review.pdf', encryptedIds), 'lihat_laporan')"
              class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white font-medium rounded-md text-sm transition-colors hover:from-indigo-800 hover:to-sky-700"
              :class="{ 'opacity-50 cursor-not-allowed': isLoading && currentAction === 'lihat_laporan' }"
            >
              <span v-if="isLoading && currentAction === 'lihat_laporan'">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg> 
                Memuat Laporan...
              </span>
              <span v-else>Lihat Laporan PDF</span>
            </Link>
          </template>

          <!-- Tampilan tombol untuk status 'approved' -->
          <template v-else-if="inspection.status === 'approved' && inspection.file">
            <!-- Tombol Download (jika sudah ada file dan transaksi paid) -->
            <a
              v-if="canShowPdf"
              :href="route('inspections.download.approved.pdf', encryptedIds)"
              @click="handleDownload(route('inspections.download.approved.pdf', encryptedIds))"
              class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-blue-600 text-white font-medium rounded-md text-sm transition-colors hover:bg-blue-700"
              :disabled="isLoading"
              :class="{ 'opacity-50 cursor-not-allowed': isLoading && currentAction === 'download' }"
              download
            >
              <span v-if="isLoading && currentAction === 'download'">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Sedang di download...
              </span>
              <span v-else>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                PDF
              </span>
            </a>

            <!-- Pesan jika transaksi belum paid -->
            <div v-else class="flex-1 flex items-center justify-center px-3 py-2 bg-gray-200 text-gray-600 font-medium rounded-md text-sm">
              <span class="text-xs">Transaksi belum selesai</span>
            </div>

            <!-- Tombol Kirim WhatsApp -->
            <Link
              :href="route('inspections.whatsapp', encryptedIds)"
              class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-green-600 text-white font-medium rounded-md text-sm transition-colors hover:bg-green-700"
            >
              <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.864 3.488"/>
              </svg>
              WhatsApp
            </Link>

            <!-- Tombol Kirim Email -->
            <button
              @click="showEmailModal = true"
              class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-teal-500 text-white font-medium rounded-md text-sm transition-colors hover:bg-teal-600"
              :disabled="isLoading"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
              </svg>
              Email
            </button>
          </template>

                  <!-- Tampilan default (tombol lebar penuh) -->
        <template v-if="inspection.status === 'completed' && inspection.file && isAdminOrCoordinator ">
                      <!-- Tombol Download (jika sudah ada file dan transaksi paid) -->
          <!-- Tombol Download PDF -->
          <a
            :href="canShowPdf ? route('inspections.download.approved.pdf', encryptedIds) : null"
            @click="canShowPdf && handleDownload(route('inspections.download.approved.pdf', encryptedIds))"
            class="flex-1 inline-flex items-center justify-center px-3 py-2 font-medium rounded-md text-sm transition-colors"
            :class="[
              canShowPdf
                ? 'bg-blue-600 text-white hover:bg-blue-700'
                : 'bg-gray-300 text-gray-500 cursor-not-allowed'
            ]"
            :disabled="!canShowPdf || (isLoading && currentAction === 'download')"
          >
            <span v-if="isLoading && currentAction === 'download'">
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Sedang diunduh...
            </span>

            <span v-else>
              <template v-if="canShowPdf">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Download PDF
              </template>
              <template v-else>
                PDF tersedia — selesaikan transaksi untuk mengunduh
              </template>
            </span>
          </a>

        </template>
        </div>

        <!-- Tampilan default (tombol lebar penuh) -->
        <template v-if="inspection.status === 'approved' && inspection.file">
          <Link
            :href="route('inspections.review.pdf', encryptedIds)"
            :disabled="isLoading"
            @click="handleAction(route('inspections.review.pdf', encryptedIds), 'lihat_laporan')"
            class="inline-flex items-center justify-center w-full px-3 py-2 bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white font-medium rounded-md text-sm transition-colors hover:from-indigo-800 hover:to-sky-700"
            :class="{ 'opacity-50 cursor-not-allowed': isLoading && currentAction === 'lihat_laporan' }"
          >
            <span v-if="isLoading && currentAction === 'lihat_laporan'">
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Memuat Laporan...
            </span>
            <span v-else>Lihat Laporan</span>
          </Link>
        </template>

      </div>
    </div>

    <!-- Modal Customer & Transaksi -->
    <!-- Modal Customer & Transaksi -->
    <div v-if="showCustomerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-bold mb-4">
          {{ hasCustomer ? 'Edit Data Customer & Transaksi' : 'Tambah Data Customer & Transaksi' }}
        </h3>

        <form @submit.prevent="submitUnifiedForm" class="space-y-4">
          <!-- Data Customer -->
          <div>
            <h4 class="text-sm font-medium text-gray-700 mb-2">Data Customer</h4>
            <div class="space-y-3">
              <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nama Customer *</label>
                <input
                  v-model="unifiedForm.customer_name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                />
              </div>
              
              <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nomor WhatsApp *</label>
                <input
                  v-model="unifiedForm.customer_phone"
                  type="tel"
                  required
                  placeholder="Contoh: 628123456789"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                />
                <p class="text-xs text-gray-500 mt-1">Gunakan format internasional (62 bukan 0)</p>
              </div>
              
              <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                <input
                  v-model="unifiedForm.customer_email"
                  type="email"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                />
              </div>
              
              <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Alamat</label>
                <textarea
                  v-model="unifiedForm.customer_address"
                  rows="2"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Data Transaksi -->
          <div>
            <h4 class="text-sm font-medium text-gray-700 mb-2">Data Transaksi</h4>
            <div class="space-y-3">
              <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nominal Pembayaran *</label>
                <input
                  v-model="formattedAmount"
                  @input="handleAmountInput"
                  type="text"
                  required
                  min="0"
                  placeholder="Contoh: 250000"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                />
              </div>
              
              <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Metode Pembayaran *</label>
                <select
                  v-model="unifiedForm.payment_method"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                >
                  <option value="">Pilih Metode</option>
                  <option value="cash">Cash</option>
                  <option value="transfer">Transfer</option>
                  <option value="debit_card">Kartu Debit</option>
                  <option value="credit_card">Kartu Kredit</option>
                  <option value="qris">QRIS</option>
                </select>
              </div>

              <input type="hidden" v-model="unifiedForm.status" />
            </div>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="showCustomerModal = false"
              class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-400"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="unifiedForm.processing"
              class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700 disabled:opacity-50"
            >
              {{ unifiedForm.processing ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Konfirmasi Revisi -->
    <div v-if="showRevisionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-xl w-96">
        <h3 class="text-lg font-bold mb-4">Konfirmasi Revisi</h3>
        <p class="text-gray-700 mb-2">
          Apakah Anda yakin ingin revisi pada laporan ini?
        </p>
        <p class="text-gray-700 mb-4">Setelah ini akan masuk ke halaman Inspeksi.</p>
        <div class="flex justify-end space-x-4">
          <button @click="showRevisionModal = false" :disabled="isLoading" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400">
            Batal
          </button>
          <Link
            :href="route('inspections.revisi', encryptedIds)"
            :disabled="isLoading"
            @click="handleAction(route('inspections.revisi', encryptedIds), 'revisi')"
            class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600"
            :class="{ 'opacity-50 cursor-not-allowed': isLoading && currentAction === 'revisi' }"
          >
            <span v-if="isLoading && currentAction === 'revisi'" class="flex items-center">
              <svg class="animate-spin h-5 w-5 text-white mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Memproses...
            </span>
            <span v-else>Ya, Revisi</span>
          </Link>
        </div>
      </div>
    </div>

    <!-- Modal Kirim Email -->
    <div v-if="showEmailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-xl w-96">
        <h3 class="text-lg font-bold mb-4">Kirim Laporan Via Email</h3>
        <p class="text-gray-700 mb-2">
          Masukkan alamat email tujuan:
        </p>
        <input
          type="email"
          v-model="emailAddress"
          class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500"
          placeholder="contoh@email.com"
        />
        <div class="flex justify-end space-x-4 mt-6">
          <button @click="showEmailModal = false" :disabled="isLoading" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400">
            Batal
          </button>
          <button @click="sendEmail" disabled class="bg-teal-500 text-white px-4 py-2 rounded-md hover:bg-teal-600">
            Masih dalam pengembangan
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>