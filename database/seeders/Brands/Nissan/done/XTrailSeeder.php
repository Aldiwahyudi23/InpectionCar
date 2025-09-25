<?php

namespace Database\Seeders\Brands\Nissan;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class XTrailSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Nissan']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'X-Trail'
        ]);

        $types = [
            ['name' => 'ST'],
            ['name' => 'XT'],
            ['name' => 'X-Tremer'],
            ['name' => '2.0'],
            ['name' => '2.5'],
            ['name' => 'VL'],
        ];

        $typeConfigurations = [
            'ST' => [
                'generations' => [
                    '2001-2008 (Gen 1)' => [
                        'engine' => [
                            ['cc' => 2000, 'code' => 'QR20DE', 'fuel_type' => 'Bensin',
                             'transmission' => ['MT', 'AT'], 'power' => 140, 'torque' => 200]
                        ]
                    ],
                ]
            ],
            'XT' => [
                'generations' => [
                    '2001-2008 (Gen 1)' => [
                        'engine' => [
                            ['cc' => 2500, 'code' => 'QR25DE', 'fuel_type' => 'Bensin',
                             'transmission' => ['AT'], 'power' => 180, 'torque' => 245]
                        ]
                    ],
                ]
            ],
            'X-Tremer' => [
                'generations' => [
                    '2008-2013 (Gen 2)' => [
                        'engine' => [
                            ['cc' => 2500, 'code' => 'QR25DE', 'fuel_type' => 'Bensin',
                             'transmission' => ['CVT'], 'power' => 170, 'torque' => 230]
                        ]
                    ],
                ]
            ],
            '2.0' => [
                'generations' => [
                    '2014-2019 (Gen 3)' => [
                        'engine' => [
                            ['cc' => 2000, 'code' => 'MR20DD', 'fuel_type' => 'Bensin',
                             'transmission' => ['CVT'], 'power' => 144, 'torque' => 200]
                        ]
                    ],
                ]
            ],
            '2.5' => [
                'generations' => [
                    '2014-2019 (Gen 3)' => [
                        'engine' => [
                            ['cc' => 2500, 'code' => 'QR25DE', 'fuel_type' => 'Bensin',
                             'transmission' => ['CVT'], 'power' => 171, 'torque' => 233]
                        ]
                    ],
                ]
            ],
            'VL' => [
                'generations' => [
                    '2022-Now (Gen 4)' => [
                        'engine' => [
                            ['cc' => 2500, 'code' => 'PR25DD', 'fuel_type' => 'Bensin',
                             'transmission' => ['CVT'], 'power' => 181, 'torque' => 245]
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
            str_contains($period, '2001-2008') => range(2001, 2008),
            str_contains($period, '2008-2013') => range(2008, 2013),
            str_contains($period, '2014-2019') => range(2014, 2019),
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

        return "Nissan X-Trail {$typeName} {$engineSize} {$transText} {$fuelType} "
             . "({$power} hp, {$torque} Nm) tahun {$year}. Mesin {$engineCode}, "
             . "Generasi {$generation}, SUV andalan Nissan untuk pasar Indonesia.";
    }
}