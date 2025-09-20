<?php

namespace Database\Seeders\Brands\Honda;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class CRVSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Honda']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'CR-V'
        ]);

        $crvTypes = [
            ['name' => '-'],
            ['name' => 'Prestige'],
            ['name' => '7-Seater'],
            ['name' => 'Hybrid'],
        ];

        $typeConfigurations = [
            '-' => [
                'generations' => [
                    '2000-2002 (Gen 1)' => [
                        'engine' => [
                            [
                                'cc' => 1997,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'B20B',
                                'transmission' => ['MT','AT'],
                                'power' => '126 HP'
                            ],
                            [
                                'cc' => 1997,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'B20Z',
                                'transmission' => ['MT','AT'],
                                'power' => '146 HP'
                            ],
                        ]
                    ],
                    '2002-2005 (Gen 2 awal)' => [
                        'engine' => [
                            [
                                'cc' => 1998,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K20A',
                                'transmission' => ['MT','AT'],
                                'power' => '150 HP'
                            ],
                        ]
                    ],
                    '2005-2007 (Gen 2 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1998,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K20A',
                                'transmission' => ['MT','AT'],
                                'power' => '150 HP'
                            ],
                            [
                                'cc' => 2354,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K24A',
                                'transmission' => ['AT'],
                                'power' => '160 HP'
                            ],
                        ]
                    ],
                    '2007-2010 (Gen 3 awal)' => [
                        'engine' => [
                            [
                                'cc' => 1997,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'R20A',
                                'transmission' => ['MT','AT'],
                                'power' => '150 HP'
                            ],
                            [
                                'cc' => 2354,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K24Z',
                                'transmission' => ['AT'],
                                'power' => '170 HP'
                            ],
                        ]
                    ],
                    '2010-2012 (Gen 3 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1997,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'R20A',
                                'transmission' => ['MT','AT'],
                                'power' => '150 HP'
                            ],
                            [
                                'cc' => 2354,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K24Z',
                                'transmission' => ['AT'],
                                'power' => '170 HP'
                            ],
                        ]
                    ],
                    '2012-2015 (Gen 4 awal)' => [
                        'engine' => [
                            [
                                'cc' => 1997,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'R20A',
                                'transmission' => ['MT','AT'],
                                'power' => '153 HP'
                            ],
                            [
                                'cc' => 2354,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K24Z',
                                'transmission' => ['AT'],
                                'power' => '190 HP'
                            ],
                        ]
                    ],
                    '2015-2017 (Gen 4 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1997,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'R20A',
                                'transmission' => ['MT','AT'],
                                'power' => '153 HP'
                            ],
                            [
                                'cc' => 2354,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K24Z',
                                'transmission' => ['AT'],
                                'power' => '190 HP'
                            ],
                        ]
                    ],
                    '2017-2020 (Gen 5 awal)' => [
                        'engine' => [
                            [
                                'cc' => 1997,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'R20A',
                                'transmission' => ['CVT'],
                                'power' => '153 HP'
                            ],
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15B7',
                                'transmission' => ['CVT'],
                                'power' => '190 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'Prestige' => [
                'generations' => [
                    '2012-2015 (Gen 4)' => [
                        'engine' => [
                            [
                                'cc' => 2354,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K24Z',
                                'transmission' => ['AT'],
                                'power' => '190 HP'
                            ],
                        ]
                    ],
                    '2015-2017 (Gen 4 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 2354,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K24Z',
                                'transmission' => ['AT'],
                                'power' => '190 HP'
                            ],
                        ]
                    ],
                    '2017-2020 (Gen 5)' => [
                        'engine' => [
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15B7',
                                'transmission' => ['CVT'],
                                'power' => '190 HP'
                            ],
                        ]
                    ],
                    '2020-2023 (Gen 5 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15B7',
                                'transmission' => ['CVT'],
                                'power' => '190 HP'
                            ],
                        ]
                    ],
                    '2023-Sekarang (Gen 6)' => [
                        'engine' => [
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15BL',
                                'transmission' => ['CVT'],
                                'power' => '190 HP'
                            ],
                        ]
                    ],
                ]
            ],
            '7-Seater' => [
                'generations' => [
                    '2000-2002 (Gen 1)' => [
                        'engine' => [
                            [
                                'cc' => 1997,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'B20B',
                                'transmission' => ['MT','AT'],
                                'power' => '126 HP'
                            ],
                        ]
                    ],
                    '2017-2020 (Gen 5)' => [
                        'engine' => [
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15B7',
                                'transmission' => ['CVT'],
                                'power' => '190 HP'
                            ],
                        ]
                    ],
                    '2020-2023 (Gen 5 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15B7',
                                'transmission' => ['CVT'],
                                'power' => '190 HP'
                            ],
                        ]
                    ],
                    '2023-Sekarang (Gen 6)' => [
                        'engine' => [
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15BL',
                                'transmission' => ['CVT'],
                                'power' => '190 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'Hybrid' => [
                'generations' => [
                    '2020-2023 (Gen 5)' => [
                        'engine' => [
                            [
                                'cc' => 1993,
                                'fuel_type' => 'Hybrid',
                                'engine_code' => 'LFB11',
                                'transmission' => ['e-CVT'],
                                'power' => '184 HP'
                            ],
                        ]
                    ],
                    '2023-Sekarang (Gen 6)' => [
                        'engine' => [
                            [
                                'cc' => 1993,
                                'fuel_type' => 'Hybrid',
                                'engine_code' => 'LFB12',
                                'transmission' => ['e-CVT'],
                                'power' => '204 HP'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($crvTypes as $typeData) {
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
                                'segment' => 'Compact SUV',
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
        if (str_contains($period, '2000-2002')) {
            return range(2000, 2002);
        } elseif (str_contains($period, '2002-2005')) {
            return range(2002, 2005);
        } elseif (str_contains($period, '2005-2007')) {
            return range(2005, 2007);
        } elseif (str_contains($period, '2007-2010')) {
            return range(2007, 2010);
        } elseif (str_contains($period, '2010-2012')) {
            return range(2010, 2012);
        } elseif (str_contains($period, '2012-2015')) {
            return range(2012, 2015);
        } elseif (str_contains($period, '2015-2017')) {
            return range(2015, 2017);
        } elseif (str_contains($period, '2017-2020')) {
            return range(2017, 2020);
        } elseif (str_contains($period, '2020-2023')) {
            return range(2020, 2023);
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
        $seating = $typeName === '7-Seater' ? '7-seater' : '5-seater';

        return "Honda CR-V {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. SUV kompak {$seating} yang nyaman dan tangguh.";
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