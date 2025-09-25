<?php

namespace Database\Seeders\Brands\Ford;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class RangerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Ford']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Ranger'
        ]);

        $rangerTypes = [
            ['name' => 'Single Cab'],
            ['name' => 'Double Cab'],
            ['name' => 'Wildtrak'],
            ['name' => 'Raptor'],
            ['name' => 'XL'],
        ];

        $typeConfigurations = [
            'Single Cab' => [
                'generations' => [
                    '2012-2022' => [
                        'engine' => [
                            [
                                'cc' => 2200,
                                'fuel_type' => 'Diesel',
                                'engine_code' => 'P4AT Duratorq',
                                'transmission' => ['MT'],
                                'power' => '125 PS'
                            ],
                        ]
                    ],
                ]
            ],
            'Double Cab' => [
                'generations' => [
                    '2012-2022' => [
                        'engine' => [
                            [
                                'cc' => 2200,
                                'fuel_type' => 'Diesel',
                                'engine_code' => 'P4AT Duratorq',
                                'transmission' => ['MT', 'AT'],
                                'power' => '150 PS'
                            ],
                            [
                                'cc' => 3200,
                                'fuel_type' => 'Diesel',
                                'engine_code' => 'P5AT Duratorq',
                                'transmission' => ['AT'],
                                'power' => '200 PS'
                            ],
                        ]
                    ],
                    '2022-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 2000,
                                'fuel_type' => 'Diesel',
                                'engine_code' => 'EcoBlue Bi-Turbo',
                                'transmission' => ['AT'],
                                'power' => '210 PS'
                            ],
                        ]
                    ],
                ]
            ],
            'Wildtrak' => [
                'generations' => [
                    '2012-2022' => [
                        'engine' => [
                            [
                                'cc' => 3200,
                                'fuel_type' => 'Diesel',
                                'engine_code' => 'P5AT Duratorq',
                                'transmission' => ['AT'],
                                'power' => '200 PS'
                            ],
                        ]
                    ],
                    '2022-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 2000,
                                'fuel_type' => 'Diesel',
                                'engine_code' => 'EcoBlue Bi-Turbo',
                                'transmission' => ['AT'],
                                'power' => '210 PS'
                            ],
                        ]
                    ],
                ]
            ],
            'Raptor' => [
                'generations' => [
                    '2018-2022' => [
                        'engine' => [
                            [
                                'cc' => 2000,
                                'fuel_type' => 'Diesel',
                                'engine_code' => 'EcoBlue Bi-Turbo',
                                'transmission' => ['AT'],
                                'power' => '213 PS'
                            ],
                        ]
                    ],
                    '2022-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 2000,
                                'fuel_type' => 'Diesel',
                                'engine_code' => 'EcoBlue Bi-Turbo',
                                'transmission' => ['AT'],
                                'power' => '210 PS'
                            ],
                            [
                                'cc' => 3000,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'EcoBoost V6',
                                'transmission' => ['AT'],
                                'power' => '397 PS'
                            ],
                        ]
                    ],
                ]
            ],
            'XL' => [
                'generations' => [
                    '2022-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 2000,
                                'fuel_type' => 'Diesel',
                                'engine_code' => 'EcoBlue Single-Turbo',
                                'transmission' => ['MT', 'AT'],
                                'power' => '170 PS'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($rangerTypes as $typeData) {
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
                                'segment' => 'Pickup Truck',
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
        $bodyType = "Pickup Truck";
        $specialFeatures = " Kendaraan tangguh yang ideal untuk pekerjaan berat dan petualangan.";

        if ($typeName === 'Raptor') {
            $specialFeatures = " Varian performa tinggi yang dirancang khusus untuk off-road ekstrim.";
        }
        return "Ford Ranger {$typeName} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Kendaraan {$bodyType} yang sangat fungsional dan serbaguna.{$specialFeatures}";
    }
}
