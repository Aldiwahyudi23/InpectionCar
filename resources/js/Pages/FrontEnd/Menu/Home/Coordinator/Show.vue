<template>
    <AppLayout>
        <Head :title="`Detail Inspeksi - ${inspection.car ? `${inspection.car.brand.name} ${inspection.car.model.name}` : inspection.car_name}`" />

        <div class="flex-1 overflow-y-auto bg-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-4">

                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Detail Inspeksi</h3>
                            <p class="text-sm text-gray-500">Informasi lengkap tentang inspeksi kendaraan.</p>
                        </div>
                        <div class="mt-4 sm:mt-0">
                            <span :class="`px-4 py-2 inline-flex text-sm leading-5 font-bold rounded-full ${statusClass(inspection.status)}`">
                                {{ formatStatus(inspection.status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-if="inspection.car" class="bg-white rounded-xl shadow-md p-4">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Informasi Kendaraan</h4>
                    <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-4">
                        <img class="h-24 w-24 rounded-lg object-cover shadow-md flex-shrink-0" :src="inspection.car.image || 'https://placehold.co/100x100/22c55e/ffffff?text=Mobil'" :alt="inspection.car.brand.name">
                        <div class="text-center sm:text-left">
                            <div class="text-xl font-bold text-gray-900">
                                {{ inspection.car.brand.name }} {{ inspection.car.model.name }}
                            </div>
                            <div class="text-sm text-gray-600">
                                {{ inspection.car.type.name }} | {{ inspection.car.year }}
                            </div>
                            <div class="text-sm text-gray-500 mt-1">
                                Plat Nomor: <span class="font-medium text-gray-800">{{ inspection.plate_number }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-y-4 gap-x-6 mt-6 border-t pt-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500">No. Mesin</label>
                            <p class="mt-1 text-sm text-gray-900">{{ inspection.nosin || '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">No. Rangka</label>
                            <p class="mt-1 text-sm text-gray-900">{{ inspection.noka || '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Warna</label>
                            <p class="mt-1 text-sm text-gray-900">{{ inspection.color || '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Transmisi</label>
                            <p class="mt-1 text-sm text-gray-900">{{ inspection.car.transmission }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Bahan Bakar</label>
                            <p class="mt-1 text-sm text-gray-900">{{ inspection.car.fuel_type }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">CC</label>
                            <p class="mt-1 text-sm text-gray-900">{{ inspection.car.cc }} cc</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Periode Produksi</label>
                            <p class="mt-1 text-sm text-gray-900">{{ inspection.car.production_period }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Odometer</label>
                            <p class="mt-1 text-sm text-gray-900">{{ inspection.km ? `${inspection.km} km` : 'Belum tercatat' }}</p>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white rounded-xl shadow-md p-4">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Informasi Kendaraan</h4>
                    <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-4">
                        <img class="h-24 w-24 rounded-lg object-cover shadow-md flex-shrink-0" src="https://placehold.co/100x100/22c55e/ffffff?text=Mobil" alt="Mobil">
                        <div class="text-center sm:text-left">
                            <div class="text-xl font-bold text-gray-900">{{ inspection.car_name }}</div>
                            <div class="text-sm text-gray-500 mt-1">
                                Plat Nomor: <span class="font-medium text-gray-800">{{ inspection.plate_number }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-y-4 gap-x-6 mt-6 border-t pt-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500">No. Mesin</label>
                            <p class="mt-1 text-sm text-gray-900">{{ inspection.nosin || '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">No. Rangka</label>
                            <p class="mt-1 text-sm text-gray-900">{{ inspection.noka || '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Warna</label>
                            <p class="mt-1 text-sm text-gray-900">{{ inspection.color || '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Odometer</label>
                            <p class="mt-1 text-sm text-gray-900">{{ inspection.km ? `${inspection.km} km` : 'Belum tercatat' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6 relative">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Informasi Inspektur</h4>
                    
                    <div class="flex items-center space-x-4" v-if="inspection.user">
                        <img class="h-16 w-16 rounded-full object-cover shadow flex-shrink-0" :src="inspection.user.profile_photo_url || 'https://placehold.co/48x48/22c55e/ffffff?text=USR'" :alt="inspection.user.name">
                        
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-gray-900">{{ inspection.user.name }}</div>
                            <div class="text-sm text-gray-500">{{ inspection.user.email }}</div>
                            <div class="text-sm text-gray-500">{{ inspection.user.numberPhone }}</div>
                        </div>
                    </div>
                    <div v-else class="text-gray-500 italic">Belum ada inspektur yang ditugaskan.</div>

                    <a
                        v-if="inspection.user && inspection.user.numberPhone"
                        :href="`https://wa.me/62${inspection.user.numberPhone}`"
                        target="_blank"
                        class="absolute bottom-6 right-6 text-green-500 hover:text-green-600 transition-colors"
                        aria-label="Hubungi Inspektur via WhatsApp"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12.04 2.167c-5.462 0-9.917 4.455-9.917 9.916 0 1.583.376 3.107 1.077 4.485l-1.161 4.24 4.321-1.139a9.854 9.854 0 0 0 4.298 1.056c5.46 0 9.914-4.453 9.914-9.913s-4.454-9.915-9.914-9.915zm4.84 14.73c-.15.223-.523.292-.79.358-.266.066-.62.099-.967.126-.347.027-1.127.054-2.147-.393-1.02-.448-1.742-1.282-2.13-1.898-.386-.615-.812-1.637-.456-2.585.356-.948 1.144-1.258 1.393-1.464.25-.206.536-.292.834-.292.3.001.594-.099.86-.099.266 0 .47.009.682.045.212.036.467.247.66.757.2.51.688 1.637.747 1.765.059.128.094.275.028.468-.066.193-.362.593-.523.702-.16.11-.322.22-.48.329-.158.109-.323.238-.426.377-.103.139-.214.33-.06.6.155.27.425.432.748.552.324.12.656.168.99.25.334.08.665.178.966.262.3.084.577.162.77.25.192.088.356.168.487.24.131.071.257.147.34.19.083.04.168.105.28.188.112.083.272.253.377.425.105.171.18.34.25.503.07.163.14.33.19.5.05.173.085.34.103.5.018.16.035.32.035.597 0 .167-.008.31-.027.442-.02.132-.045.242-.08.332-.034.09-.07.167-.105.23-.035.063-.122.14-.2.203-.078.063-.173.123-.28.188-.108.065-.25.127-.4.188-.15.061-.31.115-.487.168z"/>
                        </svg>
                    </a>
                </div>

                <div class="bg-white rounded-xl shadow-md p-4" v-if="inspection.notes">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Catatan Tambahan</h4>
                    <p class="mt-1 text-sm text-gray-900 leading-relaxed whitespace-pre-wrap">
                        <div v-html="inspection.notes || '-'"></div>
                    </p>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-4">
                    <h2 class="text-lg font-semibold mb-4">Riwayat Proses</h2>
                    <div v-if="inspection.logs.length > 0" class="relative border-l-2 border-gray-200 pl-6">
                        <div
                            v-for="log in [...inspection.logs].sort((a, b) => new Date(a.created_at) - new Date(b.created_at))"
                            :key="log.id"
                            class="mb-6 relative"
                        >
                            <div class="absolute -left-[11px] top-1 w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow"></div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-sm text-gray-900">
                                    <span class="font-bold">{{ log.user?.name ?? 'Sistem' }}</span> {{ log.description }}
                                </p>
                                <small class="text-gray-500">{{ formatDateTime(log.created_at) }}</small>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-gray-500 text-sm italic">Belum ada riwayat proses yang tersedia.</div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6 flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                    <div class="text-sm text-gray-500">
                        Terakhir diperbarui: <span class="font-medium text-gray-700">{{ formatDateTime(inspection.updated_at) }}</span>
                    </div>
                    <div class="flex flex-wrap justify-center sm:justify-end gap-3">
                        <button
                            v-if="inspection.status === 'draft' || inspection.status === 'pending'"
                            @click="showAssignModal = true"
                            class="btn-primary bg-green-600 hover:bg-green-700"
                        >
                            Tugaskan Inspektur
                        </button>
                        <button
                            v-if="inspection.status === 'pending_review'"
                            @click="handleAction('approve')"
                            class="btn-primary bg-green-600 hover:bg-green-700"
                        >
                            Setujui
                        </button>
                        <button
                            v-if="inspection.status === 'pending_review'"
                            @click="handleAction('revision')"
                            class="btn-primary bg-orange-600 hover:bg-orange-700"
                        >
                            Minta Revisi
                        </button>
                        <a
                            v-if="inspection.file"
                            :href="route('inspections.download.approved.pdf', props.encryptedIds)"
                            target="_blank"
                            class="btn-primary bg-blue-600 hover:bg-blue-700"
                        >
                            Unduh PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showActionModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 py-6 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Konfirmasi Aksi</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">{{ modalMessage }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="confirmAction" :disabled="form.processing" class="btn-primary bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed sm:ml-3 sm:w-auto">
                            <span v-if="form.processing">Memproses...</span>
                            <span v-else>Ya, Lanjutkan</span>
                        </button>
                        <button type="button" @click="showActionModal = false" class="mt-3 sm:mt-0 btn-primary bg-gray-300 hover:bg-gray-400 text-gray-800">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showAssignModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 py-6 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 flex items-center space-x-3 rounded-t-2xl">
                        <div class="bg-white/20 p-2 rounded-full">
                            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1a9 9 0 1118 0 9 9 0 01-18 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white" id="modal-title">Tugaskan Inspektur</h3>
                            <p class="text-blue-100 text-sm mt-1">Pilih inspektur dan tentukan jadwal inspeksi</p>
                        </div>
                    </div>

                    <div class="bg-white px-6 py-6">
                        <form @submit.prevent="assignInspector" class="space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3" for="inspector-select">
                                    <span class="flex items-center">
                                        <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Pilih Inspektur
                                    </span>
                                </label>
                                <select 
                                    v-model="form.inspector_id" 
                                    id="inspector-select" 
                                    class="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                                    :class="{ 'border-red-500': form.errors.inspector_id }"
                                >
                                    <option value="" disabled selected>-- Pilih Inspektur --</option>
                                    <option v-for="user in inspectors" :key="user.id" :value="user.id">
                                        {{ user.name }} - {{ user.email }}
                                    </option>
                                </select>
                                <div v-if="form.errors.inspector_id" class="text-sm text-red-600 mt-2 flex items-center">
                                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ form.errors.inspector_id }}
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-3" for="schedule-date">
                                        <span class="flex items-center">
                                            <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h.01M16 11h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Tanggal Inspeksi
                                        </span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="date"
                                            id="schedule-date"
                                            v-model="form.scheduled_at_date"
                                            :min="new Date().toISOString().split('T')[0]"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                            :class="{ 'border-red-500': form.errors.scheduled_at_date }"
                                            required
                                        >
                                    </div>
                                    <div v-if="form.errors.scheduled_at_date" class="text-sm text-red-600 mt-2 flex items-center">
                                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ form.errors.scheduled_at_date }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-3" for="schedule-time">
                                        <span class="flex items-center">
                                            <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Waktu Inspeksi
                                        </span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="time"
                                            id="schedule-time"
                                            v-model="form.scheduled_at_time"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                            :class="{ 'border-red-500': form.errors.scheduled_at_time }"
                                            required
                                        >
                                    </div>
                                    <div v-if="form.errors.scheduled_at_time" class="text-sm text-red-600 mt-2 flex items-center">
                                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ form.errors.scheduled_at_time }}
                                    </div>
                                </div>
                            </div>

                            <div v-if="selectedInspector && form.scheduled_at_date && form.scheduled_at_time" 
                                class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                <h4 class="font-semibold text-blue-800 mb-2">Rincian Penugasan:</h4>
                                <p class="text-sm text-blue-700">
                                    Inspektur: <strong>{{ selectedInspector.name }}</strong><br>
                                    Tanggal: <strong>{{ formatDate(form.scheduled_at_date) }}</strong><br>
                                    Waktu: <strong>{{ form.scheduled_at_time }}</strong>
                                </p>
                            </div>
                        </form>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 rounded-b-2xl">
                        <div class="flex flex-col sm:flex-row-reverse sm:space-x-4 sm:space-x-reverse">
                            <button 
                                type="button" 
                                @click="assignInspector" 
                                :disabled="form.processing || !form.inspector_id || !form.scheduled_at_date || !form.scheduled_at_time"
                                class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold rounded-xl shadow-lg hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none hover:scale-105"
                            >
                                <span v-if="form.processing" class="flex items-center justify-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Menugaskan...
                                </span>
                                <span v-else class="flex items-center">
                                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Tugaskan Inspektur
                                </span>
                            </button>
                            
                            <button 
                                type="button" 
                                @click="showAssignModal = false" 
                                :disabled="form.processing"
                                class="w-full sm:w-auto px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl border border-gray-300 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 mt-3 sm:mt-0 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span class="flex items-center">
                                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Batal
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 600;
    font-size: 0.75rem;
    line-height: 1;
    color: white;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}
</style>

<script setup>
import { ref, defineProps, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    inspection: {
        type: Object,
        required: true
    },
    inspectors: {
        type: Array,
        default: () => []
    },
    encryptedIds: Object
});

const form = useForm({
    inspector_id: '',
    scheduled_at_date: '',
    scheduled_at_time: '',
});

const showActionModal = ref(false);
const showAssignModal = ref(false);
const modalMessage = ref('');
const modalAction = ref('');

const selectedInspector = computed(() => {
    return props.inspectors.find(inspector => inspector.id === form.inspector_id);
});

const formatDateTime = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('id-ID', options);
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('id-ID', options);
};

const formatStatus = (status) => {
    const statusMap = {
        'draft': 'Dibuat',
        'in_progress': 'Dalam Proses',
        'pending': 'Tertunda',
        'pending_review': 'Menunggu Tinjauan',
        'approved': 'Disetujui',
        'rejected': 'Ditolak',
        'revision': 'Revisi',
        'completed': 'Selesai',
        'cancelled': 'Dibatalkan'
    };
    return statusMap[status] || status;
};

const statusClass = (status) => {
    const classMap = {
        'draft': 'bg-gray-100 text-gray-800',
        'in_progress': 'bg-blue-100 text-blue-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'pending_review': 'bg-purple-100 text-purple-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
        'revision': 'bg-orange-100 text-orange-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return classMap[status] || 'bg-gray-100 text-gray-800';
};

const handleAction = (action) => {
    let message = '';
    if (action === 'approve') {
        message = 'Apakah Anda yakin ingin menyetujui inspeksi ini?';
    } else if (action === 'revision') {
        message = 'Apakah Anda yakin ingin meminta revisi untuk inspeksi ini?';
    } else if (action === 'reject') {
        message = 'Apakah Anda yakin ingin menolak inspeksi ini?';
    }
    modalMessage.value = message;
    modalAction.value = action;
    showActionModal.value = true;
};

const confirmAction = () => {
    router.post(route('coordinator.inspections.update-status', props.encryptedIds), {
        status: modalAction.value
    }, {
        onSuccess: () => {
            showActionModal.value = false;
        }
    });
};

const assignInspector = () => {
    form.post(route('coordinator.inspections.assign', props.encryptedIds), {
        inspector_id: form.inspector_id,
        scheduled_at_date: form.scheduled_at_date,
        scheduled_at_time: form.scheduled_at_time,
    }, {
        onSuccess: () => {
            showAssignModal.value = false;
        }
    });
};

const closeAssignModal = () => {
    showAssignModal.value = true;
    form.reset();
};
</script>

<style scoped>
/* Smooth transitions for modal */
.modal-enter-active, .modal-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.modal-enter-from, .modal-leave-to {
    opacity: 0;
    transform: scale(0.95) translateY(-20px);
}

/* Custom scrollbar for select */
select {
    scrollbar-width: thin;
    scrollbar-color: #3b82f6 #f1f5f9;
}

select::-webkit-scrollbar {
    width: 6px;
}

select::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

select::-webkit-scrollbar-thumb {
    background: #3b82f6;
    border-radius: 3px;
}

select::-webkit-scrollbar-thumb:hover {
    background: #2563eb;
}
</style>