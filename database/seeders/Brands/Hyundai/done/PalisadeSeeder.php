<?php

namespace Database\Seeders\Brands\Hyundai;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class PalisadeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Hyundai']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Palisade'
        ]);

        $palisadeTypes = [
            ['name' => 'Prime'],
            ['name' => 'Signature'],
            ['name' => 'Calligraphy'],
        ];

        $typeConfigurations = [
            'Prime' => [
                'generations' => [
                    '2019-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 2200,
                                'fuel_type' => 'Diesel',
                                'engine_code' => '2.2 CRDi-EVGT',
                                'transmission' => ['AT'],
                                'power' => '200 PS'
                            ],
                        ]
                    ],
                ]
            ],
            'Signature' => [
                'generations' => [
                    '2019-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 2200,
                                'fuel_type' => 'Diesel',
                                'engine_code' => '2.2 CRDi-EVGT',
                                'transmission' => ['AT'],
                                'power' => '200 PS'
                            ],
                        ]
                    ],
                ]
            ],
            'Calligraphy' => [
                'generations' => [
                    '2022-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 2200,
                                'fuel_type' => 'Diesel',
                                'engine_code' => '2.2 CRDi-EVGT',
                                'transmission' => ['AT'],
                                'power' => '200 PS'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($palisadeTypes as $typeData) {
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
                                'segment' => 'SUV Premium',
                                'production_period' => $period
                            ],
                            [
                                'description' => $this->generateDescription(
                                    $typeName,
                                    $year,
                                    $engineConfig['cc'],
                                    $engineConfig['power'],
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
    private function generateDescription($typeName, $year, $cc, $power, $fuelType, $generation): string
    {
        $ccText = round($cc / 1000, 1) . 'L';
        $fuelText = strtolower($fuelType);
        $bodyType = "SUV Premium";
        $specialFeatures = " Dikenal dengan ruang kabin yang mewah, kapasitas 7-seater, dan fitur keselamatan yang lengkap.";

        if ($typeName === 'Calligraphy') {
            $specialFeatures = " Varian tertinggi dengan sentuhan desain lebih elegan dan fitur terlengkap.";
        }

        return "Hyundai Palisade {$typeName} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Kendaraan {$bodyType} yang menawarkan kemewahan dan kenyamanan.{$specialFeatures}";
    }
}
