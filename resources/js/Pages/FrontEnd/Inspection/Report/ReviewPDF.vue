<script setup>
import { computed ,defineProps} from "vue";
import { Head } from "@inertiajs/vue3";

const props = defineProps({
  inspection: Object,
  inspection_points: Array,
  coverImage: Object,
});

// Grouping points berdasarkan component.name
const groupedPoints = computed(() => {
  const groups = {};
  props.inspection_points.forEach((point) => {
    const comp = point.component?.name ?? "Tanpa Komponen";
    if (!groups[comp]) groups[comp] = [];
    groups[comp].push(point);
  });
  return groups;
});
</script>

<template>
  <Head title="Laporan Inspeksi" />

  <div class="p-6 text-sm text-gray-800">
    
    <!-- Header dengan foto utama dan data mobil -->
    <div class="flex mb-6">
      <div v-if="coverImage" class="mr-4">
       <img
          v-if="coverImage && coverImage.file_path"
          :src="`/${coverImage.image_path}`"
          alt="Foto Utama"
          class="w-64 border rounded"
        />
      </div>

      <div class="car-info">
        <h2 class="text-lg font-bold uppercase">
          {{
            inspection.car.brand?.name +
            " " +
            inspection.car.model?.name +
            " " +
            inspection.car.type?.name
          }}
        </h2>
        <h3 class="text-base text-gray-600">
          {{
            inspection.car.year +
            " " +
            inspection.car.engine_size +
            " CC " +
            inspection.car.fuel_type +
            " (" +
            (inspection.car.model?.period ?? "-") +
            ")"
          }}
        </h3>

        <table class="mt-3">
          <tbody>
            <tr>
              <td class="font-bold pr-2">Nomor Polisi</td>
              <td>{{ inspection.car.license_plate }}</td>
            </tr>
            <tr>
              <td class="font-bold pr-2">Merek</td>
              <td>{{ inspection.car.brand?.name }}</td>
            </tr>
            <tr>
              <td class="font-bold pr-2">Model</td>
              <td>{{ inspection.car.model?.name }}</td>
            </tr>
            <tr>
              <td class="font-bold pr-2">Tipe</td>
              <td>{{ inspection.car.type?.name }}</td>
            </tr>
            <tr>
              <td class="font-bold pr-2">Periode Model</td>
              <td>{{ inspection.car.model?.period ?? "-" }}</td>
            </tr>
            <tr>
              <td class="font-bold pr-2">Warna</td>
              <td>{{ inspection.car.color }}</td>
            </tr>
            <tr>
              <td class="font-bold pr-2">Tahun Pembuatan</td>
              <td>{{ inspection.car.year }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <h2 class="text-lg font-semibold mb-4">Hasil Inspeksi</h2>

    <!-- Group Komponen -->
    <div v-for="(points, componentName) in groupedPoints" :key="componentName" class="mb-6">
      <div class="font-bold text-base mb-2">{{ componentName }}</div>

      <!-- Looping point -->
      <div v-for="point in points" :key="point.id" class="ml-4 mb-4">
        <div>
          <span class="inline-block min-w-[150px]">{{ point.name ?? "-" }}</span>

          <!-- Hasil Result -->
          <span v-if="point.results && point.results.length">
            <span v-for="res in point.results" :key="res.id">
              {{ res.status ?? "" }}
              <span v-if="res.note">, {{ res.note }}</span>
            </span>
          </span>
        </div>

        <!-- Foto -->
        <div
          v-if="point.images && point.images.length"
          class="flex flex-wrap gap-2 mt-2 ml-6"
        >
          <img
            v-for="img in point.images"
            :key="img.id"
            :src="`/${img.image_path}`"
            alt="Foto"
            class="w-[18%] max-w-[120px] border rounded"
          />
        </div>
      </div>
    </div>
    <!-- Tombol Download PDF -->
    <div class="mt-6">
      <a
        :href="route('inspections.download.pdf', inspection.id)"
        class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700"
      >
        Download PDF
      </a>
    </div>
  </div>
</template>

<style scoped>
table td {
  padding: 2px 4px;
  vertical-align: top;
}


</style>
