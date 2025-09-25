<?php

namespace Database\Seeders\Brands\Daihatsu;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarDetail;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use Illuminate\Database\Seeder;

class LuxioSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Daihatsu']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Luxio'
        ]);

        $luxioTypes = [
            ['name' => 'D'],
            ['name' => 'M'],
            ['name' => 'X'],
        ];

        $typeConfigurations = [
            'D' => [
                'generations' => [
                    '2009-2014 (Gen 1)' => [
                        'engines' => [
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT']],
                        ]
                    ],
                    '2014-2021 (Facelift)' => [
                        'engines' => [
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT']],
                        ]
                    ],
                    '2021-now (Facelift 2)' => [
                        'engines' => [
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT']],
                        ]
                    ]
                ]
            ],
            'M' => [
                'generations' => [
                    '2009-2014 (Gen 1)' => [
                        'engines' => [
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2014-2021 (Facelift)' => [
                        'engines' => [
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2021-now (Facelift 2)' => [
                        'engines' => [
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT','AT']],
                        ]
                    ]
                ]
            ],
            'X' => [
                'generations' => [
                    '2009-2014 (Gen 1)' => [
                        'engines' => [
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2014-2021 (Facelift)' => [
                        'engines' => [
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2021-now (Facelift 2)' => [
                        'engines' => [
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT','AT']],
                        ]
                    ]
                ]
            ],
        ];

        foreach ($luxioTypes as $typeData) {
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
            str_contains($period, '2009-2014') => range(2009, 2014),
            str_contains($period, '2014-2021') => range(2014, 2021),
            str_contains($period, '2021-now') => range(2021, $currentYear),
            default => []
        };
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $generation): string
    {
        $transmissionText = $transmission === 'AT' ? 'matic' : 'manual';
        $ccText = round($cc/1000, 1) . 'L';

        return "Daihatsu Luxio {$typeName} {$ccText} {$transmissionText} (mesin {$engineCode}) tahun {$year}. " .
            "Generasi {$generation}, MPV premium berbasis Gran Max dengan kenyamanan ekstra.";
    }
}
