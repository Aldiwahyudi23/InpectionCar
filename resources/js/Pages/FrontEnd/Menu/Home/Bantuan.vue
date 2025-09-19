<template>
    <AppLayout title="Pusat Bantuan">
        <Head title="Pusat Bantuan" />
        <!-- <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 relative"> -->
        <div class="w-full px-4 sm:px-6 lg:px-6 py-6">
            <div class="space-y-4 bg-white rounded-xl shadow-md p-4 relative">
                <!-- Header dengan pencarian -->
                <div class="flex justify-between items-center mb-4">
                    <h1 class="text-xl md:text-3xl font-bold text-gray-900 text-center flex-grow">
                        Pusat Bantuan
                    </h1>
                </div>

                <div class="relative mb-4">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input
                        v-model="searchQuery"
                        type="text"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        placeholder="Cari komponen, inspection point, atau deskripsi..."
                        @input="performSearch"
                    >
                </div>

                <!-- Daftar komponen -->
                <div v-if="filteredComponents.length > 0" class="space-y-4">
                    <div 
                        v-for="component in filteredComponents" 
                        :key="component.id"
                        :ref="el => setComponentRef(component.id, el)"
                        class="bg-white rounded-xl shadow-md overflow-hidden component-item"
                    >
                        <!-- Header komponen -->
                        <div 
                            class="p-2 flex justify-between items-center cursor-pointer bg-indigo-50 hover:bg-indigo-100 transition-colors"
                            @click="toggleComponent(component.id)"
                        >
                            <h2 class="text-lg md:text-xl font-semibold text-indigo-700">{{ component.name }}</h2>
                            <svg 
                                :class="{'rotate-180': expandedComponentId === component.id}" 
                                class="h-5 w-5 text-indigo-600 transform transition-transform" 
                                fill="none" 
                                viewBox="0 0 24 24" 
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>

                        <!-- Deskripsi komponen -->
                        <div v-if="expandedComponentId === component.id" class="p-2 border-t border-gray-200">
                            <div class="text-gray-700 mb-4">
                                <div v-if="component.description && !expandedDescriptions.includes(component.id)">
                                    {{ truncateText(stripHtml(component.description), 300) }}
                                    <button 
                                        v-if="stripHtml(component.description).length > 300"
                                        @click.stop="expandDescription(component.id)"
                                        class="text-indigo-600 hover:text-indigo-800 text-sm font-medium ml-1"
                                    >
                                        Selengkapnya
                                    </button>
                                </div>
                                <div 
                                    v-if="expandedDescriptions.includes(component.id)" 
                                    class="prose max-w-none text-gray-700"
                                    v-html="component.description"
                                ></div>
                                <!-- Gambar Komponen -->
                                <div v-if="component.file_path" class="mt-4">
                                    <img
                                        :src="`/storage/${component.file_path}`"
                                        alt="Component Image"
                                        class="w-full h-auto rounded-lg shadow-md cursor-pointer"
                                        @click.stop="openLightbox(`/storage/${component.file_path}`)"
                                    />
                                </div>
                            </div>

                            <!-- Inspection points -->
                            <div v-if="hasInspectionPoints(component)" class="mt-2">
                                <h3 class="text-lg font-medium text-gray-800 mb-2">Point:</h3>
                                <div class="space-y-4">
                                    <div 
                                        v-for="point in getFilteredInspectionPoints(component)" 
                                        :key="point.id"
                                        :ref="el => setPointRef(point.id, el)"
                                        class="bg-gray-50 rounded-lg p-4 border border-gray-200 point-item"
                                    >
                                        <div class="flex justify-between items-start">
                                            <h4 class="font-medium text-gray-800">{{ point.name }}</h4>
                                            <button 
                                                @click="toggleInspectionPoint(point.id)"
                                                class="text-indigo-600 hover:text-indigo-800 text-sm font-medium"
                                            >
                                                {{ expandedPointId === point.id ? 'Sembunyikan' : 'Selengkapnya' }}
                                            </button>
                                        </div>
                                        <div 
                                            v-if="expandedPointId === point.id" 
                                            class="text-gray-600 mt-2 prose max-w-none"
                                            v-html="point.notes"
                                        ></div>
                                        <div 
                                            v-else 
                                            class="text-gray-600 mt-2"
                                        >
                                            {{ truncateText(stripHtml(point.description), 200) }}
                                        </div>
                                        <!-- Gambar Inspection Point -->
                                        <div v-if="expandedPointId === point.id && point.file_path" class="mt-2">
                                            <img
                                                :src="`/storage/${point.file_path}`"
                                                alt="Inspection Point Image"
                                                class="w-full h-auto rounded-lg shadow-md cursor-pointer"
                                                @click.stop="openLightbox(`/storage/${point.file_path}`)"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else-if="component.inspection_point && component.inspection_point.length === 0">
                                <p class="text-gray-500 italic">Tidak ada inspection points untuk komponen ini.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- State kosong -->
                <div v-else class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">Tidak ada hasil ditemukan</h3>
                    <p class="mt-1 text-gray-500">Coba dengan kata kunci pencarian yang berbeda.</p>
                </div>
            </div>
        </div>
        
        <!-- Lightbox untuk gambar full-screen -->
        <div 
            v-if="showLightbox" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-90"
            @click="closeLightbox"
        >
            <div class="relative max-w-full max-h-full flex items-center justify-center">
                <button 
                    @click.stop="closeLightbox" 
                    class="absolute top-4 right-4 z-10 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-70 transition-colors"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <img
                    :src="lightboxImageSrc"
                    alt="Full-screen image"
                    class="max-w-full max-h-full object-contain"
                />
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import debounce from 'lodash/debounce'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    components: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

