<?php

namespace Database\Seeders\Brands\Daihatsu;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarDetail;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use Illuminate\Database\Seeder;

class SigraSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Daihatsu']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Sigra'
        ]);

        $sigraTypes = [
            ['name' => 'D'],
            ['name' => 'M'],
            ['name' => 'X'],
            ['name' => 'R'],
        ];

        $typeConfigurations = [
            'D' => [
                'generations' => [
                    '2016-2019 (Gen 1)' => [
                        'engines' => [
                            ['cc' => 998, 'code' => '1KR-VE', 'transmission' => ['MT']],
                        ]
                    ],
                    '2019-2022 (Facelift)' => [
                        'engines' => [
                            ['cc' => 998, 'code' => '1KR-VE', 'transmission' => ['MT']],
                        ]
                    ],
                    '2022-now (Facelift 2)' => [
                        'engines' => [
                            ['cc' => 998, 'code' => '1KR-VE', 'transmission' => ['MT']],
                        ]
                    ]
                ]
            ],
            'M' => [
                'generations' => [
                    '2016-2019 (Gen 1)' => [
                        'engines' => [
                            ['cc' => 998, 'code' => '1KR-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2019-2022 (Facelift)' => [
                        'engines' => [
                            ['cc' => 998, 'code' => '1KR-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2022-now (Facelift 2)' => [
                        'engines' => [
                            ['cc' => 998, 'code' => '1KR-VE', 'transmission' => ['MT','CVT']],
                        ]
                    ]
                ]
            ],
            'X' => [
                'generations' => [
                    '2016-2019 (Gen 1)' => [
                        'engines' => [
                            ['cc' => 1197, 'code' => '3NR-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2019-2022 (Facelift)' => [
                        'engines' => [
                            ['cc' => 1197, 'code' => '3NR-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2022-now (Facelift 2)' => [
                        'engines' => [
                            ['cc' => 1197, 'code' => '3NR-VE', 'transmission' => ['MT','CVT']],
                        ]
                    ]
                ]
            ],
            'R' => [
                'generations' => [
                    '2016-2019 (Gen 1)' => [
                        'engines' => [
                            ['cc' => 1197, 'code' => '3NR-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2019-2022 (Facelift)' => [
                        'engines' => [
                            ['cc' => 1197, 'code' => '3NR-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2022-now (Facelift 2)' => [
                        'engines' => [
                            ['cc' => 1197, 'code' => '3NR-VE', 'transmission' => ['MT','CVT']],
                        ]
                    ]
                ]
            ],
        ];

        foreach ($sigraTypes as $typeData) {
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
                                'segment' => 'LCGC MPV',
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
            str_contains($period, '2016-2019') => range(2016, 2019),
            str_contains($period, '2019-2022') => range(2019, 2022),
            str_contains($period, '2022-now') => range(2022, $currentYear),
            default => []
        };
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $generation): string
    {
        $transmissionText = $transmission === 'AT'
            ? 'matic'
            : ($transmission === 'CVT' ? 'CVT' : 'manual');

        $ccText = round($cc/1000, 1) . 'L';

        return "Daihatsu Sigra {$typeName} {$ccText} {$transmissionText} (mesin {$engineCode}) tahun {$year}. " .
            "Generasi {$generation}, MPV LCGC dengan 7 penumpang dan efisiensi bahan bakar.";
    }
}
