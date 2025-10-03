<?php

namespace Database\Seeders\Brands\Nissan;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class EvaliaSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Nissan']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Evalia'
        ]);

        $evaliaTypes = [
            ['name' => 'S'],
            ['name' => 'SV'],
            ['name' => 'XV'],
            ['name' => 'ST'],
        ];

        $typeConfigurations = [
            'S' => [
                'generations' => [
                    '2012-2019 (Generasi Pertama)' => [
                        'engine' => [
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'HR15DE',
                                'transmission' => ['MT', 'AT'],
                                'power' => '109 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'SV' => [
                'generations' => [
                    '2012-2019 (Generasi Pertama)' => [
                        'engine' => [
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'HR15DE',
                                'transmission' => ['MT', 'AT'],
                                'power' => '109 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'XV' => [
                'generations' => [
                    '2012-2019 (Generasi Pertama)' => [
                        'engine' => [
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'HR15DE',
                                'transmission' => ['MT', 'AT'],
                                'power' => '109 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'ST' => [
                'generations' => [
                    '2012-2019 (Generasi Pertama)' => [
                        'engine' => [
                            [
                                'cc' => 1498,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'HR15DE',
                                'transmission' => ['MT', 'AT'],
                                'power' => '109 HP'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($evaliaTypes as $typeData) {
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
                                'segment' => 'MPV Komersial',
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
        if (str_contains($period, '2012-2019')) {
            return range(2012, 2019);
        }
        return [];
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $fuelType, $generation, $power): string
    {
        $transmissionText = $this->getTransmissionText($transmission);
        $ccText = round($cc/1000, 1) . 'L';
        $fuelText = strtolower($fuelType);
        $bodyType = "MPV komersial 7-8 seat";

        $specialFeatures = "";
        if ($typeName === 'ST') {
            $specialFeatures = " Varian fleet/komersial dengan spesifikasi dasar, jarang dipasarkan ke retail.";
        } elseif ($typeName === 'S') {
            $specialFeatures = " Varian entry level dengan fitur standar untuk keluarga.";
        } elseif ($typeName === 'SV') {
            $specialFeatures = " Varian mid-range dengan kelengkapan fitur yang lebih baik.";
        } elseif ($typeName === 'XV') {
            $specialFeatures = " Varian tertinggi dengan fitur paling lengkap dalam jajaran Evalia.";
        }

        return "Nissan Evalia {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power} dan torsi 148 Nm. " .
               "MPV berbasis platform NV200 yang diproduksi di Purwakarta, Indonesia.{$specialFeatures}";
    }

    private function getTransmissionText($transmission): string
    {
        $mapping = [
            'MT' => 'manual 5-speed',
            'AT' => 'matic 4-speed'
        ];
        
        return $mapping[$transmission] ?? $transmission;
    }
}