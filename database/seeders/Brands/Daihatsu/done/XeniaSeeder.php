<?php

namespace Database\Seeders\Brands\Daihatsu;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarDetail;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use Illuminate\Database\Seeder;

class XeniaSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Daihatsu']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Xenia'
        ]);

        $xeniaTypes = [
            ['name' => 'Mi'],
            ['name' => 'Li'],
            ['name' => 'Xi'],
            ['name' => 'D'],
            ['name' => 'M'],
            ['name' => 'X'],
            ['name' => 'R'],
            ['name' => 'R ADS'],
        ];

        $typeConfigurations = [
            'Mi' => [
                'generations' => [
                    '2004-2011 (Gen 1)' => [
                        'engines' => [
                            ['cc' => 989, 'code' => 'EJ-VE', 'transmission' => ['MT']],
                        ]
                    ]
                ]
            ],
            'Li' => [
                'generations' => [
                    '2004-2011 (Gen 1)' => [
                        'engines' => [
                            ['cc' => 989, 'code' => 'EJ-VE', 'transmission' => ['MT']],
                        ]
                    ]
                ]
            ],
            'Xi' => [
                'generations' => [
                    '2004-2011 (Gen 1)' => [
                        'engines' => [
                            ['cc' => 1298, 'code' => 'K3-VE', 'transmission' => ['MT','AT']],
                        ]
                    ]
                ]
            ],
            'D' => [
                'generations' => [
                    '2011-2019 (Gen 2)' => [
                        'engines' => [
                            ['cc' => 989, 'code' => 'EJ-VE', 'transmission' => ['MT']],
                        ]
                    ]
                ]
            ],
            'M' => [
                'generations' => [
                    '2011-2019 (Gen 2)' => [
                        'engines' => [
                            ['cc' => 1298, 'code' => 'K3-VE', 'transmission' => ['MT']],
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
            'X' => [
                'generations' => [
                    '2011-2019 (Gen 2)' => [
                        'engines' => [
                            ['cc' => 1298, 'code' => 'K3-VE', 'transmission' => ['MT','AT']],
                            ['cc' => 1329, 'code' => '1NR-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2021-now (Gen 3)' => [
                        'engines' => [
                            ['cc' => 1329, 'code' => '1NR-VE', 'transmission' => ['MT','CVT']],
                            ['cc' => 1496, 'code' => '2NR-VE', 'transmission' => ['MT','CVT']],
                        ]
                    ]
                ]
            ],
            'R' => [
                'generations' => [
                    '2011-2019 (Gen 2)' => [
                        'engines' => [
                            ['cc' => 1329, 'code' => '1NR-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2021-now (Gen 3)' => [
                        'engines' => [
                            ['cc' => 1329, 'code' => '1NR-VE', 'transmission' => ['MT','CVT']],
                            ['cc' => 1496, 'code' => '2NR-VE', 'transmission' => ['MT','CVT']],
                        ]
                    ]
                ]
            ],
            'R ADS' => [
                'generations' => [
                    '2021-now (Gen 3)' => [
                        'engines' => [
                            ['cc' => 1329, 'code' => '1NR-VE', 'transmission' => ['MT','CVT']],
                            ['cc' => 1496, 'code' => '2NR-VE', 'transmission' => ['MT','CVT']],
                        ]
                    ]
                ]
            ],
        ];

        foreach ($xeniaTypes as $typeData) {
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
            str_contains($period, '2004-2011') => range(2004, 2011),
            str_contains($period, '2011-2019') => range(2011, 2019),
            str_contains($period, '2021-now') => range(2021, $currentYear),
            default => []
        };
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $generation): string
    {
        $transmissionText = $transmission === 'AT' || $transmission === 'CVT'
            ? strtolower($transmission)
            : 'manual';

        $ccText = round($cc/1000, 1) . 'L';

        return "Daihatsu Xenia {$typeName} {$ccText} {$transmissionText} (mesin {$engineCode}) tahun {$year}. " .
            "Generasi {$generation}, MPV keluarga serbaguna dengan kenyamanan khas LMPV.";
    }
}
