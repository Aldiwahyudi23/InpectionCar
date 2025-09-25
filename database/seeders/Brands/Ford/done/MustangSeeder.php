<?php

namespace Database\Seeders\Brands\Ford;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class MustangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Ford']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Mustang'
        ]);

        $mustangTypes = [
            ['name' => 'EcoBoost'],
            ['name' => 'GT'],
            ['name' => 'Mach 1'],
        ];

        $typeConfigurations = [
            'EcoBoost' => [
                'generations' => [
                    '2015-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 2300,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'EcoBoost',
                                'transmission' => ['AT', 'MT'],
                                'power' => '310 PS'
                            ],
                        ]
                    ],
                ]
            ],
            'GT' => [
                'generations' => [
                    '2015-2023' => [
                        'engine' => [
                            [
                                'cc' => 5000,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'Coyote V8',
                                'transmission' => ['AT', 'MT'],
                                'power' => '460 PS'
                            ],
                        ]
                    ],
                    '2024-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 5000,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'Coyote V8',
                                'transmission' => ['AT', 'MT'],
                                'power' => '480 PS'
                            ],
                        ]
                    ],
                ]
            ],
            'Mach 1' => [
                'generations' => [
                    '2021-2023' => [
                        'engine' => [
                            [
                                'cc' => 5000,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'Coyote V8',
                                'transmission' => ['AT', 'MT'],
                                'power' => '480 PS'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($mustangTypes as $typeData) {
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
                                'segment' => 'Sport Car',
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
        $bodyType = "Sport Car";
        $specialFeatures = " Dikenal sebagai ikon muscle car Amerika dengan desain agresif dan performa tinggi.";

        return "Ford Mustang {$typeName} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Kendaraan {$bodyType} yang menawarkan pengalaman berkendara yang sporty.{$specialFeatures}";
    }
}
