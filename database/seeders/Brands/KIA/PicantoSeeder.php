<?php

namespace Database\Seeders\Brands\Kia;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class PicantoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'KIA']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Picanto'
        ]);

        $picantoTypes = [
            ['name' => 'Cosmo'],
            ['name' => 'Platinum'],
            ['name' => 'GT-Line'],
            ['name' => 'X-Line'],
        ];

        $typeConfigurations = [
            'Cosmo' => [
                'generations' => [
                    '2004-2011' => [
                        'engine' => [
                            [
                                'cc' => 1100,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'G4HE',
                                'transmission' => ['MT', 'AT'],
                                'power' => '65 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'Platinum' => [
                'generations' => [
                    '2011-2017' => [
                        'engine' => [
                            [
                                'cc' => 1200,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'Kappa 1.25 MPI',
                                'transmission' => ['MT', 'AT'],
                                'power' => '87 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'GT-Line' => [
                'generations' => [
                    '2017-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1200,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'Kappa 1.25 MPI',
                                'transmission' => ['MT', 'AT'],
                                'power' => '84 HP'
                            ],
                        ]
                    ],
                ]
            ],
            'X-Line' => [
                'generations' => [
                    '2018-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1200,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'Kappa 1.25 MPI',
                                'transmission' => ['AT'],
                                'power' => '84 HP'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($picantoTypes as $typeData) {
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

        $specialFeatures = " Dikenal sebagai city car yang lincah dan ekonomis.";
        if ($typeName === 'GT-Line' || $typeName === 'X-Line') {
            $specialFeatures = " Memiliki desain sporty dan modern dengan fitur yang lebih lengkap.";
        }

        return "KIA Picanto {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. Kendaraan {$bodyType} yang menawarkan kemudahan manuver di perkotaan.{$specialFeatures}";
    }

    /**
     * Map a transmission code to a descriptive string.
     */
    private function getTransmissionText($transmission): string
    {
        $mapping = [
            'MT' => 'manual',
            'AT' => 'matic',
            'CVT' => 'CVT (Continuously Variable Transmission)',
            'AMT' => 'AMT (Automated Manual Transmission)',
            'SSG' => 'single speed gear',
        ];
        
        return $mapping[$transmission] ?? $transmission;
    }
}
