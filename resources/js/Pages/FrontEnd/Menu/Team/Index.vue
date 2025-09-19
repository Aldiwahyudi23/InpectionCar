<template>
    <AppLayout title="Team">
        <Head title="Team Region" />

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header dengan Tombol Tambah User -->
            <div class="mb-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="text-center">
                    <h1 class="text-2xl font-bold text-gray-900">Tim {{ userRegion.name }}</h1>
                </div>

                <!-- Tombol Tambah User (Hanya untuk Admin/Coordinator) -->
                <button
                    v-if="canAddUser"
                    @click="showAddUserModal = true; message.text = ''; message.isError = false;"
                    class="px-5 py-2.5 bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white font-medium rounded-full shadow-md transition-all duration-300 ease-in-out transform hover:-translate-y-0.5 flex items-center"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Tambah Anggota
                </button>
            </div>

            <!-- Section Profil User -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-4">
                <div class="px-6 py-8 text-center">
                    <!-- Foto Profil -->
                    <div class="flex justify-center mb-6">
                        <img
                            :src="user.profile_photo_url || '/images/default-avatar.png'"
                            :alt="user.name"
                            class="w-40 h-40 rounded-full object-cover border-4 border-white shadow-lg mx-auto"
                        >
                    </div>

                    <!-- Nama User -->
                    <h2 class="text-2xl font-semibold text-gray-900 mb-2">{{ user.name }}</h2>

                    <!-- Informasi Region -->
                    <div class="mb-2">
                        <p class="text-gray-600">
                            <span class="font-medium">Terdaftar di wilayah:</span>
                            <span v-if="userRegion">{{ userRegion.name }} ({{ userRegion.code }})</span>
                            <span v-else class="text-yellow-600">Belum tergabung dalam region</span>
                        </p>
                    </div>

                    <!-- Role dan Status -->
                    <div class="flex justify-center space-x-4 text-sm text-gray-500">
                        <span class="capitalize">{{ userRoles[0] || 'N/A' }}</span>
                        <span>•</span>
                        <span :class="{
                            'text-green-600': userTeamStatus === 'active',
                            'text-red-600': userTeamStatus === 'inactive',
                            'text-yellow-600': userTeamStatus === 'paused'
                        }">
                            {{ getStatusText(userTeamStatus) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Section Anggota Team dalam Region yang Sama -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Anggota Tim</h3>
                    <p class="text-sm text-gray-600 mt-1">Daftar anggota</p>
                </div>

                <div class="p-4">
                    <!-- Filter dan Pencarian (Hanya untuk Admin/Coordinator) -->
                    <div v-if="canAddUser" class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="relative flex-1 max-w-md">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari anggota team..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <select
                            v-model="statusFilter"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                            <option value="paused">Menunggu Persetujuan</option>
                        </select>
                    </div>

                    <!-- Daftar Anggota Team -->
                    <div v-if="filteredTeamMembers.length > 0" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-4">
                        <div
                            v-for="member in filteredTeamMembers"
                            :key="member.id"
                            class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow relative"
                            :class="{
                                'bg-blue-50 border-blue-200': member.user.id === user.id,
                                'bg-white': member.user.id !== user.id,
                                'bg-yellow-50 border-yellow-200': member.status === 'paused'
                            }"
                        >
                            <!-- Tautan Pengaturan (Hanya untuk Admin/Coordinator) -->
                            <div
                                v-if="canAddUser"
                                class="absolute top-2 right-2"
                            >
                                <a :href="route('setting.team',member.id)" class="text-gray-400 hover:text-indigo-600 transition" title="Pengaturan Tim">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.223 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </a>
                            </div>
                            
                            <!-- Badge Status Pending -->
                            <div v-if="member.status === 'paused'" class="absolute top-2 right-2">
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">
                                    {{ getCombinedStatusText(member) }}
                                </span>
                            </div>

                            <div class="flex items-center space-x-4">
                                <!-- Foto Profil -->
                                <img
                                    :src="member.user.profile_photo_url || '/images/default-avatar.png'"
                                    :alt="member.user.name"
                                    class="w-16 h-16 rounded-full object-cover border-2 border-white shadow"
                                >

                                <!-- Informasi Member -->
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-gray-900 truncate">
                                        {{ member.user.name }}
                                        <span v-if="member.user.id === user.id" class="text-blue-600 text-sm">(Anda)</span>
                                    </h4>
                                    <p class="text-sm text-gray-600 truncate">{{ member.user.email }}</p>

                                    <div class="flex items-center mt-1 space-x-3">
                                        <span class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded-full capitalize">
                                            {{ member.user.roles[0] || 'N/A' }}
                                        </span>
                                        <span
                                            class="text-xs px-2 py-1 rounded-full"
                                            :class="{
                                                'bg-green-100 text-green-700': member.status === 'active',
                                                'bg-red-100 text-red-700': member.status === 'inactive',
                                                'bg-yellow-100 text-yellow-700': member.status === 'paused'
                                            }"
                                        >
                                            {{ getStatusText(member.status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- WhatsApp dan Tanggal Bergabung -->
                            <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between items-center">
                                <p class="text-xs text-gray-500">
                                    Bergabung: {{ formatDate(member.created_at) }}
                                </p>

                                <!-- Tombol WhatsApp -->
                                <a
                                    v-if="member.user.phone && member.status === 'active'"
                                    :href="`https://wa.me/62${member.user.phone}`"
                                    target="_blank"
                                    class="text-green-600 hover:text-green-800 transition"
                                    title="Hubungi via WhatsApp"
                                >
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.465 3.488"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- State Kosong -->
                    <div v-else class="text-center py-12">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <p class="mt-4 text-gray-500">
                            {{ searchQuery || statusFilter ? 'Tidak ada anggota yang sesuai dengan filter' : 'Tidak ada anggota team dalam region ini' }}
                        </p>
                        <button
                            v-if="searchQuery || statusFilter"
                            @click="clearFilters"
                            class="mt-3 px-4 py-2 text-sm text-indigo-600 hover:text-indigo-800 transition"
                        >
                            Hapus Filter
                        </button>
                    </div>
                </div>
            </div>

<!-- Modal Tambah User -->
<div v-if="showAddUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Tambah Anggota Baru</h3>

        <div v-if="message.text"
             :class="{ 'bg-green-100 text-green-700': !message.isError, 'bg-red-100 text-red-700': message.isError }"
             class="p-3 rounded-lg text-center mb-4 transition-all duration-300 ease-in-out">
            {{ message.text }}
        </div>

        <form @submit.prevent="submitNewUser" >
            <!-- Nama -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="newUserForm.name"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Masukkan nama lengkap"
                >
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Email <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="newUserForm.email"
                    type="email"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Masukkan alamat email"
                >
                <p v-if="newUserForm.email && !isEmailValid" class="text-xs text-red-500 mt-1">
                    Format email tidak valid
                </p>
            </div>

            <!-- Nomor Telepon -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nomor WhatsApp <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="newUserForm.phone"
                    type="tel"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Contoh: 08123456789"
                >
                <p class="text-xs text-gray-500 mt-1">Gunakan format angka, minimal 9 digit</p>
                <p v-if="newUserForm.phone && !isPhoneValid" class="text-xs text-red-500 mt-1">
                    Nomor HP tidak valid
                </p>
            </div>

            <!-- Role -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Posisi</label>
                <select
                    v-model="newUserForm.role"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="inspector">Inspector</option>
                    <option disabled value="admin_region">Admin Wilayah</option>
                </select>
            </div>

            <!-- Tombol -->
            <div class="flex justify-end space-x-3">
                <button type="button" @click="showAddUserModal = false" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition">
                    Batal
                </button>
                <button
                    type="submit"
                    :disabled="!isFormValid || isDuplicateUser || addingUser"
                    class="px-4 py-2 bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white rounded-md transition disabled:from-gray-400 disabled:to-gray-500 disabled:opacity-70 disabled:cursor-not-allowed"
                >
                    <span v-if="addingUser">Menambahkan...</span>
                    <span v-else-if="isDuplicateUser">Akun ini sudah terdaftar</span>
                    <span v-else>{{ isFormValid ? 'Tambahkan Anggota' : 'Lengkapi data' }}</span>
                </button>
            </div>
        </form>
    </div>
</div>

        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue';
import { usePage, Head } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';

const page = usePage();

// Menambahkan props allUsers dari controller Anda
const props = defineProps({
    allUsers: {
        type: Object,
        required: true,
    }
});

const user = page.props.auth.user;
const allRegions = ref(page.props.regions || []);
const roleName = page.props.roleName;
const allTeamMembers = ref(page.props.teamMembers || []);

const searchQuery = ref('');
const statusFilter = ref('');
const showAddUserModal = ref(false);
const addingUser = ref(false);

// Menambahkan state untuk pesan dari backend
const message = reactive({
    text: '',
    isError: false
});

// Form untuk user baru
const newUserForm = reactive({
    name: '',
    email: '',
    phone: '',
    role: 'inspector'
});

// Validasi Email
const isEmailValid = computed(() => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(newUserForm.email);
});

// Validasi Nomor HP (hanya angka, minimal 9 digit, maksimal 15)
const isPhoneValid = computed(() => {
    const phoneRegex = /^[0-9]{9,15}$/;
    return phoneRegex.test(newUserForm.phone.replace(/^0/, '')); // hilangkan 0 awal
});



// Computed properties
const userRoles = computed(() => page.props.roleName || 'inspection')
const canAddUser = computed(() => {
    return userRoles.value.includes('Admin') || userRoles.value.includes('coordinator')
})

const userRegion = computed(() => {
    const userTeam = allTeamMembers.value.find(member => member?.user?.id === user?.id);
    if (!userTeam) return null;
    return allRegions.value.find(region => region?.id === userTeam.region_id);
});

const userTeamMembers = computed(() => {
    const regionId = userRegion.value?.id;
    if (!regionId) return [];
    return allTeamMembers.value.filter(member => member?.region_id === regionId);
});

const userRole = computed(() => {
    const userTeam = allTeamMembers.value.find(member => member?.user?.id === user?.id);
    return userTeam ? userTeam.role : 'anggota';
});

const userTeamStatus = computed(() => {
    const userTeam = allTeamMembers.value.find(member => member?.user?.id === user?.id);
    return userTeam ? userTeam.status : 'inactive';
});

// Perbaikan: computed property untuk cek email dan nomor telepon yang duplikat
const isDuplicateUser = computed(() => {
    const currentEmail = newUserForm.email.trim().toLowerCase();
    const currentPhone = newUserForm.phone.trim();

    // Perbaikan utama: Menggunakan optional chaining `?.` untuk memastikan `props.allUsers` bukan `null` atau `undefined`
    const users = props.allUsers || [];
    const emailExists = users.some(user => user.email?.toLowerCase() === currentEmail);
    const phoneExists = users.some(user => user.numberPhone === currentPhone);

    return emailExists || phoneExists;
});

// // Perbaikan: computed property untuk validasi form
// const isFormValid = computed(() => {
//     return newUserForm.name && newUserForm.email && newUserForm.phone;
// });
// Form validasi keseluruhan
const isFormValid = computed(() => {
    return (
        newUserForm.name &&
        isEmailValid.value &&
        isPhoneValid.value
    );
});

const filteredTeamMembers = computed(() => {
    let filtered = userTeamMembers.value;

    if (canAddUser.value) {
        if (searchQuery.value) {
            const query = searchQuery.value.toLowerCase();
            filtered = filtered.filter(member => {
                const user = member?.user;
                if (!user) return false;
                const nameMatch = user.name?.toLowerCase().includes(query);
                const emailMatch = user.email?.toLowerCase().includes(query);
                const roleMatch = user.roles?.some(role => role.toLowerCase().includes(query));
                return nameMatch || emailMatch || roleMatch;
            });
        }

        if (statusFilter.value) {
            filtered = filtered.filter(member => member?.status === statusFilter.value);
        }
    }

    return filtered;
});

// Methods
const formatDate = (dateString) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    }).format(date);
};

