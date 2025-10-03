<?php

namespace Database\Seeders\Brands\Suzuki;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class SPressoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Suzuki']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'S-Presso'
        ]);

        $sPressoTypes = [
            ['name' => 'Standar'],
        ];

        $typeConfigurations = [
            'Standar' => [
                'generations' => [
                    '2022-2023' => [
                        'engine' => [
                            [
                                'cc' => 998,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K10B',
                                'transmission' => ['MT', 'AGS'],
                                'power' => '67 HP'
                            ],
                        ]
                    ],
                    '2023-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 998,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'K10C',
                                'transmission' => ['MT', 'AGS'],
                                'power' => '68 HP'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($sPressoTypes as $typeData) {
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
                                'segment' => 'City Car',
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
        $bodyType = "City Car";

        $specialFeatures = " Varian ini dikenal dengan desainnya yang kompak dan harga yang terjangkau.";
        if (str_contains($generation, '2023-Sekarang')) {
            $specialFeatures = " Varian facelift dengan peningkatan fitur keselamatan dan efisiensi bahan bakar.";
        }

        return "Suzuki S-Presso {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. Mobil {$bodyType} yang lincah dan ekonomis.{$specialFeatures}";
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
