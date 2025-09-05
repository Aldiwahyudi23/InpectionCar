<template>
    <AppLayout title="Buat Inspeksi Baru">
        <Head title="Coordinator Dashboard" />
        <!-- Kontainer utama untuk konten halaman -->
        <div class="flex-1 overflow-y-auto">
            <!-- Header -->
            <header class="bg-white shadow-md p-4 md:p-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Dashboard Coordinator</h1>
                <div class="flex items-center space-x-4 text-sm text-gray-600">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Region: {{ region.name }}</span>
                </div>
            </header>

            <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                <!-- Filter Section -->
                <div class="px-4 sm:px-0 mb-6">
                    <div class="bg-white overflow-hidden shadow-lg rounded-2xl p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Filter Inspections</h3>
                        <!-- Container untuk Status & Date Range -->
                        <div class="grid grid-cols-2 sm:grid-cols-2 gap-4 mb-4">
                            <!-- Status Filter -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select 
                                    id="status" 
                                    v-model="filters.status"
                                    @change="updateFilters"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                >
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <!-- Date Range Filter -->
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700">Date Range</label>
                                <select 
                                    id="date" 
                                    v-model="filters.dateRange"
                                    @change="updateFilters"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                >
                                    <option value="all">All Time</option>
                                    <option value="today">Hari Ini</option>
                                    <option value="week">Minggu Ini</option>
                                    <option value="month">Bulan Ini</option>
                                </select>
                            </div>
                        </div>
                        <!-- Search Input (Full Width) -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                            <input 
                                type="text" 
                                id="search" 
                                v-model="filters.search"
                                @input="debouncedUpdateFilters"
                                placeholder="Search by car or inspector..."
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            >
                        </div>
                    </div>
                </div>

                <!-- Stats Overview -->
                <div class="px-4 sm:px-0 mb-6">
                    <div class="flex flex-col gap-5">
                        <!-- Total Inspections Card (Full Width) -->
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white p-6 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                            <dt class="text-sm font-medium opacity-80">Total Inspections</dt>
                            <dd class="mt-1 text-3xl font-semibold">{{ stats.total }}</dd>
                        </div>
                        <!-- Other Stats Cards (Side-by-Side) -->
                        <div class="grid grid-cols-3 sm:grid-cols-3 gap-4">
                            <div class="bg-white p-6 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                                <dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
                                <dd class="mt-1 text-2xl font-semibold text-yellow-600">{{ stats.pending }}</dd>
                            </div>
                            <div class="bg-white p-6 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                                <dt class="text-sm font-medium text-gray-500 truncate">In Progress</dt>
                                <dd class="mt-1 text-2xl font-semibold text-blue-600">{{ stats.in_progress }}</dd>
                            </div>
                            <div class="bg-white p-6 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                                <dt class="text-sm font-medium text-gray-500 truncate">Completed</dt>
                                <dd class="mt-1 text-2xl font-semibold text-green-600">{{ stats.completed }}</dd>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inspections Table -->
                <div class="px-4 sm:px-0">
                    <div class="bg-white shadow-lg overflow-hidden rounded-2xl">
                        <div class="flex flex-col">
                            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                                    <div class="shadow overflow-hidden border-b border-gray-200 rounded-xl">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Car Details
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Inspector
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Inspection Date
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Status
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Actions
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <tr v-for="inspection in inspections.data" :key="inspection.id">
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="flex items-center">
                                                            <div class="ml-4">
                                                                <div class="text-sm font-medium text-gray-900">{{ inspection.car_name }}</div>
                                                                <div class="text-sm text-gray-500">{{ inspection.plate_number }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm text-gray-900">{{ inspection.user.name }}</div>
                                                        <div class="text-sm text-gray-500">{{ inspection.user.email }}</div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm text-gray-900">{{ formatDate(inspection.inspection_date) }}</div>
                                                        <div class="text-sm text-gray-500">{{ formatTime(inspection.inspection_date) }}</div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span :class="`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass(inspection.status)}`">
                                                            {{ formatStatus(inspection.status) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <Link :href="route('coordinator.inspections.show', inspection.id)" class="text-blue-600 hover:text-blue-900 mr-3">View</Link>
                                                        <button v-if="inspection.status === 'pending'" @click="assignInspection(inspection.id)" class="text-green-600 hover:text-green-900">Assign</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Pagination -->
                                    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 mt-4">
                                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                            <div>
                                                <p class="text-sm text-gray-700">
                                                    Showing
                                                    <span class="font-medium">{{ inspections.from }}</span>
                                                    to
                                                    <span class="font-medium">{{ inspections.to }}</span>
                                                    of
                                                    <span class="font-medium">{{ inspections.total }}</span>
                                                    results
                                                </p>
                                            </div>
                                            <div>
                                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                                    <Link 
                                                        v-for="link in inspections.links"
                                                        :key="link.label"
                                                        :href="link.url || '#'"
                                                        :class="`relative inline-flex items-center px-4 py-2 border text-sm font-medium ${link.active ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'} ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}`"
                                                        v-html="link.label"
                                                        preserve-state
                                                    >
                                                    </Link>
                                                </nav>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, watch, reactive, computed, onMounted } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import AppLayout from '@/Layouts/AppLayout.vue';

// Get props from Inertia
const props = defineProps({
    inspections: Object,
    filters: Object,
    stats: Object,
    region: Object
});

// Filters
const filters = reactive({
    status: props.filters.status || '',
    dateRange: props.filters.dateRange || 'all',
    search: props.filters.search || ''
});

// Debounced search function
const debouncedUpdateFilters = debounce(() => {
    updateFilters();
}, 500);

// Update filters and reload page
const updateFilters = () => {
    router.get(route('coordinator.inspections.index'), filters, {
        preserveState: true,
        replace: true
    });
};

// Format date
const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

// Format time
const formatTime = (dateString) => {
    const options = { hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleTimeString('id-ID', options);
};

// Format status
const formatStatus = (status) => {
    const statusMap = {
        'pending': 'Pending',
        'in_progress': 'In Progress',
        'completed': 'Completed',
        'rejected': 'Rejected'
    };
    return statusMap[status] || status;
};

// Status class
const statusClass = (status) => {
    const classMap = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'in_progress': 'bg-blue-100 text-blue-800',
        'completed': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800'
    };
    return classMap[status] || 'bg-gray-100 text-gray-800';
};

// Assign inspection
const assignInspection = async (id) => {
    try {
        await router.post(route('coordinator.inspections.assign', id));
        // Refresh data
        router.reload({ only: ['inspections', 'stats'] });
    } catch (error) {
        console.error('Error assigning inspection:', error);
    }
};
</script>
