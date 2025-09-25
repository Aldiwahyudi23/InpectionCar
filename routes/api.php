<?php

use App\Http\Controllers\DataCar\CarController;
use App\Http\Controllers\Inspection\CarDataController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/cars/{id}/images', [CarController::class, 'images']);

// ===========================Kebutuhan untuk menambahkan data mobil baru=========================
// File: routes/api.php

Route::get('/cars/brands', [CarController::class, 'getBrands']);
Route::get('/cars/models', [CarController::class, 'getModels']);
Route::get('/cars/types', [CarController::class, 'getTypes']);

// API Routes untuk car management
Route::post('/brands/check-duplicate', [CarController::class, 'checkBrandDuplicate']);
Route::post('/models/check-duplicate', [CarController::class, 'checkModelDuplicate']);
Route::post('/types/check-duplicate', [CarController::class, 'checkTypeDuplicate']);

Route::post('/cars/store-brand', [CarController::class, 'storeBrand']);
Route::post('/cars/store-model', [CarController::class, 'storeModel']);
Route::post('/cars/store-type', [CarController::class, 'storeType']);
Route::post('/cars/store-car-detail', [CarController::class, 'storeCarDetail']);

Route::get('/api/cars/{carId}/images', [CarController::class, 'getCarImages']);


// =================Kebutuhan Create Inspection=============================

Route::get('/car-brands', [CarDataController::class, 'getBrands']);
Route::get('/car-models/{brandId}', [CarDataController::class, 'getModels']);
Route::get('/car-types/{modelId}', [CarDataController::class, 'getTypes']);
Route::get('/car-capacities/{typeId}', [CarDataController::class, 'getCapacities']);
Route::get('/car-years/{typeId}/{capacity}', [CarDataController::class, 'getYears']);
Route::get('/car-transmissions/{typeId}/{capacity}/{year}', [CarDataController::class, 'getTransmissions']);
Route::get('/car-fuels/{typeId}/{capacity}/{year}/{transmission}', [CarDataController::class, 'getFuels']);
Route::get('/car-periods/{typeId}/{capacity}/{year}/{transmission}/{fuel}', [CarDataController::class, 'getPeriods']);
Route::get('/car-details/{typeId}/{capacity}/{year}/{transmission}/{fuel}/{period}', [CarDataController::class, 'getCarDetail']);