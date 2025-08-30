<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DataInspection\Categorie;
use App\Models\DataInspection\Component;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create default categories
        $categories = [
            ['name' => 'Mo', 'order' => '1'],
            ['name' => 'Aldi Wahyudi', 'order' => '2'],
            ['name' => 'Inspeksi 2', 'order' => '3'],
        ];

        foreach ($categories as $category) {
            Categorie::firstOrCreate(['name' => $category['name']], $category);
        }

        // Create default components
        $components = [
            ['name' => 'Dokumen', 'description' => 'Bagian luar kendaraan'],
            ['name' => 'Foto Kendaraan', 'description' => 'Dokumentasi foto kendaraan'],
            ['name' => 'Eksterior', 'description' => 'Bagian luar kendaraan'],
            ['name' => 'Interior', 'description' => 'Bagian dalam kendaraan'],
            ['name' => 'Mesin', 'description' => 'Komponen mesin kendaraan'],
            ['name' => 'Transmisi', 'description' => 'Komponen mesin kendaraan'],
            ['name' => 'Kelistrikan', 'description' => 'Sistem kelistrikan kendaraan'],
            ['name' => 'Rangka (Validasi Tabrak)', 'description' => 'Dokumentasi foto kendaraan'],
            ['name' => 'Interior (Validasi Banjir)', 'description' => 'Dokumentasi foto kendaraan'],
            ['name' => 'Kaki Kaki', 'description' => 'Dokumentasi foto kendaraan'],
        ];

        foreach ($components as $component) {
            Component::firstOrCreate(['name' => $component['name']], $component);
        }

        $this->command->info('Master data seeded successfully!');
    }
}