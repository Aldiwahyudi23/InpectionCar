<?php

namespace Database\Seeders\Brands\Mitsubishi;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class PajeroSportSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Mitsubishi']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Pajero Sport'
        ]);

        $pajeroSportTypes = [
            ['name' => 'GLX'],
            ['name' => 'Exceed'],
            ['name' => 'Dakar'],
            ['name' => 'Ultimate'],
        ];

        $typeConfigurations = [
            'GLX' => [
                'generations' => [
                    '2008-2015 (Gen 1)' => [
                        'engine' => [
                            ['cc' => 2477, 'code' => '4D56', 'fuel_type' => 'Diesel', 'transmission' => ['MT','AT'], 'power' => '136 HP', 'torque' => 314]
                        ]
                    ],
                    '2015-2023 (Gen 2)' => [
                        'engine' => [
                            ['cc' => 2442, 'code' => '4N15', 'fuel_type' => 'Diesel', 'transmission' => ['AT'], 'power' => '181 HP', 'torque' => 430]
                        ]
                    ],
                    '2023-Now (Gen 3)' => [
                        'engine' => [
                            ['cc' => 2442, 'code' => '4N15', 'fuel_type' => 'Diesel', 'transmission' => ['AT'], 'power' => '181 HP', 'torque' => 430]
                        ]
                    ],
                ]
            ],
            'Exceed' => [
                'generations' => [
                    '2008-2015 (Gen 1)' => [
                        'engine' => [
                            ['cc' => 2477, 'code' => '4D56', 'fuel_type' => 'Diesel', 'transmission' => ['AT'], 'power' => '136 HP', 'torque' => 314]
                        ]
                    ],
                    '2015-2023 (Gen 2)' => [
                        'engine' => [
                            ['cc' => 2442, 'code' => '4N15', 'fuel_type' => 'Diesel', 'transmission' => ['AT'], 'power' => '181 HP', 'torque' => 430]
                        ]
                    ],
                    '2023-Now (Gen 3)' => [
                        'engine' => [
                            ['cc' => 2442, 'code' => '4N15', 'fuel_type' => 'Diesel', 'transmission' => ['AT'], 'power' => '181 HP', 'torque' => 430]
                        ]
                    ],
                ]
            ],
            'Dakar' => [
                'generations' => [
                    '2015-2023 (Gen 2)' => [
                        'engine' => [
                            ['cc' => 2442, 'code' => '4N15', 'fuel_type' => 'Diesel', 'transmission' => ['AT'], 'power' => '181 HP', 'torque' => 430]
                        ]
                    ],
                    '2023-Now (Gen 3)' => [
                        'engine' => [
                            ['cc' => 2442, 'code' => '4N15', 'fuel_type' => 'Diesel', 'transmission' => ['AT'], 'power' => '181 HP', 'torque' => 430]
                        ]
                    ],
                ]
            ],
            'Ultimate' => [
                'generations' => [
                    '2015-2023 (Gen 2)' => [
                        'engine' => [
                            ['cc' => 2442, 'code' => '4N15', 'fuel_type' => 'Diesel', 'transmission' => ['AT'], 'power' => '181 HP', 'torque' => 430]
                        ]
                    ],
                    '2023-Now (Gen 3)' => [
                        'engine' => [
                            ['cc' => 2442, 'code' => '4N15', 'fuel_type' => 'Diesel', 'transmission' => ['AT'], 'power' => '181 HP', 'torque' => 430]
                        ]
                    ],
                ]
            ],
        ];

        foreach ($pajeroSportTypes as $typeData) {
            $type = CarType::firstOrCreate([
                'name' => $typeData['name'],
                'car_model_id' => $model->id
            ]);

            $this->generateTypeVariants(
                $brand->id, 
                $model->id, 
                $type->id, 
                $typeData['name'], 
                $typeConfigurations
            );
        }
    }

    private function generateTypeVariants($brandId, $modelId, $typeId, $typeName, $configurations): void
    {
        $currentYear = date('Y');

        foreach ($configurations[$typeName]['generations'] as $period => $generationConfig) {
            $years = $this->getYearsForGeneration($period, $currentYear);

            foreach ($years as $year) {
                foreach ($generationConfig['engine'] as $engineConfig) {
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
                                'engine_code' => $engineConfig['code'],
                                'segment' => 'SUV',
                                'production_period' => $period
                            ],
                            [
                                'description' => $this->generateDescription(
                                    $typeName,
                                    $year,
                                    $engineConfig['cc'],
                                    $engineConfig['code'],
                                    $transmission,
                                    $engineConfig['fuel_type'],
                                    $engineConfig['power'],
                                    $engineConfig['torque'],
                                    $period
                                )
                            ]
                        );
                    }
                }
            }
        }
    }

    private function getYearsForGeneration($period, $currentYear): array
    {
        if (str_contains($period, '2008-2015')) return range(2008, 2015);
        if (str_contains($period, '2015-2023')) return range(2015, 2023);
        if (str_contains($period, '2023-Now')) return range(2023, $currentYear);
        return [];
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $fuelType, $power, $torque, $generation): string
    {
        $transmissionText = $transmission === 'AT' ? 'matic' : 'manual';
        $ccText = round($cc/1000, 1) . 'L';

        return "Mitsubishi Pajero Sport {$typeName} {$ccText} {$transmissionText} ({$power} HP, {$torque} Nm, {$fuelType}) tahun {$year}. Generasi {$generation}, SUV tangguh dan nyaman.";
    }
}
