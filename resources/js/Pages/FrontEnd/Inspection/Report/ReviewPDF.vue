<script setup>
import { computed } from 'vue';

const props = defineProps({
    inspection: Object,
    inspection_points: Array,
    coverImage: Object,
});

// Mengelompokkan point berdasarkan nama komponen
const groupedPoints = computed(() => {
    const groups = {};
    props.inspection_points.forEach((point) => {
        const comp = point.component?.name ?? 'Tanpa Komponen';
        if (!groups[comp]) {
            groups[comp] = [];
        }
        groups[comp].push(point);
    });
    return groups;
});

// Fungsi untuk menentukan kelas CSS berdasarkan status
const getStatusClass = (status) => {
    const lowerStatus = status ? status.toLowerCase() : '';
    if (lowerStatus.includes('rusak') || lowerStatus.includes('tidak baik') || lowerStatus.includes('bad')) {
        return 'status-bad';
    }
    if (lowerStatus.includes('perbaikan') || lowerStatus.includes('warning')) {
        return 'status-warning';
    }
    return 'status-good';
};
</script>

<template>
    <body>
        <div class="header">
          <!-- Tombol Download PDF -->
            <div class="mt-6">
              <a
                :href="route('inspections.download.pdf', inspection.id)"
                class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700"
              >
                Download PDF
              </a>
            </div>

            <div class="header-content">
                <template v-if="coverImage && coverImage.img_fath">
                    <img :src="coverImage.img_fath" alt="Foto Utama">
                </template>
                <template v-else>
                    <div class="image-placeholder">
                        <span>Gambar tidak tersedia</span>
                    </div>
                </template>
                <div>
                    <h2>{{ inspection.car.brand.name.toUpperCase() }} {{ inspection.car.model.name.toUpperCase() }} {{ inspection.car.type.name.toUpperCase() }}</h2>
                    <h3>{{ inspection.car.year }} {{ inspection.car.engine_size }} CC {{ inspection.car.fuel_type }} ({{ inspection.car.model.period ?? '' }})</h3>
                </div>
            </div>

            <div class="car-info">
                <table>
                    <tbody>
                        <tr>
                            <td>Nomor Polisi</td>
                            <td>{{ inspection.plate_number }}</td>
                        </tr>
                        <tr>
                            <td>Merek</td>
                            <td>{{ inspection.car.brand.name }}</td>
                        </tr>
                        <tr>
                            <td>Model</td>
                            <td>{{ inspection.car.model.name }}</td>
                        </tr>
                        <tr>
                            <td>Tipe</td>
                            <td>{{ inspection.car.type.name }}</td>
                        </tr>
                        <tr>
                            <td>Periode Model</td>
                            <td>{{ inspection.car.model.period ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Warna</td>
                            <td>{{ inspection.car.color }}</td>
                        </tr>
                        <tr>
                            <td>Tahun Pembuatan</td>
                            <td>{{ inspection.car.year }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <h2 class="results-title">Hasil Inspeksi</h2>

        <div v-for="(points, componentName) in groupedPoints" :key="componentName" :class="['section', 'avoid-break', { 'photo-component': componentName === 'Foto Kendaraan' }]">
            <div class="component-title">{{ componentName }}</div>

            <template v-if="componentName === 'Foto Kendaraan'">
                <div class="images">
                    <template v-for="point in points" :key="point.id">
                        <template v-if="point.images && point.images.length">
                            <img v-for="img in point.images" :key="img.id" :src="img.img_fath" alt="Foto Kendaraan" />
                        </template>
                        <template v-else>
                            <div class="image-placeholder-small">
                                <span>Gambar tidak ditemukan</span>
                            </div>
                        </template>
                    </template>
                </div>
            </template>
            <template v-else>
                <div v-for="point in points" :key="point.id" class="point avoid-break">
                    <span class="point-name">{{ point.name ?? '-' }}</span>

                    <div class="point-content">
                        <template v-if="point.results && point.results.length">
                            <span :class="['status-badge', getStatusClass(point.results[0].status)]">
                                {{ point.results[0].status ?? '' }}
                            </span>
                            <div v-if="point.results[0].note" class="point-note">
                                {{ point.results[0].note }}
                            </div>
                        </template>
                        <template v-else>
                            <span class="status-badge status-warning">Belum diperiksa</span>
                        </template>
                    </div>

                    <div v-if="point.images && point.images.length" class="images">
                        <img v-for="img in point.images" :key="img.id" :src="img.full_url" alt="image" />
                    </div>
                </div>
            </template>
        </div>
    </body>
</template>

<style scoped>
    body {
        font-family: Arial, sans-serif;
        font-size: 13px;
        color: #333;
        margin: 0;
        padding: 20px;
    }
    .header {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin-bottom: 20px;
    }
    .header-content {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    .header img {
        width: 250px;
        height: 188px;
        object-fit: cover;
        border: 1px solid #ccc;
    }
    .image-placeholder, .image-placeholder-small {
        border: 1px solid #ccc;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
    }
    .image-placeholder {
        width: 250px;
        height: 188px;
    }
    .image-placeholder-small {
        width: 120px;
        height: 90px;
    }
    .car-info {
        font-size: 13px;
        flex: 1;
    }
    .car-info h2 {
        margin: 0 0 5px 0;
        font-size: 16px;
        font-weight: bold;
    }
    .car-info h3 {
        margin: 0 0 10px 0;
        font-size: 14px;
        font-weight: normal;
        color: #555;
    }
    .car-info table {
        margin-top: 10px;
        border-collapse: collapse;
        width: 100%;
    }
    .car-info td {
        padding: 8px 0;
        vertical-align: top;
        border-bottom: 1px solid #ccc;
    }
    .car-info td:first-child {
        width: 30%;
        font-weight: bold;
    }
    .results-title {
        border-bottom: 2px solid #333;
        padding-bottom: 5px;
        margin-top: 20px;
    }
    .section {
        margin-bottom: 20px;
    }
    .component-title {
        font-weight: bold;
        font-size: 14px;
        margin-top: 15px;
        background-color: #f5f5f5;
        padding: 5px 10px;
        border-left: 3px solid #333;
    }
    .point {
        margin-left: 15px;
        margin-bottom: 10px;
        padding: 5px 0;
        border-bottom: 1px dotted #eee;
    }
    .point-name {
        display: inline-block;
        min-width: 150px;
        font-weight: bold;
        vertical-align: top;
    }
    .point-content {
        display: inline-block;
        width: calc(100% - 170px);
        vertical-align: top;
    }
    .point-note {
        margin: 5px 0;
        font-style: italic;
        color: #555;
    }
    .images {
        display: flex;
        flex-wrap: wrap;
        margin-top: 5px;
        gap: 10px;
    }
    .images img {
        width: 120px;
        height: 90px;
        object-fit: cover;
        border: 1px solid #ddd;
        border-radius: 3px;
    }
    .photo-component .images {
        margin-left: 0;
        justify-content: flex-start;
        gap: 15px;
    }
    .photo-component .images img {
        width: 45%;
        max-width: 300px;
        height: auto;
        min-height: 180px;
        margin: 0;
        flex: 1 1 calc(50% - 15px);
    }
    .photo-component .point {
        display: none;
    }
    .status-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 12px;
        margin-right: 8px;
    }
    .status-good {
        background-color: #d4edda;
        color: #155724;
    }
    .status-bad {
        background-color: #f8d7da;
        color: #721c24;
    }
    .status-warning {
        background-color: #fff3cd;
        color: #856404;
    }
</style>

