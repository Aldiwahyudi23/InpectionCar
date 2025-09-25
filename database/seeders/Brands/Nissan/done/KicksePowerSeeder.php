<?php

namespace Database\Seeders\Brands\Nissan;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class KicksePowerSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Nissan']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Kicks'
        ]);

        $types = [
            ['name' => 'e-Power VL One Tone'],
            ['name' => 'e-Power VL Two Tone'],
        ];

        $typeConfigurations = [
            'e-Power VL One Tone' => [
                'generations' => [
                    '2020-Now (Gen e-Power Indonesia)' => [
                        'engine' => [
                            [
                                'cc' => 1198,
                                'code' => 'HR12DE + EM57',
                                'fuel_type' => 'Hybrid',
                                'transmission' => ['e-CVT'],
                                'power' => 129,
                                'torque' => 260
                            ]
                        ]
                    ]
                ]
            ],
            'e-Power VL Two Tone' => [
                'generations' => [
                    '2020-Now (Gen e-Power Indonesia)' => [
                        'engine' => [
                            [
                                'cc' => 1198,
                                'code' => 'HR12DE + EM57',
                                'fuel_type' => 'Hybrid',
                                'transmission' => ['e-CVT'],
                                'power' => 129,
                                'torque' => 260
                            ]
                        ]
                    ]
                ]
            ],
        ];

        foreach ($types as $typeData) {
            $type = CarType::firstOrCreate([
                'name' => $typeData['name'],
                'car_model_id' => $model->id
            ]);

            if (isset($typeConfigurations[$typeData['name']])) {
                $this->generateTypeVariants(
                    $brand->id, $model->id, $type->id, $typeData['name'], $typeConfigurations
                );
            }
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
                                'engine_code' => $engineConfig['code'],
                                'segment' => 'SUV',
                                'production_period' => $period
                            ],
                            [
                                'description' => $this->generateDescription(
                                    $typeName,
                                    $year,
                                    $engineConfig['cc'],
                                    $engineConfig['code'],
                                    $transmission,
                                    $engineConfig['fuel_type'],
                                    $engineConfig['power'],
                                    $engineConfig['torque'],
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
        return match (true) {
            str_contains($period, '2020-Now') => range(2020, $currentYear),
            default => []
        };
    }

    private function generateDescription(
        $typeName,
        $year,
        $cc,
        $engineCode,
        $transmission,
        $fuelType,
        $power,
        $torque,
        $generation
    ): string {
        $transText = $transmission === 'e-CVT' ? 'e-CVT' : ($transmission === 'AT' ? 'matic' : 'manual');
        $engineSize = round($cc / 1000, 1) . 'L';

        return "Nissan Kicks {$typeName} {$engineSize} {$transText} {$fuelType} "
             . "({$power} hp, {$torque} Nm) tahun {$year}. Mesin {$engineCode}, "
             . "Generasi {$generation}, compact SUV e-Power di Indonesia.";
    }
}