const searchQuery = ref(props.filters.search || '')
const expandedComponentId = ref(null) 
const expandedPointId = ref(null) 
const expandedDescriptions = ref([])

// Refs untuk menyimpan referensi elemen DOM
const componentRefs = ref({});
const pointRefs = ref({});

const setComponentRef = (id, el) => {
    if (el) componentRefs.value[id] = el;
};
const setPointRef = (id, el) => {
    if (el) pointRefs.value[id] = el;
};

// State untuk lightbox
const showLightbox = ref(false);
const lightboxImageSrc = ref('');

// Fungsi untuk menghapus tag HTML
const stripHtml = (html) => {
    if (!html) return ''
    const tmp = document.createElement('DIV')
    tmp.innerHTML = html
    return tmp.textContent || tmp.innerText || ''
}

// Fungsi untuk memotong teks
const truncateText = (text, length) => {
    if (!text) return ''
    if (text.length <= length) return text
    return text.substring(0, length) + '...'
}

// Cek apakah komponen memiliki inspection points
const hasInspectionPoints = (component) => {
    return component.inspection_point && component.inspection_point.length > 0
}

// Dapatkan inspection points yang difilter berdasarkan pencarian
const getFilteredInspectionPoints = (component) => {
    if (!searchQuery.value || !hasInspectionPoints(component)) {
        return component.inspection_point || []
    }
    
    const query = searchQuery.value.toLowerCase()
    return component.inspection_point.filter(point => {
        return point.name.toLowerCase().includes(query) || 
               stripHtml(point.description).toLowerCase().includes(query)
    })
}

// Komponen yang difilter berdasarkan pencarian
const filteredComponents = computed(() => {
    if (!searchQuery.value) return props.components
    
    const query = searchQuery.value.toLowerCase()
    return props.components.filter(component => {
        // Cek apakah komponen cocok dengan pencarian
        const componentMatch = component.name.toLowerCase().includes(query) || 
                               stripHtml(component.description).toLowerCase().includes(query)
        
        // Cek apakah ada inspection point yang cocok dengan pencarian
        const pointMatch = hasInspectionPoints(component) && 
                           component.inspection_point.some(point => 
                               point.name.toLowerCase().includes(query) || 
                               stripHtml(point.description).toLowerCase().includes(query)
                           )
        
        return componentMatch || pointMatch
    })
})

// Toggle komponen dan scroll ke elemen
const toggleComponent = async (id) => {
    if (expandedComponentId.value === id) {
        expandedComponentId.value = null;
    } else {
        expandedComponentId.value = id;
        expandedPointId.value = null;
        await nextTick();
        const el = componentRefs.value[id];
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
}

// Toggle inspection point dan scroll ke elemen
const toggleInspectionPoint = async (id) => {
    if (expandedPointId.value === id) {
        expandedPointId.value = null;
    } else {
        expandedPointId.value = id;
        await nextTick();
        const el = pointRefs.value[id];
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
}

// Expand description
const expandDescription = (id) => {
    if (!expandedDescriptions.value.includes(id)) {
        expandedDescriptions.value.push(id)
    }
}

// Buka lightbox dengan gambar yang dipilih
const openLightbox = (imagePath) => {
    lightboxImageSrc.value = imagePath;
    showLightbox.value = true;
};

// Tutup lightbox
const closeLightbox = () => {
    showLightbox.value = false;
    lightboxImageSrc.value = '';
};

// Pencarian dengan debounce
const performSearch = debounce(() => {
    router.get('/bantuan', { search: searchQuery.value }, {
        preserveState: true,
        replace: true
    })
}, 300)

// Ekspansi otomatis komponen jika ada pencarian dan scroll
onMounted(async () => {
    if (searchQuery.value && filteredComponents.value.length > 0) {
        expandedComponentId.value = filteredComponents.value[0].id;
        await nextTick();
        const el = componentRefs.value[expandedComponentId.value];
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
})
</script>

<style>
.prose {
    max-width: none;
}
.prose p {
    margin-bottom: 1rem;
}
.prose ul, .prose ol {
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}
.prose li {
    margin-bottom: 0.5rem;
}
/* Menambahkan margin atas untuk mengimbangi fixed navbar saat scroll */
.component-item, .point-item {
    scroll-margin-top: 80px; /* Sesuaikan nilai ini dengan tinggi navbar Anda */
}
</style>
