<?php

namespace Database\Seeders\Brands\Honda;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class MobilioSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Honda']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Mobilio'
        ]);

        $mobilioTypes = [
            ['name' => 'S'],
            ['name' => 'E'],
            ['name' => 'E Prestige'],
            ['name' => 'RS'],
        ];

        $typeConfigurations = [
            'S' => [
                'generations' => [
                    '2014-2016 (Gen 1)' => [
                        'engine' => [[
                            'cc' => 1497,
                            'fuel_type' => 'Bensin',
                            'engine_code' => 'L15Z1',
                            'transmission' => ['MT'],
                            'power' => '118 HP'
                        ]]
                    ],
                    '2017-2021 (Gen 1 facelift)' => [
                        'engine' => [[
                            'cc' => 1497,
                            'fuel_type' => 'Bensin',
                            'engine_code' => 'L15Z1',
                            'transmission' => ['MT','CVT'],
                            'power' => '118 HP'
                        ]]
                    ],
                    '2022-2024 (Fleet only)' => [
                        'engine' => [[
                            'cc' => 1497,
                            'fuel_type' => 'Bensin',
                            'engine_code' => 'L15Z1',
                            'transmission' => ['MT'],
                            'power' => '118 HP'
                        ]]
                    ],
                ]
            ],
            'E' => [
                'generations' => [
                    '2014-2016 (Gen 1)' => [
                        'engine' => [[
                            'cc' => 1497,
                            'fuel_type' => 'Bensin',
                            'engine_code' => 'L15Z1',
                            'transmission' => ['MT','CVT'],
                            'power' => '118 HP'
                        ]]
                    ],
                    '2017-2021 (Gen 1 facelift)' => [
                        'engine' => [[
                            'cc' => 1497,
                            'fuel_type' => 'Bensin',
                            'engine_code' => 'L15Z1',
                            'transmission' => ['MT','CVT'],
                            'power' => '118 HP'
                        ]]
                    ],
                ]
            ],
            'E Prestige' => [
                'generations' => [
                    '2014-2016 (Gen 1)' => [
                        'engine' => [[
                            'cc' => 1497,
                            'fuel_type' => 'Bensin',
                            'engine_code' => 'L15Z1',
                            'transmission' => ['CVT'],
                            'power' => '118 HP'
                        ]]
                    ],
                ]
            ],
            'RS' => [
                'generations' => [
                    '2014-2016 (Gen 1)' => [
                        'engine' => [[
                            'cc' => 1497,
                            'fuel_type' => 'Bensin',
                            'engine_code' => 'L15Z1',
                            'transmission' => ['MT','CVT'],
                            'power' => '118 HP'
                        ]]
                    ],
                    '2017-2021 (Gen 1 facelift)' => [
                        'engine' => [[
                            'cc' => 1497,
                            'fuel_type' => 'Bensin',
                            'engine_code' => 'L15Z1',
                            'transmission' => ['MT','CVT'],
                            'power' => '118 HP'
                        ]]
                    ],
                ]
            ],
        ];

        foreach ($mobilioTypes as $typeData) {
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
        if (str_contains($period, '2014-2016')) {
            return range(2014, 2016);
        } elseif (str_contains($period, '2017-2021')) {
            return range(2017, 2021);
        } elseif (str_contains($period, '2022-2024')) {
            return range(2022, 2024);
        }
        return [];
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $fuelType, $generation, $power): string
    {
        $transmissionText = $this->getTransmissionText($transmission);
        $ccText = round($cc/1000, 1) . 'L';
        $fuelText = strtolower($fuelType);
        $bodyType = "MPV 7 penumpang";

        $specialFeatures = "";
        if ($typeName === 'S') {
            $specialFeatures = " Varian entry level dengan fitur dasar.";
        } elseif ($typeName === 'E') {
            $specialFeatures = " Varian menengah dengan fitur lebih lengkap.";
        } elseif ($typeName === 'E Prestige') {
            $specialFeatures = " Varian lebih mewah dengan transmisi CVT.";
        } elseif ($typeName === 'RS') {
            $specialFeatures = " Varian sporty dengan desain dan fitur premium.";
        }

        return "Honda Mobilio {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. Mobil keluarga {$bodyType} yang nyaman dan efisien.{$specialFeatures}";
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
