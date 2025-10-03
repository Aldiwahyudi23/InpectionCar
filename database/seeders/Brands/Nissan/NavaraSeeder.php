<?php

namespace Database\Seeders\Brands\Nissan;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class NavaraSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Nissan']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Navara'
        ]);

        $navaraTypes = [
            ['name' => 'SL'],
            ['name' => 'VL'],
            ['name' => 'ST-X'],
            ['name' => 'ST'],
            ['name' => 'RX'],
            ['name' => 'DX'],
            ['name' => 'PRO-4X'],
        ];

        $typeConfigurations = [
            'SL' => [
                'generations' => [
                    '2015-Sekarang (D23 NP300)' => [
                        'engine' => [
                            [
                                'cc' => 2298,
                                'fuel_type' => 'Diesel',
                                'engine_code' => 'YS23DDTT',
                                'transmission' => ['MT', 'AT'],
                                'power' => '163 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'VL' => [
                'generations' => [
                    '2015-Sekarang (D23 NP300)' => [
                        'engine' => [
                            [
                                'cc' => 2298,
                                'fuel_type' => 'Diesel',
                                'engine_code' => 'YS23DDTT',
                                'transmission' => ['MT', 'AT'],
                                'power' => '190 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'ST-X' => [
                'generations' => [
                    '2005-2015 (D40)' => [
                        'engine' => [
                            [
                                'cc' => 2488,
                                'fuel_type' => 'Diesel',
                                'engine_code' => 'YD25DDTi',
                                'transmission' => ['MT', 'AT'],
                                'power' => '128 kW'
                            ],
                            [
                                'cc' => 3954,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'VQ40DE',
                                'transmission' => ['MT', 'AT'],
                                'power' => '198 kW'
                            ],
                        ]
                    ],
                    '2015-Sekarang (D23 NP300)' => [
                        'engine' => [
                            [
                                'cc' => 2298,
                                'fuel_type' => 'Diesel',
                                'engine_code' => 'YS23DDTT',
                                'transmission' => ['MT', 'AT'],
                                'power' => '190 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'ST' => [
                'generations' => [
                    '2005-2015 (D40)' => [
                        'engine' => [
                            [
                                'cc' => 2488,
                                'fuel_type' => 'Diesel',
                                'engine_code' => 'YD25DDTi',
                                'transmission' => ['MT', 'AT'],
                                'power' => '128 kW'
                            ],
                        ]
                    ],
                ]
            ],
            'RX' => [
                'generations' => [
                    '2005-2015 (D40)' => [
                        'engine' => [
                            [
                                'cc' => 2488,
                                'fuel_type' => 'Diesel',
                                'engine_code' => 'YD25DDTi',
                                'transmission' => ['MT', 'AT'],
                                'power' => '128 kW'
                            ],
                        ]
                    ],
                    '2015-Sekarang (D23 NP300)' => [
                        'engine' => [
                            [
                                'cc' => 2298,
                                'fuel_type' => 'Diesel',
                                'engine_code' => 'YS23DDTT',
                                'transmission' => ['MT', 'AT'],
                                'power' => '163 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'DX' => [
                'generations' => [
                    '2015-Sekarang (D23 NP300)' => [
                        'engine' => [
                            [
                                'cc' => 2488,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'QR25',
                                'transmission' => ['MT', 'AT'],
                                'power' => '122 kW'
                            ],
                            [
                                'cc' => 2298,
                                'fuel_type' => 'Diesel',
                                'engine_code' => 'YS23DDTT',
                                'transmission' => ['MT'],
                                'power' => '120 kW'
                            ],
                        ]
                    ],
                ]
            ],
            'PRO-4X' => [
                'generations' => [
                    '2018-Sekarang (D23 NP300)' => [
                        'engine' => [
                            [
                                'cc' => 2298,
                                'fuel_type' => 'Diesel',
                                'engine_code' => 'YS23DDTT',
                                'transmission' => ['AT'],
                                'power' => '190 HP'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($navaraTypes as $typeData) {
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
                                'segment' => 'Pickup Truck',
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
        if (str_contains($period, '1985-1997')) {
            return range(1985, 1997);
        } elseif (str_contains($period, '1997-2015')) {
            return range(1997, 2015);
        } elseif (str_contains($period, '2005-2015')) {
            return range(2005, 2015);
        } elseif (str_contains($period, '2015-Sekarang')) {
            return range(2015, $currentYear);
        } elseif (str_contains($period, '2018-Sekarang')) {
            return range(2018, $currentYear);
        }
        return [];
    }

    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $fuelType, $generation, $power): string
    {
        $transmissionText = $this->getTransmissionText($transmission);
        $ccText = round($cc/1000, 1) . 'L';
        $fuelText = strtolower($fuelType);
        $bodyType = "Pickup Double Cabin";

        $specialFeatures = "";
        if ($typeName === 'SL') {
            $specialFeatures = " Varian yang sering ditujukan untuk pasar fleet atau korporat, dengan fokus pada efisiensi bahan bakar:cite[9].";
        } elseif ($typeName === 'VL') {
            $specialFeatures = " Varian untuk konsumen retail/hobi dengan tenaga lebih tinggi dan fitur yang lebih lengkap:cite[9].";
        } elseif ($typeName === 'ST-X') {
            $specialFeatures = " Varian flagship dengan kelengkapan fitur terbaik, seringkali dengan kemampuan off-road yang ditingkatkan.";
        } elseif ($typeName === 'PRO-4X') {
            $specialFeatures = " Varian performa tinggi dengan penyetelan khusus untuk petualangan off-road yang ekstrem:cite[10].";
        } elseif (str_contains($generation, 'D23')) {
            $specialFeatures = " Generasi NP300 dengan sasis dan teknologi terbaru, termasuk pilihan suspensi coil spring untuk kenyamanan yang lebih baik:cite[6]:cite[8].";
        }

        return "Nissan Navara {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. {$bodyType} yang tangguh dan andal.{$specialFeatures}";
    }

    private function getTransmissionText($transmission): string
    {
        $mapping = [
            'MT' => 'manual',
            'AT' => 'otomatis'
        ];
        
        return $mapping[$transmission] ?? $transmission;
    }
}