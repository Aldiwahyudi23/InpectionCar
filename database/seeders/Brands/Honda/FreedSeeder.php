<?php

namespace Database\Seeders\Brands\Honda;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class FreedSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Honda']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Freed'
        ]);

        $types = [
            ['name' => 'S'],
            ['name' => 'E'],
            ['name' => 'E+ AT'],
            ['name' => 'PSD AT'],
        ];

        $typeConfigurations = [
            'S' => [
                'generations' => [
                    '2009-2016 (Gen 1)' => [
                        'engine' => [
                            ['cc' => 1496, 'code' => 'L15Z1', 'fuel_type' => 'Bensin', 'transmission' => ['MT', 'AT'], 'power' => 118, 'torque' => 146]
                        ]
                    ],
                    '2016-Now (Gen 2)' => [
                        'engine' => [
                            ['cc' => 1496, 'code' => 'L15Z2', 'fuel_type' => 'Bensin', 'transmission' => ['AT'], 'power' => 118, 'torque' => 145]
                        ]
                    ]
                ]
            ],
            'E' => [
                'generations' => [
                    '2009-2016 (Gen 1)' => [
                        'engine' => [
                            ['cc' => 1496, 'code' => 'L15Z1', 'fuel_type' => 'Bensin', 'transmission' => ['MT', 'AT'], 'power' => 118, 'torque' => 146]
                        ]
                    ],
                    '2016-Now (Gen 2)' => [
                        'engine' => [
                            ['cc' => 1496, 'code' => 'L15Z2', 'fuel_type' => 'Bensin', 'transmission' => ['AT'], 'power' => 118, 'torque' => 145]
                        ]
                    ]
                ]
            ],
            'E+' => [
                'generations' => [
                    '2009-2016 (Gen 1)' => [
                        'engine' => [
                            ['cc' => 1496, 'code' => 'L15Z1', 'fuel_type' => 'Bensin', 'transmission' => ['AT'], 'power' => 118, 'torque' => 146]
                        ]
                    ],
                    '2016-Now (Gen 2)' => [
                        'engine' => [
                            ['cc' => 1496, 'code' => 'L15Z2', 'fuel_type' => 'Bensin', 'transmission' => ['AT'], 'power' => 118, 'torque' => 145]
                        ]
                    ]
                ]
            ],
            'PSD' => [
                'generations' => [
                    '2009-2016 (Gen 1)' => [
                        'engine' => [
                            ['cc' => 1496, 'code' => 'L15Z1', 'fuel_type' => 'Bensin', 'transmission' => ['AT'], 'power' => 118, 'torque' => 146]
                        ]
                    ],
                    '2016-Now (Gen 2)' => [
                        'engine' => [
                            ['cc' => 1496, 'code' => 'L15Z2', 'fuel_type' => 'Bensin', 'transmission' => ['AT'], 'power' => 118, 'torque' => 145]
                        ]
                    ]
                ]
            ],
        ];

        foreach ($types as $typeData) {
            $type = CarType::firstOrCreate([
                'name' => $typeData['name'],
                'car_model_id' => $model->id
            ]);

            if (isset($typeConfigurations[$typeData['name']])) {
                $this->generateTypeVariants(
                    $brand->id, $model->id, $type->id, $typeData['name'], $typeConfigurations
                );
            }
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
                                'segment' => 'MPV',
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
        return match (true) {
            str_contains($period, '2009-2016') => range(2009, 2016),
            str_contains($period, '2016-Now') => range(2016, $currentYear),
            default => []
        };
    }

    private function generateDescription(
        $typeName,
        $year,
        $cc,
        $engineCode,
        $transmission,
        $fuelType,
        $power,
        $torque,
        $generation
    ): string {
        $transText = $transmission === 'AT' ? 'matic' : 'manual';
        $engineSize = round($cc / 1000, 1) . 'L';

        return "Honda Freed {$typeName} {$engineSize} {$transText} {$fuelType} "
             . "({$power} hp, {$torque} Nm) tahun {$year}. Mesin {$engineCode}, "
             . "Generasi {$generation}, MPV kompak Honda di Indonesia.";
    }
}
