<?php

namespace Database\Seeders\Brands\Wuling;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class AirEVSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Wuling']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Air EV'
        ]);

        $airEvTypes = [
            ['name' => 'Lite'],
            ['name' => 'Standard Range'],
            ['name' => 'Long Range'],
        ];

        $typeConfigurations = [
            'Lite' => [
                'generations' => [
                    '2023-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 0,
                                'fuel_type' => 'Listrik',
                                'engine_code' => 'PML',
                                'transmission' => ['SSG'],
                                'power' => '30 kW'
                            ],
                        ]
                    ],
                ]
            ],
            'Standard Range' => [
                'generations' => [
                    '2022-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 0,
                                'fuel_type' => 'Listrik',
                                'engine_code' => 'PML',
                                'transmission' => ['SSG'],
                                'power' => '30 kW'
                            ],
                        ]
                    ],
                ]
            ],
            'Long Range' => [
                'generations' => [
                    '2022-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 0,
                                'fuel_type' => 'Listrik',
                                'engine_code' => 'PML',
                                'transmission' => ['SSG'],
                                'power' => '30 kW'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($airEvTypes as $typeData) {
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
        $ccText = $cc > 0 ? (round($cc / 1000, 1) . 'L') : 'Electric Motor';
        $fuelText = strtolower($fuelType);
        $bodyType = "City Car";

        $specialFeatures = " Dikenal sebagai kendaraan listrik yang ringkas dan ramah lingkungan.";
        if ($typeName === 'Standard Range') {
            $specialFeatures = " Varian ini menawarkan jarak tempuh yang lebih jauh dari versi Lite.";
        } elseif ($typeName === 'Long Range') {
            $specialFeatures = " Varian Long Range memiliki kapasitas baterai terbesar dan jarak tempuh terjauh.";
        }

        return "Wuling Air EV {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. Kendaraan {$bodyType} yang inovatif, cocok untuk penggunaan perkotaan.{$specialFeatures}";
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
            'SSG' => 'single speed gear',
        ];
        
        return $mapping[$transmission] ?? $transmission;
    }
}
