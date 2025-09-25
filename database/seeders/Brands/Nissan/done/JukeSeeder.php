<?php

namespace Database\Seeders\Brands\Nissan;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class JukeSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Nissan']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Juke'
        ]);

        $types = [
            ['name' => 'RX'],
            ['name' => 'RX Red Edition'],
            ['name' => 'RX Revolution'],
            ['name' => 'XV'],
            ['name' => 'Nismo'],
            ['name' => 'Nismo RS'],
        ];

        $typeConfigurations = [
            'RX' => [
                'generations' => [
                    '2010-2014 (Gen 1 Pre-Facelift)' => [
                        'engine' => [
                            ['cc' => 1500, 'code' => 'HR15DE', 'fuel_type' => 'Bensin',
                             'transmission' => ['AT'], 'power' => 114, 'torque' => 150]
                        ]
                    ],
                    '2015-2019 (Gen 1 Facelift)' => [
                        'engine' => [
                            ['cc' => 1500, 'code' => 'HR15DE', 'fuel_type' => 'Bensin',
                             'transmission' => ['CVT'], 'power' => 114, 'torque' => 150]
                        ]
                    ],
                ]
            ],
            'RX Red Edition' => [
                'generations' => [
                    '2013-2014 (Gen 1 Special Edition)' => [
                        'engine' => [
                            ['cc' => 1500, 'code' => 'HR15DE', 'fuel_type' => 'Bensin',
                             'transmission' => ['AT'], 'power' => 114, 'torque' => 150]
                        ]
                    ],
                ]
            ],
            'RX Revolution' => [
                'generations' => [
                    '2015-2019 (Gen 1 Facelift)' => [
                        'engine' => [
                            ['cc' => 1500, 'code' => 'HR15DE', 'fuel_type' => 'Bensin',
                             'transmission' => ['CVT'], 'power' => 114, 'torque' => 150]
                        ]
                    ],
                ]
            ],
            'XV' => [
                'generations' => [
                    '2010-2019 (Gen 1)' => [
                        'engine' => [
                            ['cc' => 1500, 'code' => 'HR15DE', 'fuel_type' => 'Bensin',
                             'transmission' => ['AT','CVT'], 'power' => 114, 'torque' => 150]
                        ]
                    ],
                ]
            ],
            'Nismo' => [
                'generations' => [
                    '2013-2019 (Gen 1 Performance)' => [
                        'engine' => [
                            ['cc' => 1600, 'code' => 'MR16DDT Turbo', 'fuel_type' => 'Bensin',
                             'transmission' => ['MT','CVT'], 'power' => 197, 'torque' => 250]
                        ]
                    ],
                ]
            ],
            'Nismo RS' => [
                'generations' => [
                    '2014-2019 (Gen 1 Performance RS)' => [
                        'engine' => [
                            ['cc' => 1600, 'code' => 'MR16DDT Turbo', 'fuel_type' => 'Bensin',
                             'transmission' => ['MT'], 'power' => 215, 'torque' => 280]
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
                                'segment' => 'Crossover',
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
            str_contains($period, '2010-2014') => range(2010, 2014),
            str_contains($period, '2013-2014') => range(2013, 2014),
            str_contains($period, '2015-2019') => range(2015, 2019),
            str_contains($period, '2010-2019') => range(2010, 2019),
            str_contains($period, '2013-2019') => range(2013, 2019),
            str_contains($period, '2014-2019') => range(2014, 2019),
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

        return "Nissan Juke {$typeName} {$engineSize} {$transText} {$fuelType} "
             . "({$power} hp, {$torque} Nm) tahun {$year}. Mesin {$engineCode}, "
             . "Generasi {$generation}, crossover ikonik dengan desain unik.";
    }
}
