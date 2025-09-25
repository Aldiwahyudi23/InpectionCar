<?php

namespace Database\Seeders\Brands\Mitsubishi;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class XpanderCrossSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Mitsubishi']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Xpander Cross'
        ]);

        $xpanderCrossTypes = [
            ['name' => 'Exceed'],
            ['name' => 'Ultimate'],
        ];

        $typeConfigurations = [
            'Exceed' => [
                'generations' => [
                    '2019-Now (Gen 1)' => [
                        'engine' => [
                            [
                                'cc' => 1499,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '4A91 MIVEC',
                                'transmission' => ['MT','AT'],
                                'power' => '104 HP',
                                'torque' => '141 Nm'
                            ]
                        ]
                    ]
                ]
            ],
            'Ultimate' => [
                'generations' => [
                    '2019-Now (Gen 1)' => [
                        'engine' => [
                            [
                                'cc' => 1499,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '4A91 MIVEC',
                                'transmission' => ['AT'],
                                'power' => '104 HP',
                                'torque' => '141 Nm'
                            ]
                        ]
                    ]
                ]
            ],
        ];

        foreach ($xpanderCrossTypes as $typeData) {
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
                                'segment' => 'MPV',
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
        }
        return [];
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $fuelType, $power, $torque, $generation): string
    {
        $transmissionText = $transmission === 'AT' ? 'matic' : 'manual';
        $ccText = round($cc / 1000, 1) . 'L';
        return "Mitsubishi Xpander Cross {$typeName} {$ccText} {$transmissionText} {$fuelType} ({$power}, {$torque} Nm) tahun {$year}. Generasi {$generation}, MPV keluarga dengan tampilan lebih tangguh, ground clearance tinggi, dan kenyamanan maksimal.";
    }
}
