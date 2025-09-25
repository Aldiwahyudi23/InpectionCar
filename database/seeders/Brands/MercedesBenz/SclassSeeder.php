<?php

namespace Database\Seeders\Brands\MercedesBenz;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class SclassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Mercedes-Benz']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'S-Class'
        ]);

        $sClassTypes = [
            ['name' => 'S350'],
            ['name' => 'S400'],
            ['name' => 'S500'],
            ['name' => 'S450'],
        ];

        $typeConfigurations = [
            'S350' => [
                'generations' => [
                    '2005-2013 (W221)' => [
                        'engine' => [['cc' => 3500, 'fuel_type' => 'Bensin', 'engine_code' => 'M272', 'transmission' => ['AT'], 'power' => '272 PS']],
                    ],
                    '2013-2020 (W222)' => [
                        'engine' => [['cc' => 3500, 'fuel_type' => 'Bensin', 'engine_code' => 'M276', 'transmission' => ['AT'], 'power' => '306 PS']],
                    ],
                    '2020-Sekarang (W223)' => [
                        'engine' => [['cc' => 3000, 'fuel_type' => 'Bensin', 'engine_code' => 'M256', 'transmission' => ['AT'], 'power' => '286 PS']],
                    ],
                ]
            ],
            'S400' => [
                'generations' => [
                    '2013-2020 (W222)' => [
                        'engine' => [['cc' => 3000, 'fuel_type' => 'Bensin (Hybrid)', 'engine_code' => 'M276 + Electric', 'transmission' => ['AT'], 'power' => '333 PS']],
                    ],
                    '2020-Sekarang (W223)' => [
                        'engine' => [['cc' => 3000, 'fuel_type' => 'Bensin (Hybrid)', 'engine_code' => 'M256 + Electric', 'transmission' => ['AT'], 'power' => '367 PS']],
                    ],
                ]
            ],
            'S450' => [
                'generations' => [
                    '2013-2020 (W222)' => [
                        'engine' => [['cc' => 3000, 'fuel_type' => 'Bensin', 'engine_code' => 'M276', 'transmission' => ['AT'], 'power' => '367 PS']],
                    ],
                ]
            ],
            'S500' => [
                'generations' => [
                    '2005-2013 (W221)' => [
                        'engine' => [['cc' => 5500, 'fuel_type' => 'Bensin', 'engine_code' => 'M273', 'transmission' => ['AT'], 'power' => '388 PS']],
                    ],
                    '2013-2020 (W222)' => [
                        'engine' => [['cc' => 4700, 'fuel_type' => 'Bensin', 'engine_code' => 'M278', 'transmission' => ['AT'], 'power' => '455 PS']],
                    ],
                    '2020-Sekarang (W223)' => [
                        'engine' => [['cc' => 4000, 'fuel_type' => 'Bensin (Hybrid)', 'engine_code' => 'M176 + Electric', 'transmission' => ['AT'], 'power' => '503 PS']],
                    ],
                ]
            ],
        ];

        foreach ($sClassTypes as $typeData) {
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
        $specialFeatures = " Mobil flagship yang mewakili puncak kemewahan, inovasi teknologi, dan kenyamanan berkendara Mercedes-Benz.";

        if (str_contains($fuelType, 'Listrik')) {
            $specialFeatures = " Varian hybrid yang menggabungkan performa mesin bensin dengan motor listrik untuk efisiensi dan tenaga ekstra.";
        }

        return "Mercedes-Benz {$typeName} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Kendaraan {$bodyType} yang dikenal karena perpaduan kemewahan, kenyamanan, dan teknologi mutakhir.{$specialFeatures}";
    }
}
