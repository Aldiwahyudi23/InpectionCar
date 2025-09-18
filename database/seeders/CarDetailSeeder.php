<?php

namespace Database\Seeders;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        $currentYear = date('Y');

        // Data untuk Toyota
        $toyotaModels = [
            'Avanza' => [
                'E' => [
                    ['cc' => 1300, 'fuel_type' => 'Bensin', 'production_period' => '2006-present'],
                ],
                'G' => [
                    ['cc' => 1500, 'fuel_type' => 'Bensin', 'production_period' => '2006-present'],
                ],
                'Veloz' => [
                    ['cc' => 1500, 'fuel_type' => 'Bensin', 'production_period' => '2011-present'],
                ]
            ],
            'Innova' => [
                'G' => [
                    ['cc' => 2000, 'fuel_type' => 'Bensin', 'production_period' => '2004-present'],
                    ['cc' => 2400, 'fuel_type' => 'Diesel', 'production_period' => '2004-present'],
                ],
                'V' => [
                    ['cc' => 2000, 'fuel_type' => 'Bensin', 'production_period' => '2004-present'],
                    ['cc' => 2400, 'fuel_type' => 'Diesel', 'production_period' => '2004-present'],
                ]
            ],
            'Fortuner' => [
                'VRZ 4x2' => [
                    ['cc' => 2400, 'fuel_type' => 'Diesel', 'production_period' => '2005-present'],
                    ['cc' => 2700, 'fuel_type' => 'Bensin', 'production_period' => '2005-present'],
                ],
                'VRZ 4x4' => [
                    ['cc' => 2400, 'fuel_type' => 'Diesel', 'production_period' => '2005-present'],
                    ['cc' => 2700, 'fuel_type' => 'Bensin', 'production_period' => '2005-present'],
                ]
            ],
            'Yaris' => [
                'E' => [
                    ['cc' => 1200, 'fuel_type' => 'Bensin', 'production_period' => '2005-present'],
                    ['cc' => 1500, 'fuel_type' => 'Bensin', 'production_period' => '2005-present'],
                ],
                'G' => [
                    ['cc' => 1200, 'fuel_type' => 'Bensin', 'production_period' => '2005-present'],
                    ['cc' => 1500, 'fuel_type' => 'Bensin', 'production_period' => '2005-present'],
                ],
                'GR Sport' => [
                    ['cc' => 1500, 'fuel_type' => 'Bensin', 'production_period' => '2021-present'],
                ]
            ]
        ];

        // Data untuk Honda
        $hondaModels = [
            'Brio' => [
                'E' => [
                    ['cc' => 1200, 'fuel_type' => 'Bensin', 'production_period' => '2011-present'],
                ],
                'RS' => [
                    ['cc' => 1200, 'fuel_type' => 'Bensin', 'production_period' => '2011-present'],
                ]
            ],
            'Jazz' => [
                'RS CVT' => [
                    ['cc' => 1500, 'fuel_type' => 'Bensin', 'production_period' => '2002-present'],
                ],
                'Crosstar CVT' => [
                    ['cc' => 1500, 'fuel_type' => 'Bensin', 'production_period' => '2020-present'],
                ]
            ],
            'City' => [
                'E CVT' => [
                    ['cc' => 1500, 'fuel_type' => 'Bensin', 'production_period' => '1996-present'],
                ],
                'RS CVT' => [
                    ['cc' => 1500, 'fuel_type' => 'Bensin', 'production_period' => '1996-present'],
                ],
                'Hatchback RS' => [
                    ['cc' => 1500, 'fuel_type' => 'Bensin', 'production_period' => '2021-present'],
                ]
            ],
            'Civic' => [
                'Turbo CVT' => [
                    ['cc' => 1500, 'fuel_type' => 'Bensin', 'production_period' => '1972-present'],
                    ['cc' => 1800, 'fuel_type' => 'Bensin', 'production_period' => '1972-present'],
                ],
                'RS CVT' => [
                    ['cc' => 1500, 'fuel_type' => 'Bensin', 'production_period' => '1972-present'],
                    ['cc' => 1800, 'fuel_type' => 'Bensin', 'production_period' => '1972-present'],
                ],
                'Type R' => [
                    ['cc' => 2000, 'fuel_type' => 'Bensin', 'production_period' => '1997-present'],
                ]
            ]
        ];

        // Data untuk Suzuki
        $suzukiModels = [
            'Ertiga' => [
                'GA' => [
                    ['cc' => 1500, 'fuel_type' => 'Bensin', 'production_period' => '2012-present'],
                ],
                'GL' => [
                    ['cc' => 1500, 'fuel_type' => 'Bensin', 'production_period' => '2012-present'],
                ]
            ],
            'XL7' => [
                'Alpha' => [
                    ['cc' => 1500, 'fuel_type' => 'Bensin', 'production_period' => '2019-present'],
                ],
                'Beta' => [
                    ['cc' => 1500, 'fuel_type' => 'Bensin', 'production_period' => '2019-present'],
                ]
            ],
            'Ignis' => [
                'GL' => [
                    ['cc' => 1200, 'fuel_type' => 'Bensin', 'production_period' => '2000-present'],
                ],
                'GL CVT' => [
                    ['cc' => 1200, 'fuel_type' => 'Bensin', 'production_period' => '2000-present'],
                ]
            ],
            'Baleno' => [
                'GL' => [
                    ['cc' => 1200, 'fuel_type' => 'Bensin', 'production_period' => '1995-present'],
                ],
                'GL CVT' => [
                    ['cc' => 1200, 'fuel_type' => 'Bensin', 'production_period' => '1995-present'],
                ],
                'GLS CVT' => [
                    ['cc' => 1200, 'fuel_type' => 'Bensin', 'production_period' => '1995-present'],
                ]
            ]
        ];

        // Data untuk Daihatsu
        $daihatsuModels = [
            'Ayla' => [
                'D' => [
                    ['cc' => 1000, 'fuel_type' => 'Bensin', 'production_period' => '2013-present'],
                    ['cc' => 1200, 'fuel_type' => 'Bensin', 'production_period' => '2013-present'],
                ],
                'R' => [
                    ['cc' => 1200, 'fuel_type' => 'Bensin', 'production_period' => '2013-present'],
                ]
            ],
            'Sigra' => [
                'D' => [
                    ['cc' => 1200, 'fuel_type' => 'Bensin', 'production_period' => '2016-present'],
                ],
                'X' => [
                    ['cc' => 1200, 'fuel_type' => 'Bensin', 'production_period' => '2016-present'],
                ]
            ],
            'Xenia' => [
                'R' => [
                    ['cc' => 1300, 'fuel_type' => 'Bensin', 'production_period' => '2004-present'],
                    ['cc' => 1500, 'fuel_type' => 'Bensin', 'production_period' => '2004-present'],
                ],
                'R Deluxe' => [
                    ['cc' => 1300, 'fuel_type' => 'Bensin', 'production_period' => '2004-present'],
                    ['cc' => 1500, 'fuel_type' => 'Bensin', 'production_period' => '2004-present'],
                ]
            ],
            'Terios' => [
                'R' => [
                    ['cc' => 1500, 'fuel_type' => 'Bensin', 'production_period' => '1997-present'],
                ],
                'R Luxury' => [
                    ['cc' => 1500, 'fuel_type' => 'Bensin', 'production_period' => '1997-present'],
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
                        // Parse production period
                        $period = $this->parseProductionPeriod($variant['production_period']);
                        $startYear = $period['start_year'];
                        $endYear = $period['end_year'];
                        
                        // Generate data untuk setiap tahun dari startYear hingga endYear
                        for ($year = $startYear; $year <= $endYear; $year++) {
                            // Skip tahun sebelum 2010
                            if ($year < 2010) continue;
                            
                            // Generate untuk kedua tipe transmisi (kecuali ada pengecualian)
                            $transmissions = ['MT', 'AT'];
                            
                            foreach ($transmissions as $transmission) {
                                // Skip kombinasi yang tidak mungkin (misal Type R dengan AT)
                                if ($modelName === 'Civic' && $typeName === 'Type R' && $transmission === 'AT') {
                                    continue;
                                }
                                
                                $carDetails[] = [
                                    'brand_id' => $brand->id,
                                    'car_model_id' => $carModel->id,
                                    'car_type_id' => $carType->id,
                                    'year' => $year,
                                    'cc' => $variant['cc'],
                                    'transmission' => $transmission,
                                    'fuel_type' => $variant['fuel_type'],
                                    'production_period' => $variant['production_period'],
                                    'description' => $this->generateDescription($brandName, $modelName, $typeName, $variant, $year, $transmission),
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }
                    }
                }
            }
        }

        // Insert data ke database
        DB::table('car_details')->insert($carDetails);
        
        $this->command->info('Car details seeded successfully!');
        $this->command->info('Total car details generated: ' . count($carDetails));
    }

    /**
     * Parse production period string to get start and end year
     */
    private function parseProductionPeriod($period)
    {
        $parts = explode('-', $period);
        $startYear = (int)$parts[0];
        $endYear = trim($parts[1]) === 'present' ? (int)date('Y') : (int)$parts[1];
        
        return [
            'start_year' => $startYear,
            'end_year' => $endYear
        ];
    }

    /**
     * Generate description based on car details
     */
    private function generateDescription($brand, $model, $type, $variant, $year, $transmission)
    {
        $transmissionText = $transmission == 'MT' ? 'manual' : 'otomatis';
        $fuelType = $variant['fuel_type'] == 'Bensin' ? 'bensin' : 'diesel';
        
        return "{$brand} {$model} tipe {$type} tahun {$year} dengan mesin {$variant['cc']}cc, transmisi {$transmissionText}, bahan bakar {$fuelType}. {$this->getAdditionalFeatures($brand, $model, $type)}";
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