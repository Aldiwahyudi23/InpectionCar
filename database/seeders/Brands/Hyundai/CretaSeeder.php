<?php

namespace Database\Seeders\Brands\Hyundai;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class CretaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Hyundai']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Creta'
        ]);

        $cretaTypes = [
            ['name' => 'Active'],
            ['name' => 'Trend'],
            ['name' => 'Style'],
            ['name' => 'Prime'],
            ['name' => 'Alpha'],
        ];

        $typeConfigurations = [
            'Active' => [
                'generations' => [
                    '2021-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1500,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'Smartstream G 1.5 MPI',
                                'transmission' => ['MT'],
                                'power' => '115 PS'
                            ],
                        ]
                    ],
                ]
            ],
            'Trend' => [
                'generations' => [
                    '2021-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1500,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'Smartstream G 1.5 MPI',
                                'transmission' => ['MT', 'IVT'],
                                'power' => '115 PS'
                            ],
                        ]
                    ],
                ]
            ],
            'Style' => [
                'generations' => [
                    '2021-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1500,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'Smartstream G 1.5 MPI',
                                'transmission' => ['IVT'],
                                'power' => '115 PS'
                            ],
                        ]
                    ],
                ]
            ],
            'Prime' => [
                'generations' => [
                    '2021-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1500,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'Smartstream G 1.5 MPI',
                                'transmission' => ['IVT'],
                                'power' => '115 PS'
                            ],
                        ]
                    ],
                ]
            ],
            'Alpha' => [
                'generations' => [
                    '2024-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 1500,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'Smartstream G 1.5 MPI',
                                'transmission' => ['IVT'],
                                'power' => '115 PS'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($cretaTypes as $typeData) {
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
                                'segment' => 'Compact SUV',
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
        $bodyType = "Compact SUV";

        $specialFeatures = " Dikenal dengan desain futuristik dan fitur canggih.";
        if ($typeName === 'Alpha') {
            $specialFeatures = " Varian edisi khusus yang memiliki tampilan lebih eksklusif.";
        }

        return "Hyundai Creta {$typeName} {$ccText} {$engineCode} {$transmissionText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. Kendaraan {$bodyType} yang sangat populer di Indonesia.{$specialFeatures}";
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
            'IVT' => 'IVT (Intelligent Variable Transmission)',
            'AMT' => 'AMT (Automated Manual Transmission)',
            'Single Speed Gear' => 'single speed gear',
        ];
        
        return $mapping[$transmission] ?? $transmission;
    }
}
