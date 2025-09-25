<?php

namespace Database\Seeders\Brands\Suzuki;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class ErtigaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Suzuki']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Ertiga'
        ]);

        $ertigaTypes = [
            ['name' => 'GA'],
            ['name' => 'GL'],
            ['name' => 'GX'],
            ['name' => 'Sporty'],
            ['name' => 'Dreza'],
            ['name' => 'Hybrid'],
            ['name' => 'Suzuki Sport'],
        ];

        $typeConfigurations = [
            'GA' => [
                'generations' => [
                    '2012-2018 (Gen 1)' => [
                        'engine' => [
                            [
                                'cc' => 1373,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K14B',
                                'transmission' => ['MT', 'AT'],
                                'power' => '95 HP'
                            ],
                        ]
                    ],
                    '2018-2022 (Gen 2)' => [
                        'engine' => [
                            [
                                'cc' => 1462,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K15B',
                                'transmission' => ['MT', 'AT'],
                                'power' => '104 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'GL' => [
                'generations' => [
                    '2012-2018 (Gen 1)' => [
                        'engine' => [
                            [
                                'cc' => 1373,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K14B',
                                'transmission' => ['MT', 'AT'],
                                'power' => '95 HP'
                            ],
                        ]
                    ],
                    '2018-2022 (Gen 2)' => [
                        'engine' => [
                            [
                                'cc' => 1462,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K15B',
                                'transmission' => ['MT', 'AT'],
                                'power' => '104 HP'
                            ],
                        ]
                    ],
                    '2022-Sekarang (Smart Hybrid)' => [
                        'engine' => [
                            [
                                'cc' => 1462,
                                'fuel_type' => 'Bensin (Hybrid)',
                                'engine_code' => 'K15B + SHVS',
                                'transmission' => ['MT', 'AT'],
                                'power' => '104 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'GX' => [
                'generations' => [
                    '2012-2018 (Gen 1)' => [
                        'engine' => [
                            [
                                'cc' => 1373,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K14B',
                                'transmission' => ['MT', 'AT'],
                                'power' => '95 HP'
                            ],
                        ]
                    ],
                    '2018-2022 (Gen 2)' => [
                        'engine' => [
                            [
                                'cc' => 1462,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K15B',
                                'transmission' => ['MT', 'AT'],
                                'power' => '104 HP'
                            ],
                        ]
                    ],
                    '2022-Sekarang (Smart Hybrid)' => [
                        'engine' => [
                            [
                                'cc' => 1462,
                                'fuel_type' => 'Bensin (Hybrid)',
                                'engine_code' => 'K15B + SHVS',
                                'transmission' => ['MT', 'AT'],
                                'power' => '104 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'Sporty' => [
                'generations' => [
                    '2014-2016 (Gen 1)' => [
                        'engine' => [
                            [
                                'cc' => 1373,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K14B',
                                'transmission' => ['MT', 'AT'],
                                'power' => '95 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'Dreza' => [
                'generations' => [
                    '2016-2018 (Gen 1 Facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1373,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K14B',
                                'transmission' => ['MT', 'AT'],
                                'power' => '95 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'Suzuki Sport' => [
                'generations' => [
                    '2019-2022 (Gen 2)' => [
                        'engine' => [
                            [
                                'cc' => 1462,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K15B',
                                'transmission' => ['MT', 'AT'],
                                'power' => '104 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'Hybrid' => [
                'generations' => [
                    '2022-Sekarang (Smart Hybrid)' => [
                        'engine' => [
                            [
                                'cc' => 1462,
                                'fuel_type' => 'Bensin (Hybrid)',
                                'engine_code' => 'K15B + SHVS',
                                'transmission' => ['MT', 'AT'],
                                'power' => '104 HP'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($ertigaTypes as $typeData) {
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

    /**
     * Generate car detail variants for a given car type.
     */
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
                                'segment' => 'MPV',
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

    /**
     * Get an array of years for a given production period.
     */
    private function getYearsForGeneration($period, $currentYear): array
    {
        $yearParts = explode('-', $period);
        $startYear = (int)$yearParts[0];
        $endYear = str_contains($yearParts[1], 'Sekarang') ? $currentYear : (int)explode(' ', $yearParts[1])[0];
        
        return range($startYear, $endYear);
    }

    /**
     * Generate a descriptive string for a car detail.
     */
    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $fuelType, $generation, $power): string
    {
        $transmissionText = $this->getTransmissionText($transmission);
        $ccText = round($cc / 1000, 1) . 'L';
        $fuelText = strtolower($fuelType);
        $bodyType = "MPV 7-Seater";

        $specialFeatures = "";
        if (str_contains($generation, 'Smart Hybrid')) {
            $specialFeatures = " Dilengkapi teknologi Smart Hybrid Vehicle by Suzuki (SHVS) untuk efisiensi bahan bakar.";
        } elseif ($typeName === 'Sporty') {
            $specialFeatures = " Varian dengan tampilan eksterior yang lebih sporty dan modern.";
        } elseif ($typeName === 'Dreza') {
            $specialFeatures = " Varian facelift dengan sentuhan mewah dan elegan.";
        }

        return "Suzuki Ertiga {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. Mobil {$bodyType} yang nyaman dan stylish.{$specialFeatures}";
    }

    /**
     * Map a transmission code to a descriptive string.
     */
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
