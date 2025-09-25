<?php

namespace Database\Seeders\Brands\Honda;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class JazzSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Honda']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Jazz'
        ]);

        $jazzTypes = [
            ['name' => 'IDSI'],
            ['name' => 'VTEC'],
            ['name' => 'S'],
            ['name' => 'RS'],
            ['name' => 'A'],
        ];

        $typeConfigurations = [
            'IDSI' => [
                'generations' => [
                    '2003-2007 (Gen 1)' => [[
                        'cc' => 1339,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L13A',
                        'transmission' => ['MT','CVT'],
                        'power' => '82 HP'
                    ]],
                ]
            ],
            'VTEC' => [
                'generations' => [
                    '2003-2007 (Gen 1)' => [[
                        'cc' => 1497,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L15A',
                        'transmission' => ['MT','CVT'],
                        'power' => '109 HP'
                    ]],
                ]
            ],
            'S' => [
                'generations' => [
                    '2008-2013 (Gen 2)' => [[
                        'cc' => 1497,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L15A7',
                        'transmission' => ['MT','AT'],
                        'power' => '120 HP'
                    ]],
                    '2014-2021 (Gen 3)' => [[
                        'cc' => 1497,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L15Z1',
                        'transmission' => ['MT','CVT'],
                        'power' => '118 HP'
                    ]],
                ]
            ],
            'RS' => [
                'generations' => [
                    '2008-2013 (Gen 2)' => [[
                        'cc' => 1497,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L15A7',
                        'transmission' => ['MT','AT'],
                        'power' => '120 HP'
                    ]],
                    '2014-2021 (Gen 3)' => [[
                        'cc' => 1497,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L15Z1',
                        'transmission' => ['MT','CVT'],
                        'power' => '118 HP'
                    ]],
                ]
            ],
            'A' => [
                'generations' => [
                    '2014-2021 (Gen 3)' => [[
                        'cc' => 1497,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L15Z1',
                        'transmission' => ['MT'],
                        'power' => '118 HP'
                    ]],
                ]
            ],
        ];

        foreach ($jazzTypes as $typeData) {
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

        foreach ($configurations[$typeName]['generations'] as $period => $engineConfigs) {
            $years = $this->getYearsForGeneration($period, $currentYear);

            foreach ($years as $year) {
                foreach ($engineConfigs as $engineConfig) {
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
                                'segment' => 'Hatchback',
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
                                    $period,
                                    $engineConfig['power']
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
        if (str_contains($period, '2003-2007')) {
            return range(2003, 2007);
        } elseif (str_contains($period, '2008-2013')) {
            return range(2008, 2013);
        } elseif (str_contains($period, '2014-2021')) {
            return range(2014, 2021);
        }
        return [];
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $fuelType, $generation, $power): string
    {
        $transmissionText = $this->getTransmissionText($transmission);
        $ccText = round($cc/1000, 1) . 'L';
        $fuelText = strtolower($fuelType);
        $bodyType = "Hatchback 5 penumpang";

        $specialFeatures = "";
        if ($typeName === 'IDSI') {
            $specialFeatures = " Varian hemat bahan bakar dengan teknologi i-DSI.";
        } elseif ($typeName === 'VTEC') {
            $specialFeatures = " Varian bertenaga dengan mesin VTEC.";
        } elseif ($typeName === 'S') {
            $specialFeatures = " Varian menengah dengan fitur standar modern.";
        } elseif ($typeName === 'RS') {
            $specialFeatures = " Varian sporty dengan tampilan dan fitur premium.";
        } elseif ($typeName === 'A') {
            $specialFeatures = " Varian entry level dengan fitur dasar.";
        }

        return "Honda Jazz {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. {$bodyType} stylish dan lincah.{$specialFeatures}";
    }

    private function getTransmissionText($transmission): string
    {
        $mapping = [
            'MT' => 'manual',
            'CVT' => 'CVT',
            'AT' => 'automatic'
        ];
        return $mapping[$transmission] ?? $transmission;
    }
}
