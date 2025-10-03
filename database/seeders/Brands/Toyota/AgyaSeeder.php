<?php

namespace Database\Seeders\Brands\Toyota;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class AgyaSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Toyota']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Agya'
        ]);

        $agyaTypes = [
            ['name' => 'E'],
            ['name' => 'G'],
            ['name' => 'TRD'],
            ['name' => 'GR Sport']
        ];

        $typeConfigurations = [
            'E' => [
                'generations' => [
                    '2013-2020 (Gen 1)' => [
                        'engine' => [
                            [
                                'cc' => 998,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '1KR-DE', // ✅ 1.0L 3 silinder
                                'transmission' => ['MT']
                            ],
                        ]
                    ]
                ]
            ],
            'G' => [
                'generations' => [
                    '2013-2020 (Gen 1)' => [
                        'engine' => [
                            [
                                'cc' => 998,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '1KR-DE',
                                'transmission' => ['MT','AT']
                            ],
                            [
                                'cc' => 1197,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '3NR-VE', // ✅ 1.2L NR series
                                'transmission' => ['MT','AT']
                            ],
                        ]
                    ],
                    '2020-2023 (Gen 2 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1197,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '3NR-VE',
                                'transmission' => ['MT','AT']
                            ],
                        ]
                    ]
                ]
            ],
            'TRD' => [
                'generations' => [
                    '2017-2020 (Gen 1 facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1197,
                                'fuel_type' => 'Bensin',
                                'engine_code' => '3NR-VE',
                                'transmission' => ['MT','AT']
                            ],
                        ]
                    ]
                ]
            ],
            'GR Sport' => [
                'generations' => [
                    '2023-Now (Gen 3)' => [
                        'engine' => [
                            [
                                'cc' => 1197,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'WA-VE', // ✅ mesin baru 1.2L WA-VE
                                'transmission' => ['CVT'] // GR Sport khusus CVT
                            ],
                        ]
                    ]
                ]
            ]
        ];


        foreach ($agyaTypes as $typeData) {
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

        if (!isset($configurations[$typeName])) {
            return;
        }

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
                                'segment' => 'City Car / Hatchback (A-Segment)',
                                'production_period' => $period
                            ],
                            [
                                'description' => $this->generateDescription(
                                    $typeName,
                                    $year,
                                    $engineConfig['cc'],
                                    $transmission,
                                    $engineConfig['fuel_type'],
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
        if (str_contains($period, '2013-2020')) {
            return range(2013, 2020);
        } elseif (str_contains($period, '2017-2020')) {
            return range(2017, 2020);
        } elseif (str_contains($period, '2020-2023')) {
            return range(2020, 2023);
        } elseif (str_contains($period, '2023-Now')) {
            return range(2023, $currentYear);
        }
        return [];
    }

    private function generateDescription($typeName, $year, $cc, $transmission, $fuelType, $generation): string
    {
        $transmissionText = $transmission === 'AT' ? 'matic' : ($transmission === 'CVT' ? 'CVT' : 'manual');
        $ccText = $this->getEngineSizeText($cc);
        $fuelText = strtolower($fuelType);

        return "Toyota Agya {$typeName} {$ccText} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan desain compact dan efisiensi bahan bakar tinggi.";
    }

    private function getEngineSizeText($cc): string
    {
        $engineSizes = [
            998 => '1.0L',
            1197 => '1.2L'
        ];

        return $engineSizes[$cc] ?? round($cc/1000, 1) . 'L';
    }
}
