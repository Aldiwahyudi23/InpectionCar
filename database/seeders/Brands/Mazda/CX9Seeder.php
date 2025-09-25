<?php

namespace Database\Seeders\Brands\Mazda;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class CX9Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Mazda']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'CX-9'
        ]);

        $cx9Types = [
            ['name' => 'Touring'],
            ['name' => 'Grand Touring'],
            ['name' => 'Signature'],
        ];

        $typeConfigurations = [
            'Touring' => [
                'generations' => [
                    '2016-Sekarang' => [
                        'engine' => [['cc' => 2488, 'fuel_type' => 'Bensin', 'engine_code' => 'PY-VPTS', 'transmission' => ['AT'], 'power' => '250 PS']],
                    ],
                ]
            ],
            'Grand Touring' => [
                'generations' => [
                    '2016-Sekarang' => [
                        'engine' => [['cc' => 2488, 'fuel_type' => 'Bensin', 'engine_code' => 'PY-VPTS', 'transmission' => ['AT'], 'power' => '250 PS']],
                    ],
                ]
            ],
            'Signature' => [
                'generations' => [
                    '2016-Sekarang' => [
                        'engine' => [['cc' => 2488, 'fuel_type' => 'Bensin', 'engine_code' => 'PY-VPTS', 'transmission' => ['AT'], 'power' => '250 PS']],
                    ],
                ]
            ],
        ];

        foreach ($cx9Types as $typeData) {
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
        $bodyType = "7-seater SUV";
        $specialFeatures = " Dikenal dengan mesin Skyactiv-G Turbo yang bertenaga, kabin yang sangat senyap, dan fitur keselamatan i-Activsense.";

        if ($typeName === 'Grand Touring') {
            $specialFeatures = " Varian menengah yang dilengkapi dengan sunroof, head-up display, dan sistem audio premium Bose.";
        } elseif ($typeName === 'Signature') {
            $specialFeatures = " Varian tertinggi yang menawarkan kemewahan maksimal dengan Nappa leather, trim kayu asli, dan detail eksterior yang eksklusif.";
        }

        return "Mazda CX-9 {$typeName} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Kendaraan {$bodyType} yang mengedepankan kemewahan, performa, dan kenyamanan.{$specialFeatures}";
    }
}
