<?php

namespace Database\Seeders\Brands\Chevrolet;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class SpinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Chevrolet']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Spin'
        ]);

        $spinTypes = [
            ['name' => 'MPV']
        ];

        $typeConfigurations = [
            'MPV' => [
                'generations' => [
                    '2013-2015' => [
                        'engine' => [
                            ['cc' => 1200, 'fuel_type' => 'Bensin', 'engine_code' => 'S-TEC III (L2C)', 'transmission' => ['MT'], 'power' => '86 PS'],
                            ['cc' => 1500, 'fuel_type' => 'Bensin', 'engine_code' => 'Ecotec (LXV)', 'transmission' => ['MT', 'AT'], 'power' => '109 PS'],
                            ['cc' => 1300, 'fuel_type' => 'Diesel', 'engine_code' => 'Multijet (LUD)', 'transmission' => ['MT'], 'power' => '75 PS'],
                        ],
                    ],
                ]
            ],
        ];

        foreach ($spinTypes as $typeData) {
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
                                'segment' => 'Compact MPV',
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
        $specialFeatures = "Dikenal dengan kabinnya yang luas dan pilihan mesin bensin serta diesel yang efisien.";

        return "Chevrolet Spin {$bodyType} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "MPV kompak yang praktis untuk keluarga. " .
               "{$specialFeatures}";
    }
}
