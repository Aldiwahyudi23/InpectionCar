<?php

namespace Database\Seeders\Brands\Toyota;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class CorollaAltisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Toyota']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Corolla Altis'
        ]);

        $altisTypes = [
            ['name' => 'G'],
            ['name' => 'V'],
            ['name' => 'Q'],
            ['name' => 'Hybrid'],
        ];

        $typeConfigurations = [
            'G' => [
                'generations' => [
                    '2000-2007' => [
                        'engine' => [
                            [
                                'cc' => 1800,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '1ZZ-FE',
                                'transmission' => ['MT', 'AT'],
                                'power' => '134 HP'
                            ],
                        ]
                    ],
                    '2008-2013' => [
                        'engine' => [
                            [
                                'cc' => 1800,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '1ZZ-FE',
                                'transmission' => ['MT', 'AT'],
                                'power' => '132 HP'
                            ],
                        ]
                    ],
                    '2014-2019' => [
                        'engine' => [
                            [
                                'cc' => 1800,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '2ZR-FE',
                                'transmission' => ['CVT'],
                                'power' => '151 HP'
                            ],
                        ]
                    ],
                    '2019-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1800,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '2ZR-FE',
                                'transmission' => ['CVT'],
                                'power' => '138 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'V' => [
                'generations' => [
                    '2000-2007' => [
                        'engine' => [
                            [
                                'cc' => 1800,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '1ZZ-FE',
                                'transmission' => ['AT'],
                                'power' => '134 HP'
                            ],
                        ]
                    ],
                    '2008-2013' => [
                        'engine' => [
                            [
                                'cc' => 1800,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '1ZZ-FE',
                                'transmission' => ['AT'],
                                'power' => '132 HP'
                            ],
                            [
                                'cc' => 2000,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '3ZR-FE',
                                'transmission' => ['AT'],
                                'power' => '145 HP'
                            ]
                        ]
                    ],
                    '2014-2019' => [
                        'engine' => [
                            [
                                'cc' => 1800,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '2ZR-FE',
                                'transmission' => ['CVT'],
                                'power' => '151 HP'
                            ],
                        ]
                    ],
                    '2019-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1800,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '2ZR-FE',
                                'transmission' => ['CVT'],
                                'power' => '138 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'Q' => [
                'generations' => [
                    '2019-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1800,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '2ZR-FE',
                                'transmission' => ['CVT'],
                                'power' => '138 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'Hybrid' => [
                'generations' => [
                    '2019-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1800,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '2ZR-FXE',
                                'transmission' => ['e-CVT'],
                                'power' => '97 HP (mesin) + 71 HP (motor listrik)'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($altisTypes as $typeData) {
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
                                'segment' => 'Sedan',
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
        $bodyType = "Sedan";

        $specialFeatures = " Dikenal sebagai sedan legendaris yang tangguh.";
        if ($typeName === 'Hybrid') {
            $specialFeatures = " Varian Hybrid dengan teknologi TNGA untuk efisiensi dan kenyamanan.";
        }

        return "Toyota Corolla Altis {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. Kendaraan {$bodyType} yang sangat diminati di kelasnya.{$specialFeatures}";
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
