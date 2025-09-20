<?php

namespace Database\Seeders\Brands\Honda;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Honda']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'City'
        ]);

        $cityTypes = [
            ['name' => 'Sedan'],
            ['name' => 'Hatchback'],
            ['name' => 'RS'],
            ['name' => 'Hybrid'],
        ];

        $typeConfigurations = [
            'Sedan' => [
                'generations' => [
                    '2000-2003 (Gen 2 awal)' => [
                        'engine' => [
                            [
                                'cc' => 1497,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15A1',
                                'transmission' => ['MT','CVT'],
                                'power' => '110 HP'
                            ],
                        ]
                    ],
                    '2003-2005 (Gen 2 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1497,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15A1',
                                'transmission' => ['MT','CVT'],
                                'power' => '110 HP'
                            ],
                        ]
                    ],
                    '2006-2008 (Gen 3 awal)' => [
                        'engine' => [
                            [
                                'cc' => 1497,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15A7',
                                'transmission' => ['MT','AT'],
                                'power' => '120 HP'
                            ],
                        ]
                    ],
                    '2008-2011 (Gen 3 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1497,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15A7',
                                'transmission' => ['MT','AT'],
                                'power' => '120 HP'
                            ],
                        ]
                    ],
                    '2011-2014 (Gen 4 awal)' => [
                        'engine' => [
                            [
                                'cc' => 1497,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15Z1',
                                'transmission' => ['MT','CVT'],
                                'power' => '120 HP'
                            ],
                        ]
                    ],
                    '2014-2017 (Gen 4 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1497,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15Z1',
                                'transmission' => ['MT','CVT'],
                                'power' => '120 HP'
                            ],
                        ]
                    ],
                    '2017-2020 (Gen 5)' => [
                        'engine' => [
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15B2',
                                'transmission' => ['MT','CVT'],
                                'power' => '120 HP'
                            ],
                        ]
                    ],
                    '2020-2023 (Gen 6)' => [
                        'engine' => [
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15C2',
                                'transmission' => ['CVT'],
                                'power' => '121 HP'
                            ],
                        ]
                    ],
                    '2023-Sekarang (Gen 6 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15C2',
                                'transmission' => ['CVT'],
                                'power' => '121 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'Hatchback' => [
                'generations' => [
                    '2021-Sekarang (Gen 6)' => [
                        'engine' => [
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15C2',
                                'transmission' => ['CVT'],
                                'power' => '121 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'RS' => [
                'generations' => [
                    '2014-2017 (Gen 4 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1497,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15Z1',
                                'transmission' => ['CVT'],
                                'power' => '120 HP'
                            ],
                        ]
                    ],
                    '2017-2020 (Gen 5)' => [
                        'engine' => [
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15B2',
                                'transmission' => ['CVT'],
                                'power' => '120 HP'
                            ],
                        ]
                    ],
                    '2020-2023 (Gen 6)' => [
                        'engine' => [
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15C2',
                                'transmission' => ['CVT'],
                                'power' => '121 HP'
                            ],
                        ]
                    ],
                    '2023-Sekarang (Gen 6 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15C2',
                                'transmission' => ['CVT'],
                                'power' => '121 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'Hybrid' => [
                'generations' => [
                    '2021-Sekarang (Gen 6)' => [
                        'engine' => [
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Hybrid',
                                'engine_code' => 'LEB',
                                'transmission' => ['e-CVT'],
                                'power' => '126 HP'
                            ],
                        ]
                    ],
                    '2023-Sekarang (Gen 6 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Hybrid',
                                'engine_code' => 'LEB',
                                'transmission' => ['e-CVT'],
                                'power' => '126 HP'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($cityTypes as $typeData) {
            $type = CarType::firstOrCreate([
                'name' => $typeData['name'],
                'car_model_id' => $model->id
            ]);

            $this->generateTypeVariants(
                $brand->id, 
                $model->id, 
                $type->id, 
                $typeData['name'], 
                $typeConfigurations
            );
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
                                'engine_code' => $engineConfig['engine_code'],
                                'segment' => 'Subcompact Car',
                                'production_period' => $period
                            ],
                            [
                                'description' => $this->generateDescription(
                                    $typeName,
                                    $year,
                                    $engineConfig['cc'],
                                    $engineConfig['engine_code'],
                                    $transmission,
                                    $engineConfig['fuel_type'],
                                    $period,
                                    $engineConfig['power']
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
        if (str_contains($period, '2000-2003')) {
            return range(2000, 2003);
        } elseif (str_contains($period, '2003-2005')) {
            return range(2003, 2005);
        } elseif (str_contains($period, '2006-2008')) {
            return range(2006, 2008);
        } elseif (str_contains($period, '2008-2011')) {
            return range(2008, 2011);
        } elseif (str_contains($period, '2011-2014')) {
            return range(2011, 2014);
        } elseif (str_contains($period, '2014-2017')) {
            return range(2014, 2017);
        } elseif (str_contains($period, '2017-2020')) {
            return range(2017, 2020);
        } elseif (str_contains($period, '2020-2023')) {
            return range(2020, 2023);
        } elseif (str_contains($period, '2021-Sekarang')) {
            return range(2021, $currentYear);
        } elseif (str_contains($period, '2023-Sekarang')) {
            return range(2023, $currentYear);
        }
        return [];
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $fuelType, $generation, $power): string
    {
        $transmissionText = $this->getTransmissionText($transmission);
        $ccText = round($cc/1000, 1) . 'L';
        $fuelText = strtolower($fuelType);
        $bodyType = $typeName === 'Hatchback' ? 'hatchback' : 'sedan';

        return "Honda City {$typeName} {$cc} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. Mobil {$bodyType} subkompak yang efisien dan stylish.";
    }

    private function getTransmissionText($transmission): string
    {
        $mapping = [
            'MT' => 'manual',
            'AT' => 'matic',
            'CVT' => 'CVT',
            'e-CVT' => 'e-CVT'
        ];
        
        return $mapping[$transmission] ?? $transmission;
    }
}