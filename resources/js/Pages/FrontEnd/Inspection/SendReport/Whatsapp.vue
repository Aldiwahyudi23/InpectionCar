<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
  inspection: Object,
  IdInspection: Object,
  transaction: Object,
  notesForWhatsApp: String
});

const page = usePage();

// Menerjemahkan status
const statusTranslations = {
    'pending': 'Menunggu Pembayaran',
    'paid': 'Sudah di bayar',
    'failed': 'Gagal',
    'refunded': 'Dikembalikan',
    'expired': 'Kadaluarsa'
};

// Form terpadu untuk customer dan transaksi
const unifiedForm = useForm({
  // Data customer
  customer_name: props.inspection.customer?.name || '',
  customer_phone: props.inspection.customer?.phone || '',
  customer_email: props.inspection.customer?.email || '',
  customer_address: props.inspection.customer?.address || '',
  
  // Data transaksi
  amount: props.transaction?.amount || '',
  payment_method: props.transaction?.payment_method || '',
  status: props.transaction?.status || 'pending'
});

// State untuk UI
const isSending = ref(false);
const sendStatus = ref('');

// Cek apakah inspection sudah memiliki customer
const hasCustomer = computed(() => props.inspection.customer_id !== null);

// Cek apakah sudah ada transaksi
const hasTransaction = computed(() => props.transaction !== null);

// Cek status transaksi
const isPaid = computed(() => props.transaction?.status === 'paid');

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
    if (newAmount) {
        // Hapus karakter non-digit dari input untuk mencegah error
        const cleanNumber = String(newAmount).replace(/\D/g, '');
        // Format ulang nominal saat unifiedForm.amount berubah
        formattedAmount.value = formatAmountForInput(cleanNumber);
    } else {
        formattedAmount.value = '';
    }
}, { immediate: true });

// ==========================================================


// Cek apakah tombol WhatsApp bisa diklik
const canSendWhatsApp = computed(() => {
  return hasCustomer.value && hasTransaction.value && isPaid.value;
});

// Cek apakah tombol submit harus disembunyikan
const hasAllData = computed(() => hasCustomer.value && hasTransaction.value && isPaid.value);


// Computed property untuk teks tombol submit
const submitButtonText = computed(() => {
  if (unifiedForm.processing) {
    return 'Menyimpan...';
  }
  if (!hasCustomer.value && !hasTransaction.value) {
    return 'Simpan Data';
  }
  if (hasCustomer.value && !hasTransaction.value) {
    return 'Simpan Pembayaran';
  }
  if (!hasCustomer.value && hasTransaction.value) {
    return 'Tambahkan Data Customer';
  }
  return 'Simpan Data'; // Default case
});

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

    unifiedForm.post(route('inspections.updateUnified', props.IdInspection), {
        preserveScroll: true,
        onSuccess: () => {
          router.reload();
        }
    });
};

// Format pesan WhatsApp
const decodeHtmlEntities = (str) => {
  const txt = document.createElement("textarea");
  txt.innerHTML = str;
  return txt.value;
};


const whatsappMessage = computed(() => {
  if (!props.inspection.notes) {
    return `Hasil inspeksi kendaraan dengan ID ${props.inspection.code} sudah tersedia.`;
  }
  
  
  return `*HASIL INSPEKSI KENDARAAN*

*ID Inspeksi*: ${props.inspection.code}
*Plat Nomor*: ${props.inspection.plate_number}
*Kendaraan*: ${props.inspection.car_name}

*HASIL:*
${props.notesForWhatsApp}

Terima kasih telah menggunakan layanan kami.

_Catatan: Mohon gunakan ID Inspeksi untuk mengakses file, sebagai upaya kami melindungi data kendaraan._`;
});

// // URL WhatsApp
// const whatsappUrl = computed(() => {
//   const phone = hasCustomer.value  
//     ? props.inspection.customer.phone  
//     : unifiedForm.customer_phone;
  
//   if (!phone) return '#';
  
//   const formattedPhone = phone.startsWith('0') ? '62' + phone.substring(1) : phone;
//   const message = encodeURIComponent(whatsappMessage.value);
//   return `https://wa.me/${formattedPhone}?text=${message}`;
// });


