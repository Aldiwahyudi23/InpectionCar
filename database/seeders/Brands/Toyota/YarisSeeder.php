<?php

namespace Database\Seeders\Brands\Toyota;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class YarisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Toyota']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Yaris'
        ]);

        $yarisTypes = [
            ['name' => 'E'],
            ['name' => 'G'],
            ['name' => 'TRD Sportivo'],
            ['name' => 'GR Sport'],
        ];

        $typeConfigurations = [
            'E' => [
                'generations' => [
                    '2006-2013' => [
                        'engine' => [
                            [
                                'cc' => 1500,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '1NZ-FE',
                                'transmission' => ['MT', 'AT'],
                                'power' => '109 HP'
                            ],
                        ]
                    ],
                    '2014-2019' => [
                        'engine' => [
                            [
                                'cc' => 1500,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '1NZ-FE',
                                'transmission' => ['MT', 'CVT'],
                                'power' => '107 HP'
                            ],
                        ]
                    ],
                    '2020-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1500,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '2NR-FE',
                                'transmission' => ['MT', 'CVT'],
                                'power' => '107 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'G' => [
                'generations' => [
                    '2006-2013' => [
                        'engine' => [
                            [
                                'cc' => 1500,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '1NZ-FE',
                                'transmission' => ['AT'],
                                'power' => '109 HP'
                            ],
                        ]
                    ],
                    '2014-2019' => [
                        'engine' => [
                            [
                                'cc' => 1500,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '1NZ-FE',
                                'transmission' => ['MT', 'CVT'],
                                'power' => '107 HP'
                            ],
                        ]
                    ],
                    '2020-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1500,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '2NR-FE',
                                'transmission' => ['MT', 'CVT'],
                                'power' => '107 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'TRD Sportivo' => [
                'generations' => [
                    '2014-2019' => [
                        'engine' => [
                            [
                                'cc' => 1500,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '1NZ-FE',
                                'transmission' => ['MT', 'CVT'],
                                'power' => '107 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'GR Sport' => [
                'generations' => [
                    '2020-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1500,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '2NR-FE',
                                'transmission' => ['MT', 'CVT'],
                                'power' => '107 HP'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($yarisTypes as $typeData) {
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
                                'segment' => 'Hatchback',
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
        $endYear = str_contains($period, 'Sekarang') ? $currentYear : (int)explode(' ', $yearParts[1])[0];
        
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
        $bodyType = "Hatchback";

        $specialFeatures = " Dikenal dengan desainnya yang stylish dan lincah untuk penggunaan di perkotaan.";
        if ($typeName === 'GR Sport') {
            $specialFeatures = " Varian sporty dengan sentuhan Gazoo Racing yang memberikan performa lebih agresif.";
        }

        return "Toyota Yaris {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. Kendaraan {$bodyType} yang populer di kalangan anak muda karena desainnya yang stylish dan kompak.{$specialFeatures}";
    }

    /**
     * Map a transmission code to a descriptive string.
     */
    private function getTransmissionText($transmission): string
    {
        $mapping = [
            'MT' => 'manual',
            'AT' => 'matic',
            'CVT' => 'CVT (Continuously Variable Transmission)',
            'e-CVT' => 'e-CVT (Continuously Variable Transmission)',
        ];
        
        return $mapping[$transmission] ?? $transmission;
    }
}
