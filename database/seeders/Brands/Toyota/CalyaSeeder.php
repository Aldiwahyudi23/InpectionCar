<?php

namespace Database\Seeders\Brands\Toyota;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class CalyaSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Toyota']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Calya'
        ]);

        $calyaTypes = [
            ['name' => 'E'],
            ['name' => 'G'],
        ];

        $typeConfigurations = [
            'E' => [
                'generations' => [
                    '2016-2022 (Gen 1 & facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1197,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '3NR-VE',
                                'transmission' => ['MT','AT']
                            ],
                        ]
                    ],
                    '2022-Now (Gen 2 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1197,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '3NR-VE',
                                'transmission' => ['MT','AT']
                            ],
                        ]
                    ],
                ]
            ],
            'G' => [
                'generations' => [
                    '2016-2022 (Gen 1 & facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1197,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '3NR-VE',
                                'transmission' => ['MT','AT']
                            ],
                        ]
                    ],
                    '2022-Now (Gen 2 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1197,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '3NR-VE',
                                'transmission' => ['MT','AT']
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($calyaTypes as $typeData) {
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
                                'segment' => 'Low MPV (LCGC)',
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
        if (str_contains($period, '2016-2022')) {
            return range(2016, 2022);
        } elseif (str_contains($period, '2022-Now')) {
            return range(2022, $currentYear);
        }
        return [];
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $fuelType, $generation): string
    {
        $transmissionText = $transmission === 'AT' ? 'matic' : 'manual';
        $ccText = round($cc/1000, 1) . 'L';
        $fuelText = strtolower($fuelType);

        return "Toyota Calya {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan desain MPV LCGC 7-seater yang irit dan fungsional.";
    }
}