// Format nomor telepon dengan prefix +62 dan separator
const formatPhone = (phone) => {
  if (!phone) return '';

  // Hapus semua karakter selain angka
  let cleaned = ('' + phone).replace(/\D/g, '');

  // Jika diawali dengan 0 → ganti 0 jadi +62
  if (cleaned.startsWith('0')) {
    cleaned = '62' + cleaned.substring(1);
  }

  // Jika belum ada + di depan → tambahkan +
  if (!cleaned.startsWith('+')) {
    cleaned = '+' + cleaned;
  }

  // Buat hanya angka setelah prefix +62
  let body = cleaned.replace(/^\+62/, '');

  // Pisahkan tiap blok: 3 digit pertama, lalu 3–4 digit, sisanya
  let parts = [];
  if (body.length > 0) parts.push(body.substring(0, 2));   // 3 digit pertama
  if (body.length > 2) parts.push(body.substring(2, 5));   // 3-4 digit berikutnya
  if (body.length > 5) parts.push(body.substring(5, 8));      // sisanya
  if (body.length > 8) parts.push(body.substring(8));      // sisanya

  return '+62' + parts.filter(Boolean).join('-');
};



// Format currency untuk display
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount);
};

</script>

<template>
  <AppLayout>
    <Head title="Kirim WhatsApp" />

    <div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-md">
      <h1 class="text-xl font-bold text-gray-800 mb-4">Kirim Hasil Inspeksi via WhatsApp</h1>
      
      <!-- Info Inspection -->
      <div class="bg-blue-50 p-4 rounded-lg mb-4">
        <h2 class="text-lg font-semibold text-blue-800 mb-2">Informasi Inspeksi</h2>
        <p><span class="font-medium">ID:</span> {{ inspection.code }}</p>
        <p><span class="font-medium">Plat Nomor:</span> {{ inspection.plate_number }}</p>
        <p><span class="font-medium">Mobil:</span> {{ inspection.car_name }}</p>
      </div>

      <!-- Form Terpadu untuk Customer dan Transaksi -->
      <form @submit.prevent="submitUnifiedForm" class="space-y-6">
        <!-- Section Customer -->
        <div :class="['p-4 rounded-lg', hasCustomer ? 'bg-green-50' : 'bg-yellow-50']">
          <h2 class="text-lg font-semibold mb-4" :class="hasCustomer ? 'text-green-800' : 'text-yellow-800'">
            Informasi Customer
          </h2>
          
<div v-if="hasCustomer" class="mb-4 relative">
  <p><span class="font-medium">Nama:</span> {{ inspection.customer.name }}</p>
  <p>
    <span class="font-medium">WhatsApp:</span>
    {{ formatPhone(inspection.customer.phone) }}
  </p>
  <p v-if="inspection.customer.email">
    <span class="font-medium">Email:</span> {{ inspection.customer.email }}
  </p>
  <p v-if="inspection.customer.address">
    <span class="font-medium">Alamat:</span> {{ inspection.customer.address }}
  </p>

  <!-- Floating WhatsApp Button -->
  <a
    v-if="inspection.customer.phone"
    :href="`https://wa.me/${inspection.customer.phone.replace(/^0/, '62')}`"
    target="_blank"
    class="botton-2 right-6 inline-flex items-center px-4 py-1 bg-green-600 text-white rounded-md hover:bg-green-700"
  >
    <!-- Ikon WhatsApp -->
     <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.864 3.488"/>
      </svg>
  </a>
