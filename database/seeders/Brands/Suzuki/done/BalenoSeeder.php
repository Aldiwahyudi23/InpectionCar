<?php

namespace Database\Seeders\Brands\Suzuki;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class BalenoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Suzuki']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Baleno'
        ]);

        $balenoTypes = [
            ['name' => 'Sedan'],
            ['name' => 'Hatchback'],
            ['name' => 'RS'],
        ];

        $typeConfigurations = [
            'Sedan' => [
                'generations' => [
                    '2000-2003 (Gen 2 Facelift)' => [
                        'engine' => [
                            [
                                'cc' => 1493,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'G15A',
                                'transmission' => ['MT', 'AT'],
                                'power' => '108 HP'
                            ],
                        ]
                    ],
                    '2003-2007 (Next-G)' => [
                        'engine' => [
                            [
                                'cc' => 1493,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'M15A',
                                'transmission' => ['MT', 'AT'],
                                'power' => '100 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'Hatchback' => [
                'generations' => [
                    '2017-2022 (Gen 3)' => [
                        'engine' => [
                            [
                                'cc' => 1373,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K14B',
                                'transmission' => ['MT', 'AT'],
                                'power' => '92 HP'
                            ],
                        ]
                    ],
                    '2022-Sekarang (Gen 4)' => [
                        'engine' => [
                            [
                                'cc' => 1462,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K15B',
                                'transmission' => ['MT', 'AT'],
                                'power' => '104 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'RS' => [
                'generations' => [
                    '2017-2022 (Gen 3)' => [
                        'engine' => [
                            [
                                'cc' => 1373,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K14C',
                                'transmission' => ['MT', 'AT'],
                                'power' => '100 HP'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($balenoTypes as $typeData) {
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
                                'segment' => $typeName === 'Hatchback' ? 'Hatchback' : 'Sedan',
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
        $endYear = str_contains($yearParts[1], 'Sekarang') ? $currentYear : (int)explode(' ', $yearParts[1])[0];
        
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
        $bodyType = $typeName === 'Hatchback' ? "Hatchback" : "Sedan";

        $specialFeatures = "";
        if ($typeName === 'Hatchback') {
            $specialFeatures = " Desain modern dan kompak untuk penggunaan perkotaan.";
        } elseif ($typeName === 'RS') {
            $specialFeatures = " Varian sporty dengan performa yang lebih tinggi.";
        } elseif (str_contains($generation, 'Next-G')) {
            $specialFeatures = " Dikenal sebagai Baleno generasi 'Next-G' dengan desain dan fitur yang lebih segar.";
        }

        return "Suzuki Baleno {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. Mobil {$bodyType} yang lincah dan elegan.{$specialFeatures}";
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
