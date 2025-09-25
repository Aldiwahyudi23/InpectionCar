<?php

namespace Database\Seeders\Brands\Chevrolet;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class CaptivaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Chevrolet']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Captiva'
        ]);

        $captivaTypes = [
            ['name' => 'SUV']
        ];

        $typeConfigurations = [
            'SUV' => [
                'generations' => [
                    '2007-2011' => [
                        'engine' => [
                            ['cc' => 2405, 'fuel_type' => 'Bensin', 'engine_code' => 'Z24SED', 'transmission' => ['AT'], 'power' => '135 PS'],
                            ['cc' => 1991, 'fuel_type' => 'Diesel', 'engine_code' => 'Z20S', 'transmission' => ['AT'], 'power' => '150 PS'],
                        ],
                    ],
                    '2011-2016' => [
                        'engine' => [
                            ['cc' => 2384, 'fuel_type' => 'Bensin', 'engine_code' => 'LE5', 'transmission' => ['AT'], 'power' => '171 PS'],
                            ['cc' => 1998, 'fuel_type' => 'Diesel', 'engine_code' => 'Z20D1', 'transmission' => ['AT'], 'power' => '163 PS'],
                        ],
                    ],
                    '2016-2019' => [
                        'engine' => [
                            ['cc' => 1998, 'fuel_type' => 'Diesel', 'engine_code' => 'Z20D1', 'transmission' => ['AT'], 'power' => '163 PS'],
                        ],
                    ],
                ]
            ],
        ];

        foreach ($captivaTypes as $typeData) {
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
            case '2007-2011':
                $specialFeatures = "Dikenal sebagai SUV 7-seater pertama Chevrolet di Indonesia.";
                break;
            case '2011-2016':
                $specialFeatures = "Model facelift dengan pilihan mesin bensin dan diesel yang lebih bertenaga.";
                break;
            case '2016-2019':
                $specialFeatures = "Facelift terakhir dengan fitur canggih seperti MyLink infotainment.";
                break;
        }

        return "Chevrolet Captiva {$bodyType} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "SUV tangguh dengan desain Amerika yang khas. " .
               "{$specialFeatures}";
    }
}
