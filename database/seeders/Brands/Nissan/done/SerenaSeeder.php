<?php

namespace Database\Seeders\Brands\Nissan;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class SerenaSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Nissan']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Serena'
        ]);

        $types = [
            ['name' => 'X'],
            ['name' => 'Highway Star'],
            ['name' => 'Panoramic'],
            ['name' => 'e-Power'],
        ];

        $typeConfigurations = [
            'X' => [
                'generations' => [
                    '2000-2005 (Gen 2 C24)' => [
                        'engine' => [
                            ['cc' => 2000, 'code' => 'QR20DE', 'fuel_type' => 'Bensin',
                             'transmission' => ['AT'], 'power' => 150, 'torque' => 200]
                        ]
                    ],
                    '2013-2018 (Gen 4 C26)' => [
                        'engine' => [
                            ['cc' => 2000, 'code' => 'MR20DE', 'fuel_type' => 'Bensin',
                             'transmission' => ['CVT'], 'power' => 147, 'torque' => 200]
                        ]
                    ],
                ]
            ],
            'Highway Star' => [
                'generations' => [
                    '2005-2010 (Gen 3 C25)' => [
                        'engine' => [
                            ['cc' => 2000, 'code' => 'MR20DE', 'fuel_type' => 'Bensin',
                             'transmission' => ['CVT'], 'power' => 138, 'torque' => 200]
                        ]
                    ],
                    '2013-2018 (Gen 4 C26)' => [
                        'engine' => [
                            ['cc' => 2000, 'code' => 'MR20DE', 'fuel_type' => 'Bensin',
                             'transmission' => ['CVT'], 'power' => 147, 'torque' => 200]
                        ]
                    ],
                    '2019-2021 (Gen 5 C27)' => [
                        'engine' => [
                            ['cc' => 2000, 'code' => 'MR20DD', 'fuel_type' => 'Bensin',
                             'transmission' => ['CVT'], 'power' => 150, 'torque' => 200]
                        ]
                    ],
                ]
            ],
            'Panoramic' => [
                'generations' => [
                    '2013-2018 (Gen 4 C26)' => [
                        'engine' => [
                            ['cc' => 2000, 'code' => 'MR20DE', 'fuel_type' => 'Bensin',
                             'transmission' => ['CVT'], 'power' => 147, 'torque' => 200]
                        ]
                    ],
                ]
            ],
            'e-Power' => [
                'generations' => [
                    '2022-Now (Gen 6 C28)' => [
                        'engine' => [
                            ['cc' => 1500, 'code' => 'HR15DE + EM57', 'fuel_type' => 'Hybrid',
                             'transmission' => ['AT'], 'power' => 136, 'torque' => 320]
                        ]
                    ],
                ]
            ],
        ];

        foreach ($types as $typeData) {
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
            str_contains($period, '2000-2005') => range(2000, 2005),
            str_contains($period, '2005-2010') => range(2005, 2010),
            str_contains($period, '2013-2018') => range(2013, 2018),
            str_contains($period, '2019-2021') => range(2019, 2021),
            str_contains($period, '2022-Now') => range(2022, $currentYear),
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
        $transText = $transmission === 'CVT' ? 'CVT' : ($transmission === 'AT' ? 'matic' : 'manual');
        $engineSize = round($cc / 1000, 1) . 'L';

        return "Nissan Serena {$typeName} {$engineSize} {$transText} {$fuelType} "
             . "({$power} hp, {$torque} Nm) tahun {$year}. Mesin {$engineCode}, "
             . "Generasi {$generation}, MPV keluarga andalan Nissan.";
    }
}
