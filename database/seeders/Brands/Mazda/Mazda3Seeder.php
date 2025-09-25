<?php

namespace Database\Seeders\Brands\Mazda;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class Mazda3Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Mazda']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => '3'
        ]);

        $mazda3Types = [
            ['name' => 'Hatchback'],
            ['name' => 'Sedan'],
        ];

        $typeConfigurations = [
            'Hatchback' => [
                'generations' => [
                    '2003-2009' => [
                        'engine' => [
                            ['cc' => 1598, 'fuel_type' => 'Bensin', 'engine_code' => 'Z6', 'transmission' => ['MT', 'AT'], 'power' => '105 PS'],
                            ['cc' => 1999, 'fuel_type' => 'Bensin', 'engine_code' => 'LF-DE', 'transmission' => ['MT', 'AT'], 'power' => '150 PS'],
                        ],
                    ],
                    '2010-2013' => [
                        'engine' => [
                            ['cc' => 1598, 'fuel_type' => 'Bensin', 'engine_code' => 'Z6', 'transmission' => ['MT', 'AT'], 'power' => '105 PS'],
                            ['cc' => 1999, 'fuel_type' => 'Bensin', 'engine_code' => 'LF-DE', 'transmission' => ['AT'], 'power' => '148 PS'],
                        ],
                    ],
                    '2014-2019' => [
                        'engine' => [
                            ['cc' => 1998, 'fuel_type' => 'Bensin', 'engine_code' => 'PE-VPS', 'transmission' => ['AT'], 'power' => '155 PS'],
                            ['cc' => 2488, 'fuel_type' => 'Bensin', 'engine_code' => 'PY-VPS', 'transmission' => ['AT'], 'power' => '184 PS'],
                        ],
                    ],
                    '2019-Sekarang' => [
                        'engine' => [
                            ['cc' => 1998, 'fuel_type' => 'Bensin', 'engine_code' => 'PE-VPS', 'transmission' => ['AT'], 'power' => '155 PS'],
                        ],
                    ],
                ]
            ],
            'Sedan' => [
                'generations' => [
                    '2003-2009' => [
                        'engine' => [
                            ['cc' => 1598, 'fuel_type' => 'Bensin', 'engine_code' => 'Z6', 'transmission' => ['MT', 'AT'], 'power' => '105 PS'],
                            ['cc' => 1999, 'fuel_type' => 'Bensin', 'engine_code' => 'LF-DE', 'transmission' => ['MT', 'AT'], 'power' => '150 PS'],
                        ],
                    ],
                    '2010-2013' => [
                        'engine' => [
                            ['cc' => 1598, 'fuel_type' => 'Bensin', 'engine_code' => 'Z6', 'transmission' => ['MT', 'AT'], 'power' => '105 PS'],
                            ['cc' => 1999, 'fuel_type' => 'Bensin', 'engine_code' => 'LF-DE', 'transmission' => ['AT'], 'power' => '148 PS'],
                        ],
                    ],
                    '2014-2019' => [
                        'engine' => [
                            ['cc' => 1998, 'fuel_type' => 'Bensin', 'engine_code' => 'PE-VPS', 'transmission' => ['AT'], 'power' => '155 PS'],
                            ['cc' => 2488, 'fuel_type' => 'Bensin', 'engine_code' => 'PY-VPS', 'transmission' => ['AT'], 'power' => '184 PS'],
                        ],
                    ],
                    '2019-Sekarang' => [
                        'engine' => [
                            ['cc' => 1998, 'fuel_type' => 'Bensin', 'engine_code' => 'PE-VPS', 'transmission' => ['AT'], 'power' => '155 PS'],
                        ],
                    ],
                ]
            ],
        ];

        foreach ($mazda3Types as $typeData) {
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
                                'engine_code' => $engineConfig['engine_code'], // Kode mesin ditambahkan di sini
                                'segment' => 'Compact Car',
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
        $bodyType = $typeName;
        $specialFeatures = "";
        
        switch ($generation) {
            case '2003-2009':
                $specialFeatures = "Generasi pertama ini dikenal dengan desain yang sporty dan handling yang responsif.";
                break;
            case '2010-2013':
                $specialFeatures = "Dikenal dengan desain 'smiley face' yang khas dan handling yang lebih baik dari generasi sebelumnya.";
                break;
            case '2014-2019':
                $specialFeatures = "Generasi ini mengadopsi desain KODO dan teknologi Skyactiv untuk efisiensi dan performa.";
                break;
            case '2019-Sekarang':
                $specialFeatures = "Desain yang sangat premium dan interior yang lebih mewah dengan filosofi desain KODO terbaru.";
                break;
        }

        return "Mazda 3 {$bodyType} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Kendaraan kompak yang menawarkan gaya, performa, dan teknologi mutakhir. " .
               "{$specialFeatures}";
    }
}
