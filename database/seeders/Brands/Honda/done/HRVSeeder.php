<?php

namespace Database\Seeders\Brands\Honda;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class HRVSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Honda']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'HR-V'
        ]);

        $hrvTypes = [
            ['name' => 'A'],
            ['name' => 'S'],
            ['name' => 'E'],
            ['name' => 'SE'],
            ['name' => 'Prestige'],
            ['name' => 'RS Turbo'],
        ];

        $typeConfigurations = [
            'A' => [
                'generations' => [
                    '2014-2021 (Gen 2)' => [[
                        'cc' => 1497,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L15Z1',
                        'transmission' => ['MT'],
                        'power' => '120 HP'
                    ]],
                ]
            ],
            'S' => [
                'generations' => [
                    '2014-2021 (Gen 2)' => [[
                        'cc' => 1497,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L15Z1',
                        'transmission' => ['MT','CVT'],
                        'power' => '120 HP'
                    ]],
                    '2022-Sekarang (Gen 3)' => [[
                        'cc' => 1498,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L15ZF',
                        'transmission' => ['CVT'],
                        'power' => '121 HP'
                    ]],
                ]
            ],
            'E' => [
                'generations' => [
                    '2014-2021 (Gen 2)' => [[
                        'cc' => 1497,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L15Z1',
                        'transmission' => ['CVT'],
                        'power' => '120 HP'
                    ]],
                    '2022-Sekarang (Gen 3)' => [[
                        'cc' => 1498,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L15ZF',
                        'transmission' => ['CVT'],
                        'power' => '121 HP'
                    ]],
                ]
            ],
            'SE' => [
                'generations' => [
                    '2014-2021 (Gen 2)' => [[
                        'cc' => 1497,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L15Z1',
                        'transmission' => ['CVT'],
                        'power' => '120 HP'
                    ]],
                    '2022-Sekarang (Gen 3)' => [[
                        'cc' => 1498,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L15ZF',
                        'transmission' => ['CVT'],
                        'power' => '121 HP'
                    ]],
                ]
            ],
            'Prestige' => [
                'generations' => [
                    '2014-2021 (Gen 2)' => [[
                        'cc' => 1799,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'R18Z1',
                        'transmission' => ['CVT'],
                        'power' => '139 HP'
                    ]],
                ]
            ],
            'RS Turbo' => [
                'generations' => [
                    '2022-Sekarang (Gen 3)' => [[
                        'cc' => 1498,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L15C3',
                        'transmission' => ['CVT'],
                        'power' => '177 HP'
                    ]],
                ]
            ],
        ];

        foreach ($hrvTypes as $typeData) {
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
        if (str_contains($period, '2014-2021')) {
            return range(2014, 2021);
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
        $bodyType = "SUV 5 penumpang";

        $specialFeatures = "";
        if ($typeName === 'A') {
            $specialFeatures = " Varian paling dasar dengan fitur standar.";
        } elseif ($typeName === 'S') {
            $specialFeatures = " Varian menengah dengan opsi transmisi manual atau CVT.";
        } elseif ($typeName === 'E') {
            $specialFeatures = " Varian lebih lengkap dengan fitur tambahan.";
        } elseif ($typeName === 'SE') {
            $specialFeatures = " Varian special edition dengan fitur eksklusif.";
        } elseif ($typeName === 'Prestige') {
            $specialFeatures = " Varian mewah dengan mesin 1.8L dan fitur premium.";
        } elseif ($typeName === 'RS Turbo') {
            $specialFeatures = " Varian sporty dengan mesin turbo 177 HP.";
        }

        return "Honda HR-V {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. {$bodyType} modern dan nyaman.{$specialFeatures}";
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
