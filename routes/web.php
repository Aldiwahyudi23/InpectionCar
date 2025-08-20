<?php

use App\Http\Controllers\Inspection\InspectionController;
use App\Http\Controllers\Menu\Job\JobController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

Route::get('/job', [JobController::class, 'index'])->name('job.index');
 Route::get('/job/history', [JobController::class, 'history'])->name('inspections.history');

  Route::get('/inspections/{inspection}/create', [InspectionController::class, 'create'])
        ->name('inspections.create');
        
    // Store inspection results
    Route::post('/inspections/{inspection}/store-results', [InspectionController::class, 'storeResults'])
        ->name('inspections.store-results');

// routes/web.php
Route::post('/inspections/save-result', [InspectionController::class, 'saveResult'])->name('inspections.save-result');
Route::post('/inspections/upload-image', [InspectionController::class, 'uploadImage'])->name('inspections.upload-image');
Route::delete('/inspections/delete-image', [InspectionController::class, 'deleteImage'])->name('inspections.delete-image');
// Final submit
Route::post('/inspections/{id}/final-submit', [InspectionController::class, 'finalSubmit'])
    ->name('inspections.final-submit');

// Review page
Route::get('/inspections/{id}/review', [InspectionController::class, 'review'])
    ->name('inspections.review');

Route::get('/inspections/{id}/review-pdf', [InspectionController::class, 'reviewPdf'])
    ->name('inspections.review.pdf');

    // Generate PDF
Route::get('/inspections/{id}/download-pdf', [InspectionController::class, 'downloadPdf'])
    ->name('inspections.download.pdf');
