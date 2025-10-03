<?php

namespace Database\Seeders\Brands\Ford;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class FiestaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Ford']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Fiesta'
        ]);

        $fiestaTypes = [
            ['name' => 'Trend'],
            ['name' => 'Sport'],
            ['name' => 'Ecoboost'],
        ];

        $typeConfigurations = [
            'Trend' => [
                'generations' => [
                    '2010-2016' => [
                        'engine' => [
                            [
                                'cc' => 1400,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'Duratec 1.4',
                                'transmission' => ['MT', 'AT'],
                                'power' => '96 PS'
                            ],
                        ]
                    ],
                ]
            ],
            'Sport' => [
                'generations' => [
                    '2010-2016' => [
                        'engine' => [
                            [
                                'cc' => 1600,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'Duratec 1.6 Ti-VCT',
                                'transmission' => ['AT'],
                                'power' => '120 PS'
                            ],
                        ]
                    ],
                ]
            ],
            'Ecoboost' => [
                'generations' => [
                    '2016-2017' => [
                        'engine' => [
                            [
                                'cc' => 1000,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'EcoBoost 1.0',
                                'transmission' => ['AT'],
                                'power' => '125 PS'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($fiestaTypes as $typeData) {
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
                                    $engineConfig['power'],
                                    $engineConfig['fuel_type'],
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
    private function generateDescription($typeName, $year, $cc, $power, $fuelType, $generation): string
    {
        $ccText = round($cc / 1000, 1) . 'L';
        $fuelText = strtolower($fuelType);
        $bodyType = "Hatchback";
        $specialFeatures = " Dikenal dengan desain sporty, handling lincah, dan efisiensi bahan bakar.";

        return "Ford Fiesta {$typeName} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Kendaraan {$bodyType} yang sangat cocok untuk mobilitas di perkotaan.{$specialFeatures}";
    }
}
