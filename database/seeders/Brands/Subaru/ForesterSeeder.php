<?php

namespace Database\Seeders\Brands\Subaru;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class ForesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Subaru']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Forester'
        ]);

        $foresterTypes = [
            ['name' => 'SUV']
        ];

        $typeConfigurations = [
            'SUV' => [
                'generations' => [
                    '2013-2018' => [
                        'engine' => [
                            ['cc' => 2000, 'fuel_type' => 'Bensin', 'transmission' => ['AT'], 'power' => '150 PS', 'engine_code' => 'FB20'],
                            ['cc' => 2000, 'fuel_type' => 'Bensin', 'transmission' => ['AT'], 'power' => '240 PS', 'engine_code' => 'FA20DIT', 'trim' => 'XT'],
                        ],
                    ],
                    '2019-Sekarang' => [
                        'engine' => [
                            ['cc' => 2500, 'fuel_type' => 'Bensin', 'transmission' => ['AT'], 'power' => '182 PS', 'engine_code' => 'FB25'],
                            ['cc' => 2000, 'fuel_type' => 'Bensin', 'transmission' => ['AT'], 'power' => '150 PS', 'engine_code' => 'FB20', 'trim' => 'e-Boxer'],
                        ],
                    ],
                ]
            ],
        ];

        foreach ($foresterTypes as $typeData) {
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
                                'segment' => 'Compact SUV',
                                'production_period' => $period
                            ],
                            [
                                'description' => $this->generateDescription(
                                    $typeName,
                                    $year,
                                    $engineConfig['cc'],
                                    $engineConfig['power'],
                                    $engineConfig['fuel_type'],
                                    $period,
                                    $engineConfig['trim'] ?? null
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
    private function generateDescription($typeName, $year, $cc, $power, $fuelType, $generation, $trim = null): string
    {
        $ccText = round($cc / 1000, 1) . 'L';
        $fuelText = strtolower($fuelType);
        $bodyType = $typeName;
        $trimText = $trim ? " varian {$trim}" : "";
        $specialFeatures = "Dikenal dengan ruang kabin luas dan sistem Symmetrical All-Wheel Drive (AWD) yang handal.";

        return "Subaru Forester {$bodyType} {$ccText} {$fuelText}{$trimText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "SUV yang praktis dan serbaguna, ideal untuk keluarga maupun petualangan. " .
               "{$specialFeatures}";
    }
}
