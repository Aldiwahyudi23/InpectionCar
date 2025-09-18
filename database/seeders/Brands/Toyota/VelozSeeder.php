<?php

namespace Database\Seeders\Brands\Toyota;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class VelozSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Toyota']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Veloz'
        ]);

        $velozTypes = [
            ['name' => 'MT'],
            ['name' => 'Q'],
            ['name' => 'Q TSS'],
        ];

        $typeConfigurations = [
            'MT' => [
                'generations' => [
                    '2021-Now (Gen 2)' => [
                        'engine' => [
                            [
                                'cc' => 1496,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '2NR-VE',
                                'transmission' => ['MT']
                            ],
                        ]
                    ]
                ]
            ],
            'Q' => [
                'generations' => [
                    '2021-Now (Gen 2)' => [
                        'engine' => [
                            [
                                'cc' => 1496,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '2NR-VE',
                                'transmission' => ['CVT']
                            ],
                        ]
                    ]
                ]
            ],
            'Q TSS' => [
                'generations' => [
                    '2021-Now (Gen 2)' => [
                        'engine' => [
                            [
                                'cc' => 1496,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '2NR-VE',
                                'transmission' => ['CVT']
                            ],
                        ]
                    ]
                ]
            ],
        ];

        foreach ($velozTypes as $typeData) {
            $type = CarType::firstOrCreate([
                'name' => $typeData['name'],
                'car_model_id' => $model->id
            ]);

            $this->generateTypeVariants(
                $brand->id, $model->id, $type->id, $typeData['name'], $typeConfigurations
            );
        }
    }

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
                                'segment' => 'Low MPV',
                                'production_period' => $period
                            ],
                            [
                                'description' => $this->generateDescription(
                                    $typeName,
                                    $year,
                                    $engineConfig['cc'],
                                    $engineConfig['engine_code'],
                                    $transmission,
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

    private function getYearsForGeneration($period, $currentYear): array
    {
        if (str_contains($period, '2021-Now')) {
            return range(2021, $currentYear);
        }
        return [];
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $fuelType, $generation): string
    {
        $transmissionText = $transmission === 'CVT' ? 'CVT' : 'manual';
        $ccText = round($cc/1000, 1) . 'L';
        $fuelText = strtolower($fuelType);

        return "Toyota Veloz {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan fitur modern, desain elegan, dan 7-seater premium.";
    }
}
