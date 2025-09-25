<?php

namespace Database\Seeders\Brands\Mazda;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class CX60Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Mazda']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'CX-60'
        ]);

        $cx60Types = [
            ['name' => 'Elite'],
            ['name' => 'Kuro'],
        ];

        $typeConfigurations = [
            'Elite' => [
                'generations' => [
                    '2023-Sekarang' => [
                        'engine' => [['cc' => 3289, 'fuel_type' => 'Bensin', 'engine_code' => 'e-Skyactiv G Turbo', 'transmission' => ['AT'], 'power' => '280 PS']],
                    ],
                ]
            ],
            'Kuro' => [
                'generations' => [
                    '2023-Sekarang' => [
                        'engine' => [['cc' => 3289, 'fuel_type' => 'Bensin', 'engine_code' => 'e-Skyactiv G Turbo', 'transmission' => ['AT'], 'power' => '280 PS']],
                    ],
                ]
            ],
        ];

        foreach ($cx60Types as $typeData) {
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
                                'segment' => 'Premium SUV',
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
        $bodyType = "Premium SUV";
        $specialFeatures = " Dikenal sebagai SUV premium pertama Mazda dengan platform RWD dan mesin 6-silinder segaris yang bertenaga.";

        if ($typeName === 'Kuro') {
            $specialFeatures = " Varian Kuro menonjolkan gaya yang lebih sporty dengan aksen eksterior hitam dan interior yang khas.";
        } else {
            $specialFeatures = " Varian Elite menawarkan tampilan elegan dan fitur-fitur premium yang lengkap.";
        }

        return "Mazda CX-60 {$typeName} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Kendaraan {$bodyType} yang mengedepankan performa, kemewahan, dan teknologi i-Activsense yang canggih.{$specialFeatures}";
    }
}
