<?php

namespace Database\Seeders\Brands\BMW;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class Seri7Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'BMW']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Seri 7'
        ]);

        $seri7Types = [
            ['name' => '730Li'],
            ['name' => '740Li'],
            ['name' => '750Li'],
            ['name' => '760Li'],
            ['name' => '740e'],
        ];

        $typeConfigurations = [
            '730Li' => [
                'generations' => [
                    '2008-2015 (F01/F02)' => [
                        'engine' => [['cc' => 3000, 'fuel_type' => 'Bensin', 'engine_code' => 'N52B30', 'transmission' => ['AT'], 'power' => '258 PS']],
                    ],
                    '2015-2022 (G11/G12)' => [
                        'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'B48B20', 'transmission' => ['AT'], 'power' => '258 PS']],
                    ],
                ]
            ],
            '740Li' => [
                'generations' => [
                    '2008-2015 (F01/F02)' => [
                        'engine' => [['cc' => 3000, 'fuel_type' => 'Bensin', 'engine_code' => 'N54B30', 'transmission' => ['AT'], 'power' => '326 PS']],
                    ],
                    '2015-2022 (G11/G12)' => [
                        'engine' => [['cc' => 3000, 'fuel_type' => 'Bensin', 'engine_code' => 'B58B30', 'transmission' => ['AT'], 'power' => '326 PS']],
                    ],
                    '2022-Sekarang (G70)' => [
                        'engine' => [['cc' => 3000, 'fuel_type' => 'Bensin', 'engine_code' => 'B58B30M2', 'transmission' => ['AT'], 'power' => '380 PS']],
                    ],
                ]
            ],
            '750Li' => [
                'generations' => [
                    '2008-2015 (F01/F02)' => [
                        'engine' => [['cc' => 4400, 'fuel_type' => 'Bensin', 'engine_code' => 'N63B44', 'transmission' => ['AT'], 'power' => '407 PS']],
                    ],
                    '2015-2022 (G11/G12)' => [
                        'engine' => [['cc' => 4400, 'fuel_type' => 'Bensin', 'engine_code' => 'N63B44', 'transmission' => ['AT'], 'power' => '450 PS']],
                    ],
                ]
            ],
            '760Li' => [
                'generations' => [
                    '2008-2015 (F01/F02)' => [
                        'engine' => [['cc' => 6000, 'fuel_type' => 'Bensin', 'engine_code' => 'N74B60', 'transmission' => ['AT'], 'power' => '544 PS']],
                    ],
                    '2015-2022 (G11/G12)' => [
                        'engine' => [['cc' => 6600, 'fuel_type' => 'Bensin', 'engine_code' => 'N74B66', 'transmission' => ['AT'], 'power' => '610 PS']],
                    ],
                ]
            ],
            '740e' => [
                'generations' => [
                    '2016-2022 (G11/G12)' => [
                        'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin & Listrik', 'engine_code' => 'B48B20', 'transmission' => ['AT'], 'power' => '326 PS']],
                    ],
                ]
            ],
        ];

        foreach ($seri7Types as $typeData) {
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
                                'segment' => 'Sedan Mewah',
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
        $bodyType = "Sedan Mewah";
        $specialFeatures = " Kendaraan {$bodyType} andalan yang menawarkan teknologi terkini, kenyamanan, dan kemewahan tertinggi.";

        if (str_contains($fuelType, 'Listrik')) {
            $specialFeatures = " Varian hybrid yang menggabungkan efisiensi bahan bakar dengan performa dan kemewahan yang tak tertandingi.";
        }
        
        if (str_contains($typeName, '760Li')) {
            $specialFeatures = " Varian paling bertenaga dan termewah dari Seri 7, menggunakan mesin V12 untuk performa superlatif.";
        }

        return "BMW Seri 7 {$typeName} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Kendaraan {$bodyType} yang dikenal karena perpaduan gaya dan performa.{$specialFeatures}";
    }
}
