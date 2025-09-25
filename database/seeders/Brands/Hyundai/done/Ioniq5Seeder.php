<?php

namespace Database\Seeders\Brands\Hyundai;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class Ioniq5Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Hyundai']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Ioniq 5'
        ]);

        $ioniq5Types = [
            ['name' => 'Prime'],
            ['name' => 'Signature'],
            ['name' => 'Batik'],
        ];

        $typeConfigurations = [
            'Prime' => [
                'generations' => [
                    '2022-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 0,
                                'fuel_type' => 'Baterai',
                                'engine_code' => 'EV',
                                'transmission' => ['SSG'],
                                'power' => '170 PS',
                                'range' => '384 km',
                                'battery_capacity' => '58 kWh'
                            ],
                        ]
                    ],
                ]
            ],
            'Signature' => [
                'generations' => [
                    '2022-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 0,
                                'fuel_type' => 'Baterai',
                                'engine_code' => 'EV',
                                'transmission' => ['SSG'],
                                'power' => '217 PS',
                                'range' => '451 km',
                                'battery_capacity' => '72.6 kWh'
                            ],
                        ]
                    ],
                ]
            ],
            'Batik' => [
                'generations' => [
                    '2022-2023' => [
                        'engine' => [
                            [
                                'cc' => 0,
                                'fuel_type' => 'Baterai',
                                'engine_code' => 'EV',
                                'transmission' => ['SSG'],
                                'power' => '217 PS',
                                'range' => '451 km',
                                'battery_capacity' => '72.6 kWh'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($ioniq5Types as $typeData) {
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
                                'segment' => 'Electric Vehicle',
                                'production_period' => $period
                            ],
                            [
                                'description' => $this->generateDescription(
                                    $typeName,
                                    $year,
                                    $engineConfig['power'],
                                    $engineConfig['range'],
                                    $engineConfig['battery_capacity'],
                                    $period
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
    private function generateDescription($typeName, $year, $power, $range, $batteryCapacity, $generation): string
    {
        $bodyType = "Crossover";
        $specialFeatures = " Dikenal sebagai mobil listrik pertama yang diproduksi di Indonesia, dengan desain futuristik dan fitur V2L (Vehicle-to-Load).";

        if ($typeName === 'Batik') {
            $specialFeatures = " Edisi khusus Ioniq 5 yang menampilkan motif batik, sebagai penghormatan budaya Indonesia.";
        }

        return "Hyundai Ioniq 5 {$typeName} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}, jangkauan {$range}, dan kapasitas baterai {$batteryCapacity}. " .
               "Kendaraan {$bodyType} yang sangat populer sebagai mobil listrik.{$specialFeatures}";
    }
}
