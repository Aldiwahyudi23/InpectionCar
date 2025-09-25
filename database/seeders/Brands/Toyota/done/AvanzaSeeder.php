<?php

namespace Database\Seeders\Brands\Toyota;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarDetail;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use Illuminate\Database\Seeder;

class AvanzaSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Toyota']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Avanza'
        ]);

        $avanzaTypes = [
            ['name' => 'E'],
            ['name' => 'G'],
            ['name' => 'Veloz'] // hanya sampai 2021
        ];

        $typeConfigurations = [
            'E' => [
                'generations' => [
                    '2003-2011 (Gen 1)' => [
                        'engines' => [
                            ['cc' => 1298, 'code' => 'K3-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2011-2021 (Gen 2 & facelift)' => [
                        'engines' => [
                            ['cc' => 1298, 'code' => 'K3-VE', 'transmission' => ['MT','AT']],
                            ['cc' => 1329, 'code' => '1NR-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2021-now (Gen 3)' => [
                        'engines' => [
                            ['cc' => 1329, 'code' => '1NR-VE', 'transmission' => ['MT','CVT']],
                        ]
                    ]
                ]
            ],
            'G' => [
                'generations' => [
                    '2003-2011 (Gen 1)' => [
                        'engines' => [
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2011-2021 (Gen 2 & facelift)' => [
                        'engines' => [
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT','AT']],
                            ['cc' => 1496, 'code' => '2NR-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2021-now (Gen 3)' => [
                        'engines' => [
                            ['cc' => 1496, 'code' => '2NR-VE', 'transmission' => ['MT','CVT']],
                        ]
                    ]
                ]
            ],
            'Veloz' => [
                'generations' => [
                    '2011-2021 (Gen 2)' => [
                        'engines' => [
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT','AT']],
                            ['cc' => 1496, 'code' => '2NR-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                ]
            ]
        ];

        foreach ($avanzaTypes as $typeData) {
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
                                'segment' => 'Low MPV (Multi-Purpose Vehicle)',
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
            str_contains($period, '2003-2011') => range(2003, 2011),
            str_contains($period, '2011-2021') => range(2011, 2021),
            str_contains($period, '2021-now') => range(2021, $currentYear),
            default => []
        };
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $generation): string
    {
        $transmissionText = $transmission === 'AT'
            ? 'matic'
            : ($transmission === 'CVT' ? 'CVT' : 'manual');

        $ccText = round($cc/1000, 1) . 'L';

        return "Toyota Avanza {$typeName} {$ccText} {$transmissionText} (mesin {$engineCode}) tahun {$year}. " .
            "Generasi {$generation}, MPV keluarga dengan kepraktisan dan efisiensi bahan bakar.";
    }
}
