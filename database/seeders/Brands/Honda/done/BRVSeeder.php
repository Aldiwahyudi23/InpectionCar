<?php

namespace Database\Seeders\Brands\Honda;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class BRVSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Honda']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'BR-V'
        ]);

        $brvTypes = [
            ['name' => 'S'],
            ['name' => 'E'],
            ['name' => 'Prestige'],
            ['name' => 'Prestige Honda Sensing'],
        ];

        $typeConfigurations = [
            'S' => [
                'generations' => [
                    '2016-2021 (Gen 1)' => [[
                        'cc' => 1497,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L15Z1',
                        'transmission' => ['MT'],
                        'power' => '118 HP'
                    ]],
                    '2022-Sekarang (Gen 2)' => [[
                        'cc' => 1498,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L15ZF',
                        'transmission' => ['MT'],
                        'power' => '121 HP'
                    ]],
                ]
            ],
            'E' => [
                'generations' => [
                    '2016-2021 (Gen 1)' => [[
                        'cc' => 1497,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L15Z1',
                        'transmission' => ['MT','CVT'],
                        'power' => '118 HP'
                    ]],
                    '2022-Sekarang (Gen 2)' => [[
                        'cc' => 1498,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L15ZF',
                        'transmission' => ['MT','CVT'],
                        'power' => '121 HP'
                    ]],
                ]
            ],
            'Prestige' => [
                'generations' => [
                    '2016-2021 (Gen 1)' => [[
                        'cc' => 1497,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L15Z1',
                        'transmission' => ['CVT'],
                        'power' => '118 HP'
                    ]],
                    '2022-Sekarang (Gen 2)' => [[
                        'cc' => 1498,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L15ZF',
                        'transmission' => ['CVT'],
                        'power' => '121 HP'
                    ]],
                ]
            ],
            'Prestige Honda Sensing' => [
                'generations' => [
                    '2022-Sekarang (Gen 2)' => [[
                        'cc' => 1498,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L15ZF',
                        'transmission' => ['CVT'],
                        'power' => '121 HP'
                    ]],
                ]
            ],
        ];

        foreach ($brvTypes as $typeData) {
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
                                'segment' => 'SUV',
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
        if (str_contains($period, '2016-2021')) {
            return range(2016, 2021);
        } elseif (str_contains($period, '2022-Sekarang')) {
            return range(2022, $currentYear);
        }
        return [];
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $fuelType, $generation, $power): string
    {
        $transmissionText = $this->getTransmissionText($transmission);
        $ccText = round($cc/1000, 1) . 'L';
        $fuelText = strtolower($fuelType);
        $bodyType = "SUV 7 penumpang";

        $specialFeatures = "";
        if ($typeName === 'S') {
            $specialFeatures = " Varian entry level dengan fitur dasar.";
        } elseif ($typeName === 'E') {
            $specialFeatures = " Varian menengah dengan fitur lebih lengkap.";
        } elseif ($typeName === 'Prestige') {
            $specialFeatures = " Varian mewah dengan transmisi CVT dan fitur premium.";
        } elseif ($typeName === 'Prestige Honda Sensing') {
            $specialFeatures = " Varian tertinggi dengan fitur Honda Sensing (ADAS).";
        }

        return "Honda BR-V {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. {$bodyType} tangguh untuk keluarga.{$specialFeatures}";
    }

    private function getTransmissionText($transmission): string
    {
        $mapping = [
            'MT' => 'manual',
            'CVT' => 'CVT'
        ];
        return $mapping[$transmission] ?? $transmission;
    }
}
