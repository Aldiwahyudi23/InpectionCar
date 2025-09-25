<?php

namespace Database\Seeders\Brands\Suzuki;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class XL7Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Suzuki']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'XL7'
        ]);

        $xl7Types = [
            ['name' => 'Zeta'],
            ['name' => 'Beta'],
            ['name' => 'Alpha'],
        ];

        $typeConfigurations = [
            'Zeta' => [
                'generations' => [
                    '2020-Sekarang (Gen 1)' => [
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
            'Beta' => [
                'generations' => [
                    '2020-2022 (Gen 1 Non-Hybrid)' => [
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
                    '2023-Sekarang (Smart Hybrid)' => [
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
            'Alpha' => [
                'generations' => [
                    '2020-2022 (Gen 1 Non-Hybrid)' => [
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
                    '2023-Sekarang (Smart Hybrid)' => [
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

        foreach ($xl7Types as $typeData) {
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
        if (str_contains($period, '2020-Sekarang')) {
            return range(2020, $currentYear);
        }
        if (str_contains($period, '2020-2022')) {
            return range(2020, 2022);
        }
        if (str_contains($period, '2023-Sekarang')) {
            return range(2023, $currentYear);
        }
        return [];
    }

    /**
     * Generate a descriptive string for a car detail.
     */
    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $fuelType, $generation, $power): string
    {
        $transmissionText = $this->getTransmissionText($transmission);
        $ccText = round($cc / 1000, 1) . 'L';
        $fuelText = strtolower($fuelType);
        $bodyType = "SUV 7-Seater";

        $specialFeatures = "";
        if ($typeName === 'Zeta') {
            $specialFeatures = " Varian entry-level yang menawarkan fitur dasar dengan desain sporty.";
        } elseif ($typeName === 'Beta') {
            if (str_contains($generation, 'Hybrid')) {
                $specialFeatures = " Varian mid-range dengan fitur kenyamanan dan keselamatan tambahan, serta dilengkapi teknologi Smart Hybrid Vehicle by Suzuki (SHVS).";
            } else {
                $specialFeatures = " Varian mid-range dengan fitur kenyamanan dan keselamatan tambahan.";
            }
        } elseif ($typeName === 'Alpha') {
            if (str_contains($generation, 'Hybrid')) {
                $specialFeatures = " Varian tertinggi dengan fitur terlengkap, termasuk tampilan eksterior paling premium dan teknologi Smart Hybrid Vehicle by Suzuki (SHVS).";
            } else {
                $specialFeatures = " Varian tertinggi dengan fitur terlengkap, termasuk tampilan eksterior yang paling premium.";
            }
        }

        return "Suzuki XL7 {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. Mobil {$bodyType} yang tangguh dan stylish.{$specialFeatures}";
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
