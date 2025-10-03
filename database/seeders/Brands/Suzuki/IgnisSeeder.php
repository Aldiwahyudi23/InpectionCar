<?php

namespace Database\Seeders\Brands\Suzuki;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class IgnisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Suzuki']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Ignis'
        ]);

        $ignisTypes = [
            ['name' => 'GL'],
            ['name' => 'GX'],
            ['name' => 'Sport Edition'],
        ];

        $typeConfigurations = [
            'GL' => [
                'generations' => [
                    '2017-2020' => [
                        'engine' => [
                            [
                                'cc' => 1197,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K12M',
                                'transmission' => ['MT', 'AGS'],
                                'power' => '83 HP'
                            ],
                        ]
                    ],
                    '2020-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1197,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K12M',
                                'transmission' => ['MT', 'AGS'],
                                'power' => '83 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'GX' => [
                'generations' => [
                    '2017-2020' => [
                        'engine' => [
                            [
                                'cc' => 1197,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K12M',
                                'transmission' => ['MT', 'AGS'],
                                'power' => '83 HP'
                            ],
                        ]
                    ],
                    '2020-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1197,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K12M',
                                'transmission' => ['MT', 'AGS'],
                                'power' => '83 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'Sport Edition' => [
                'generations' => [
                    '2017-2019' => [
                        'engine' => [
                            [
                                'cc' => 1197,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K12M',
                                'transmission' => ['MT', 'AGS'],
                                'power' => '83 HP'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($ignisTypes as $typeData) {
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
                                'segment' => 'City Car / Urban SUV',
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
        $bodyType = "Urban SUV";

        $specialFeatures = " Dikenal dengan desainnya yang unik dan fitur-fitur modern.";
        if ($typeName === 'Sport Edition') {
            $specialFeatures = " Varian dengan tampilan sporty dan aksesoris tambahan.";
        }

        return "Suzuki Ignis {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. Kendaraan {$bodyType} yang lincah dan bergaya.{$specialFeatures}";
    }

    /**
     * Map a transmission code to a descriptive string.
     */
    private function getTransmissionText($transmission): string
    {
        $mapping = [
            'MT' => 'manual',
            'AT' => 'matic',
            'AGS' => 'AGS (Auto Gear Shift)'
        ];
        
        return $mapping[$transmission] ?? $transmission;
    }
}
