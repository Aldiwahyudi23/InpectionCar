<?php

namespace Database\Seeders\Brands\Daihatsu;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarDetail;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use Illuminate\Database\Seeder;

class AylaSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Daihatsu']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Ayla'
        ]);

        $aylaTypes = [
            ['name' => 'D'],
            ['name' => 'M'],
            ['name' => 'X'],
            ['name' => 'R'],
            ['name' => 'ADS'], // hanya ada di gen 2 (2023+)
        ];

        $typeConfigurations = [
            'D' => [
                'generations' => [
                    '2013-2017 (Gen 1 Pre-facelift)' => [
                        'engines' => [
                            ['cc' => 998, 'code' => '1KR-DE', 'transmission' => ['MT']],
                        ]
                    ],
                    '2017-2023 (Gen 1 Facelift)' => [
                        'engines' => [
                            ['cc' => 998, 'code' => '1KR-VE', 'transmission' => ['MT']],
                        ]
                    ],
                ]
            ],
            'M' => [
                'generations' => [
                    '2013-2017 (Gen 1 Pre-facelift)' => [
                        'engines' => [
                            ['cc' => 998, 'code' => '1KR-DE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2017-2023 (Gen 1 Facelift)' => [
                        'engines' => [
                            ['cc' => 998, 'code' => '1KR-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                ]
            ],
            'X' => [
                'generations' => [
                    '2013-2017 (Gen 1 Pre-facelift)' => [
                        'engines' => [
                            ['cc' => 998, 'code' => '1KR-DE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2017-2023 (Gen 1 Facelift)' => [
                        'engines' => [
                            ['cc' => 998, 'code' => '1KR-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2023-now (Gen 2)' => [
                        'engines' => [
                            ['cc' => 998, 'code' => '1KR-VE', 'transmission' => ['MT','CVT']],
                            ['cc' => 1198, 'code' => 'WA-VE', 'transmission' => ['MT','CVT']],
                        ]
                    ]
                ]
            ],
            'R' => [
                'generations' => [
                    '2017-2023 (Gen 1 Facelift)' => [
                        'engines' => [
                            ['cc' => 998, 'code' => '1KR-VE', 'transmission' => ['MT','AT']],
                            ['cc' => 1198, 'code' => '3NR-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2023-now (Gen 2)' => [
                        'engines' => [
                            ['cc' => 998, 'code' => '1KR-VE', 'transmission' => ['MT','CVT']],
                            ['cc' => 1198, 'code' => 'WA-VE', 'transmission' => ['MT','CVT']],
                        ]
                    ]
                ]
            ],
            'ADS' => [
                'generations' => [
                    '2023-now (Gen 2)' => [
                        'engines' => [
                            ['cc' => 998, 'code' => '1KR-VE', 'transmission' => ['MT','CVT']],
                            ['cc' => 1198, 'code' => 'WA-VE', 'transmission' => ['MT','CVT']],
                        ]
                    ],
                ]
            ]
        ];

        foreach ($aylaTypes as $typeData) {
            $type = CarType::firstOrCreate([
                'name' => $typeData['name'],
                'car_model_id' => $model->id
            ]);

            if (isset($typeConfigurations[$typeData['name']])) {
                $this->generateTypeVariants($brand->id, $model->id, $type->id, $typeData['name'], $typeConfigurations);
            }
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
                                'segment' => 'LCGC Hatchback',
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
            str_contains($period, '2013-2017') => range(2013, 2017),
            str_contains($period, '2017-2023') => range(2017, 2023),
            str_contains($period, '2023-now') => range(2023, $currentYear),
            default => []
        };
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $generation): string
    {
        $transmissionText = $transmission === 'AT'
            ? 'matic'
            : ($transmission === 'CVT' ? 'CVT' : 'manual');

        $ccText = round($cc/1000, 1) . 'L';

        return "Daihatsu Ayla {$typeName} {$ccText} {$transmissionText} (mesin {$engineCode}) tahun {$year}. " .
            "Generasi {$generation}, LCGC hatchback yang praktis dan irit.";
    }
}
