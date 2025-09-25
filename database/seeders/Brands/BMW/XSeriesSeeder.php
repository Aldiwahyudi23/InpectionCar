<?php

namespace Database\Seeders\Brands\BMW;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class XSeriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'BMW']);

        $xSeriesModels = [
            'X1' => [
                'types' => [
                    ['name' => 'sDrive18i'],
                    ['name' => 'sDrive20i'],
                ],
                'configurations' => [
                    'sDrive18i' => [
                        'generations' => [
                            '2015-2022' => [
                                'engine' => [['cc' => 1500, 'fuel_type' => 'Bensin', 'engine_code' => 'B38B15', 'transmission' => ['AT'], 'power' => '140 PS']],
                            ],
                            '2022-Sekarang' => [
                                'engine' => [['cc' => 1500, 'fuel_type' => 'Bensin', 'engine_code' => 'B38B15', 'transmission' => ['AT'], 'power' => '156 PS']],
                            ],
                        ]
                    ],
                    'sDrive20i' => [
                        'generations' => [
                            '2015-2022' => [
                                'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'B48A20', 'transmission' => ['AT'], 'power' => '192 PS']],
                            ],
                            '2022-Sekarang' => [
                                'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'B48B20', 'transmission' => ['AT'], 'power' => '204 PS']],
                            ],
                        ]
                    ],
                ]
            ],
            'X3' => [
                'types' => [
                    ['name' => 'sDrive20i'],
                    ['name' => 'xDrive30i'],
                ],
                'configurations' => [
                    'sDrive20i' => [
                        'generations' => [
                            '2017-Sekarang' => [
                                'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'B48B20', 'transmission' => ['AT'], 'power' => '184 PS']],
                            ],
                        ]
                    ],
                    'xDrive30i' => [
                        'generations' => [
                            '2017-Sekarang' => [
                                'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'B48B20', 'transmission' => ['AT'], 'power' => '252 PS']],
                            ],
                        ]
                    ],
                ]
            ],
            'X5' => [
                'types' => [
                    ['name' => 'xDrive40i'],
                    ['name' => 'xDrive45e'],
                ],
                'configurations' => [
                    'xDrive40i' => [
                        'generations' => [
                            '2018-Sekarang' => [
                                'engine' => [['cc' => 3000, 'fuel_type' => 'Bensin', 'engine_code' => 'B58B30', 'transmission' => ['AT'], 'power' => '340 PS']],
                            ],
                        ]
                    ],
                    'xDrive45e' => [
                        'generations' => [
                            '2019-Sekarang' => [
                                'engine' => [['cc' => 3000, 'fuel_type' => 'Bensin & Listrik', 'engine_code' => 'B58B30', 'transmission' => ['AT'], 'power' => '394 PS']],
                            ],
                        ]
                    ],
                ]
            ],
            'X7' => [
                'types' => [
                    ['name' => 'xDrive40i'],
                ],
                'configurations' => [
                    'xDrive40i' => [
                        'generations' => [
                            '2019-Sekarang' => [
                                'engine' => [['cc' => 3000, 'fuel_type' => 'Bensin', 'engine_code' => 'B58B30', 'transmission' => ['AT'], 'power' => '340 PS']],
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($xSeriesModels as $modelName => $modelData) {
            $model = CarModel::firstOrCreate([
                'brand_id' => $brand->id,
                'name' => $modelName
            ]);

            foreach ($modelData['types'] as $typeData) {
                $type = CarType::firstOrCreate([
                    'name' => $typeData['name'],
                    'car_model_id' => $model->id
                ]);

                $this->generateTypeVariants(
                    $brand->id,
                    $model->id,
                    $type->id,
                    $typeData['name'],
                    $modelData['configurations']
                );
            }
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
        $bodyType = "SUV";
        $specialFeatures = " Kendaraan {$bodyType} premium yang menawarkan kombinasi performa, kemewahan, dan fungsionalitas.";

        if (str_contains($fuelType, 'Listrik')) {
            $specialFeatures = " Varian hybrid yang menggabungkan efisiensi bahan bakar dan performa dari motor listrik.";
        }

        return "BMW {$typeName} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "{$bodyType} yang dikenal karena performa dan kenyamanannya.{$specialFeatures}";
    }
}
