<?php

namespace Database\Seeders\Brands\Daihatsu;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarDetail;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use Illuminate\Database\Seeder;

class TeriosSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Daihatsu']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Terios'
        ]);

        $teriosTypes = [
            ['name' => 'TS'],
            ['name' => 'TX'],
            ['name' => 'R'],
            ['name' => 'X'], // Gen 2
            ['name' => 'R Custom'], // Gen 2
        ];

        $typeConfigurations = [
            'TS' => [
                'generations' => [
                    '2006-2017 (Gen 1)' => [
                        'engines' => [
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                ]
            ],
            'TX' => [
                'generations' => [
                    '2006-2017 (Gen 1)' => [
                        'engines' => [
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                ]
            ],
            'R' => [
                'generations' => [
                    '2006-2017 (Gen 1)' => [
                        'engines' => [
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2017-now (Gen 2)' => [
                        'engines' => [
                            ['cc' => 1496, 'code' => '2NR-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                ]
            ],
            'X' => [
                'generations' => [
                    '2017-now (Gen 2)' => [
                        'engines' => [
                            ['cc' => 1496, 'code' => '2NR-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                ]
            ],
            'R Custom' => [
                'generations' => [
                    '2017-now (Gen 2)' => [
                        'engines' => [
                            ['cc' => 1496, 'code' => '2NR-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($teriosTypes as $typeData) {
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
                foreach ($generationConfig['engines'] as $engineConfig) {
                    foreach ($engineConfig['transmission'] as $transmission) {
                        CarDetail::firstOrCreate(
                            [
                                'brand_id' => $brandId,
                                'car_model_id' => $modelId,
                                'car_type_id' => $typeId,
                                'year' => $year,
                                'cc' => $engineConfig['cc'],
                                'transmission' => $transmission,
                                'fuel_type' => 'Bensin',
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
            str_contains($period, '2006-2017') => range(2006, 2017),
            str_contains($period, '2017-now') => range(2017, $currentYear),
            default => []
        };
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $generation): string
    {
        $transmissionText = $transmission === 'AT'
            ? 'matic'
            : 'manual';

        $ccText = round($cc/1000, 1) . 'L';

        return "Daihatsu Terios {$typeName} {$ccText} {$transmissionText} (mesin {$engineCode}) tahun {$year}. " .
            "Generasi {$generation}, SUV keluarga dengan tampilan sporty dan ketangguhan.";
    }
}
