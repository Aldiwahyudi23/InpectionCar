<?php

namespace Database\Seeders\Brands\Mazda;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class Mazda2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Mazda']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => '2'
        ]);

        $mazda2Types = [
            ['name' => 'Hatchback'],
            ['name' => 'Sedan'],
        ];

        $typeConfigurations = [
            'Hatchback' => [
                'generations' => [
                    '2002-2007' => [
                        'engine' => [
                            ['cc' => 1349, 'fuel_type' => 'Bensin', 'engine_code' => 'ZJ-VE', 'transmission' => ['AT', 'MT'], 'power' => '84 PS'],
                            ['cc' => 1498, 'fuel_type' => 'Bensin', 'engine_code' => 'ZY-VE', 'transmission' => ['AT', 'MT'], 'power' => '101 PS'],
                        ],
                    ],
                    '2007-2014' => [
                        'engine' => [
                            ['cc' => 1498, 'fuel_type' => 'Bensin', 'engine_code' => 'Z6', 'transmission' => ['AT', 'MT'], 'power' => '103 PS'],
                        ],
                    ],
                    '2014-Sekarang' => [
                        'engine' => [
                            ['cc' => 1496, 'fuel_type' => 'Bensin', 'engine_code' => 'Skyactiv-G', 'transmission' => ['AT'], 'power' => '110 PS'],
                        ],
                    ],
                ]
            ],
            'Sedan' => [
                'generations' => [
                    '2007-2014' => [
                        'engine' => [
                            ['cc' => 1498, 'fuel_type' => 'Bensin', 'engine_code' => 'Z6', 'transmission' => ['AT', 'MT'], 'power' => '103 PS'],
                        ],
                    ],
                    '2014-Sekarang' => [
                        'engine' => [
                            ['cc' => 1496, 'fuel_type' => 'Bensin', 'engine_code' => 'Skyactiv-G', 'transmission' => ['AT'], 'power' => '110 PS'],
                        ],
                    ],
                ]
            ],
        ];

        foreach ($mazda2Types as $typeData) {
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
                                'segment' => 'Compact Car',
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
        $bodyType = $typeName === 'Hatchback' ? "Hatchback" : "Sedan";
        $specialFeatures = " Dikenal karena handling-nya yang lincah dan desain 'KODO: Soul of Motion' yang menawan. ";

        if ($generation === '2014-Sekarang') {
            $specialFeatures = " Dilengkapi dengan teknologi Skyactiv yang memberikan efisiensi bahan bakar dan performa optimal.";
        }

        return "Mazda 2 {$typeName} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Kendaraan {$bodyType} yang menawarkan kombinasi sempurna antara gaya, performa, dan kenyamanan.{$specialFeatures}";
    }
}
