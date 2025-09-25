<?php

namespace Database\Seeders\Brands\BMW;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class Seri4Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'BMW']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Seri 4'
        ]);

        $seri4Types = [
            ['name' => '420i Coupe'],
            ['name' => '430i Coupe'],
            ['name' => '430i Gran Coupe'],
            ['name' => 'M440i Coupe'],
        ];

        $typeConfigurations = [
            '420i Coupe' => [
                'generations' => [
                    '2014-2020' => [
                        'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'N20B20', 'transmission' => ['AT'], 'power' => '184 PS']],
                    ],
                    '2020-Sekarang' => [
                        'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'B48B20', 'transmission' => ['AT'], 'power' => '184 PS']],
                    ],
                ]
            ],
            '430i Coupe' => [
                'generations' => [
                    '2014-2020' => [
                        'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'B48B20', 'transmission' => ['AT'], 'power' => '252 PS']],
                    ],
                    '2020-Sekarang' => [
                        'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'B48B20', 'transmission' => ['AT'], 'power' => '258 PS']],
                    ],
                ]
            ],
            '430i Gran Coupe' => [
                'generations' => [
                    '2021-Sekarang' => [
                        'engine' => [['cc' => 2000, 'fuel_type' => 'Bensin', 'engine_code' => 'B48B20', 'transmission' => ['AT'], 'power' => '245 PS']],
                    ],
                ]
            ],
            'M440i Coupe' => [
                'generations' => [
                    '2020-Sekarang' => [
                        'engine' => [['cc' => 3000, 'fuel_type' => 'Bensin', 'engine_code' => 'B58B30', 'transmission' => ['AT'], 'power' => '387 PS']],
                    ],
                ]
            ],
        ];

        foreach ($seri4Types as $typeData) {
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
                                'segment' => 'Coupe & Gran Coupe',
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
        $bodyType = str_contains($typeName, 'Gran Coupe') ? 'Gran Coupe' : 'Coupe';
        $specialFeatures = " Kendaraan {$bodyType} sport yang menawarkan desain elegan, performa dinamis, dan handling superior.";

        if ($typeName === 'M440i Coupe') {
            $specialFeatures = " Varian performa tinggi dengan mesin 6 silinder, memberikan performa balap yang disempurnakan untuk jalan raya.";
        }

        return "BMW Seri 4 {$typeName} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Kendaraan {$bodyType} yang dikenal karena perpaduan gaya dan performa.{$specialFeatures}";
    }
}
