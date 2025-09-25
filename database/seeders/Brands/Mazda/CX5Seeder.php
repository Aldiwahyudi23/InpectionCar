<?php

namespace Database\Seeders\Brands\Mazda;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class CX5Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Mazda']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'CX-5'
        ]);

        $cx5Types = [
            ['name' => 'Touring'],
            ['name' => 'Grand Touring'],
            ['name' => 'Elite'],
            ['name' => 'Kuro Edition'],
            ['name' => 'Signature'],
        ];

        $typeConfigurations = [
            'Touring' => [
                'generations' => [
                    '2017-Sekarang' => [
                        'engine' => [['cc' => 2488, 'fuel_type' => 'Bensin', 'engine_code' => 'PY-VPS', 'transmission' => ['AT'], 'power' => '190 PS']],
                    ],
                ]
            ],
            'Grand Touring' => [
                'generations' => [
                    '2017-Sekarang' => [
                        'engine' => [['cc' => 2488, 'fuel_type' => 'Bensin', 'engine_code' => 'PY-VPS', 'transmission' => ['AT'], 'power' => '190 PS']],
                    ],
                ]
            ],
            'Elite' => [
                'generations' => [
                    '2017-Sekarang' => [
                        'engine' => [['cc' => 2488, 'fuel_type' => 'Bensin', 'engine_code' => 'PY-VPS', 'transmission' => ['AT'], 'power' => '190 PS']],
                    ],
                ]
            ],
            'Kuro Edition' => [
                'generations' => [
                    '2021-Sekarang' => [
                        'engine' => [['cc' => 2488, 'fuel_type' => 'Bensin', 'engine_code' => 'PY-VPS', 'transmission' => ['AT'], 'power' => '190 PS']],
                    ],
                ]
            ],
            'Signature' => [
                'generations' => [
                    '2017-Sekarang' => [
                        'engine' => [['cc' => 2488, 'fuel_type' => 'Bensin', 'engine_code' => 'PY-VPS', 'transmission' => ['AT'], 'power' => '190 PS']],
                    ],
                ]
            ],
        ];

        foreach ($cx5Types as $typeData) {
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
        $bodyType = "Compact SUV";
        $specialFeatures = " Dikenal dengan desain KODO yang elegan, interior mewah, dan performa berkendara yang tangguh.";

        if ($typeName === 'Signature') {
            $specialFeatures = " Varian teratas yang menawarkan fitur premium, material interior berkualitas tinggi, dan kenyamanan ekstra.";
        } elseif ($typeName === 'Kuro Edition') {
            $specialFeatures = " Edisi spesial yang menampilkan aksen eksterior dan interior berwarna hitam glossy untuk tampilan yang lebih sporty dan modern.";
        }

        return "Mazda CX-5 {$typeName} {$ccText} {$fuelText} tahun {$year}. " .
               "Generasi {$generation} dengan tenaga {$power}. " .
               "Kendaraan {$bodyType} yang sangat populer di kelasnya.{$specialFeatures}";
    }
}
