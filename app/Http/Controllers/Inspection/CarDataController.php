<?php

namespace App\Http\Controllers\Inspection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\DataCar\CarDetail;
use App\Models\DataCar\Brand;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;

class CarDataController extends Controller
{
    public function getBrands()
    {
        $brands = CarDetail::with('brand')
            ->select('brand_id')
            ->distinct()
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->brand_id,
                    'name' => $item->brand->name
                ];
            });

        return response()->json($brands);
    }

    public function getModels($brandId)
    {
        $models = CarDetail::with('model')
            ->where('brand_id', $brandId)
            ->select('car_model_id')
            ->distinct()
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->car_model_id,
                    'name' => $item->model->name
                ];
            });

        return response()->json($models);
    }

    public function getTypes($modelId)
    {
        $types = CarDetail::with('type')
            ->where('car_model_id', $modelId)
            ->select('car_type_id')
            ->distinct()
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->car_type_id,
                    'name' => $item->type->name
                ];
            });

        return response()->json($types);
    }

    public function getCapacities($typeId)
    {
        $capacities = CarDetail::where('car_type_id', $typeId)
            ->select('cc')
            ->distinct()
            ->orderBy('cc')
            ->get()
            ->map(function($item) {
                return [
                    'cc' => $item->cc,
                    'display' => round($item->cc / 1000, 1)
                ];
            })
        // filter unik berdasarkan 'display'
        ->unique('display')
        // reset index biar rapi
        ->values();

        return response()->json($capacities);
    }

    public function getYears($typeId, $capacity)
    {
        $years = CarDetail::where('car_type_id', $typeId)
            ->where('cc', $capacity)
            ->select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->get();

        return response()->json($years);
    }

    public function getTransmissions($typeId, $capacity, $year)
    {
        $transmissions = CarDetail::where('car_type_id', $typeId)
            ->where('cc', $capacity)
            ->where('year', $year)
            ->select('transmission')
            ->distinct()
            ->get();

        return response()->json($transmissions);
    }

    public function getFuels($typeId, $capacity, $year, $transmission)
    {
        $fuels = CarDetail::where('car_type_id', $typeId)
            ->where('cc', $capacity)
            ->where('year', $year)
            ->where('transmission', $transmission)
            ->select('fuel_type')
            ->distinct()
            ->get();

        return response()->json($fuels);
    }

    public function getPeriods($typeId, $capacity, $year, $transmission, $fuel)
    {
        $periods = CarDetail::where('car_type_id', $typeId)
            ->where('cc', $capacity)
            ->where('year', $year)
            ->where('transmission', $transmission)
            ->where('fuel_type', $fuel)
            ->select('production_period')
            ->distinct()
            ->get();

        return response()->json($periods);
    }

    public function getCarDetail($typeId, $capacity, $year, $transmission, $fuel, $period)
    {
        $carDetail = CarDetail::with(['brand', 'model', 'type'])
            ->where('car_type_id', $typeId)
            ->where('cc', $capacity)
            ->where('year', $year)
            ->where('transmission', $transmission)
            ->where('fuel_type', $fuel)
            ->where('production_period', $period)
            ->first();

        return response()->json($carDetail);
    }
}
