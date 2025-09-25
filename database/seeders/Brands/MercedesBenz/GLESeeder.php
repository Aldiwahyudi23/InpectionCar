<?php

namespace Database\Seeders\Brands\MercedesBenz;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class GLESeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Mercedes-Benz']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'GLE'
        ]);

        $gleTypes = [
            ['name' => 'GLE 400'],
            ['name' => 'GLE 450'],
            ['name' => 'AMG GLE 53'],
            ['name' => 'AMG GLE 63'],
        ];

        $typeConfigurations = [
            'GLE 400' => [
                'generations' => [
                    '2015-2019 (W166)' => [
                        'engine' => [['cc' => 3000, 'fuel_type' => 'Bensin', 'engine_code' => 'M276', 'transmission' => ['AT'], 'power' => '333 PS']],
                    ],
                ]
            ],
            'GLE 450' => [
                'generations' => [
                    '2019-Sekarang (V167)' => [
                        'engine' => [['cc' => 3000, 'fuel_type' => 'Bensin (Hybrid)', 'engine_code' => 'M256', 'transmission' => ['AT'], 'power' => '367 PS']],
                    ],
                ]
            ],
            'AMG GLE 53' => [
                'generations' => [
                    '2019-Sekarang (C167)' => [
                        'engine' => [['cc' => 3000, 'fuel_type' => 'Bensin (Hybrid)', 'engine_code' => 'M256', 'transmission' => ['AT'], 'power' => '435 PS']],
                    ],
                ]
            ],
            'AMG GLE 63' => [
                'generations' => [
                    '2019-Sekarang (V167)' => [
                        'engine' => [['cc' => 4000, 'fuel_type' => 'Bensin', 'engine_code' => 'M177', 'transmission' => ['AT'], 'power' => '571-612 PS']],
                    ],
                ]
            ],
        ];

        foreach ($gleTypes as $typeData) {
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
        $bodyType = "SUV Premium";
        $specialFeatures = " SUV yang memadukan kemewahan, performa, dan utilitas.";

        if (str_contains($fuelType, 'Hybrid')) {
            $specialFeatures = " Varian hybrid ringan yang menawarkan efisiensi bahan bakar dan performa lebih baik.";
        }
        if (str_contains($typeName, 'AMG')) {
            $specialFeatures = " Varian performa tinggi dari AMG yang menggabungkan kemewahan dengan performa balap.";
        }

        return "Mercedes-Benz {$typeName} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Kendaraan {$bodyType} yang dikenal karena kenyamanan, fitur canggih, dan desain yang elegan.{$specialFeatures}";
    }
}
