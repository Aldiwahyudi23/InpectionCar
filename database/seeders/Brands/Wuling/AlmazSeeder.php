<?php

namespace Database\Seeders\Brands\Wuling;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class AlmazSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Wuling']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Almaz'
        ]);

        $almazTypes = [
            ['name' => 'Smart Enjoy'],
            ['name' => 'Exclusive'],
            ['name' => 'RS'],
            ['name' => 'Hybrid'],
        ];

        $typeConfigurations = [
            'Smart Enjoy' => [
                'generations' => [
                    '2019-2021' => [
                        'engine' => [
                            [
                                'cc' => 1500,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'LJ0Q',
                                'transmission' => ['MT', 'CVT'],
                                'power' => '140 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'Exclusive' => [
                'generations' => [
                    '2019-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1500,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'LJ0Q',
                                'transmission' => ['CVT'],
                                'power' => '140 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'RS' => [
                'generations' => [
                    '2021-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1500,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'LJ0Q',
                                'transmission' => ['CVT'],
                                'power' => '140 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'Hybrid' => [
                'generations' => [
                    '2022-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 2000,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'LFE',
                                'transmission' => ['DHT'],
                                'power' => '123 HP'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($almazTypes as $typeData) {
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

        $specialFeatures = " Dikenal dengan fitur Voice Command canggih (Wuling Indonesia Command).";
        if ($typeName === 'RS') {
            $specialFeatures = " Varian RS dilengkapi dengan fitur Wuling Interconnected Smart Ecosystem (WISE) yang lebih lengkap.";
        } elseif ($typeName === 'Hybrid') {
            $specialFeatures = " Varian Hybrid yang menggabungkan mesin bensin dan motor listrik untuk efisiensi bahan bakar maksimal.";
        }

        return "Wuling Almaz {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. Kendaraan {$bodyType} yang menonjol dengan fitur-fitur pintar dan modern.{$specialFeatures}";
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
            'DHT' => 'DHT (Dedicated Hybrid Transmission)',
        ];
        
        return $mapping[$transmission] ?? $transmission;
    }
}
