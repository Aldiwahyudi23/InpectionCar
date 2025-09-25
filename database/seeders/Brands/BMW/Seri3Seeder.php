<?php

namespace Database\Seeders\Brands\BMW;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class Seri3Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'BMW']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Seri 3'
        ]);

        $seri3Types = [
            ['name' => '320i'],
            ['name' => '330i'],
            ['name' => '330e'],
            ['name' => 'M340i'],
        ];

        $typeConfigurations = [
            '320i' => [
                'generations' => [
                    '2005-2012' => [
                        'engine' => [
                            [
                                'cc' => 2000,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'N46B20',
                                'transmission' => ['AT', 'MT'],
                                'power' => '150 PS'
                            ],
                        ]
                    ],
                    '2012-2019' => [
                        'engine' => [
                            [
                                'cc' => 2000,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'N20B20',
                                'transmission' => ['AT'],
                                'power' => '184 PS'
                            ],
                        ]
                    ],
                    '2019-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 2000,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'B48B20',
                                'transmission' => ['AT'],
                                'power' => '184 PS'
                            ],
                        ]
                    ],
                ]
            ],
            '330i' => [
                'generations' => [
                    '2005-2012' => [
                        'engine' => [
                            [
                                'cc' => 3000,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'N52B30',
                                'transmission' => ['AT', 'MT'],
                                'power' => '258 PS'
                            ],
                        ]
                    ],
                    '2012-2019' => [
                        'engine' => [
                            [
                                'cc' => 2000,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'B48B20',
                                'transmission' => ['AT'],
                                'power' => '252 PS'
                            ],
                        ]
                    ],
                    '2019-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 2000,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'B48B20',
                                'transmission' => ['AT'],
                                'power' => '258 PS'
                            ],
                        ]
                    ],
                ]
            ],
            '330e' => [
                'generations' => [
                    '2016-2019' => [
                        'engine' => [
                            [
                                'cc' => 2000,
                                'fuel_type' => 'Bensin & Listrik',
                                'engine_code' => 'B48B20',
                                'transmission' => ['AT'],
                                'power' => '252 PS'
                            ],
                        ]
                    ],
                    '2019-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 2000,
                                'fuel_type' => 'Bensin & Listrik',
                                'engine_code' => 'B48B20',
                                'transmission' => ['AT'],
                                'power' => '292 PS'
                            ],
                        ]
                    ],
                ]
            ],
            'M340i' => [
                'generations' => [
                    '2019-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 3000,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'B58B30',
                                'transmission' => ['AT'],
                                'power' => '387 PS'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($seri3Types as $typeData) {
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
                                'segment' => 'Sedan',
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
        $bodyType = "Sedan";
        $specialFeatures = " Dikenal sebagai sedan sport premium dengan handling responsif dan desain ikonik.";

        if (str_contains($fuelType, 'Listrik')) {
            $specialFeatures = " Varian hybrid yang menggabungkan efisiensi dan performa khas BMW.";
        }
        if ($typeName === 'M340i') {
            $specialFeatures = " Varian performa tinggi dari Seri 3, menawarkan tenaga dan handling yang superior.";
        }

        return "BMW Seri 3 {$typeName} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Kendaraan {$bodyType} yang menawarkan perpaduan antara kemewahan dan performa.{$specialFeatures}";
    }
}
