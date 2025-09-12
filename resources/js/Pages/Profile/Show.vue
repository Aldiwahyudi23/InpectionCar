<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});

// State untuk mengelola visibilitas setiap bagian
const showProfile = ref(false);
const showPassword = ref(false);
const showTwoFactor = ref(false);
const showSessions = ref(false);
const showDelete = ref(false);

const toggleSection = (section) => {
    // Fungsi untuk menutup semua section, kecuali yang sedang diklik
    showProfile.value = section === 'profile' ? !showProfile.value : false;
    showPassword.value = section === 'password' ? !showPassword.value : false;
    showTwoFactor.value = section === 'twoFactor' ? !showTwoFactor.value : false;
    showSessions.value = section === 'sessions' ? !showSessions.value : false;
    showDelete.value = section === 'delete' ? !showDelete.value : false;
};
</script>

<template>
    <AppLayout title="Profil">
        <div>
           <div class="max-w-7xl mx-auto px-4 sm:px-4 lg:px-4 py-6">

                <div class="bg-white rounded-xl shadow-sm p-2 mb-4 ring-1 ring-gray-100">
                    <div
                        class="flex justify-between items-center cursor-pointer transition-all duration-200 hover:bg-gray-50 px-4 py-2 rounded-lg -mx-4"
                        @click="toggleSection('profile')"
                    >
                        <h3 class="font-semibold text-lg text-gray-800">
                            Informasi Profil
                        </h3>
                        <span class="text-gray-400 transform transition-transform" :class="{'rotate-90': showProfile}">▶</span>
                    </div>
                    <div v-if="showProfile">
                        <UpdateProfileInformationForm :user="$page.props.auth.user" />
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-2 mb-4 ring-1 ring-gray-100">
                    <div
                        class="flex justify-between items-center cursor-pointer transition-all duration-200 hover:bg-gray-50 px-4 py-2 rounded-lg -mx-4"
                        @click="toggleSection('password')"
                    >
                        <h3 class="font-semibold text-lg text-gray-800">
                            Perbarui Kata Sandi
                        </h3>
                        <span class="text-gray-400 transform transition-transform" :class="{'rotate-90': showPassword}">▶</span>
                    </div>
                    <div v-if="showPassword" class="mt-2">
                        <UpdatePasswordForm />
                    </div>
                </div>

                <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication" class="bg-white rounded-xl shadow-sm p-2 mb-4 ring-1 ring-gray-100">
                    <div
                        class="flex justify-between items-center cursor-pointer transition-all duration-200 hover:bg-gray-50 px-4 py-2 rounded-lg -mx-4"
                        @click="toggleSection('twoFactor')"
                    >
                        <h3 class="font-semibold text-lg text-gray-800">
                            Autentikasi Dua Faktor
                        </h3>
                        <span class="text-gray-400 transform transition-transform" :class="{'rotate-90': showTwoFactor}">▶</span>
                    </div>
                    <div v-if="showTwoFactor" class="mt-2">
                        <TwoFactorAuthenticationForm
                            :requires-confirmation="confirmsTwoFactorAuthentication"
                        />
                    </div>
                </div>
<!-- 
                <div class="bg-white rounded-xl shadow-sm p-4 mb-2 ring-1 ring-gray-100">
                    <div
                        class="flex justify-between items-center cursor-pointer transition-all duration-200 hover:bg-gray-50 px-4 py-2 rounded-lg -mx-4"
                        @click="toggleSection('sessions')"
                    >
                        <h3 class="font-semibold text-lg text-gray-800">
                            Sesi Peramban
                        </h3>
                        <span class="text-gray-400 transform transition-transform" :class="{'rotate-90': showSessions}">▶</span>
                    </div>
                    <div v-if="showSessions" class="mt-4">
                        <LogoutOtherBrowserSessionsForm :sessions="sessions" />
                    </div>
                </div> -->

                <template v-if="$page.props.jetstream.hasAccountDeletionFeatures">
                    <div class="bg-white rounded-xl shadow-sm p-2 ring-1 ring-gray-100">
                        <div
                            class="flex justify-between items-center cursor-pointer transition-all duration-200 hover:bg-gray-50 px-4 py-2 rounded-lg -mx-4"
                            @click="toggleSection('delete')"
                        >
                            <h3 class="font-semibold text-lg text-gray-800">
                                Hapus Akun
                            </h3>
                            <span class="text-gray-400 transform transition-transform" :class="{'rotate-90': showDelete}">▶</span>
                        </div>
                        <div v-if="showDelete" class="mt-2">
                            <DeleteUserForm />
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Transisi untuk animasi pembukaan/penutupan */
.transition-all {
    transition: all 0.3s ease-in-out;
}
.rotate-90 {
    transform: rotate(90deg);
}
</style>