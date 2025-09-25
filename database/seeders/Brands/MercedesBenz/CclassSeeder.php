<?php

namespace Database\Seeders\Brands\MercedesBenz;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class CclassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Mercedes-Benz']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'C-Class'
        ]);

        $cClassTypes = [
            ['name' => 'C200'],
            ['name' => 'C250'],
            ['name' => 'C300'],
        ];

        $typeConfigurations = [
            'C200' => [
                'generations' => [
                    '2007-2014 (W204)' => [
                        'engine' => [['cc' => 1800, 'fuel_type' => 'Bensin', 'engine_code' => 'M271', 'transmission' => ['AT'], 'power' => '184 PS']],
                    ],
                    '2014-2021 (W205)' => [
                        'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'M274', 'transmission' => ['AT'], 'power' => '184 PS']],
                    ],
                    '2021-Sekarang (W206)' => [
                        'engine' => [['cc' => 1500, 'fuel_type' => 'Bensin', 'engine_code' => 'M254', 'transmission' => ['AT'], 'power' => '204 PS']],
                    ],
                ]
            ],
            'C250' => [
                'generations' => [
                    '2007-2014 (W204)' => [
                        'engine' => [['cc' => 1800, 'fuel_type' => 'Bensin', 'engine_code' => 'M271', 'transmission' => ['AT'], 'power' => '204 PS']],
                    ],
                    '2014-2021 (W205)' => [
                        'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'M274', 'transmission' => ['AT'], 'power' => '211 PS']],
                    ],
                ]
            ],
            'C300' => [
                'generations' => [
                    '2007-2014 (W204)' => [
                        'engine' => [['cc' => 3000, 'fuel_type' => 'Bensin', 'engine_code' => 'M272', 'transmission' => ['AT'], 'power' => '231 PS']],
                    ],
                    '2014-2021 (W205)' => [
                        'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'M274', 'transmission' => ['AT'], 'power' => '245 PS']],
                    ],
                    '2021-Sekarang (W206)' => [
                        'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'M254', 'transmission' => ['AT'], 'power' => '258 PS']],
                    ],
                ]
            ],
        ];

        foreach ($cClassTypes as $typeData) {
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
        $specialFeatures = " Kendaraan {$bodyType} populer yang dikenal karena perpaduan kemewahan, teknologi, dan performa yang seimbang.";

        if (str_contains($typeName, 'C300')) {
            $specialFeatures = " Varian paling bertenaga dari C-Class non-AMG, menawarkan performa lebih sporty dan fitur premium.";
        }

        return "Mercedes-Benz {$typeName} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Kendaraan {$bodyType} yang dikenal karena perpaduan gaya dan performa.{$specialFeatures}";
    }
}
