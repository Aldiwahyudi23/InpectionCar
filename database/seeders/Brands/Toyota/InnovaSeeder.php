<?php

namespace Database\Seeders\Brands\Toyota;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class InnovaSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Toyota']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Innova'
        ]);

        $innovaTypes = [
            ['name' => 'G'],
            ['name' => 'V'],
            ['name' => 'Venturer'],
            ['name' => 'Q']
        ];

        $typeConfigurations = [
            'G' => [
                'generations' => [
                    '2004-2015 (Gen 1)' => [
                        'engine' => [
                            ['cc' => 1998, 'code' => '1TR-FE', 'fuel_type' => 'Bensin', 'transmission' => ['MT','AT']],
                            ['cc' => 2494, 'code' => '2KD-FTV', 'fuel_type' => 'Solar', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2016-2022 (Gen 2 / Reborn)' => [
                        'engine' => [
                            ['cc' => 1998, 'code' => '1TR-FE Dual VVT-i', 'fuel_type' => 'Bensin', 'transmission' => ['MT','AT']],
                            ['cc' => 2393, 'code' => '2GD-FTV', 'fuel_type' => 'Solar', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2022-Now (Gen 3 / Zenix)' => [
                        'engine' => [
                            ['cc' => 1987, 'code' => 'M20A-FKS', 'fuel_type' => 'Bensin', 'transmission' => ['CVT']],
                            ['cc' => 1987, 'code' => 'M20A-FXS', 'fuel_type' => 'Hybrid', 'transmission' => ['CVT']],
                        ]
                    ],
                ]
            ],
            'V' => [
                'generations' => [
                    '2004-2015 (Gen 1)' => [
                        'engine' => [
                            ['cc' => 1998, 'code' => '1TR-FE', 'fuel_type' => 'Bensin', 'transmission' => ['AT']],
                            ['cc' => 2494, 'code' => '2KD-FTV', 'fuel_type' => 'Solar', 'transmission' => ['AT']],
                        ]
                    ],
                    '2016-2022 (Gen 2 / Reborn)' => [
                        'engine' => [
                            ['cc' => 1998, 'code' => '1TR-FE Dual VVT-i', 'fuel_type' => 'Bensin', 'transmission' => ['AT']],
                            ['cc' => 2393, 'code' => '2GD-FTV', 'fuel_type' => 'Solar', 'transmission' => ['AT']],
                        ]
                    ],
                    '2022-Now (Gen 3 / Zenix)' => [
                        'engine' => [
                            ['cc' => 1987, 'code' => 'M20A-FKS', 'fuel_type' => 'Bensin', 'transmission' => ['CVT']],
                            ['cc' => 1987, 'code' => 'M20A-FXS', 'fuel_type' => 'Hybrid', 'transmission' => ['CVT']],
                        ]
                    ],
                ]
            ],
            'Venturer' => [
                'generations' => [
                    '2016-2022 (Gen 2 / Reborn)' => [
                        'engine' => [
                            ['cc' => 1998, 'code' => '1TR-FE Dual VVT-i', 'fuel_type' => 'Bensin', 'transmission' => ['AT']],
                            ['cc' => 2393, 'code' => '2GD-FTV', 'fuel_type' => 'Solar', 'transmission' => ['AT']],
                        ]
                    ]
                ]
            ],
            'Q' => [
                'generations' => [
                    '2016-2022 (Gen 2 / Reborn)' => [
                        'engine' => [
                            ['cc' => 1998, 'code' => '1TR-FE Dual VVT-i', 'fuel_type' => 'Bensin', 'transmission' => ['AT']],
                            ['cc' => 2393, 'code' => '2GD-FTV', 'fuel_type' => 'Solar', 'transmission' => ['AT']],
                        ]
                    ]
                ]
            ]
        ];

        foreach ($innovaTypes as $typeData) {
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
                                 'engine_code' => $engineConfig['code'],
                                'segment' => 'Medium MPV (Multi-Purpose Vehicle)',
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
        if (str_contains($period, '2004-2015')) {
            return range(2004, 2015);
        } elseif (str_contains($period, '2016-2022')) {
            return range(2016, 2022);
        } elseif (str_contains($period, '2022-Now')) {
            return range(2022, $currentYear);
        }
        return [];
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $fuelType, $generation): string
    {
        $transmissionText = $transmission === 'AT' ? 'matic' : ($transmission === 'CVT' ? 'CVT' : 'manual');
        $ccText = round($cc/1000, 1) . 'L';
        $fuelText = strtolower($fuelType);

        return "Toyota Innova {$typeName} {$ccText} {$transmissionText} {$fuelText} (mesin {$engineCode}) tahun {$year}. " .
               "Generasi {$generation} dengan kenyamanan premium dan kapasitas 7-8 penumpang.";
    }
}
