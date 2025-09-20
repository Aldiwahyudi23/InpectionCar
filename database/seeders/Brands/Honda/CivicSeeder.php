<?php

namespace Database\Seeders\Brands\Honda;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class CivicSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Honda']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Civic'
        ]);

        $civicTypes = [
            ['name' => 'Sedan'],
            ['name' => 'Hatchback'],
            ['name' => 'Coupe'],
            ['name' => 'Type R'],
            ['name' => 'Hybrid'],
        ];

        $typeConfigurations = [
            'Sedan' => [
                'generations' => [
                    '2000-2005 (Gen 7)' => [
                        'engine' => [
                            [
                                'cc' => 1395,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'D14Z2',
                                'transmission' => ['MT','AT'],
                                'power' => '90 HP'
                            ],
                            [
                                'cc' => 1590,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'D16W7',
                                'transmission' => ['MT','AT'],
                                'power' => '110 HP'
                            ],
                        ]
                    ],
                    '2006-2011 (Gen 8)' => [
                        'engine' => [
                            [
                                'cc' => 1799,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'R18A1',
                                'transmission' => ['MT','AT'],
                                'power' => '140 HP'
                            ],
                        ]
                    ],
                    '2012-2015 (Gen 9)' => [
                        'engine' => [
                            [
                                'cc' => 1799,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'R18Z1',
                                'transmission' => ['MT','AT'],
                                'power' => '142 HP'
                            ],
                        ]
                    ],
                    '2016-2021 (Gen 10)' => [
                        'engine' => [
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15B7',
                                'transmission' => ['MT','CVT'],
                                'power' => '173 HP'
                            ],
                            [
                                'cc' => 1596,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K20C2',
                                'transmission' => ['CVT'],
                                'power' => '158 HP'
                            ],
                        ]
                    ],
                    '2022-Sekarang (Gen 11)' => [
                        'engine' => [
                            [
                                'cc' => 1996,
                                'fuel_type' => 'Hybrid',
                                'engine_code' => 'LFC',
                                'transmission' => ['e-CVT'],
                                'power' => '184 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'Hatchback' => [
                'generations' => [
                    '2000-2005 (Gen 7)' => [
                        'engine' => [
                            [
                                'cc' => 1590,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'D16V1',
                                'transmission' => ['MT','AT'],
                                'power' => '110 HP'
                            ],
                        ]
                    ],
                    '2006-2011 (Gen 8)' => [
                        'engine' => [
                            [
                                'cc' => 1998,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K20Z3',
                                'transmission' => ['MT','AT'],
                                'power' => '197 HP'
                            ],
                        ]
                    ],
                    '2017-2021 (Gen 10)' => [
                        'engine' => [
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15BA',
                                'transmission' => ['MT','CVT'],
                                'power' => '180 HP'
                            ],
                        ]
                    ],
                    '2022-Sekarang (Gen 11)' => [
                        'engine' => [
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L15C7',
                                'transmission' => ['CVT'],
                                'power' => '182 HP'
                            ],
                            [
                                'cc' => 1996,
                                'fuel_type' => 'Hybrid',
                                'engine_code' => 'LFC',
                                'transmission' => ['e-CVT'],
                                'power' => '184 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'Coupe' => [
                'generations' => [
                    '2000-2005 (Gen 7)' => [
                        'engine' => [
                            [
                                'cc' => 1590,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'D16V1',
                                'transmission' => ['MT','AT'],
                                'power' => '115 HP'
                            ],
                        ]
                    ],
                    '2006-2011 (Gen 8)' => [
                        'engine' => [
                            [
                                'cc' => 1998,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K20Z3',
                                'transmission' => ['MT','AT'],
                                'power' => '197 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'Type R' => [
                'generations' => [
                    '2001-2005 (EP3)' => [
                        'engine' => [
                            [
                                'cc' => 1998,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K20A2',
                                'transmission' => ['MT'],
                                'power' => '212 HP'
                            ],
                        ]
                    ],
                    '2007-2011 (FD2/FN2)' => [
                        'engine' => [
                            [
                                'cc' => 1998,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K20A',
                                'transmission' => ['MT'],
                                'power' => '225 HP'
                            ],
                        ]
                    ],
                    '2015-2021 (FK8)' => [
                        'engine' => [
                            [
                                'cc' => 1996,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K20C1',
                                'transmission' => ['MT'],
                                'power' => '306 HP'
                            ],
                        ]
                    ],
                    '2022-Sekarang (FL5)' => [
                        'engine' => [
                            [
                                'cc' => 1996,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K20C1',
                                'transmission' => ['MT'],
                                'power' => '315 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'Hybrid' => [
                'generations' => [
                    '2003-2005 (Gen 7)' => [
                        'engine' => [
                            [
                                'cc' => 1339,
                                'fuel_type' => 'Hybrid',
                                'engine_code' => 'LDA-MF5',
                                'transmission' => ['CVT'],
                                'power' => '93 HP'
                            ],
                        ]
                    ],
                    '2012-2015 (Gen 9)' => [
                        'engine' => [
                            [
                                'cc' => 1497,
                                'fuel_type' => 'Hybrid',
                                'engine_code' => 'LDA-MF6',
                                'transmission' => ['CVT'],
                                'power' => '110 HP'
                            ],
                        ]
                    ],
                    '2022-Sekarang (Gen 11)' => [
                        'engine' => [
                            [
                                'cc' => 1996,
                                'fuel_type' => 'Hybrid',
                                'engine_code' => 'LFC',
                                'transmission' => ['e-CVT'],
                                'power' => '184 HP'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($civicTypes as $typeData) {
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
                        $ccBulet = round($engineConfig['cc']/1000, 1);
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
                                'segment' => $this->getSegment($typeName),
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
        if (str_contains($period, '2000-2005')) {
            return range(2000, 2005);
        } elseif (str_contains($period, '2001-2005')) {
            return range(2001, 2005);
        } elseif (str_contains($period, '2003-2005')) {
            return range(2003, 2005);
        } elseif (str_contains($period, '2006-2011')) {
            return range(2006, 2011);
        } elseif (str_contains($period, '2007-2011')) {
            return range(2007, 2011);
        } elseif (str_contains($period, '2012-2015')) {
            return range(2012, 2015);
        } elseif (str_contains($period, '2015-2021')) {
            return range(2015, 2021);
        } elseif (str_contains($period, '2016-2021')) {
            return range(2016, 2021);
        } elseif (str_contains($period, '2017-2021')) {
            return range(2017, 2021);
        } elseif (str_contains($period, '2022-Sekarang')) {
            return range(2022, $currentYear);
        }
        return [];
    }

    private function getSegment($typeName): string
    {
        if ($typeName === 'Type R') {
            return 'Performance Compact';
        }
        return 'Compact Car';
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $fuelType, $generation, $power): string
    {
        $transmissionText = $this->getTransmissionText($transmission);
        $ccText = round($cc/1000, 1) . 'L';
        $fuelText = strtolower($fuelType);
        $bodyType = $this->getBodyType($typeName);

        return "Honda Civic {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. Desain {$bodyType} yang sporty dan teknologi canggih khas Honda.";
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

    private function getBodyType($typeName): string
    {
        if ($typeName === 'Sedan') return 'sedan 4 pintu';
        if ($typeName === 'Hatchback') return 'hatchback 5 pintu';
        if ($typeName === 'Coupe') return 'coupe 2 pintu';
        if ($typeName === 'Type R') return 'hot hatch';
        
        return 'mobil';
    }
}