<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import PWAInstallButton from '@/Components/PWAInstallButton.vue';
import FlashMessage from '@/Components/FlashMessage.vue';

defineProps({
    title: String,
});

const isLoading = ref(false)

onMounted(() => {
  // event global Inertia
  router.on("start", () => {
    isLoading.value = true
  })
  router.on("finish", () => {
    isLoading.value = false
  })
  router.on("error", () => {
    isLoading.value = false
  })
})

const showProfileDropdown = ref(false);

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
};

const toggleProfileDropdown = () => {
    showProfileDropdown.value = !showProfileDropdown.value;
};

// ⚠️ Floating Warning untuk layar Desktop
const showDesktopWarning = ref(false);
const screenWidth = ref(window.innerWidth);
const isClosed = ref(false);

const checkScreen = () => {
    screenWidth.value = window.innerWidth;
    if (!isClosed.value) {
        showDesktopWarning.value = screenWidth.value > 768;
    }
};

const closeWarning = () => {
    isClosed.value = true;
    showDesktopWarning.value = false;
};

onMounted(() => {
    checkScreen();
    window.addEventListener('resize', checkScreen);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', checkScreen);
});
</script>



<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
            <!-- Header -->
            <nav class="bg-gradient-to-r from-sky-600 to-indigo-700 shadow-lg fixed w-full top-0 z-50">
                <!-- <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"> -->
                <div class="w-full px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <div class="text-2xl font-bold text-white drop-shadow-md">
                                CekMobil
                            </div>
                              
                        <!-- Install Button untuk Desktop -->
                        <button
                            v-if="canInstall && !isAppInstalled"
                            @click="installApp"
                            class="hidden md:flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Install App
                        </button>
                        </div>

                        <!-- Menu Desktop -->
                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <div class="flex space-x-4">
                                <NavLink 
                                    :href="route('dashboard')" 
                                    :active="route().current('dashboard')"
                                    class="text-white hover:bg-blue-500/20 px-3 py-2 rounded-lg transition-all duration-300"
                                >
                                    <span class="font-medium">Dashboard</span>
                                </NavLink>
                                <NavLink 
                                    :href="route('job.index')" 
                                    :active="route().current('job.index')"
                                    class="text-white hover:bg-blue-500/20 px-3 py-2 rounded-lg transition-all duration-300"
                                >
                                    <span class="font-medium">Tugas</span>
                                </NavLink>
                                <NavLink 
                                    :href="route('team.index')" 
                                    :active="route().current('team.index')"
                                    class="text-white hover:bg-blue-500/20 px-3 py-2 rounded-lg transition-all duration-300"
                                >
                                    <span class="font-medium">Team</span>
                                </NavLink>
                            </div>

                            <!-- Profil dan Logout (Desktop) -->
                            <div class="ms-4 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <div class="flex items-center space-x-2 cursor-pointer p-2 rounded-lg hover:bg-blue-500/20 transition-all duration-300">
                                            <div v-if="$page.props.jetstream.managesProfilePhotos" class="flex items-center">
                                                <img class="size-8 rounded-full object-cover border-2 border-white/80" 
                                                    :src="$page.props.auth.user.profile_photo_url" 
                                                    :alt="$page.props.auth.user.name">
                                            </div>
                                            
                                            <div v-else class="flex items-center space-x-2">
                                                <div class="bg-white/20 p-2 rounded-full">
                                                    <svg class="size-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                </div>
                                            </div>
                                            
                                            <span class="text-white font-medium text-sm hidden md:block">
                                                {{ $page.props.auth.user.name }}
                                            </span>
                                            
                                            <svg class="size-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                            </svg>
                                        </div>
                                    </template>

                                    <template #content>
                                        <!-- Account Management -->
                                        <div class="block px-4 py-2 text-xs text-gray-500 font-semibold">
                                            Kelola Akun
                                        </div>

                                        <DropdownLink :href="route('profile.show')" class="hover:bg-blue-50">
                                            <div class="flex items-center space-x-2">
                                                <svg class="size-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                <span>Profil</span>
                                            </div>
                                        </DropdownLink>

                                        <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')" class="hover:bg-blue-50">
                                            <div class="flex items-center space-x-2">
                                                <svg class="size-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                                </svg>
                                                <span>API Tokens</span>
                                            </div>
                                        </DropdownLink>

                                        <div class="border-t border-gray-200" />

                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button" class="hover:bg-red-50 text-red-600">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                    </svg>
                                                    <span>Log Out</span>
                                                </div>
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger Menu (Mobile) -->
                        <div class="flex items-center sm:hidden">
                            <div class="relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <div class="flex items-center space-x-2 cursor-pointer p-2 rounded-lg hover:bg-blue-500/20 transition-all duration-300">
                                            <div v-if="$page.props.jetstream.managesProfilePhotos">
                                                <img class="size-8 rounded-full object-cover border-2 border-white/80" 
                                                    :src="$page.props.auth.user.profile_photo_url" 
                                                    :alt="$page.props.auth.user.name">
                                            </div>
                                            <div v-else class="bg-white/20 p-2 rounded-full">
                                                <svg class="size-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                        </div>
                                    </template>

                                    <template #content>
                                        <!-- Account Management -->
                                        <div class="block px-4 py-2 text-xs text-gray-500 font-semibold">
                                            Kelola Akun
                                        </div>

                                        <DropdownLink :href="route('profile.show')" class="hover:bg-blue-50">
                                            <div class="flex items-center space-x-2">
                                                <svg class="size-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                <span>Profil</span>
                                            </div>
                                        </DropdownLink>

                                        <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')" class="hover:bg-blue-50">
                                            <div class="flex items-center space-x-2">
                                                <svg class="size-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                                </svg>
                                                <span>API Tokens</span>
                                            </div>
                                        </DropdownLink>

                                        <div class="border-t border-gray-200" />

                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button" class="hover:bg-red-50 text-red-600">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                    </svg>
                                                    <span>Log Out</span>
                                                </div>
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Mobile Bottom Navigation -->
            <nav class="md:hidden fixed bottom-0 w-full bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg z-50 rounded-t-2xl">
                <div class="flex justify-around items-center py-2">
                    <!-- Home -->
                    <Link
                        :href="route('dashboard')"
                        class="flex flex-col items-center transition-all duration-300 transform hover:scale-110"
                        :class="{ 
                            'text-white': $page.url === '/dashboard',
                            'text-blue-200': $page.url !== '/dashboard'
                        }"
                    >
                        <div class="p-2 rounded-lg" :class="{ 'bg-white/20': $page.url === '/dashboard' }">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </div>
                        <span class="text-xs mt-1 font-medium">Home</span>
                    </Link>

                    <!-- Tugas -->
                    <Link
                        :href="route('job.index')"
                        class="flex flex-col items-center transition-all duration-300 transform hover:scale-110"
                        :class="{ 
                            'text-white': $page.url === '/job',
                            'text-blue-200': $page.url !== '/job'
                        }"
                    >
                        <div class="p-2 rounded-lg" :class="{ 'bg-white/20': $page.url === '/job' }">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V7a2 2 0 012-2h3.5l1-1h2l1 1H17a2 2 0 012 2v12a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="text-xs mt-1 font-medium">Tugas</span>
                    </Link>

                    <!-- Team -->
                    <Link
                        :href="route('team.index')"
                        class="flex flex-col items-center transition-all duration-300 transform hover:scale-110"
                        :class="{ 
                            'text-white': $page.url === '/team',
                            'text-blue-200': $page.url !== '/team'
                        }"
                    >
                        <div class="p-2 rounded-lg" :class="{ 'bg-white/20': $page.url === '/team' }">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <span class="text-xs mt-1 font-medium">Team</span>
                    </Link>
                </div>
            </nav>
            
              <!-- Loader Global -->
    <div 
      v-if="isLoading" 
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999]"
    >
      <div class="w-16 h-16 border-4 border-white border-t-transparent rounded-full animate-spin"></div>
    </div>
    
            <!-- Page Content -->
            <main class="pt-14 pb-20 md:pb-0">
                <!-- <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6"> -->
                    <slot />
                <!-- </div> -->
                  <FlashMessage />
            </main>
            <!-- Floating Install Button untuk Mobile -->
            <PWAInstallButton />

               <!-- ⚠️ Floating Warning (Desktop) -->
            <div
                v-if="showDesktopWarning"
                class="fixed bottom-4 right-4 bg-yellow-100 text-yellow-800 border border-yellow-400 rounded-lg px-4 py-3 shadow-lg z-[1000] max-w-sm"
            >
                <div class="flex justify-between items-start space-x-2">
                    <div>
                        <strong class="block font-semibold">⚠ Tampilan Desktop</strong>
                        <span class="text-xs leading-snug">
                            Halaman ini dirancang untuk tampilan <b>Mobile</b>.<br>
                            Lebar layar Anda saat ini: <b>{{ screenWidth }}px</b>.<br>
                            Tampilan mungkin sedikit berbeda atau tidak sepenuhnya responsif.
                        </span>
                    </div>
                    <button @click="closeWarning" class="text-yellow-800 hover:text-yellow-600 text-lg font-bold leading-none">
                        ×
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Smooth transitions */
.nav-link-enter-active,
.nav-link-leave-active {
    transition: all 0.3s ease;
}

.nav-link-enter-from,
.nav-link-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

/* Custom scrollbar for dropdown */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>

<!-- warna dasar web -->

<!-- bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white -->