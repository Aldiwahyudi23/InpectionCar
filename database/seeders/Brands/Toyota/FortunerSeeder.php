<?php

namespace Database\Seeders\Brands\Toyota;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class FortunerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Toyota']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Fortuner'
        ]);

        $fortunerTypes = [
            ['name' => 'G'],
            ['name' => 'VRZ'],
            ['name' => 'TRD Sportivo'],
            ['name' => 'GR Sport'],
        ];

        $typeConfigurations = [
            'G' => [
                'generations' => [
                    '2005-2015' => [
                        'engine' => [
                            [
                                'cc' => 2700,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '2TR-FE',
                                'transmission' => ['AT'],
                                'power' => '158 HP'
                            ],
                            [
                                'cc' => 2500,
                                'fuel_type' => 'Diesel',
                                'engine_code' => '2KD-FTV',
                                'transmission' => ['MT', 'AT'],
                                'power' => '102 HP'
                            ],
                        ]
                    ],
                    '2016-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 2400,
                                'fuel_type' => 'Diesel',
                                'engine_code' => '2GD-FTV',
                                'transmission' => ['AT'],
                                'power' => '149 HP'
                            ],
                            [
                                'cc' => 2700,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '2TR-FE',
                                'transmission' => ['AT'],
                                'power' => '163 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'VRZ' => [
                'generations' => [
                    '2016-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 2400,
                                'fuel_type' => 'Diesel',
                                'engine_code' => '2GD-FTV',
                                'transmission' => ['AT'],
                                'power' => '149 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'TRD Sportivo' => [
                'generations' => [
                    '2016-2021' => [
                        'engine' => [
                            [
                                'cc' => 2400,
                                'fuel_type' => 'Diesel',
                                'engine_code' => '2GD-FTV',
                                'transmission' => ['AT'],
                                'power' => '149 HP'
                            ],
                            [
                                'cc' => 2700,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '2TR-FE',
                                'transmission' => ['AT'],
                                'power' => '163 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'GR Sport' => [
                'generations' => [
                    '2021-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 2800,
                                'fuel_type' => 'Diesel',
                                'engine_code' => '1GD-FTV',
                                'transmission' => ['AT'],
                                'power' => '201 HP'
                            ],
                            [
                                'cc' => 2700,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '2TR-FE',
                                'transmission' => ['AT'],
                                'power' => '163 HP'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($fortunerTypes as $typeData) {
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
                                'segment' => 'SUV',
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
        $bodyType = "SUV";

        $specialFeatures = " Dikenal sebagai SUV tangguh yang cocok untuk segala medan.";
        if ($typeName === 'VRZ') {
            $specialFeatures = " Varian mewah dengan fitur lengkap dan kenyamanan lebih baik.";
        } elseif ($typeName === 'TRD Sportivo') {
            $specialFeatures = " Varian dengan sentuhan sporty dari Toyota Racing Development.";
        } elseif ($typeName === 'GR Sport') {
            $specialFeatures = " Varian tertinggi dengan performa dan tampilan yang lebih agresif.";
        }

        return "Toyota Fortuner {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. Kendaraan {$bodyType} yang sangat populer di Indonesia.{$specialFeatures}";
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
