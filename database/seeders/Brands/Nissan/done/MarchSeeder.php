<?php

namespace Database\Seeders\Brands\Nissan;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class MarchSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Nissan']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'March'
        ]);

        $types = [
            ['name' => 'Mid'],
            ['name' => 'XS'],
        ];

        $typeConfigurations = [
            'Mid' => [
                'generations' => [
                    '2010-2019 (Gen K13)' => [
                        'engine' => [
                            ['cc' => 1198, 'code' => 'HR12DE', 'fuel_type' => 'Bensin',
                             'transmission' => ['MT'], 'power' => 80, 'torque' => 108]
                        ]
                    ]
                ]
            ],
            'XS' => [
                'generations' => [
                    '2010-2019 (Gen K13)' => [
                        'engine' => [
                            ['cc' => 1198, 'code' => 'HR12DE', 'fuel_type' => 'Bensin',
                             'transmission' => ['AT'], 'power' => 80, 'torque' => 108],

                            ['cc' => 1498, 'code' => 'HR15DE', 'fuel_type' => 'Bensin',
                             'transmission' => ['MT','AT'], 'power' => 102, 'torque' => 145]
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
                                'segment' => 'Hatchback',
                                'production_period' => $period
                            ],
                            [
                                'description' => $this->generateDescription(
                                    $typeName, $year, $engineConfig['cc'],
                                    $engineConfig['code'], $transmission,
                                    $engineConfig['fuel_type'],
                                    $engineConfig['power'], $engineConfig['torque'],
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
            str_contains($period, '2010-2019') => range(2010, 2019),
            default => []
        };
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission,
        $fuelType, $power, $torque, $generation): string
    {
        $transText = $transmission === 'AT' ? 'matic' : 'manual';
        $engineSize = round($cc / 1000, 1) . 'L';

        return "Nissan March {$typeName} {$engineSize} {$transText} {$fuelType} "
             . "({$power} hp, {$torque} Nm) tahun {$year}. Mesin {$engineCode}, "
             . "Generasi {$generation}, city car andalan Nissan untuk pasar Indonesia.";
    }
}
