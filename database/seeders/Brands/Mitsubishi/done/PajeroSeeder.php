<?php

namespace Database\Seeders\Brands\Mitsubishi;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class PajeroSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Mitsubishi']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Pajero'
        ]);

        $pajeroTypes = [
            ['name' => 'GL'],
            ['name' => 'GLS'],
            ['name' => 'GLS-L'],
            ['name' => 'Exceed'],
            ['name' => 'VR-I'],
            ['name' => 'VR-II'],
            ['name' => 'GLX'],
            ['name' => 'Super Exceed'],
        ];

        $typeConfigurations = [
            'GL' => [
                'generations' => [
                    '2000-2006 (Gen 3)' => [
                        'engine' => [
                            ['cc' => 2998, 'fuel_type' => 'Bensin', 'engine_code' => '6G72', 'transmission' => ['MT','AT'], 'power' => '177 HP']
                        ]
                    ],
                    '2006-2021 (Gen 4)' => [
                        'engine' => [
                            ['cc' => 3496, 'fuel_type' => 'Bensin', 'engine_code' => '6G74', 'transmission' => ['AT'], 'power' => '220 HP'],
                            ['cc' => 3200, 'fuel_type' => 'Diesel', 'engine_code' => '4M41', 'transmission' => ['AT','MT'], 'power' => '178 HP']
                        ]
                    ],
                ]
            ],
            'GLS' => [
                'generations' => [
                    '2000-2006 (Gen 3)' => [
                        'engine' => [
                            ['cc' => 2998, 'fuel_type' => 'Bensin', 'engine_code' => '6G72', 'transmission' => ['MT','AT'], 'power' => '177 HP']
                        ]
                    ],
                    '2006-2021 (Gen 4)' => [
                        'engine' => [
                            ['cc' => 3496, 'fuel_type' => 'Bensin', 'engine_code' => '6G74', 'transmission' => ['AT'], 'power' => '220 HP'],
                            ['cc' => 3200, 'fuel_type' => 'Diesel', 'engine_code' => '4M41', 'transmission' => ['AT','MT'], 'power' => '178 HP']
                        ]
                    ],
                ]
            ],
            'GLS-L' => [
                'generations' => [
                    '2000-2006 (Gen 3)' => [
                        'engine' => [
                            ['cc' => 2998, 'fuel_type' => 'Bensin', 'engine_code' => '6G72', 'transmission' => ['AT'], 'power' => '177 HP']
                        ]
                    ],
                    '2006-2021 (Gen 4)' => [
                        'engine' => [
                            ['cc' => 3496, 'fuel_type' => 'Bensin', 'engine_code' => '6G74', 'transmission' => ['AT'], 'power' => '220 HP'],
                            ['cc' => 3200, 'fuel_type' => 'Diesel', 'engine_code' => '4M41', 'transmission' => ['AT'], 'power' => '178 HP']
                        ]
                    ],
                ]
            ],
            'Exceed' => [
                'generations' => [
                    '2000-2006 (Gen 3)' => [
                        'engine' => [
                            ['cc' => 2998, 'fuel_type' => 'Bensin', 'engine_code' => '6G72', 'transmission' => ['AT'], 'power' => '177 HP']
                        ]
                    ],
                    '2006-2021 (Gen 4)' => [
                        'engine' => [
                            ['cc' => 3496, 'fuel_type' => 'Bensin', 'engine_code' => '6G74', 'transmission' => ['AT'], 'power' => '220 HP'],
                            ['cc' => 3200, 'fuel_type' => 'Diesel', 'engine_code' => '4M41', 'transmission' => ['AT'], 'power' => '178 HP']
                        ]
                    ],
                ]
            ],
            'VR-I' => [
                'generations' => [
                    '2000-2006 (Gen 3)' => [
                        'engine' => [
                            ['cc' => 2998, 'fuel_type' => 'Bensin', 'engine_code' => '6G72', 'transmission' => ['AT'], 'power' => '177 HP']
                        ]
                    ],
                ]
            ],
            'VR-II' => [
                'generations' => [
                    '2000-2006 (Gen 3)' => [
                        'engine' => [
                            ['cc' => 2998, 'fuel_type' => 'Bensin', 'engine_code' => '6G72', 'transmission' => ['AT'], 'power' => '177 HP']
                        ]
                    ],
                ]
            ],
            'GLX' => [
                'generations' => [
                    '2006-2021 (Gen 4)' => [
                        'engine' => [
                            ['cc' => 3496, 'fuel_type' => 'Bensin', 'engine_code' => '6G74', 'transmission' => ['AT'], 'power' => '220 HP'],
                            ['cc' => 3200, 'fuel_type' => 'Diesel', 'engine_code' => '4M41', 'transmission' => ['AT'], 'power' => '178 HP']
                        ]
                    ],
                ]
            ],
            'Super Exceed' => [
                'generations' => [
                    '2006-2021 (Gen 4)' => [
                        'engine' => [
                            ['cc' => 3496, 'fuel_type' => 'Bensin', 'engine_code' => '6G74', 'transmission' => ['AT'], 'power' => '220 HP'],
                            ['cc' => 3200, 'fuel_type' => 'Diesel', 'engine_code' => '4M41', 'transmission' => ['AT'], 'power' => '178 HP']
                        ]
                    ],
                ]
            ],
        ];

        foreach ($pajeroTypes as $typeData) {
            $type = CarType::firstOrCreate([
                'name' => $typeData['name'],
                'car_model_id' => $model->id
            ]);

            $this->generateTypeVariants($brand->id, $model->id, $type->id, $typeData['name'], $typeConfigurations);
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
                                'engine_code' => $engineConfig['engine_code'],
                                'segment' => 'SUV',
                                'production_period' => $period
                            ],
                            [
                                'description' => $this->generateDescription(
                                    $typeName,
                                    $year,
                                    $engineConfig['cc'],
                                    $engineConfig['engine_code'],
                                    $transmission,
                                    $engineConfig['fuel_type'],
                                    $period,
                                    $engineConfig['power']
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
        if (str_contains($period, '2000-2006')) {
            return range(2000, 2006);
        } elseif (str_contains($period, '2006-2021')) {
            return range(2006, 2021);
        }
        return [];
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $fuelType, $generation, $power): string
    {
        $transmissionText = $transmission === 'AT' ? 'matic' : 'manual';
        $ccText = round($cc/1000,1).'L';

        return "Mitsubishi Pajero {$typeName} {$ccText} {$transmissionText} ({$fuelType}) tahun {$year}. " .
               "Mesin {$engineCode} dengan tenaga {$power}. Generasi {$generation}, SUV legendaris untuk segala medan.";
    }
}
