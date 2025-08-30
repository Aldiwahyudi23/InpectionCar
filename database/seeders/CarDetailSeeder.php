<?php

namespace Database\Seeders;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class CarDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $carDetails = [];

        // Data untuk Toyota
        $toyotaModels = [
            'Avanza' => [
                'E' => [
                    ['year' => 2022, 'cc' => 1300, 'transmission' => 'MT', 'fuel_type' => 'Bensin', 'production_period' => '2020-present'],
                    ['year' => 2022, 'cc' => 1300, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2020-present'],
                ],
                'G' => [
                    ['year' => 2022, 'cc' => 1500, 'transmission' => 'MT', 'fuel_type' => 'Bensin', 'production_period' => '2020-present'],
                    ['year' => 2022, 'cc' => 1500, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2020-present'],
                ],
                'Veloz' => [
                    ['year' => 2022, 'cc' => 1500, 'transmission' => 'MT', 'fuel_type' => 'Bensin', 'production_period' => '2020-present'],
                    ['year' => 2022, 'cc' => 1500, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2020-present'],
                ]
            ],
            'Innova' => [
                'G' => [
                    ['year' => 2022, 'cc' => 2000, 'transmission' => 'MT', 'fuel_type' => 'Bensin', 'production_period' => '2016-present'],
                    ['year' => 2022, 'cc' => 2000, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2016-present'],
                ],
                'V' => [
                    ['year' => 2022, 'cc' => 2000, 'transmission' => 'MT', 'fuel_type' => 'Bensin', 'production_period' => '2016-present'],
                    ['year' => 2022, 'cc' => 2000, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2016-present'],
                ],
                'V ' => [
                    ['year' => 2022, 'cc' => 2400, 'transmission' => 'MT', 'fuel_type' => 'Diesel', 'production_period' => '2016-present'],
                    ['year' => 2022, 'cc' => 2400, 'transmission' => 'AT', 'fuel_type' => 'Diesel', 'production_period' => '2016-present'],
                ]
            ],
            'Fortuner' => [
                'VRZ 4x2' => [
                    ['year' => 2022, 'cc' => 2400, 'transmission' => 'MT', 'fuel_type' => 'Diesel', 'production_period' => '2016-present'],
                    ['year' => 2022, 'cc' => 2400, 'transmission' => 'AT', 'fuel_type' => 'Diesel', 'production_period' => '2016-present'],
                ],
                'VRZ 4x4' => [
                    ['year' => 2022, 'cc' => 2400, 'transmission' => 'MT', 'fuel_type' => 'Diesel', 'production_period' => '2016-present'],
                    ['year' => 2022, 'cc' => 2400, 'transmission' => 'AT', 'fuel_type' => 'Diesel', 'production_period' => '2016-present'],
                ]
            ],
            'Yaris' => [
                'E' => [
                    ['year' => 2022, 'cc' => 1200, 'transmission' => 'MT', 'fuel_type' => 'Bensin', 'production_period' => '2018-present'],
                    ['year' => 2022, 'cc' => 1200, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2018-present'],
                ],
                ' G' => [
                    ['year' => 2022, 'cc' => 1200, 'transmission' => 'MT', 'fuel_type' => 'Bensin', 'production_period' => '2018-present'],
                    ['year' => 2022, 'cc' => 1200, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2018-present'],
                ],
                ' GR Sport' => [
                    ['year' => 2022, 'cc' => 1500, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2021-present'],
                ]
            ]
        ];

        // Data untuk Honda
        $hondaModels = [
            'Brio' => [
                ' E' => [
                    ['year' => 2022, 'cc' => 1200, 'transmission' => 'MT', 'fuel_type' => 'Bensin', 'production_period' => '2018-present'],
                ],
                ' E CVT' => [
                    ['year' => 2022, 'cc' => 1200, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2018-present'],
                ],
                ' RS' => [
                    ['year' => 2022, 'cc' => 1200, 'transmission' => 'MT', 'fuel_type' => 'Bensin', 'production_period' => '2018-present'],
                ],
                'RS CVT' => [
                    ['year' => 2022, 'cc' => 1200, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2018-present'],
                ]
            ],
            'Jazz' => [
                ' RS CVT' => [
                    ['year' => 2022, 'cc' => 1500, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2020-present'],
                ],
                'Crosstar CVT' => [
                    ['year' => 2022, 'cc' => 1500, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2020-present'],
                ]
            ],
            'City' => [
                'E CVT' => [
                    ['year' => 2022, 'cc' => 1500, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2020-present'],
                ],
                'RS CVT' => [
                    ['year' => 2022, 'cc' => 1500, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2020-present'],
                ],
                'Hatchback RS' => [
                    ['year' => 2022, 'cc' => 1500, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2021-present'],
                ]
            ],
            'Civic' => [
                'Turbo CVT' => [
                    ['year' => 2022, 'cc' => 1500, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2021-present'],
                ],
                'RS CVT' => [
                    ['year' => 2022, 'cc' => 1500, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2021-present'],
                ],
                'Type R' => [
                    ['year' => 2022, 'cc' => 2000, 'transmission' => 'MT', 'fuel_type' => 'Bensin', 'production_period' => '2021-present'],
                ]
            ]
        ];

        // Data untuk Suzuki
        $suzukiModels = [
            'Ertiga' => [
                'GA' => [
                    ['year' => 2022, 'cc' => 1500, 'transmission' => 'MT', 'fuel_type' => 'Bensin', 'production_period' => '2018-present'],
                    ['year' => 2022, 'cc' => 1500, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2018-present'],
                ],
                'GL' => [
                    ['year' => 2022, 'cc' => 1500, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2018-present'],
                ]
            ],
            'XL7' => [
                'Alpha' => [
                    ['year' => 2022, 'cc' => 1500, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2020-present'],
                ],
                'Beta' => [
                    ['year' => 2022, 'cc' => 1500, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2020-present'],
                ]
            ],
            'Ignis' => [
                'GL' => [
                    ['year' => 2022, 'cc' => 1200, 'transmission' => 'MT', 'fuel_type' => 'Bensin', 'production_period' => '2017-present'],
                ],
                'GL CVT' => [
                    ['year' => 2022, 'cc' => 1200, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2017-present'],
                ]
            ],
            'Baleno' => [
                'GL' => [
                    ['year' => 2022, 'cc' => 1200, 'transmission' => 'MT', 'fuel_type' => 'Bensin', 'production_period' => '2016-present'],
                ],
                'GL CVT' => [
                    ['year' => 2022, 'cc' => 1200, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2016-present'],
                ],
                'GLS CVT' => [
                    ['year' => 2022, 'cc' => 1200, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2016-present'],
                ]
            ]
        ];

        // Data untuk Daihatsu
        $daihatsuModels = [
            'Ayla' => [
                'D' => [
                    ['year' => 2022, 'cc' => 1000, 'transmission' => 'MT', 'fuel_type' => 'Bensin', 'production_period' => '2013-present'],
                    ['year' => 2022, 'cc' => 1000, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2013-present'],
                ],
                'R' => [
                    ['year' => 2022, 'cc' => 1200, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2013-present'],
                ]
            ],
            'Sigra' => [
                'D' => [
                    ['year' => 2022, 'cc' => 1200, 'transmission' => 'MT', 'fuel_type' => 'Bensin', 'production_period' => '2016-present'],
                    ['year' => 2022, 'cc' => 1200, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2016-present'],
                ],
                'X' => [
                    ['year' => 2022, 'cc' => 1200, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2016-present'],
                ]
            ],
            'Xenia' => [
                'R' => [
                    ['year' => 2022, 'cc' => 1300, 'transmission' => 'MT', 'fuel_type' => 'Bensin', 'production_period' => '2017-present'],
                    ['year' => 2022, 'cc' => 1300, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2017-present'],
                ],
                'R Deluxe' => [
                    ['year' => 2022, 'cc' => 1300, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2017-present'],
                ]
            ],
            'Terios' => [
                'R' => [
                    ['year' => 2022, 'cc' => 1500, 'transmission' => 'MT', 'fuel_type' => 'Bensin', 'production_period' => '2017-present'],
                    ['year' => 2022, 'cc' => 1500, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2017-present'],
                ],
                'R Luxury' => [
                    ['year' => 2022, 'cc' => 1500, 'transmission' => 'AT', 'fuel_type' => 'Bensin', 'production_period' => '2017-present'],
                ]
            ]
        ];

        // Gabungkan semua data
        $allModelsData = [
            'Toyota' => $toyotaModels,
            'Honda' => $hondaModels,
            'Suzuki' => $suzukiModels,
            'Daihatsu' => $daihatsuModels,
        ];

        // Generate car details
        foreach ($allModelsData as $brandName => $models) {
            $brand = Brand::where('name', $brandName)->first();
            
            if (!$brand) continue;
            
            foreach ($models as $modelName => $types) {
                $carModel = CarModel::where('brand_id', $brand->id)
                                    ->where('name', $modelName)
                                    ->first();
                
                if (!$carModel) continue;
                
                foreach ($types as $typeName => $variants) {
                    $carType = CarType::where('car_model_id', $carModel->id)
                                      ->where('name', $typeName)
                                      ->first();
                    
                    if (!$carType) continue;
                    
                    foreach ($variants as $variant) {
                        $carDetails[] = [
                            'brand_id' => $brand->id,
                            'car_model_id' => $carModel->id,
                            'car_type_id' => $carType->id,
                            'year' => $variant['year'],
                            'cc' => $variant['cc'],
                            'transmission' => $variant['transmission'],
                            'fuel_type' => $variant['fuel_type'],
                            'production_period' => $variant['production_period'],
                            'description' => $this->generateDescription($brandName, $modelName, $typeName, $variant),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
        }

        // Insert data ke database
        DB::table('car_details')->insert($carDetails);
        
        $this->command->info('Car details seeded successfully!');
    }

    /**
     * Generate description based on car details
     */
    private function generateDescription($brand, $model, $type, $variant)
    {
        $transmission = $variant['transmission'] == 'MT' ? 'manual' : 'otomatis';
        $fuelType = $variant['fuel_type'] == 'Bensin' ? 'bensin' : 'diesel';
        
        return "{$brand} {$model} tipe {$type} tahun {$variant['year']} dengan mesin {$variant['cc']}cc, transmisi {$transmission}, bahan bakar {$fuelType}. {$this->getAdditionalFeatures($brand, $model, $type)}";
    }

    /**
     * Get additional features based on brand and model
     */
    private function getAdditionalFeatures($brand, $model, $type)
    {
        $features = [
            'Toyota' => [
                'Avanza' => 'Dilengkapi dengan fitur keselamatan lengkap dan kabin yang nyaman untuk keluarga.',
                'Innova' => 'Mobil keluarga dengan performa tangguh dan interior yang luas.',
                'Fortuner' => 'SUV tangguh dengan kemampuan off-road dan desain yang gagah.',
                'Yaris' => 'City car yang stylish dengan fitur teknologi terkini.',
            ],
            'Honda' => [
                'Brio' => 'City car compact dengan desain modern dan efisiensi bahan bakar yang baik.',
                'Jazz' => 'Hatchback versatile dengan Magic Seat dan desain yang funky.',
                'City' => 'Sedan compact dengan performa responsif dan fitur canggih.',
                'Civic' => 'Sedan sporty dengan teknologi canggih dan performa tinggi.',
            ],
            'Suzuki' => [
                'Ertiga' => 'MPV praktis dengan efisiensi bahan bakar terbaik di kelasnya.',
                'XL7' => 'SUV dengan 7 tempat duduk dan ground clearance yang tinggi.',
                'Ignis' => 'Compact crossover dengan desain unik dan fitur komprehensif.',
                'Baleno' => 'Hatchback premium dengan kabin yang luas dan fitur lengkap.',
            ],
            'Daihatsu' => [
                'Ayla' => 'City car ekonomis dengan harga terjangkau dan irit bahan bakar.',
                'Sigra' => 'MPV compact dengan interior yang luas dan praktis.',
                'Xenia' => 'MPV keluarga dengan varian mesin yang irit dan tangguh.',
                'Terios' => 'SUV compact dengan desain sporty dan kemampuan off-road.',
            ],
        ];

        return $features[$brand][$model] ?? 'Mobil dengan performa andal dan kenyamanan berkendara yang optimal.';
    }
}