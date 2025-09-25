<?php

namespace Database\Seeders\Brands\Subaru;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class BRZSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Subaru']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'BRZ'
        ]);

        $brzTypes = [
            ['name' => 'Coupe']
        ];

        $typeConfigurations = [
            'Coupe' => [
                'generations' => [
                    '2012-2020' => [
                        'engine' => [
                            ['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'FA20D', 'transmission' => ['MT', 'AT'], 'power' => '200 PS'],
                        ],
                    ],
                    '2021-Sekarang' => [
                        'engine' => [
                            ['cc' => 2400, 'fuel_type' => 'Bensin', 'engine_code' => 'FA24D', 'transmission' => ['MT', 'AT'], 'power' => '234 PS'],
                        ],
                    ],
                ]
            ],
        ];

        foreach ($brzTypes as $typeData) {
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
                                'segment' => 'Sports Car',
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
        $specialFeatures = "Mobil sport yang ringan dan lincah, dengan pusat gravitasi rendah berkat mesin boxer.";

        return "Subaru BRZ {$bodyType} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Mobil sport yang berfokus pada pengalaman berkendara murni. " .
               "{$specialFeatures}";
    }
}
