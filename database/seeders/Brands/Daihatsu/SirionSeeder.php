<?php

namespace Database\Seeders\Brands\Daihatsu;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarDetail;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use Illuminate\Database\Seeder;

class SirionSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Daihatsu']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Sirion'
        ]);

        $sirionTypes = [
            ['name' => 'D'],
            ['name' => 'M'],
            ['name' => 'D Sporty'],
            ['name' => 'STD MT'],
            ['name' => 'STD AT'],
        ];

        $typeConfigurations = [
            'D' => [
                'generations' => [
                    '2007-2011 (Gen 1)' => [
                        'engines' => [
                            ['cc' => 1298, 'code' => 'K3-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                    '2011-2018 (Gen 2)' => [
                        'engines' => [
                            ['cc' => 1298, 'code' => 'K3-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                ]
            ],
            'M' => [
                'generations' => [
                    '2007-2011 (Gen 1)' => [
                        'engines' => [
                            ['cc' => 1298, 'code' => 'K3-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                ]
            ],
            'D Sporty' => [
                'generations' => [
                    '2011-2018 (Gen 2)' => [
                        'engines' => [
                            ['cc' => 1298, 'code' => 'K3-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                ]
            ],
            'STD' => [
                'generations' => [
                    '2018-now (Gen 3)' => [
                        'engines' => [
                            ['cc' => 1329, 'code' => '1NR-VE', 'transmission' => ['MT','AT']],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($sirionTypes as $typeData) {
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
                                'segment' => 'Hatchback',
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
            str_contains($period, '2007-2011') => range(2007, 2011),
            str_contains($period, '2011-2018') => range(2011, 2018),
            str_contains($period, '2018-now') => range(2018, $currentYear),
            default => []
        };
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $generation): string
    {
        $transmissionText = $transmission === 'AT'
            ? 'matic'
            : ($transmission === 'CVT' ? 'CVT' : 'manual');

        $ccText = round($cc/1000, 1) . 'L';

        return "Daihatsu Sirion {$typeName} {$ccText} {$transmissionText} (mesin {$engineCode}) tahun {$year}. " .
            "Generasi {$generation}, hatchback praktis untuk perkotaan.";
    }
}
