<?php

namespace Database\Seeders\Brands\Suzuki;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class APVSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Suzuki']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'APV'
        ]);

        $apvTypes = [
            ['name' => 'GL'],
            ['name' => 'GX'],
            ['name' => 'Arena'],
            ['name' => 'Luxury'],
        ];

        $typeConfigurations = [
            'GL' => [
                'generations' => [
                    '2004-2007' => [
                        'engine' => [
                            [
                                'cc' => 1493,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'G15A',
                                'transmission' => ['MT'],
                                'power' => '92 HP'
                            ],
                        ]
                    ],
                    '2007-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1493,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'G15A',
                                'transmission' => ['MT', 'AT'],
                                'power' => '94 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'GX' => [
                'generations' => [
                    '2004-2007' => [
                        'engine' => [
                            [
                                'cc' => 1493,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'G15A',
                                'transmission' => ['MT'],
                                'power' => '92 HP'
                            ],
                        ]
                    ],
                    '2007-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1493,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'G15A',
                                'transmission' => ['MT', 'AT'],
                                'power' => '94 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'Arena' => [
                'generations' => [
                    '2007-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1493,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'G15A',
                                'transmission' => ['MT', 'AT'],
                                'power' => '94 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'Luxury' => [
                'generations' => [
                    '2009-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1493,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'G15A',
                                'transmission' => ['MT', 'AT'],
                                'power' => '94 HP'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($apvTypes as $typeData) {
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

    /**
     * Generate car detail variants for a given car type.
     */
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

    /**
     * Get an array of years for a given production period.
     */
    private function getYearsForGeneration($period, $currentYear): array
    {
        $yearParts = explode('-', $period);
        $startYear = (int)$yearParts[0];
        $endYear = str_contains($period, 'Sekarang') ? $currentYear : (int)explode(' ', $yearParts[1])[0];
        
        return range($startYear, $endYear);
    }

    /**
     * Generate a descriptive string for a car detail.
     */
    private function generateDescription($typeName, $year, $cc, $engineCode, $transmission, $fuelType, $generation, $power): string
    {
        $transmissionText = $this->getTransmissionText($transmission);
        $ccText = round($cc / 1000, 1) . 'L';
        $fuelText = strtolower($fuelType);
        $bodyType = "MPV";

        $specialFeatures = "";
        if ($typeName === 'Luxury') {
            $specialFeatures = " Varian mewah dengan fitur premium.";
        } elseif ($typeName === 'Arena') {
            $specialFeatures = " Dikenal dengan desain dan kenyamanan yang lebih ditingkatkan.";
        }

        return "Suzuki APV {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. Mobil {$bodyType} yang luas dan tangguh.{$specialFeatures}";
    }

    /**
     * Map a transmission code to a descriptive string.
     */
    private function getTransmissionText($transmission): string
    {
        $mapping = [
            'MT' => 'manual',
            'AT' => 'matic',
            'CVT' => 'CVT'
        ];
        
        return $mapping[$transmission] ?? $transmission;
    }
}
