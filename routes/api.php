<?php

use App\Http\Controllers\DataCar\CarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/cars/{id}/images', [CarController::class, 'images']);