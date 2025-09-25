<?php

namespace Database\Seeders\Brands\Mitsubishi;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class MirageSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Mitsubishi']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Mirage'
        ]);

        // Tipe untuk generasi terbaru (2012-sekarang)
        $types = [
            ['name' => 'GLS'],
            ['name' => 'GLX'],
            ['name' => 'Black Edition'],
            ['name' => 'SE'],
            ['name' => 'RALLIART']
        ];

        $configurations = [
            'GLS' => [
                'generations' => [
                    '2012-2024 (Gen 6)' => [
                        'engine' => [
                            ['cc' => 1193, 'fuel_type' => 'Gasoline', 'engine_code' => '3A92', 'transmission' => ['MT', 'CVT'], 'power' => '76 HP', 'torque' => '100 Nm']
                        ]
                    ]
                ]
            ],
            'GLX' => [
                'generations' => [
                    '2012-2024 (Gen 6)' => [
                        'engine' => [
                            ['cc' => 1193, 'fuel_type' => 'Gasoline', 'engine_code' => '3A92', 'transmission' => ['MT', 'CVT'], 'power' => '76 HP', 'torque' => '100 Nm']
                        ]
                    ]
                ]
            ],
            'Black Edition' => [
                'generations' => [
                    '2022-Now (Gen 6 Facelift)' => [
                        'engine' => [
                            ['cc' => 1193, 'fuel_type' => 'Gasoline', 'engine_code' => '3A92', 'transmission' => ['CVT'], 'power' => '78 HP', 'torque' => '100 Nm']
                        ]
                    ]
                ]
            ],
            'SE' => [
                'generations' => [
                    '2022-Now (Gen 6 Facelift)' => [
                        'engine' => [
                            ['cc' => 1193, 'fuel_type' => 'Gasoline', 'engine_code' => '3A92', 'transmission' => ['CVT'], 'power' => '78 HP', 'torque' => '100 Nm']
                        ]
                    ]
                ]
            ],
            'RALLIART' => [
                'generations' => [
                    '2022-Now (Gen 6 Facelift)' => [
                        'engine' => [
                            ['cc' => 1193, 'fuel_type' => 'Gasoline', 'engine_code' => '3A92', 'transmission' => ['CVT'], 'power' => '78 HP', 'torque' => '100 Nm']
                        ]
                    ]
                ]
            ]
        ];

        foreach ($types as $typeData) {
            $type = CarType::firstOrCreate(['name' => $typeData['name'], 'car_model_id' => $model->id]);
            $this->generateTypeVariants($brand->id, $model->id, $type->id, $typeData['name'], $configurations);
        }
    }

    private function generateTypeVariants($brandId, $modelId, $typeId, $typeName, $configurations): void
    {
        $currentYear = date('Y');
        foreach ($configurations[$typeName]['generations'] as $period => $genConfig) {
            $years = $this->getYearsForGeneration($period, $currentYear);
            foreach ($years as $year) {
                foreach ($genConfig['engine'] as $engineConfig) {
                    foreach ($engineConfig['transmission'] as $transmission) {
                        CarDetail::firstOrCreate(
                            [
                                'brand_id' => $brandId,
                                'car_model_id' => $modelId,
                                'car_type_id' => $typeId,
                                'year' => $year,
                                'cc' => $engineConfig['cc'],
                                'transmission' => $transmission,
                                'fuel_type' => $engineConfig['fuel_type'],
                                'engine_code' => $engineConfig['engine_code'],
                                'segment' => 'Hatchback', // atau 'Sedan' untuk Mirage G4
                                'production_period' => $period
                            ],
                            ['description' => $this->generateDescription($typeName, $year, $engineConfig['cc'], $engineConfig['engine_code'], $transmission, $engineConfig['fuel_type'], $engineConfig['power'], $engineConfig['torque'], $period)]
                        );
                    }
                }
            }
        }
    }

    private function getYearsForGeneration($period, $currentYear): array
    {
        if (str_contains($period, '2012-2024')) {
            return range(2012, 2024);
        } elseif (str_contains($period, '2022-Now')) {
            return range(2022, $currentYear);
        }
        return [];
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $fuelType, $power, $torque, $generation): string
    {
        $transText = $transmission === 'CVT' ? 'CVT' : 'manual';
        $ccText = round($cc / 1000, 1) . 'L';
        return "Mitsubishi Mirage {$typeName} {$ccText} {$transText} {$engineCode} {$fuelType} ({$power}, {$torque} Nm) tahun {$year}. Generasi {$generation}, hatchback kompak yang efisien.";
    }
}