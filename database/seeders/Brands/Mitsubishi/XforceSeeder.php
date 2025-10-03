<?php

namespace Database\Seeders\Brands\Mitsubishi;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class XforceSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name'=>'Mitsubishi']);

        $model = CarModel::firstOrCreate([
            'brand_id'=>$brand->id,
            'name'=>'Xforce'
        ]);

        $types = [
            ['name'=>'Exceed'],
            ['name'=>'Ultimate']
        ];

        $configurations = [
            'Exceed'=>[
                'generations'=>[
                    '2023-Now (Gen 1)'=>[
                        'engine'=>[
                            ['cc'=>2477,'fuel_type'=>'Diesel','engine_code'=>'4N15','transmission'=>['AT'],'power'=>'181 HP','torque'=>'430 Nm']
                        ]
                    ]
                ]
            ],
            'Ultimate'=>[
                'generations'=>[
                    '2023-Now (Gen 1)'=>[
                        'engine'=>[
                            ['cc'=>2477,'fuel_type'=>'Diesel','engine_code'=>'4N15','transmission'=>['AT'],'power'=>'181 HP','torque'=>'430 Nm']
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
                                'segment'=>'Pickup',
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
        if(str_contains($period,'2023-Now')) return range(2023,$currentYear);
        return [];
    }

    private function generateDescription($typeName,$year,$cc,$engineCode,$transmission,$fuelType,$power,$torque,$generation): string
    {
        $transText = $transmission==='AT'?'matic':'manual';
        $ccText = round($cc/1000,1).'L';
        return "Mitsubishi Outlander {$typeName} {$ccText} {$transText} {$engineCode} {$fuelType} ({$power}, {$torque} Nm) tahun {$year}. Generasi {$generation}, SUV premium keluarga dengan kenyamanan maksimal.";
    }
}