<?php

namespace Database\Seeders\Brands\Honda;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class AccordSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Honda']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Accord'
        ]);

        $accordTypes = [
            ['name' => 'VTi'],
            ['name' => 'VTi-L'],
            // ['name' => '2.4'],
            ['name' => 'Turbo'],
            ['name' => 'RS e:HEV'],
        ];

        $typeConfigurations = [
            'VTi' => [
                'generations' => [
                    '2003-2007 (Gen 7)' => [[
                        'cc' => 2400,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'K24A',
                        'transmission' => ['AT'],
                        'power' => '160 HP'
                    ]],
                    '2008-2012 (Gen 8)' => [[
                        'cc' => 2400,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'K24Z2',
                        'transmission' => ['AT'],
                        'power' => '180 HP'
                    ]],
                ]
            ],
            'VTi-L' => [
                'generations' => [
                    '2003-2007 (Gen 7)' => [[
                        'cc' => 2400,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'K24A',
                        'transmission' => ['AT'],
                        'power' => '170 HP'
                    ]],
                    '2008-2012 (Gen 8)' => [[
                        'cc' => 2400,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'K24Z2',
                        'transmission' => ['AT'],
                        'power' => '180 HP'
                    ]],
                    '2013-2018 (Gen 9)' => [[
                        'cc' => 2400,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'K24W',
                        'transmission' => ['CVT'],
                        'power' => '175 HP'
                    ]],
                ]
            ],
            // '2.4' => [
            //     'generations' => [
            //         '2008-2012 (Gen 8)' => [[
            //             'cc' => 2400,
            //             'fuel_type' => 'Bensin',
            //             'engine_code' => 'K24Z3',
            //             'transmission' => ['AT'],
            //             'power' => '190 HP'
            //         ]],
            //         '2013-2018 (Gen 9)' => [[
            //             'cc' => 2400,
            //             'fuel_type' => 'Bensin',
            //             'engine_code' => 'K24W',
            //             'transmission' => ['CVT'],
            //             'power' => '175 HP'
            //         ]],
            //     ]
            // ],
            'Turbo' => [
                'generations' => [
                    '2019-2023 (Gen 10)' => [[
                        'cc' => 1500,
                        'fuel_type' => 'Bensin',
                        'engine_code' => 'L15BE VTEC Turbo',
                        'transmission' => ['CVT'],
                        'power' => '190 HP'
                    ]],
                ]
            ],
            'RS e:HEV' => [
                'generations' => [
                    '2024-Sekarang (Gen 11)' => [[
                        'cc' => 2000,
                        'fuel_type' => 'Hybrid',
                        'engine_code' => 'LFA-M20A e:HEV',
                        'transmission' => ['e-CVT'],
                        'power' => '204 HP'
                    ]],
                ]
            ],
        ];

        foreach ($accordTypes as $typeData) {
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
                                'segment' => 'Sedan',
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
        if (preg_match('/(\d{4})-(\d{4})/', $period, $match)) {
            return range((int)$match[1], (int)$match[2]);
        } elseif (str_contains($period, 'Sekarang')) {
            preg_match('/(\d{4})/', $period, $match);
            return range((int)$match[1], $currentYear);
        }
        return [];
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $fuelType, $generation, $power): string
    {
        $transmissionText = $this->getTransmissionText($transmission);
        $ccText = round($cc/1000, 1) . 'L';
        $fuelText = strtolower($fuelType);
        $bodyType = "Sedan mewah";

        $specialFeatures = "";
        if ($typeName === 'VTi') {
            $specialFeatures = " Varian standar dengan fitur dasar.";
        } elseif ($typeName === 'VTi-L') {
            $specialFeatures = " Varian lebih mewah dengan fitur tambahan.";
        } elseif ($typeName === '2.4') {
            $specialFeatures = " Varian mesin lebih bertenaga.";
        } elseif ($typeName === 'Turbo') {
            $specialFeatures = " Varian modern dengan mesin turbo 1.5L.";
        } elseif ($typeName === 'RS e:HEV') {
            $specialFeatures = " Varian hybrid terbaru dengan teknologi e:HEV.";
        }

        return "Honda Accord {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. {$bodyType} nyaman dan elegan.{$specialFeatures}";
    }

    private function getTransmissionText($transmission): string
    {
        $mapping = [
            'MT' => 'manual',
            'AT' => 'otomatis',
            'CVT' => 'CVT',
            'e-CVT' => 'e-CVT'
        ];
        return $mapping[$transmission] ?? $transmission;
    }
}
