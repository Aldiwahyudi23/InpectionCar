<?php

use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Inspection\InspectionController;
use App\Http\Controllers\Menu\Job\JobController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

# Buat symbolic link manual
// ln -s /home/u516139464/domains/cekmobil.online/public_html/storage/app/public /home/u516139464/domains/cekmobil.online/public_html/public/storage

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

// Panggil dengan parameter role yang diinginkan
// ->middleware(['auth', CheckSpatieRole::class . ':admin']);
// ->middleware(['auth', CheckSpatieRole::class . ':inspektor']);

Route::get('/login-otp', [OtpController::class, 'showLoginForm'])->name('login-otp');
Route::post('/check-phone', [OtpController::class, 'checkPhone'])->name('check-phone');
Route::post('/verify-otp', [OtpController::class, 'verifyOtp'])->name('verify-otp');
Route::post('/resend-otp', [OtpController::class, 'resendOtp'])->name('resend-otp');


Route::get('/', function () {
    return redirect()->route('login'); // Redirect langsung ke login
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Menu Tugas
    Route::get('/job', [JobController::class, 'index'])->name('job.index');
    Route::get('/job/history', [JobController::class, 'history'])->name('inspections.history');

    // Inspections Routes
    Route::get('/inspections/{inspection}/start', [InspectionController::class, 'start'])
        ->name('inspections.start');

    Route::get('/inspections/create/new', [InspectionController::class, 'create'])
            ->name('inspections.create.new');
    Route::post('/inspections', [InspectionController::class, 'store'])->name('inspections.store');
    Route::post('/inspections/{inspection}/cancel', [InspectionController::class, 'cancel'])
    ->name('inspections.cancel');


     // Store inspection results
    Route::post('/inspections/{inspection}/store-results', [InspectionController::class, 'storeResults'])
            ->name('inspections.store-results');

    // routes/web.php
    Route::post('/inspections/save-result', [InspectionController::class, 'saveResult'])->name('inspections.save-result');
    Route::post('/inspections/delete-result-image', [InspectionController::class, 'deleteResultImage'])->name('inspections.delete-result');
    // Di web.php
    Route::post('/inspections/{inspection}/vehicle-details', [InspectionController::class, 'updateVehicleDetails'])
        ->name('inspections.updateVehicleDetails');

    Route::post('/inspections/{inspection}/conclusion', [InspectionController::class, 'updateConclusion'])
        ->name('inspections.updateConclusion');
        
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
});

