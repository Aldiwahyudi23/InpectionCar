<?php

namespace Database\Seeders\Brands\Daihatsu;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarDetail;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use Illuminate\Database\Seeder;

class GranMaxSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Daihatsu']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Gran Max'
        ]);

        $granMaxTypes = [
            ['name' => 'Minibus STD'],
            ['name' => 'Minibus AC'],
            ['name' => 'Minibus AC PS'],
            ['name' => 'Pick Up STD'],
            ['name' => 'Pick Up AC'],
            ['name' => 'Pick Up AC PS'],
            ['name' => 'Blind Van STD'],
        ];

        $typeConfigurations = [
            'Minibus STD' => [
                'generations' => [
                    '2007-2014 (Gen 1)' => [
                        'engines' => [
                            ['cc' => 1298, 'code' => 'K3-DE', 'transmission' => ['MT']],
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT']],
                        ]
                    ],
                    '2014-now (Facelift)' => [
                        'engines' => [
                            ['cc' => 1298, 'code' => 'K3-DE', 'transmission' => ['MT']],
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT']],
                        ]
                    ]
                ]
            ],
            'Minibus AC' => [
                'generations' => [
                    '2007-2014 (Gen 1)' => [
                        'engines' => [
                            ['cc' => 1298, 'code' => 'K3-DE', 'transmission' => ['MT']],
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT']],
                        ]
                    ],
                    '2014-now (Facelift)' => [
                        'engines' => [
                            ['cc' => 1298, 'code' => 'K3-DE', 'transmission' => ['MT']],
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT']],
                        ]
                    ]
                ]
            ],
            'Minibus AC PS' => [
                'generations' => [
                    '2007-2014 (Gen 1)' => [
                        'engines' => [
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT']],
                        ]
                    ],
                    '2014-now (Facelift)' => [
                        'engines' => [
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT']],
                        ]
                    ]
                ]
            ],
            'Pick Up STD' => [
                'generations' => [
                    '2007-now (Gen 1)' => [
                        'engines' => [
                            ['cc' => 1298, 'code' => 'K3-DE', 'transmission' => ['MT']],
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT']],
                        ]
                    ],
                ]
            ],
            'Pick Up AC' => [
                'generations' => [
                    '2007-now (Gen 1)' => [
                        'engines' => [
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT']],
                        ]
                    ],
                ]
            ],
            'Pick Up AC PS' => [
                'generations' => [
                    '2007-now (Gen 1)' => [
                        'engines' => [
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT']],
                        ]
                    ],
                ]
            ],
            'Blind Van STD' => [
                'generations' => [
                    '2011-now (Gen 1)' => [
                        'engines' => [
                            ['cc' => 1298, 'code' => 'K3-DE', 'transmission' => ['MT']],
                            ['cc' => 1495, 'code' => '3SZ-VE', 'transmission' => ['MT']],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($granMaxTypes as $typeData) {
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
                                'segment' => $this->detectSegment($typeName),
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
            str_contains($period, '2007-2014') => range(2007, 2014),
            str_contains($period, '2014-now') => range(2014, $currentYear),
            str_contains($period, '2007-now') => range(2007, $currentYear),
            str_contains($period, '2011-now') => range(2011, $currentYear),
            default => []
        };
    }

    private function detectSegment($typeName): string
    {
        return match (true) {
            str_contains($typeName, 'Minibus') => 'MPV',
            str_contains($typeName, 'Pick Up') => 'Pickup',
            str_contains($typeName, 'Blind Van') => 'Van',
            default => 'Commercial'
        };
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $generation): string
    {
        $transmissionText = $transmission === 'AT'
            ? 'matic'
            : ($transmission === 'CVT' ? 'CVT' : 'manual');

        $ccText = round($cc/1000, 1) . 'L';

        return "Daihatsu Gran Max {$typeName} {$ccText} {$transmissionText} (mesin {$engineCode}) tahun {$year}. " .
            "Generasi {$generation}, kendaraan serbaguna untuk kebutuhan niaga maupun keluarga.";
    }
}
