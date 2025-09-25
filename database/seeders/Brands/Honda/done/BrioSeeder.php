<?php

namespace Database\Seeders\Brands\Honda;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class BrioSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Honda']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Brio'
        ]);

        $brioTypes = [
            ['name' => 'E'],
            ['name' => 'E Satya'],
            ['name' => 'S'],
            ['name' => 'RS'],
            ['name' => 'RS Black Top'],
        ];

        $typeConfigurations = [
            'E' => [
                'generations' => [
                    '2011-2016 (Gen 1)' => [
                        'engine' => [
                            [
                                'cc' => 1198,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L12B3',
                                'transmission' => ['MT'],
                                'power' => '88 HP'
                            ],
                        ]
                    ],
                    '2016-2018 (Gen 1 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1198,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L12B3',
                                'transmission' => ['MT'],
                                'power' => '89 HP'
                            ],
                        ]
                    ],
                    '2018-2023 (Gen 2)' => [
                        'engine' => [
                            [
                                'cc' => 1198,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L12B3',
                                'transmission' => ['MT'],
                                'power' => '89 HP'
                            ],
                        ]
                    ],
                    '2023-Sekarang (Gen 2 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1198,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L12B3',
                                'transmission' => ['MT'],
                                'power' => '89 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'E Satya' => [
                'generations' => [
                    '2013-2016 (Gen 1 LCGC)' => [
                        'engine' => [
                            [
                                'cc' => 1198,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L12B3',
                                'transmission' => ['MT'],
                                'power' => '89 HP'
                            ],
                        ]
                    ],
                    '2016-2018 (Gen 1 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1198,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L12B3',
                                'transmission' => ['MT'],
                                'power' => '89 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'S' => [
                'generations' => [
                    '2011-2016 (Gen 1)' => [
                        'engine' => [
                            [
                                'cc' => 1198,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L12B3',
                                'transmission' => ['MT','AT'],
                                'power' => '88 HP'
                            ],
                            [
                                'cc' => 1339,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L13Z1',
                                'transmission' => ['MT','AT'],
                                'power' => '99 HP'
                            ],
                        ]
                    ],
                    '2016-2018 (Gen 1 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1198,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L12B3',
                                'transmission' => ['MT','CVT'],
                                'power' => '89 HP'
                            ],
                        ]
                    ],
                    '2018-2023 (Gen 2)' => [
                        'engine' => [
                            [
                                'cc' => 1198,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L12B3',
                                'transmission' => ['MT','CVT'],
                                'power' => '89 HP'
                            ],
                        ]
                    ],
                    '2023-Sekarang (Gen 2 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1198,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L12B3',
                                'transmission' => ['MT','CVT'],
                                'power' => '89 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'RS' => [
                'generations' => [
                    '2016-2018 (Gen 1 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1198,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L12B3',
                                'transmission' => ['CVT'],
                                'power' => '89 HP'
                            ],
                        ]
                    ],
                    '2018-2023 (Gen 2)' => [
                        'engine' => [
                            [
                                'cc' => 1198,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L12B3',
                                'transmission' => ['CVT'],
                                'power' => '89 HP'
                            ],
                        ]
                    ],
                    '2023-Sekarang (Gen 2 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1198,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L12B3',
                                'transmission' => ['CVT'],
                                'power' => '89 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'RS Black Top' => [
                'generations' => [
                    '2018-2023 (Gen 2)' => [
                        'engine' => [
                            [
                                'cc' => 1198,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L12B3',
                                'transmission' => ['CVT'],
                                'power' => '89 HP'
                            ],
                        ]
                    ],
                    '2023-Sekarang (Gen 2 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1198,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L12B3',
                                'transmission' => ['CVT'],
                                'power' => '89 HP'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($brioTypes as $typeData) {
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
                                'segment' => 'City Car',
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
        if (str_contains($period, '2011-2016')) {
            return range(2011, 2016);
        } elseif (str_contains($period, '2013-2016')) {
            return range(2013, 2016);
        } elseif (str_contains($period, '2016-2018')) {
            return range(2016, 2018);
        } elseif (str_contains($period, '2018-2023')) {
            return range(2018, 2023);
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
        $bodyType = "hatchback 5 pintu";

        $specialFeatures = "";
        if ($typeName === 'E Satya') {
            $specialFeatures = " Varian LCGC (Low Cost Green Car) yang ekonomis dengan fitur dasar.";
        } elseif ($typeName === 'E') {
            $specialFeatures = " Varian entry level dengan fitur standar.";
        } elseif ($typeName === 'S') {
            $specialFeatures = " Varian mid-range dengan fitur lebih lengkap.";
        } elseif ($typeName === 'RS' || $typeName === 'RS Black Top') {
            $specialFeatures = " Varian sporty dengan fitur premium dan desain agresif.";
        }

        return "Honda Brio {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. Mobil city car {$bodyType} yang efisien dan lincah.{$specialFeatures}";
    }

    private function getTransmissionText($transmission): string
    {
        $mapping = [
            'MT' => 'manual',
            'AT' => 'matic',
            'CVT' => 'CVT'
        ];
        
        return $mapping[$transmission] ?? $transmission;
    }
}