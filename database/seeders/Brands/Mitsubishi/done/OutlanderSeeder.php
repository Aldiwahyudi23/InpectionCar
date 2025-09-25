<?php

namespace Database\Seeders\Brands\Mitsubishi;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class OutlanderSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name'=>'Mitsubishi']);

        $model = CarModel::firstOrCreate([
            'brand_id'=>$brand->id,
            'name'=>'Outlander'
        ]);

        $types = [
            ['name'=>'GLS'],
            ['name'=>'Exceed'],
            ['name'=>'Ultimate']
        ];

        $configurations = [
            'GLS'=>[
                'generations'=>[
                    '2015-Now (Gen 3)'=>[
                        'engine'=>[
                            ['cc'=>2360,'fuel_type'=>'Bensin','engine_code'=>'4B11 MIVEC','transmission'=>['AT'],'power'=>'165 HP','torque'=>'222 Nm']
                        ]
                    ]
                ]
            ],
            'Exceed'=>[
                'generations'=>[
                    '2015-Now (Gen 3)'=>[
                        'engine'=>[
                            ['cc'=>2360,'fuel_type'=>'Bensin','engine_code'=>'4B11 MIVEC','transmission'=>['AT'],'power'=>'165 HP','torque'=>'222 Nm']
                        ]
                    ]
                ]
            ],
            'Ultimate'=>[
                'generations'=>[
                    '2015-Now (Gen 3)'=>[
                        'engine'=>[
                            ['cc'=>2360,'fuel_type'=>'Bensin','engine_code'=>'4B11 MIVEC','transmission'=>['AT'],'power'=>'165 HP','torque'=>'222 Nm']
                        ]
                    ]
                ]
            ]
        ];

        foreach($types as $typeData){
            $type = CarType::firstOrCreate(['name'=>$typeData['name'],'car_model_id'=>$model->id]);
            $this->generateTypeVariants($brand->id,$model->id,$type->id,$typeData['name'],$configurations);
        }
    }

    private function generateTypeVariants($brandId,$modelId,$typeId,$typeName,$configurations): void
    {
        $currentYear = date('Y');
        foreach($configurations[$typeName]['generations'] as $period=>$genConfig){
            $years = $this->getYearsForGeneration($period,$currentYear);
            foreach($years as $year){
                foreach($genConfig['engine'] as $engineConfig){
                    foreach($engineConfig['transmission'] as $transmission){
                        CarDetail::firstOrCreate(
                            [
                                'brand_id'=>$brandId,
                                'car_model_id'=>$modelId,
                                'car_type_id'=>$typeId,
                                'year'=>$year,
                                'cc'=>$engineConfig['cc'],
                                'transmission'=>$transmission,
                                'fuel_type'=>$engineConfig['fuel_type'],
                                'engine_code'=>$engineConfig['engine_code'],
                                'segment'=>'SUV',
                                'production_period'=>$period
                            ],
                            ['description'=>$this->generateDescription($typeName,$year,$engineConfig['cc'],$engineConfig['engine_code'],$transmission,$engineConfig['fuel_type'],$engineConfig['power'],$engineConfig['torque'],$period)]
                        );
                    }
                }
            }
        }
    }

    private function getYearsForGeneration($period,$currentYear): array
    {
        if(str_contains($period,'2015-Now')) return range(2015,$currentYear);
        return [];
    }

    private function generateDescription($typeName,$year,$cc,$engineCode,$transmission,$fuelType,$power,$torque,$generation): string
    {
        $transText = $transmission==='AT'?'matic':'manual';
        $ccText = round($cc/1000,1).'L';
        return "Mitsubishi Outlander {$typeName} {$ccText} {$transText} {$engineCode}  {$fuelType} ({$power}, {$torque} Nm) tahun {$year}. Generasi {$generation}, SUV premium keluarga dengan kenyamanan maksimal.";
    }
}
