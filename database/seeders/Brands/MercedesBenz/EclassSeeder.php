<?php

namespace Database\Seeders\Brands\MercedesBenz;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class EclassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Mercedes-Benz']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'E-Class'
        ]);

        $eClassTypes = [
            ['name' => 'E200'],
            ['name' => 'E250'],
            ['name' => 'E300'],
            ['name' => 'E350e'],
        ];

        $typeConfigurations = [
            'E200' => [
                'generations' => [
                    '2009-2016 (W212)' => [
                        'engine' => [['cc' => 1800, 'fuel_type' => 'Bensin', 'engine_code' => 'M271', 'transmission' => ['AT'], 'power' => '184 PS']],
                    ],
                    '2016-2023 (W213)' => [
                        'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'M274', 'transmission' => ['AT'], 'power' => '184 PS']],
                    ],
                    '2023-Sekarang (W214)' => [
                        'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'M254', 'transmission' => ['AT'], 'power' => '204 PS']],
                    ],
                ]
            ],
            'E250' => [
                'generations' => [
                    '2009-2016 (W212)' => [
                        'engine' => [['cc' => 1800, 'fuel_type' => 'Bensin', 'engine_code' => 'M271', 'transmission' => ['AT'], 'power' => '204 PS']],
                    ],
                    '2016-2023 (W213)' => [
                        'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'M274', 'transmission' => ['AT'], 'power' => '211 PS']],
                    ],
                ]
            ],
            'E300' => [
                'generations' => [
                    '2009-2016 (W212)' => [
                        'engine' => [['cc' => 3500, 'fuel_type' => 'Bensin', 'engine_code' => 'M272', 'transmission' => ['AT'], 'power' => '231 PS']],
                    ],
                    '2016-2023 (W213)' => [
                        'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'M274', 'transmission' => ['AT'], 'power' => '245 PS']],
                    ],
                    '2023-Sekarang (W214)' => [
                        'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'M254', 'transmission' => ['AT'], 'power' => '258 PS']],
                    ],
                ]
            ],
            'E350e' => [
                'generations' => [
                    '2016-2023 (W213)' => [
                        'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin & Listrik', 'engine_code' => 'M274', 'transmission' => ['AT'], 'power' => '286 PS']],
                    ],
                ]
            ],
        ];

        foreach ($eClassTypes as $typeData) {
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
        $bodyType = "Sedan Eksekutif";
        $specialFeatures = " Kendaraan {$bodyType} yang dikenal dengan kenyamanan luar biasa dan teknologi canggih.";

        if (str_contains($fuelType, 'Listrik')) {
            $specialFeatures = " Varian hybrid plug-in yang menggabungkan efisiensi bahan bakar dengan performa Mercedes-Benz yang khas.";
        }

        return "Mercedes-Benz {$typeName} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Kendaraan {$bodyType} yang dikenal karena perpaduan kemewahan, kenyamanan, dan teknologi mutakhir.{$specialFeatures}";
    }
}
