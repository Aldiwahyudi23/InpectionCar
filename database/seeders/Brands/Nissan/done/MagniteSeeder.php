<?php

namespace Database\Seeders\Brands\Nissan;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class MagniteSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Nissan']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Magnite'
        ]);

        $types = [
            ['name' => 'Upper'],
            ['name' => 'Premium One Tone'],
            ['name' => 'Premium Two Tone'],
        ];

        $typeConfigurations = [
            'Upper' => [
                'generations' => [
                    '2020-Now (Gen 1 Indonesia)' => [
                        'engine' => [
                            [
                                'cc' => 999,
                                'code' => 'HRA0',
                                'fuel_type' => 'Bensin',
                                'transmission' => ['MT'],
                                'power' => 72,
                                'torque' => 96
                            ]
                        ]
                    ]
                ]
            ],
            'Premium One Tone' => [
                'generations' => [
                    '2020-Now (Gen 1 Indonesia)' => [
                        'engine' => [
                            [
                                'cc' => 999,
                                'code' => 'HRA0 Turbo',
                                'fuel_type' => 'Bensin',
                                'transmission' => ['MT','CVT'],
                                'power' => 100,
                                'torque' => 160
                            ]
                        ]
                    ]
                ]
            ],
            'Premium Two Tone' => [
                'generations' => [
                    '2020-Now (Gen 1 Indonesia)' => [
                        'engine' => [
                            [
                                'cc' => 999,
                                'code' => 'HRA0 Turbo',
                                'fuel_type' => 'Bensin',
                                'transmission' => ['MT','CVT'],
                                'power' => 100,
                                'torque' => 160
                            ]
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
                                'segment' => 'Crossover',
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
            str_contains($period, '2020-Now') => range(2020, $currentYear),
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
        $transText = $transmission === 'CVT' ? 'CVT' : ($transmission === 'MT' ? 'manual' : $transmission);
        $engineSize = round($cc / 1000, 1) . 'L';

        return "Nissan Magnite {$typeName} {$engineSize} {$transText} {$fuelType} "
             . "({$power} hp, {$torque} Nm) tahun {$year}. Mesin {$engineCode}, "
             . "Generasi {$generation}, crossover compact Nissan di Indonesia.";
    }
}
