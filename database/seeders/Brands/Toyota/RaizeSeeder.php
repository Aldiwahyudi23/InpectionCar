<?php

namespace Database\Seeders\Brands\Toyota;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class RaizeSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Toyota']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Raize'
        ]);

        // VARIAN AKTUAL TOYOTA RAIZE (BUKAN S dan TRD)
        $raizeTypes = [
            ['name' => 'G'],
            ['name' => 'Turbo G'], 
            ['name' => 'Turbo GR Sport'],
            ['name' => 'Turbo GR Sport TSS']
        ];

        $typeConfigurations = [
            'G' => [
                'generations' => [
                    '2019-Now (Gen 1)' => [
                        'engine' => [
                            ['cc' => 1198, 'code' => 'WA-VE', 'fuel_type' => 'Bensin', 
                             'transmission' => ['MT', 'CVT'], 'power' => 87, 'torque' => 113]
                        ]
                    ]
                ]
            ],
            'Turbo G' => [
                'generations' => [
                    '2019-Now (Gen 1)' => [
                        'engine' => [
                            ['cc' => 998, 'code' => '1KR-VET', 'fuel_type' => 'Bensin', 
                             'transmission' => ['MT', 'CVT'], 'power' => 97, 'torque' => 140]
                        ]
                    ]
                ]
            ],
            'Turbo GR Sport' => [
                'generations' => [
                    '2021-Now (Gen 1)' => [
                        'engine' => [
                            ['cc' => 998, 'code' => '1KR-VET', 'fuel_type' => 'Bensin', 
                             'transmission' => ['CVT'], 'power' => 97, 'torque' => 140]
                        ]
                    ]
                ]
            ],
            'Turbo GR Sport TSS' => [
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

        foreach ($raizeTypes as $typeData) {
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
        
        return "Toyota Raize {$typeName} {$engineSize} {$transmissionText} {$fuelType} " .
               "({$power} hp, {$torque} Nm) tahun {$year}. Mesin {$engineCode} dengan " .
               "konsumsi bahan bakar efisien hingga 19.3 km/L. Generasi {$generation}.";
    }
}