const getStatusText = (status) => {
    const statusMap = {
        'active': 'Aktif',
        'inactive': 'Tidak Aktif',
        'paused': 'Menunggu Persetujuan'
    };
    return statusMap[status] || status;
};

const getCombinedStatusText = (member) => {
    if (!member.user.is_active && member.status === 'paused') {
        return 'Dalam Tinjauan';
    }
    return getStatusText(member.status);
}

const clearFilters = () => {
    searchQuery.value = '';
    statusFilter.value = '';
};

const submitNewUser = async () => {
    const regionId = userRegion.value?.id;
    if (!regionId) {
        console.error('Anda tidak dapat menambahkan user karena Anda belum tergabung dalam region.');
        addingUser.value = false;
        return;
    }

     if (!isFormValid.value) return;
    
    // Pastikan tidak ada duplikasi sebelum mengirim
    if (isDuplicateUser.value) {
        return;
    }

    addingUser.value = true;
    message.text = ''; // Kosongkan pesan sebelumnya
    message.isError = false;

    try {
        const response = await axios.post(route('team.add-user'), {
            ...newUserForm,
            region_id: regionId
        });

        // Menampilkan pesan sukses dari backend
        if (response.data.success) {
            const newUser = response.data.user;
            allTeamMembers.value.push(newUser);
            message.text = response.data.message || 'Anggota berhasil ditambahkan!';
            message.isError = false;
            
            // Reset form setelah berhasil
            Object.assign(newUserForm, {
                name: '',
                email: '',
                phone: '',
                role: 'inspector'
            });

            // Otomatis tutup modal setelah 3 detik
            setTimeout(() => {
                showAddUserModal.value = false;
                message.text = '';
            }, 3000);

        } else {
            // Menampilkan pesan error dari backend
            message.text = response.data.message || 'Gagal menambahkan anggota. Silakan coba lagi.';
            message.isError = true;
        }
    } catch (error) {
        console.error('Error adding user:', error);
        // Menampilkan pesan error dari respons Axios
        if (error.response && error.response.data && error.response.data.message) {
            message.text = error.response.data.message;
        } else {
            message.text = 'Terjadi kesalahan saat menambahkan user.';
        }
        message.isError = true;
    } finally {
        addingUser.value = false;
    }
};
</script>
