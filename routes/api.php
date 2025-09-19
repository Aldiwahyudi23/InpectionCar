<?php

use App\Http\Controllers\DataCar\CarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/cars/{id}/images', [CarController::class, 'images']);


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

