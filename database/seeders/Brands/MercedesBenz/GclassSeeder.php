<?php

namespace Database\Seeders\Brands\MercedesBenz;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class GclassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Mercedes-Benz']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'G-Class'
        ]);

        $gClassTypes = [
            ['name' => 'G 300'],
            ['name' => 'G 400'],
            ['name' => 'G 500'],
            ['name' => 'AMG G 63'],
        ];

        $typeConfigurations = [
            'G 300' => [
                'generations' => [
                    '1990-2000 (W463)' => [
                        'engine' => [['cc' => 3000, 'fuel_type' => 'Diesel', 'engine_code' => 'OM603', 'transmission' => ['AT'], 'power' => '177 PS']],
                    ],
                ]
            ],
            'G 400' => [
                'generations' => [
                    '2000-2006 (W463)' => [
                        'engine' => [['cc' => 4000, 'fuel_type' => 'Diesel', 'engine_code' => 'OM628', 'transmission' => ['AT'], 'power' => '250 PS']],
                    ],
                    '2018-Sekarang (W463)' => [
                        'engine' => [['cc' => 2900, 'fuel_type' => 'Diesel', 'engine_code' => 'OM656', 'transmission' => ['AT'], 'power' => '330 PS']],
                    ],
                ]
            ],
            'G 500' => [
                'generations' => [
                    '1998-2018 (W463)' => [
                        'engine' => [['cc' => 5000, 'fuel_type' => 'Bensin', 'engine_code' => 'M113', 'transmission' => ['AT'], 'power' => '296 PS']],
                    ],
                    '2018-Sekarang (W463)' => [
                        'engine' => [['cc' => 4000, 'fuel_type' => 'Bensin', 'engine_code' => 'M176', 'transmission' => ['AT'], 'power' => '422 PS']],
                    ],
                ]
            ],
            'AMG G 63' => [
                'generations' => [
                    '2012-2018 (W463)' => [
                        'engine' => [['cc' => 5500, 'fuel_type' => 'Bensin', 'engine_code' => 'M157', 'transmission' => ['AT'], 'power' => '544 PS']],
                    ],
                    '2018-Sekarang (W463)' => [
                        'engine' => [['cc' => 4000, 'fuel_type' => 'Bensin', 'engine_code' => 'M177', 'transmission' => ['AT'], 'power' => '585 PS']],
                    ],
                ]
            ],
        ];

        foreach ($gClassTypes as $typeData) {
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
        $bodyType = "SUV Ikonik";
        $specialFeatures = " Dikenal karena desain kotak yang khas, performa off-road tangguh, dan kemewahan yang tak tertandingi.";

        if (str_contains($typeName, 'AMG')) {
            $specialFeatures = " Varian performa ekstrem dari AMG, dengan mesin V8 Bi-turbo yang bertenaga besar.";
        }

        return "Mercedes-Benz {$typeName} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Kendaraan {$bodyType} yang merupakan simbol status dan kapabilitas.{$specialFeatures}";
    }
}
