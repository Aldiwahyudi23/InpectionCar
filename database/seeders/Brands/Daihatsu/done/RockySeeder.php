<?php

namespace Database\Seeders\Brands\Daihatsu;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class RockySeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Daihatsu']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Rocky'
        ]);

        // VARIAN DAIHATSU ROCKY (Generasi A250 untuk Indonesia)
        $rockyTypes = [
            ['name' => 'X'],
            ['name' => 'R'], 
            ['name' => 'RS'],
            ['name' => 'Limited']
        ];

        $typeConfigurations = [
            'X' => [
                'generations' => [
                    '2019-Now (Gen 1)' => [
                        'engine' => [
                            ['cc' => 1198, 'code' => 'WA-VE', 'fuel_type' => 'Bensin', 
                             'transmission' => ['CVT'], 'power' => 87, 'torque' => 113]
                        ]
                    ]
                ]
            ],
            'R' => [
                'generations' => [
                    '2019-Now (Gen 1)' => [
                        'engine' => [
                            ['cc' => 1198, 'code' => 'WA-VE', 'fuel_type' => 'Bensin', 
                             'transmission' => ['CVT'], 'power' => 87, 'torque' => 113]
                        ]
                    ]
                ]
            ],
            'RS' => [
                'generations' => [
                    '2019-Now (Gen 1)' => [
                        'engine' => [
                            ['cc' => 998, 'code' => '1KR-VET', 'fuel_type' => 'Bensin', 
                             'transmission' => ['CVT'], 'power' => 97, 'torque' => 140]
                        ]
                    ]
                ]
            ],
            'Limited' => [
                'generations' => [
                    '2021-Now (Gen 1)' => [
                        'engine' => [
                            ['cc' => 998, 'code' => '1KR-VET', 'fuel_type' => 'Bensin', 
                             'transmission' => ['CVT'], 'power' => 97, 'torque' => 140]
                        ]
                    ]
                ]
            ]
        ];

        foreach ($rockyTypes as $typeData) {
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
                                'engine_code' => $engineConfig['code'],
                                'segment' => 'Subcompact Crossover SUV',
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
        if (str_contains($period, '2019-Now')) {
            return range(2019, $currentYear);
        } elseif (str_contains($period, '2021-Now')) {
            return range(2021, $currentYear);
        }
        return [];
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, 
                                       $fuelType, $power, $torque, $generation): string
    {
        $transmissionText = $transmission === 'CVT' ? 'CVT' : 'manual';
        $engineSize = $cc == 998 ? '1.0L Turbo' : '1.2L';
        
        return "Daihatsu Rocky {$typeName} {$engineSize} {$transmissionText} {$fuelType} " .
               "({$power} hp, {$torque} Nm) tahun {$year}. Mesin {$engineCode} dengan " .
               "konsumsi bahan bakar efisien hingga 21.2 km/L. Generasi {$generation}.";
    }
}