</div>

          
          <div v-else class="space-y-4">
            <p class="text-yellow-700 mb-4">Data customer belum tersedia. Silakan isi informasi customer.</p>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Customer *</label>
              <input
                v-model="unifiedForm.customer_name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': unifiedForm.errors.customer_name }"
              />
              <p v-if="unifiedForm.errors.customer_name" class="text-red-500 text-sm mt-1">{{ unifiedForm.errors.customer_name }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp *</label>
              <input
                v-model="unifiedForm.customer_phone"
                type="tel"
                required
                placeholder="Contoh: 628123456789"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': unifiedForm.errors.customer_phone }"
              />
              <p class="text-xs text-gray-500 mt-1">Gunakan format internasional (62 bukan 0)</p>
              <p v-if="unifiedForm.errors.customer_phone" class="text-red-500 text-sm mt-1">{{ unifiedForm.errors.customer_phone }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input
                v-model="unifiedForm.customer_email"
                type="email"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': unifiedForm.errors.customer_email }"
              />
              <p v-if="unifiedForm.errors.customer_email" class="text-red-500 text-sm mt-1">{{ unifiedForm.errors.customer_email }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
              <textarea
                v-model="unifiedForm.customer_address"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': unifiedForm.errors.customer_address }"
              ></textarea>
              <p v-if="unifiedForm.errors.customer_address" class="text-red-500 text-sm mt-1">{{ unifiedForm.errors.customer_address }}</p>
            </div>
          </div>
        </div>

        <!-- Section Transaksi -->
        <div :class="['p-4 rounded-lg', isPaid ? 'bg-green-50' : 'bg-purple-50']">
          <h2 class="text-lg font-semibold mb-4" :class="isPaid ? 'text-green-800' : 'text-purple-800'">
            Informasi Pembayaran
          </h2>
          
          <div v-if="hasTransaction && isPaid" class="mb-4">
            <div class="grid grid-cols-2 gap-2">
              <p><span class="font-medium">Status:</span></p>
              <span class="font-semibold" :class="{'text-green-600': isPaid, 'text-yellow-600': !isPaid}">{{ statusTranslations[props.transaction.status] }}</span>
              
              <p><span class="font-medium">Nominal:</span></p>
              <p>{{ formatCurrency(props.transaction.amount) }}</p>
              
              <p v-if="props.transaction.payment_method"><span class="font-medium">Metode:</span></p>
              <p v-if="props.transaction.payment_method">{{ props.transaction.payment_method }}</p>
            </div>
          </div>
          
          <div v-else class="space-y-4">
            <p v-if="!hasTransaction" class="text-purple-700 mb-4">Data transaksi belum tersedia. Silakan isi informasi pembayaran.</p>
            <p v-else-if="!isPaid" class="text-yellow-600 mb-4">Transaksi sudah ada, namun belum terbayar. Silakan lengkapi data pembayaran di bawah ini.</p>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nominal Pembayaran *</label>
              <input
                v-model="formattedAmount"
                @input="handleAmountInput"
                type="text"
                required
                placeholder="Contoh: Rp 250.000"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': unifiedForm.errors.amount }"
              />
              <p v-if="unifiedForm.errors.amount" class="text-red-500 text-sm mt-1">{{ unifiedForm.errors.amount }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran *</label>
              <select
                v-model="unifiedForm.payment_method"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': unifiedForm.errors.payment_method }"
              >
                <option value="">Pilih Metode</option>
                <option value="cash">Cash</option>
                <option value="transfer">Transfer</option>
                <option value="debit_card">Kartu Debit</option>
                <option value="credit_card">Kartu Kredit</option>
                <option value="qris">QRIS</option>
              </select>
              <p v-if="unifiedForm.errors.payment_method" class="text-red-500 text-sm mt-1">{{ unifiedForm.errors.payment_method }}</p>
            </div>
            
            <input type="hidden" v-model="unifiedForm.status" />
          </div>
        </div>

        <!-- Tombol Submit -->
        <div class="text-center" v-if="!hasAllData">
          <button
            type="submit"
            :disabled="unifiedForm.processing"
            class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
          >
            {{ submitButtonText }}
          </button>
        </div>
      </form>

      <!-- Preview Pesan -->
      <div class="bg-gray-50 p-4 rounded-lg mb-2 mt-2">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Pratinjau Pesan WhatsApp</h2>
        <div class="bg-white p-4 rounded border border-gray-200 whitespace-pre-wrap text-sm font-mono">
          {{ whatsappMessage }}
        </div>
        <p class="text-xs text-gray-500 mt-2">Pesan akan dikirim ke: {{ hasCustomer ? formatPhone(inspection.customer.phone) : formatPhone(unifiedForm.customer_phone) }}</p>
      </div>

      <!-- Actions -->
      <div class="text-center">
        <a
           :href="route('inspections.whatsapp.send', props.IdInspection)"
          target="_blank"
          class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 text-center"
          :class="{ 'opacity-50 pointer-events-none': !canSendWhatsApp }"
        >
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.864 3.488"/>
          </svg>
          Kirim via WhatsApp
        </a>
        
        <p v-if="!canSendWhatsApp" class="text-red-500 text-sm mt-2">
          Transaksi di perlukan (Customer, Pembayaran, dan Status Terbayar harus lengkap)
        </p>
      </div>

     <!-- Catatan -->
    <div class="mt-6 p-4 bg-yellow-50 rounded-lg">
      <h3 class="font-semibold text-yellow-800 mb-2">Catatan Penting:</h3>
      <ul class="list-disc list-inside text-yellow-700 text-sm space-y-1">
        <li>Pastikan data customer dan pembayaran sudah benar sebelum mengirim</li>
        <li>Setelah pembayaran selesai, file Report PDF akan otomatis tersimpan di Berkas</li>
        <li>Ketika menekan tombol <b>Kirim ke WhatsApp</b>, pesan akan dibuka di aplikasi WhatsApp dan file Report PDF yang baru tersimpan ke berkas harus dilampirkan sebelum di kirim </li>
        <li>Tombol WhatsApp hanya aktif jika semua data lengkap dan status pembayaran sudah <b>Lunas</b></li>
        <li>Pastikan perangkat Anda telah terinstal WhatsApp</li>
      </ul>
    </div>

    </div>
  </AppLayout>
</template>
