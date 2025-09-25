<?php

namespace Database\Seeders\Brands\Mazda;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class CX8Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Mazda']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'CX-8'
        ]);

        $cx8Types = [
            ['name' => 'Elite'],
            ['name' => 'Elite AWD'],
            ['name' => 'Signature'],
        ];

        $typeConfigurations = [
            'Elite' => [
                'generations' => [
                    '2018-Sekarang' => [
                        'engine' => [['cc' => 2488, 'fuel_type' => 'Bensin', 'engine_code' => 'PY-VPS', 'transmission' => ['AT'], 'power' => '190 PS']],
                    ],
                ]
            ],
            'Elite AWD' => [
                'generations' => [
                    '2018-Sekarang' => [
                        'engine' => [['cc' => 2191, 'fuel_type' => 'Diesel', 'engine_code' => 'SH-VPTS', 'transmission' => ['AT'], 'power' => '190 PS']],
                    ],
                ]
            ],
            'Signature' => [
                'generations' => [
                    '2018-Sekarang' => [
                        'engine' => [['cc' => 2488, 'fuel_type' => 'Bensin', 'engine_code' => 'PY-VPS', 'transmission' => ['AT'], 'power' => '190 PS']],
                    ],
                ]
            ],
        ];

        foreach ($cx8Types as $typeData) {
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
        $specialFeatures = " Dikenal dengan desain KODO yang elegan, interior premium, dan handling yang responsif.";

        if ($typeName === 'Elite AWD') {
            $specialFeatures = " Varian penggerak semua roda yang ditenagai mesin diesel, memberikan traksi superior dan efisiensi bahan bakar yang lebih baik.";
        } elseif ($typeName === 'Signature') {
            $specialFeatures = " Varian tertinggi dengan nuansa kemewahan, kursi captain di baris kedua, dan material Nappa leather pada interior.";
        }

        return "Mazda CX-8 {$typeName} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Kendaraan {$bodyType} yang menawarkan kemewahan dan kenyamanan untuk seluruh keluarga.{$specialFeatures}";
    }
}
