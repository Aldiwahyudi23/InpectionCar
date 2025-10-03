<?php

namespace Database\Seeders\Brands\Wuling;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class ConferoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Wuling']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Confero S'
        ]);

        $conferoSTypes = [
            ['name' => 'C'],
            ['name' => 'L'],
            ['name' => 'Blind Van'],
        ];

        $typeConfigurations = [
            'C' => [
                'generations' => [
                    '2017-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1500,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'LJ479Q',
                                'transmission' => ['MT'],
                                'power' => '107 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'L' => [
                'generations' => [
                    '2017-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1500,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'LJ479Q',
                                'transmission' => ['MT'],
                                'power' => '107 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'Blind Van' => [
                'generations' => [
                    '2021-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1200,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'L3C',
                                'transmission' => ['MT'],
                                'power' => '77.5 HP'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($conferoSTypes as $typeData) {
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

        $specialFeatures = " Dikenal sebagai MPV yang terjangkau dengan fitur lengkap dan ruang kabin yang luas.";
        if ($typeName === 'Blind Van') {
            $specialFeatures = " Varian kargo yang ideal untuk kebutuhan bisnis.";
        }

        return "Wuling Confero S {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. Kendaraan {$bodyType} yang populer karena value for money dan fitur yang ditawarkan.{$specialFeatures}";
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
