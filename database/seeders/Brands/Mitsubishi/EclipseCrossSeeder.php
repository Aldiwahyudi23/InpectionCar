<?php

namespace Database\Seeders\Brands\Mitsubishi;

use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use App\Models\DataCar\CarDetail;
use Illuminate\Database\Seeder;

class EclipseCrossSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(['name' => 'Mitsubishi']);

        $model = CarModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Eclipse Cross'
        ]);

        $types = [
            ['name' => 'GLX'],
            ['name' => 'Exceed'],
            ['name' => 'Ultimate'],
        ];

        $configurations = [
            'GLX' => [
                'generations' => [
                    '2018-Now (Gen 1)' => [
                        'engine' => [
                            ['cc'=>1499,'fuel_type'=>'Bensin','engine_code'=>'4A91 MIVEC','transmission'=>['MT','AT'],'power'=>'150 HP','torque'=>'250 Nm']
                        ]
                    ]
                ]
            ],
            'Exceed' => [
                'generations' => [
                    '2018-Now (Gen 1)' => [
                        'engine' => [
                            ['cc'=>1499,'fuel_type'=>'Bensin','engine_code'=>'4A91 MIVEC','transmission'=>['AT'],'power'=>'150 HP','torque'=>'250 Nm']
                        ]
                    ]
                ]
            ],
            'Ultimate' => [
                'generations' => [
                    '2018-Now (Gen 1)' => [
                        'engine' => [
                            ['cc'=>1499,'fuel_type'=>'Bensin','engine_code'=>'4A91 MIVEC','transmission'=>['AT'],'power'=>'150 HP','torque'=>'250 Nm']
                        ]
                    ]
                ]
            ],
        ];

        foreach ($types as $typeData) {
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
        if(str_contains($period,'2018-Now')) return range(2018,$currentYear);
        return [];
    }

    private function generateDescription($typeName,$year,$cc,$engineCode,$transmission,$fuelType,$power,$torque,$generation): string
    {
        $transText = $transmission==='AT'?'matic':'manual';
        $ccText = round($cc/1000,1).'L';
        return "Mitsubishi Eclipse Cross {$typeName} {$ccText} {$transText} {$fuelType} ({$power}, {$torque} Nm) tahun {$year}. Generasi {$generation}, SUV kompak stylish dan nyaman.";
    }
}
