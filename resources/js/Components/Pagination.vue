<template>
    <div v-if="links.length > 3" class="flex items-center justify-between border-t border-gray-200 px-4 py-3 sm:px-6">
        <div class="flex flex-1 justify-between sm:hidden">
            <button 
                :disabled="!prevPageUrl"
                @click="goToPage(prevPageUrl)"
                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                :class="{ 'opacity-50 cursor-not-allowed': !prevPageUrl }"
            >
                Previous
            </button>
            <button 
                :disabled="!nextPageUrl"
                @click="goToPage(nextPageUrl)"
                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                :class="{ 'opacity-50 cursor-not-allowed': !nextPageUrl }"
            >
                Next
            </button>
        </div>
        
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing <span class="font-medium">{{ from }}</span> to 
                    <span class="font-medium">{{ to }}</span> of 
                    <span class="font-medium">{{ total }}</span> results
                </p>
            </div>
            <div>
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                    <!-- Previous -->
                    <button 
                        :disabled="!prevPageUrl"
                        @click="goToPage(prevPageUrl)"
                        class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                        :class="{ 'opacity-50 cursor-not-allowed': !prevPageUrl }"
                    >
                        <span class="sr-only">Previous</span>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <!-- Page numbers -->
                    <template v-for="(link, index) in links" :key="index">
                        <button 
                            v-if="link.url && isNumeric(link.label)"
                            @click="goToPage(link.url)"
                            class="relative inline-flex items-center px-4 py-2 text-sm font-semibold"
                            :class="{
                                'z-10 bg-indigo-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600': link.active,
                                'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50': !link.active
                            }"
                        >
                            {{ link.label }}
                        </button>
                        
                        <span 
                            v-else-if="link.label.includes('...')"
                            class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700"
                        >
                            ...
                        </span>
                    </template>

                    <!-- Next -->
                    <button 
                        :disabled="!nextPageUrl"
                        @click="goToPage(nextPageUrl)"
                        class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                        :class="{ 'opacity-50 cursor-not-allowed': !nextPageUrl }"
                    >
                        <span class="sr-only">Next</span>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    links: Array,
    from: Number,
    to: Number,
    total: Number
});

const prevPageUrl = computed(() => {
    return props.links[0]?.url || null;
});

const nextPageUrl = computed(() => {
    return props.links[props.links.length - 1]?.url || null;
});

const isNumeric = (str) => {
    return /^\d+$/.test(str);
};

const goToPage = (url) => {
    if (!url) return;
    router.get(url, {}, { preserveState: true, preserveScroll: true });
};
</script>