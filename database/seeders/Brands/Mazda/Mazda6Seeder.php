<?php

namespace Database\Seeders\Brands\Mazda;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class Mazda6Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Mazda']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => '6'
        ]);

        $mazda6Types = [
            ['name' => 'Sedan'],
            ['name' => 'Wagon'],
        ];

        $typeConfigurations = [
            'Sedan' => [
                'generations' => [
                    '2002-2008' => [
                        'engine' => [
                            ['cc' => 1999, 'fuel_type' => 'Bensin', 'engine_code' => 'LF-DE', 'transmission' => ['MT', 'AT'], 'power' => '141 PS'],
                            ['cc' => 2261, 'fuel_type' => 'Bensin', 'engine_code' => 'L3-VE', 'transmission' => ['MT', 'AT'], 'power' => '166 PS'],
                        ],
                    ],
                    '2008-2012' => [
                        'engine' => [
                            ['cc' => 1999, 'fuel_type' => 'Bensin', 'engine_code' => 'LF-VE', 'transmission' => ['AT'], 'power' => '147 PS'],
                            ['cc' => 2488, 'fuel_type' => 'Bensin', 'engine_code' => 'L5-VE', 'transmission' => ['AT'], 'power' => '170 PS'],
                        ],
                    ],
                    '2012-Sekarang' => [
                        'engine' => [
                            ['cc' => 1998, 'fuel_type' => 'Bensin', 'engine_code' => 'PE-VPS', 'transmission' => ['AT'], 'power' => '155 PS'],
                            ['cc' => 2488, 'fuel_type' => 'Bensin', 'engine_code' => 'PY-VPS', 'transmission' => ['AT'], 'power' => '187 PS'],
                        ],
                    ],
                ]
            ],
            'Wagon' => [
                'generations' => [
                    '2002-2008' => [
                        'engine' => [
                            ['cc' => 1999, 'fuel_type' => 'Bensin', 'engine_code' => 'LF-DE', 'transmission' => ['MT', 'AT'], 'power' => '141 PS'],
                            ['cc' => 2261, 'fuel_type' => 'Bensin', 'engine_code' => 'L3-VE', 'transmission' => ['MT', 'AT'], 'power' => '166 PS'],
                        ],
                    ],
                    '2008-2012' => [
                        'engine' => [
                            ['cc' => 1999, 'fuel_type' => 'Bensin', 'engine_code' => 'LF-VE', 'transmission' => ['AT'], 'power' => '147 PS'],
                            ['cc' => 2488, 'fuel_type' => 'Bensin', 'engine_code' => 'L5-VE', 'transmission' => ['AT'], 'power' => '170 PS'],
                        ],
                    ],
                    '2012-Sekarang' => [
                        'engine' => [
                            ['cc' => 1998, 'fuel_type' => 'Bensin', 'engine_code' => 'PE-VPS', 'transmission' => ['AT'], 'power' => '155 PS'],
                            ['cc' => 2488, 'fuel_type' => 'Bensin', 'engine_code' => 'PY-VPS', 'transmission' => ['AT'], 'power' => '187 PS'],
                        ],
                    ],
                ]
            ],
        ];

        foreach ($mazda6Types as $typeData) {
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
                                'segment' => 'Mid-Size Sedan',
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
        $bodyType = $typeName;
        $specialFeatures = "";
        
        switch ($generation) {
            case '2002-2008':
                $specialFeatures = "Generasi pertama ini memperkenalkan desain 'Zoom-Zoom' yang sporty.";
                break;
            case '2008-2012':
                $specialFeatures = "Generasi kedua menawarkan ruang kabin yang lebih luas dan fitur yang lebih modern.";
                break;
            case '2012-Sekarang':
                $specialFeatures = "Mengadopsi filosofi desain KODO dan teknologi Skyactiv, memberikan efisiensi dan estetika premium.";
                break;
        }

        return "Mazda 6 {$bodyType} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Sedan mid-size yang memadukan desain premium, performa dinamis, dan fitur canggih. " .
               "{$specialFeatures}";
    }
}
