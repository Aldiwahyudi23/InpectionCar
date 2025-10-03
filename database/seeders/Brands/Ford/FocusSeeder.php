<?php

namespace Database\Seeders\Brands\Ford;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class FocusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Ford']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Focus'
        ]);

        $focusTypes = [
            ['name' => 'Trend'],
            ['name' => 'Sport'],
            ['name' => 'Titanium'],
            ['name' => 'ST'],
        ];

        $typeConfigurations = [
            'Trend' => [
                'generations' => [
                    '2012-2015' => [
                        'engine' => [
                            [
                                'cc' => 1600,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'Duratec Ti-VCT',
                                'transmission' => ['MT', 'AT'],
                                'power' => '125 PS'
                            ],
                        ]
                    ],
                ]
            ],
            'Sport' => [
                'generations' => [
                    '2012-2015' => [
                        'engine' => [
                            [
                                'cc' => 2000,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'Duratec GDI Ti-VCT',
                                'transmission' => ['AT'],
                                'power' => '170 PS'
                            ],
                        ]
                    ],
                    '2015-2018' => [
                        'engine' => [
                            [
                                'cc' => 1500,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'EcoBoost',
                                'transmission' => ['AT'],
                                'power' => '180 PS'
                            ],
                        ]
                    ],
                ]
            ],
            'Titanium' => [
                'generations' => [
                    '2012-2015' => [
                        'engine' => [
                            [
                                'cc' => 2000,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'Duratec GDI Ti-VCT',
                                'transmission' => ['AT'],
                                'power' => '170 PS'
                            ],
                        ]
                    ],
                    '2015-2018' => [
                        'engine' => [
                            [
                                'cc' => 1500,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'EcoBoost',
                                'transmission' => ['AT'],
                                'power' => '180 PS'
                            ],
                        ]
                    ],
                ]
            ],
            'ST' => [
                'generations' => [
                    '2013-2015' => [
                        'engine' => [
                            [
                                'cc' => 2000,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'EcoBoost',
                                'transmission' => ['MT'],
                                'power' => '250 PS'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($focusTypes as $typeData) {
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
                                'segment' => 'Hatchback/Sedan',
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
        $bodyType = "Hatchback/Sedan";
        $specialFeatures = " Dikenal dengan handling yang presisi dan teknologi modern.";

        if ($typeName === 'ST') {
            $specialFeatures = " Varian performa tinggi dengan mesin bertenaga dan handling sporty.";
        }
        return "Ford Focus {$typeName} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Kendaraan {$bodyType} yang menawarkan perpaduan antara kenyamanan dan performa.{$specialFeatures}";
    }
}
