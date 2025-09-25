<?php

namespace Database\Seeders\Brands\Wuling;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class CortezSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Wuling']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Cortez'
        ]);

        $cortezTypes = [
            ['name' => 'S'],
            ['name' => 'C'],
            ['name' => 'L'],
            ['name' => 'EX'],
        ];

        $typeConfigurations = [
            'S' => [
                'generations' => [
                    '2019-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1500,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'LJ0Q',
                                'transmission' => ['MT', 'CVT'],
                                'power' => '140 HP'
                            ],
                            [
                                'cc' => 1800,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L4C24',
                                'transmission' => ['MT'],
                                'power' => '129 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'C' => [
                'generations' => [
                    '2018-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1800,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L4C24',
                                'transmission' => ['MT', 'AMT'],
                                'power' => '129 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'L' => [
                'generations' => [
                    '2018-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1800,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L4C24',
                                'transmission' => ['AMT'],
                                'power' => '129 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'EX' => [
                'generations' => [
                    '2022-Sekarang' => [
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
        ];

        foreach ($cortezTypes as $typeData) {
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
        $bodyType = "MPV";

        $specialFeatures = " Dikenal dengan fitur-fitur modern dan interior yang nyaman.";
        if ($typeName === 'EX') {
            $specialFeatures = " Varian EX dilengkapi dengan Wuling Indonesia Command (WIND) dan transmisi CVT.";
        }

        return "Wuling Cortez {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. Kendaraan {$bodyType} yang menawarkan kenyamanan dan fitur premium di kelasnya.{$specialFeatures}";
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
            'AMT' => 'AMT (Automated Manual Transmission)',
        ];
        
        return $mapping[$transmission] ?? $transmission;
    }
}
