<?php

namespace Database\Seeders;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CarModelSeeder extends Seeder
{
    public function run(): void
    {
        $modelsData = [
            'Toyota' => [
                ['name' => 'Avanza', 'is_active' => true],
                ['name' => 'Innova', 'is_active' => true],
                ['name' => 'Fortuner', 'is_active' => true],
                ['name' => 'Yaris', 'is_active' => true],
                ['name' => 'Rush', 'is_active' => true],
                ['name' => 'Hiace', 'is_active' => true],
                ['name' => 'Corolla Altis', 'is_active' => true],
                ['name' => 'Camry', 'is_active' => true]
            ],
            'Honda' => [
                ['name' => 'Brio', 'is_active' => true],
                ['name' => 'Jazz', 'is_active' => true],
                ['name' => 'City', 'is_active' => true],
                ['name' => 'Civic', 'is_active' => true],
                ['name' => 'HR-V', 'is_active' => true],
                ['name' => 'CR-V', 'is_active' => true],
                ['name' => 'Accord', 'is_active' => true],
                ['name' => 'Mobilio', 'is_active' => true]
            ],
            'Suzuki' => [
                ['name' => 'Ertiga', 'is_active' => true],
                ['name' => 'XL7', 'is_active' => true],
                ['name' => 'Ignis', 'is_active' => true],
                ['name' => 'Baleno', 'is_active' => true],
                ['name' => 'S-Presso', 'is_active' => true],
                ['name' => 'Jimny', 'is_active' => true],
                ['name' => 'Carry', 'is_active' => true],
                ['name' => 'APV', 'is_active' => true]
            ],
            'Daihatsu' => [
                ['name' => 'Ayla', 'is_active' => true],
                ['name' => 'Sigra', 'is_active' => true],
                ['name' => 'Xenia', 'is_active' => true],
                ['name' => 'Terios', 'is_active' => true],
                ['name' => 'Rocky', 'is_active' => true],
                ['name' => 'Gran Max', 'is_active' => true],
                ['name' => 'Luxio', 'is_active' => true],
                ['name' => 'Sirion', 'is_active' => true]
            ],
            'Mitsubishi' => [
                ['name' => 'Pajero Sport', 'is_active' => true],
                ['name' => 'Xpander', 'is_active' => true],
                ['name' => 'Triton', 'is_active' => true],
                ['name' => 'Outlander', 'is_active' => true],
                ['name' => 'Eclipse Cross', 'is_active' => true],
                ['name' => 'Mirage', 'is_active' => true],
                ['name' => 'L300', 'is_active' => true],
                ['name' => 'ASX', 'is_active' => true]
            ],
            'Nissan' => [
                ['name' => 'March', 'is_active' => true],
                ['name' => 'Livina', 'is_active' => true],
                ['name' => 'Serena', 'is_active' => true],
                ['name' => 'X-Trail', 'is_active' => true],
                ['name' => 'Terra', 'is_active' => true],
                ['name' => 'Navara', 'is_active' => true],
                ['name' => 'Juke', 'is_active' => true],
                ['name' => 'Kicks', 'is_active' => true]
            ],
            'Hyundai' => [
                ['name' => 'i10', 'is_active' => true],
                ['name' => 'Creta', 'is_active' => true],
                ['name' => 'Stargazer', 'is_active' => true],
                ['name' => 'Santa Fe', 'is_active' => true],
                ['name' => 'Tucson', 'is_active' => true],
                ['name' => 'Palisade', 'is_active' => true],
                ['name' => 'Ioniq', 'is_active' => true],
                ['name' => 'Kona', 'is_active' => true]
            ],
            'KIA' => [
                ['name' => 'Picanto', 'is_active' => true],
                ['name' => 'Rio', 'is_active' => true],
                ['name' => 'Seltos', 'is_active' => true],
                ['name' => 'Sonet', 'is_active' => true],
                ['name' => 'Carnival', 'is_active' => true],
                ['name' => 'Sportage', 'is_active' => true],
                ['name' => 'Sorento', 'is_active' => true],
                ['name' => 'Stonic', 'is_active' => true]
            ],
            'Wuling' => [
                ['name' => 'Almaz', 'is_active' => true],
                ['name' => 'Cortez', 'is_active' => true],
                ['name' => 'Air ev', 'is_active' => true],
                ['name' => 'Confero', 'is_active' => true],
                ['name' => 'Formo', 'is_active' => true],
                ['name' => 'Hongguang', 'is_active' => true],
                ['name' => 'Bingo', 'is_active' => true],
                ['name' => 'Astro', 'is_active' => true]
            ],
            'Mercedes-Benz' => [
                ['name' => 'A-Class', 'is_active' => true],
                ['name' => 'C-Class', 'is_active' => true],
                ['name' => 'E-Class', 'is_active' => true],
                ['name' => 'S-Class', 'is_active' => true],
                ['name' => 'GLC', 'is_active' => true],
                ['name' => 'GLE', 'is_active' => true],
                ['name' => 'GLA', 'is_active' => true],
                ['name' => 'GLB', 'is_active' => true]
            ],
            'BMW' => [
                ['name' => '1 Series', 'is_active' => true],
                ['name' => '3 Series', 'is_active' => true],
                ['name' => '5 Series', 'is_active' => true],
                ['name' => '7 Series', 'is_active' => true],
                ['name' => 'X1', 'is_active' => true],
                ['name' => 'X3', 'is_active' => true],
                ['name' => 'X5', 'is_active' => true],
                ['name' => 'X7', 'is_active' => true]
            ],
            'Audi' => [
                ['name' => 'A1', 'is_active' => true],
                ['name' => 'A3', 'is_active' => true],
                ['name' => 'A4', 'is_active' => true],
                ['name' => 'A6', 'is_active' => true],
                ['name' => 'Q2', 'is_active' => true],
                ['name' => 'Q3', 'is_active' => true],
                ['name' => 'Q5', 'is_active' => true],
                ['name' => 'Q7', 'is_active' => true]
            ]
        ];

        foreach ($modelsData as $brandName => $models) {
            $brand = Brand::where('name', $brandName)->first();
            
            if ($brand) {
                // Ambil 4 model pertama dari setiap brand
                $selectedModels = array_slice($models, 0, 4);
                
                foreach ($selectedModels as $model) {
                    CarModel::firstOrCreate(
                        [
                            'brand_id' => $brand->id,
                            'name' => $model['name']
                        ],
                        $model
                    );
                }
            }
        }

        $this->command->info('Car models seeded successfully!');
    }
}