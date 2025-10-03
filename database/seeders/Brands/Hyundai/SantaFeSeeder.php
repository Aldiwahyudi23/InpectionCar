<?php

namespace Database\Seeders\Brands\Hyundai;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class SantaFeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Hyundai']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Santa Fe'
        ]);

        $santafeTypes = [
            ['name' => 'GLS'],
            ['name' => 'CRDi'],
            ['name' => 'Bensin'],
            ['name' => 'Diesel'],
            ['name' => 'Signature'],
        ];

        $typeConfigurations = [
            'GLS' => [
                'generations' => [
                    '2006-2012' => [
                        'engine' => [
                            [
                                'cc' => 2700,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'Delta G6BA',
                                'transmission' => ['AT'],
                                'power' => '189 PS'
                            ],
                        ]
                    ],
                ]
            ],
            'CRDi' => [
                'generations' => [
                    '2006-2012' => [
                        'engine' => [
                            [
                                'cc' => 2200,
                                'fuel_type' => 'Diesel',
                                'engine_code' => 'D4EB',
                                'transmission' => ['AT'],
                                'power' => '150 PS'
                            ],
                        ]
                    ],
                ]
            ],
            'Bensin' => [
                'generations' => [
                    '2018-2021' => [
                        'engine' => [
                            [
                                'cc' => 2400,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'Theta II MPi',
                                'transmission' => ['AT'],
                                'power' => '172 PS'
                            ],
                        ]
                    ],
                ]
            ],
            'Diesel' => [
                'generations' => [
                    '2018-2021' => [
                        'engine' => [
                            [
                                'cc' => 2200,
                                'fuel_type' => 'Diesel',
                                'engine_code' => 'R2.2 CRDi',
                                'transmission' => ['AT'],
                                'power' => '193 PS'
                            ],
                        ]
                    ],
                ]
            ],
            'Signature' => [
                'generations' => [
                    '2021-Sekarang' => [
                        'engine' => [
                            [
                                'cc' => 2500,
                                'fuel_type' => 'Bensin',
                                'engine_code' => 'Smartstream G2.5 MPi',
                                'transmission' => ['AT'],
                                'power' => '180 PS'
                            ],
                            [
                                'cc' => 2200,
                                'fuel_type' => 'Diesel',
                                'engine_code' => 'Smartstream D2.2 CRDi',
                                'transmission' => ['DCT'],
                                'power' => '202 PS'
                            ],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($santafeTypes as $typeData) {
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
                                'segment' => 'SUV',
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
        $ccText = ($cc > 0) ? round($cc / 1000, 1) . 'L' : 'EV';
        $fuelText = strtolower($fuelType);
        $bodyType = "SUV";
        $specialFeatures = " Dikenal dengan desain modern, kabin nyaman, dan fitur keselamatan yang lengkap.";

        if ($typeName === 'Signature') {
            $specialFeatures = " Varian tertinggi dengan fitur premium dan desain yang lebih mewah.";
        }
        return "Hyundai Santa Fe {$typeName} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Kendaraan {$bodyType} yang populer di segmen SUV.{$specialFeatures}";
    }
}
