<?php

namespace Database\Seeders\Brands\Toyota;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class RushSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Toyota']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Rush'
        ]);

        $rushTypes = [
            ['name' => 'G'],
            ['name' => 'S'],
            ['name' => 'TRD Sportivo'],
            ['name' => 'GR Sport'],
        ];

        $typeConfigurations = [
            'G' => [
                'generations' => [
                    '2006-2017 (Gen 1)' => [
                        'engine' => [
                            [
                                'cc' => 1495,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '3SZ-VE',
                                'transmission' => ['MT','AT']
                            ],
                        ]
                    ],
                    '2017-Now (Gen 2)' => [
                        'engine' => [
                            [
                                'cc' => 1496,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '2NR-VE',
                                'transmission' => ['MT','AT']
                            ],
                        ]
                    ],
                ]
            ],
            'S' => [
                'generations' => [
                    '2006-2017 (Gen 1)' => [
                        'engine' => [
                            [
                                'cc' => 1495,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '3SZ-VE',
                                'transmission' => ['MT','AT']
                            ],
                        ]
                    ]
                ]
            ],
            'TRD Sportivo' => [
                'generations' => [
                    '2010-2017 (Gen 1 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1495,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '3SZ-VE',
                                'transmission' => ['MT','AT']
                            ],
                        ]
                    ],
                    '2017-2020 (Gen 2 awal)' => [
                        'engine' => [
                            [
                                'cc' => 1496,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '2NR-VE',
                                'transmission' => ['MT','AT']
                            ],
                        ]
                    ]
                ]
            ],
            'GR Sport' => [
                'generations' => [
                    '2021-Now (Gen 2 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1496,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '2NR-VE',
                                'transmission' => ['MT','AT']
                            ],
                        ]
                    ]
                ]
            ],
        ];

        foreach ($rushTypes as $typeData) {
            $type = CarType::firstOrCreate([
                'name' => $typeData['name'],
                'car_model_id' => $model->id
            ]);

            $this->generateTypeVariants(
                $brand->id, $model->id, $type->id, $typeData['name'], $typeConfigurations
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
                                'engine_code' => $engineConfig['engine_code'],
                                'segment' => 'Low SUV (Sport Utility Vehicle)',
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
        if (str_contains($period, '2006-2017')) {
            return range(2006, 2017);
        } elseif (str_contains($period, '2010-2017')) {
            return range(2010, 2017);
        } elseif (str_contains($period, '2017-2020')) {
            return range(2017, 2020);
        } elseif (str_contains($period, '2021-Now')) {
            return range(2021, $currentYear);
        } elseif (str_contains($period, '2017-Now')) {
            return range(2017, $currentYear);
        }
        return [];
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $fuelType, $generation): string
    {
        $transmissionText = $transmission === 'AT' ? 'matic' : 'manual';
        $ccText = round($cc/1000, 1) . 'L';
        $fuelText = strtolower($fuelType);

        return "Toyota Rush {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan desain SUV tangguh dan 7-seater.";
    }
}
