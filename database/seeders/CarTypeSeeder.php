<?php

namespace Database\Seeders;

use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CarTypeSeeder extends Seeder
{
    public function run(): void
    {
        $typesData = [
            // Toyota
            'Avanza' => [
                ['name' => 'E', 'description' => 'Base model dengan transmisi manual/AT 1.3', 'is_active' => true],
                ['name' => 'G', 'description' => 'Mid variant dengan mesin 1.5L manual/At 1.5', 'is_active' => true],
                ['name' => 'Veloz', 'description' => 'Top variant dengan fitur lengkap manual/AT 1.5', 'is_active' => true],
            ],
            'Innova' => [
                ['name' => 'G', 'description' => 'Variant dasar Innova T/MT 2.0', 'is_active' => true],
                ['name' => 'V', 'description' => 'Variant premium manual AT/MT 2.0', 'is_active' => true],
                ['name' => 'V ', 'description' => 'Diesel variant manual 2.4', 'is_active' => true],
            ],
            'Fortuner' => [
                ['name' => 'VRZ 4x2', 'description' => 'Base variant 4x2 manual/MT 2.4', 'is_active' => true],
                ['name' => 'VRZ 4x4', 'description' => '4x4 variant manual/AT 2.4', 'is_active' => true],
                ['name' => 'VRZ 4x4', 'description' => 'Top variant 2.8L 4x4 2.8', 'is_active' => true]
            ],
            'Yaris' => [
                ['name' => 'E', 'description' => 'Base model manual/at 1.2', 'is_active' => true],
                ['name' => ' G', 'description' => 'Mid variant manual/at 1,2', 'is_active' => true],
                ['name' => ' GR Sport', 'description' => 'Sport variant 1.5', 'is_active' => true]
            ],

            // Honda
            'Brio' => [
                ['name' => ' E', 'description' => 'Base model manual 1.2', 'is_active' => true],
                ['name' => ' E CVT', 'description' => 'Base model CVT 1.2', 'is_active' => true],
                ['name' => ' RS', 'description' => 'RS variant manual 1.2', 'is_active' => true],
                ['name' => 'RS CVT', 'description' => 'RS variant CVT 1.2', 'is_active' => true]
            ],
            'Jazz' => [
                ['name' => ' RS CVT', 'description' => 'RS variant CVT 1.5', 'is_active' => true],
                ['name' => 'Crosstar CVT', 'description' => 'Crosstar variant 1.5', 'is_active' => true]
            ],
            'City' => [
                ['name' => 'E CVT', 'description' => 'Base model CVT 1.5', 'is_active' => true],
                ['name' => 'RS CVT', 'description' => 'RS variant CVT 1.5', 'is_active' => true],
                ['name' => 'Hatchback RS', 'description' => 'Hatchback RS variant 1.5', 'is_active' => true]
            ],
            'Civic' => [
                ['name' => 'Turbo CVT', 'description' => 'Turbo variant 1.5', 'is_active' => true],
                ['name' => 'RS CVT', 'description' => 'RS variant', 'is_active' => true],
                ['name' => 'Type R', 'description' => 'Performance variant', 'is_active' => true]
            ],

            // Suzuki
            'Ertiga' => [
                ['name' => 'GA', 'description' => 'Base model manual/at 1.5', 'is_active' => true],
                ['name' => 'GL', 'description' => 'Mid variant automatic', 'is_active' => true]
            ],
            'XL7' => [
                ['name' => 'Alpha', 'description' => 'Top variant automatic 1.5', 'is_active' => true],
                ['name' => 'Beta', 'description' => 'Mid variant automatic 1.5', 'is_active' => true]
            ],
            'Ignis' => [
                ['name' => 'GL', 'description' => 'Base model manual 1.2', 'is_active' => true],
                ['name' => 'GL CVT', 'description' => 'Base model CVT 1.2', 'is_active' => true]
            ],
            'Baleno' => [
                ['name' => 'GL', 'description' => 'Base model manual 1.2', 'is_active' => true],
                ['name' => 'GL CVT', 'description' => 'Base model CVT 1.2', 'is_active' => true],
                ['name' => 'GLS CVT', 'description' => 'Premium variant CVT 1.2', 'is_active' => true]
            ],

            // Daihatsu
            'Ayla' => [
                ['name' => 'D', 'description' => 'Base model manual/AT 1.0', 'is_active' => true],
                ['name' => 'R', 'description' => 'Premium variant automatic 1.2', 'is_active' => true]
            ],
            'Sigra' => [
                ['name' => 'D', 'description' => 'Base model manual/at 1.2', 'is_active' => true],
                ['name' => 'X', 'description' => 'Premium variant automatic 1.2', 'is_active' => true]
            ],
            'Xenia' => [
                ['name' => 'R', 'description' => 'Base model manual/at 1.3', 'is_active' => true],
                ['name' => 'R Deluxe', 'description' => 'Deluxe variant automatic 1.3', 'is_active' => true]
            ],
            'Terios' => [
                ['name' => 'R', 'description' => 'Base model manual/at 1.5', 'is_active' => true],
                ['name' => 'R Luxury', 'description' => 'Luxury variant automatic', 'is_active' => true]
            ]
        ];

        foreach ($typesData as $modelName => $types) {
            $carModel = CarModel::where('name', $modelName)->first();
            
            if ($carModel) {
                // Ambil 3 type pertama dari setiap model
                $selectedTypes = array_slice($types, 0, 3);
                
                foreach ($selectedTypes as $type) {
                    CarType::firstOrCreate(
                        [
                            'car_model_id' => $carModel->id,
                            'name' => $type['name']
                        ],
                        $type
                    );
                }
            }
        }

        $this->command->info('Car types seeded successfully!');
    }
}