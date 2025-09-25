<?php

namespace Database\Seeders\Brands\MercedesBenz;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class AclassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Mercedes-Benz']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'A-Class'
        ]);

        $aClassTypes = [
            ['name' => 'A 200'],
            ['name' => 'A 250'],
            ['name' => 'A 35 AMG'],
            ['name' => 'A 45 AMG'],
        ];

        $typeConfigurations = [
            'A 200' => [
                'generations' => [
                    '2012-2018 (W176)' => [
                        'engine' => [['cc' => 1600, 'fuel_type' => 'Bensin', 'engine_code' => 'M270', 'transmission' => ['AT'], 'power' => '156 PS']],
                    ],
                    '2018-Sekarang (W177)' => [
                        'engine' => [['cc' => 1300, 'fuel_type' => 'Bensin', 'engine_code' => 'M282', 'transmission' => ['AT'], 'power' => '163 PS']],
                    ],
                ]
            ],
            'A 250' => [
                'generations' => [
                    '2012-2018 (W176)' => [
                        'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'M270', 'transmission' => ['AT'], 'power' => '211 PS']],
                    ],
                    '2018-Sekarang (W177)' => [
                        'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'M260', 'transmission' => ['AT'], 'power' => '224 PS']],
                    ],
                ]
            ],
            'A 35 AMG' => [
                'generations' => [
                    '2019-Sekarang (W177)' => [
                        'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'M260', 'transmission' => ['AT'], 'power' => '306 PS']],
                    ],
                ]
            ],
            'A 45 AMG' => [
                'generations' => [
                    '2019-Sekarang (W177)' => [
                        'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'M139', 'transmission' => ['AT'], 'power' => '421 PS']],
                    ],
                ]
            ],
        ];

        foreach ($aClassTypes as $typeData) {
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
                                'segment' => 'Hatchback',
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
        $bodyType = "Hatchback";
        $specialFeatures = " Dikenal sebagai hatchback premium dengan desain sporty dan teknologi canggih.";

        if (str_contains($typeName, 'AMG')) {
            $specialFeatures = " Varian performa tinggi dari AMG, menawarkan pengalaman berkendara yang ekstrem.";
        }

        return "Mercedes-Benz {$typeName} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Kendaraan {$bodyType} yang lincah dan berkelas.{$specialFeatures}";
    }
}
