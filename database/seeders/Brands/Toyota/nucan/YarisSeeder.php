<?php

namespace Database\Seeders\Brands\Toyota;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class YarisSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Toyota']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Yaris'
        ]);

        // VARIAN AKTUAL TOYOTA YARIS BERDASARKAN PASAR GLOBAL
        $yarisTypes = [
            ['name' => 'J'],
            ['name' => 'E'],
            ['name' => 'G'],
            ['name' => 'S'],
            ['name' => 'Z'],
            ['name' => 'GR Sport'],
            ['name' => 'GR Yaris'] // Varian performa tinggi
        ];

        $typeConfigurations = [
            'J' => [
                'generations' => [
                    '2011-2019 (XP130)' => [
                        'engine' => [
                            ['cc' => 998, 'code' => '1KR-FE', 'fuel_type' => 'Bensin', 'transmission' => ['MT', 'CVT'], 'power' => 68, 'torque' => 92],
                            ['cc' => 1329, 'code' => '2SZ-FE', 'fuel_type' => 'Bensin', 'transmission' => ['CVT'], 'power' => 91, 'torque' => 118]
                        ]
                    ],
                    '2020-Now (XP210)' => [
                        'engine' => [
                            ['cc' => 1490, 'code' => 'M15A-FKS', 'fuel_type' => 'Bensin', 'transmission' => ['CVT'], 'power' => 120, 'torque' => 145]
                        ]
                    ]
                ]
            ],
            'E' => [
                'generations' => [
                    '2011-2019 (XP130)' => [
                        'engine' => [
                            ['cc' => 1329, 'code' => '2SZ-FE', 'fuel_type' => 'Bensin', 'transmission' => ['CVT'], 'power' => 91, 'torque' => 118],
                            ['cc' => 1496, 'code' => '1NZ-FE', 'fuel_type' => 'Bensin', 'transmission' => ['MT', 'CVT'], 'power' => 109, 'torque' => 141]
                        ]
                    ],
                    '2020-Now (XP210)' => [
                        'engine' => [
                            ['cc' => 1490, 'code' => 'M15A-FKS', 'fuel_type' => 'Bensin', 'transmission' => ['CVT'], 'power' => 120, 'torque' => 145],
                            ['cc' => 1490, 'code' => 'M15A-FXE', 'fuel_type' => 'Hybrid', 'transmission' => ['CVT'], 'power' => 116, 'torque' => 141]
                        ]
                    ]
                ]
            ],
            'G' => [
                'generations' => [
                    '2011-2019 (XP130)' => [
                        'engine' => [
                            ['cc' => 1496, 'code' => '1NZ-FE', 'fuel_type' => 'Bensin', 'transmission' => ['MT', 'CVT'], 'power' => 109, 'torque' => 141]
                        ]
                    ],
                    '2020-Now (XP210)' => [
                        'engine' => [
                            ['cc' => 1490, 'code' => 'M15A-FKS', 'fuel_type' => 'Bensin', 'transmission' => ['CVT'], 'power' => 120, 'torque' => 145],
                            ['cc' => 1490, 'code' => 'M15A-FXE', 'fuel_type' => 'Hybrid', 'transmission' => ['CVT'], 'power' => 116, 'torque' => 141]
                        ]
                    ]
                ]
            ],
            'S' => [
                'generations' => [
                    '2011-2019 (XP130)' => [
                        'engine' => [
                            ['cc' => 1496, 'code' => '1NZ-FE', 'fuel_type' => 'Bensin', 'transmission' => ['MT', 'CVT'], 'power' => 109, 'torque' => 141]
                        ]
                    ]
                ]
            ],
            'Z' => [
                'generations' => [
                    '2020-Now (XP210)' => [
                        'engine' => [
                            ['cc' => 1490, 'code' => 'M15A-FKS', 'fuel_type' => 'Bensin', 'transmission' => ['CVT'], 'power' => 120, 'torque' => 145],
                            ['cc' => 1490, 'code' => 'M15A-FXE', 'fuel_type' => 'Hybrid', 'transmission' => ['CVT'], 'power' => 116, 'torque' => 141]
                        ]
                    ]
                ]
            ],
            'GR Sport' => [
                'generations' => [
                    '2020-Now (XP210)' => [
                        'engine' => [
                            ['cc' => 1490, 'code' => 'M15A-FXE', 'fuel_type' => 'Hybrid', 'transmission' => ['CVT'], 'power' => 116, 'torque' => 141]
                        ]
                    ]
                ]
            ],
            'GR Yaris' => [
                'generations' => [
                    '2020-Now (XP210)' => [
                        'engine' => [
                            ['cc' => 1618, 'code' => 'G16E-GTS', 'fuel_type' => 'Bensin', 'transmission' => ['6MT'], 'power' => 261, 'torque' => 360]
                        ]
                    ]
                ]
            ]
        ];

        foreach ($yarisTypes as $typeData) {
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
                                'segment' => 'Supermini / Subcompact Car',
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
        if (str_contains($period, '2011-2019')) {
            return range(2011, 2019);
        } elseif (str_contains($period, '2020-Now')) {
            return range(2020, $currentYear);
        }
        return [];
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, 
                                       $fuelType, $power, $torque, $generation): string
    {
        $transmissionText = $this->getTransmissionText($transmission);
        $engineSize = $this->getEngineSizeText($cc);
        
        $description = "Toyota Yaris {$typeName} {$engineSize} {$transmissionText} {$fuelType} " .
               "({$power} hp, {$torque} Nm) tahun {$year}. Mesin {$engineCode} dengan " .
               "konsumsi bahan bakar efisien. Generasi {$generation}.";
        
        // Tambahan deskripsi khusus untuk varian GR Yaris
        if ($typeName === 'GR Yaris') {
            $description .= " Varian performa tinggi dengan teknologi rally-inspired, all-wheel drive, " .
                           "dan bodi yang diperkuat untuk handling yang exceptional.";
        }
        
        return $description;
    }
    
    private function getTransmissionText($transmission): string
    {
        $transmissionMap = [
            'MT' => 'manual',
            'CVT' => 'CVT',
            '6MT' => '6-speed manual'
        ];
        
        return $transmissionMap[$transmission] ?? strtolower($transmission);
    }
    
    private function getEngineSizeText($cc): string
    {
        $engineSizes = [
            998 => '1.0L',
            1329 => '1.3L',
            1490 => '1.5L',
            1496 => '1.5L',
            1618 => '1.6L Turbo'
        ];
        
        return $engineSizes[$cc] ?? round($cc/1000, 1) . 'L';
    }
}