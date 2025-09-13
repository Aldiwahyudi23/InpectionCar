<?php

use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\DataCar\CarController;
use App\Http\Controllers\Inspection\InspectionController;
use App\Http\Controllers\Menu\Home\CoordinatorController;
use App\Http\Controllers\Menu\Home\HomeController;
use App\Http\Controllers\Menu\Job\JobController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Spatie\Browsershot\Browsershot;

# Buat symbolic link manual
// ln -s /home/u516139464/domains/cekmobil.online/public_html/storage/app/public /home/u516139464/domains/cekmobil.online/public_html/public/storage
// ln -s /home/u516139464/domains/keluargamahaya.com/public_html/cekmobil/storage/app/public /home/u516139464/domains/keluargamahaya.com/public_html/cekmobil/public/storage

//mkdir -p storage/app/public/reports


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

// Error routes
Route::get('/error/403', function () {
    return inertia('Error/403');
})->name('error.403');

Route::get('/region-inactive', function () {
    return inertia('Inactive');
})->name('region.inactive');

// Route untuk halaman akun tidak aktif
Route::get('/account/inactive', function () {
    return inertia('Auth/AccountInactive');
})->name('account.inactive');

// Logout force untuk user inactive
Route::post('/account/inactive/logout', function () {
    Auth::guard('web')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('account.inactive.logout');

Route::get('/', function () {
    return redirect()->route('login'); // Redirect langsung ke login
});

// Route::middleware([
//     'auth:sanctum',  
//     config('jetstream.auth_session'),
//     'verified',
//     'role_spatie:Admin|inspector|coordinator',
//     'region.active',
//     'user.active',
// ])->group(function () {
//     Route::get('/dashboard', [HomeController::class,'index'])->name('dashboard');

//     Route::get('/welcome', function () {
//         return Inertia::render('Welcome');
//     })->name('welcome');

//     // Menu Tugas
//     Route::get('/job', [JobController::class, 'index'])->name('job.index');
//     Route::get('/job/history', [JobController::class, 'history'])->name('inspections.history');

//     //============================================ Inspections Routes ===================================================
//     Route::get('/inspections/{inspection}/start', [InspectionController::class, 'start'])
//         ->name('inspections.start');

//     Route::get('/inspections/create/new', [InspectionController::class, 'create'])
//             ->name('inspections.create.new');
//     Route::get('/inspection/{inspection}/log',[InspectionController::class, 'show'])->name('inspection.log');
//     Route::post('/inspections', [InspectionController::class, 'store'])->name('inspections.store');
//     Route::post('/inspections/{inspection}/cancel', [InspectionController::class, 'cancel'])
//     ->name('inspections.cancel');


//      // Store inspection results
//     Route::post('/inspections/{inspection}/store-results', [InspectionController::class, 'storeResults'])
//             ->name('inspections.store-results');

//     // routes/web.php
//     Route::post('/inspections/save-result', [InspectionController::class, 'saveResult'])->name('inspections.save-result');
//     Route::post('/inspections/delete-result-image', [InspectionController::class, 'deleteResultImage'])->name('inspections.delete-result');
//     // Di web.php
//     Route::post('/inspections/{inspection}/vehicle-details', [InspectionController::class, 'updateVehicleDetails'])
//         ->name('inspections.updateVehicleDetails');

//     Route::post('/inspections/{inspection}/conclusion', [InspectionController::class, 'updateConclusion'])
//         ->name('inspections.updateConclusion');
        
//     Route::post('/inspections/upload-image', [InspectionController::class, 'uploadImage'])->name('inspections.upload-image');
//     Route::delete('/inspections/delete-image', [InspectionController::class, 'deleteImage'])->name('inspections.delete-image');
//     // Final submit
//     Route::post('/inspections/{id}/final-submit', [InspectionController::class, 'finalSubmit'])
//         ->name('inspections.final-submit');

//     // Review page
//     Route::get('/inspections/{id}/review', [InspectionController::class, 'review'])
//         ->name('inspections.review');

//     Route::get('/inspections/{id}/revisi', [InspectionController::class, 'revisi'])
//         ->name('inspections.revisi');

//     Route::get('/inspections/{id}/pending', [InspectionController::class, 'pending'])
//         ->name('inspections.pending');

//     Route::get('/inspections/{id}/review-pdf', [InspectionController::class, 'reviewPdf'])
//         ->name('inspections.review.pdf');

//         // Generate PDF
//     Route::get('/inspections/{id}/download-pdf', [InspectionController::class, 'downloadPdf'])
//         ->name('inspections.download.pdf');

//     Route::get('/inspections/{id}/download-approve-pdf', [InspectionController::class, 'downloadApprovePdf'])
//         ->name('inspections.download.approved.pdf');


//         Route::get('/inspections/{id}/preview', [InspectionController::class, 'previewReport'])
//     ->name('inspections.preview');


    
//     Route::post('/inspections/{encryptedIds}/send-email', [InspectionController::class, 'sendEmail'])
//     ->name('inspections.send.email'); //belum berfungsi


//     // ========================= Rute Menu ==============================================
   
//     Route::get('/cars/create' ,[CarController::class, 'create' ])->name('car.create');
//     Route::get('/cars' ,[CarController::class, 'index' ])->name('cars');

//         // API Routes
//     Route::get('/api/brands', [CarController::class, 'getBrands']);
//     Route::get('/api/models', [CarController::class, 'getModels']);
//     Route::get('/api/types', [CarController::class, 'getTypes']);

//     Route::post('/api/brands/check-duplicate', [CarController::class, 'checkBrandDuplicate']);
//     Route::post('/api/models/check-duplicate', [CarController::class, 'checkModelDuplicate']);
//     Route::post('/types/check-duplicate', [CarController::class, 'checkTypeDuplicate']);

//         // API Routes untuk car management
//     Route::post('/api/car-details/check-duplicate', [CarController::class, 'checkDuplicateCarDetail']);

//     Route::post('/api/brands', [CarController::class, 'storeBrand']);
//     Route::post('/api/models', [CarController::class, 'storeModel']);
//     Route::post('/api/types', [CarController::class, 'storeType']);
//     Route::post('/api/car-details', [CarController::class, 'storeCarDetail'])->name('car-details.store');

//     //=========================== Bantuan ==============================
//     Route::get('/bantuan', [HomeController::class, 'bantuan'])->name('bantuan.index');

//      // Dashboard Coordinator
//     // Route::get('/dashboard', [CoordinatorController::class, 'dashboard'])->name('dashboard');
    
//     // Inspections
//     Route::get('/coordinator/inspections', [CoordinatorController::class, 'index'])->name('coordinator.inspections.index')->middleware('role_spatie:Admin|coordinator',);
//     Route::get('/coordinator/inspections/{inspection}', [CoordinatorController::class, 'show'])->name('coordinator.inspections.show')->middleware('role_spatie:Admin|coordinator',);
//     Route::post('/coordinator/inspections/{inspection}/assign', [CoordinatorController::class, 'assign'])->name('coordinator.inspections.assign');
//     Route::post('/coordinator/inspections/{inspection}/update-status', [CoordinatorController::class, 'updateStatus'])->name('coordinator.inspections.update-status');

// });

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'role_spatie:Admin|inspector|coordinator',
    'region.active',
    'user.active',
])->group(function () {

    // Dashboard - accessible by all authenticated users
    Route::get('/dashboard', [HomeController::class, 'index'])
        ->name('dashboard')
        ->middleware('permission:access dashboard');

    Route::get('/welcome', function () {
        return Inertia::render('Welcome');
    })->name('welcome');

    // ========================= INSPECTION ROUTES =========================
    Route::middleware('permission:view inspections')->group(function () {
        Route::get('/job', [JobController::class, 'index'])->name('job.index');
        
        Route::get('/job/history', [JobController::class, 'history'])->name('inspections.history');
        
        Route::get('/inspections/{inspection}/start', [InspectionController::class, 'start'])
            ->name('inspections.start')
            ->middleware('permission:start inspections');

        Route::get('/inspections/create/new', [InspectionController::class, 'create'])
            ->name('inspections.create.new')
            ->middleware('permission:create inspections');

        Route::get('/inspection/{inspection}/log', [InspectionController::class, 'show'])
            ->name('inspection.log');

        Route::post('/inspections', [InspectionController::class, 'store'])
            ->name('inspections.store')
            ->middleware('permission:create inspections');

        Route::post('/inspections/{inspection}/cancel', [InspectionController::class, 'cancel'])
            ->name('inspections.cancel')
            ->middleware('permission:cancel inspections');
    });

    // Inspection Results
    Route::middleware('permission:store inspection results')->group(function () {
        Route::post('/inspections/{inspection}/store-results', [InspectionController::class, 'storeResults'])
            ->name('inspections.store-results');

        Route::post('/inspections/save-result', [InspectionController::class, 'saveResult'])
            ->name('inspections.save-result');

        Route::post('/inspections/delete-result-image', [InspectionController::class, 'deleteResultImage'])
            ->name('inspections.delete-result');

        Route::post('/inspections/{inspection}/vehicle-details', [InspectionController::class, 'updateVehicleDetails'])
            ->name('inspections.updateVehicleDetails')
            ->middleware('permission:update vehicle details');

        Route::post('/inspections/{inspection}/conclusion', [InspectionController::class, 'updateConclusion'])
            ->name('inspections.updateConclusion')
            ->middleware('permission:update conclusion');

        Route::post('/inspections/upload-image', [InspectionController::class, 'uploadImage'])
            ->name('inspections.upload-image')
            ->middleware('permission:upload images');

        Route::delete('/inspections/delete-image', [InspectionController::class, 'deleteImage'])
            ->name('inspections.delete-image')
            ->middleware('permission:delete images');

        Route::post('/inspections/{id}/final-submit', [InspectionController::class, 'finalSubmit'])
            ->name('inspections.final-submit')
            ->middleware('permission:final submit inspections');
    });

    // Review & PDF Routes
    Route::middleware('permission:review inspections')->group(function () {
        Route::get('/inspections/{id}/review', [InspectionController::class, 'review'])
            ->name('inspections.review');

        Route::get('/inspections/{id}/revisi', [InspectionController::class, 'revisi'])
            ->name('inspections.revisi');

        Route::get('/inspections/{id}/pending', [InspectionController::class, 'pending'])
            ->name('inspections.pending');
    });

    Route::middleware('permission:download pdf reports')->group(function () {
        Route::get('/inspections/{id}/review-pdf', [InspectionController::class, 'reviewPdf'])
            ->name('inspections.review.pdf');

        Route::get('/inspections/{id}/download-pdf', [InspectionController::class, 'downloadPdf'])
            ->name('inspections.download.pdf');

        Route::get('/inspections/{id}/download-approve-pdf', [InspectionController::class, 'downloadApprovePdf'])
            ->name('inspections.download.approved.pdf');

        Route::get('/inspections/{id}/preview', [InspectionController::class, 'previewReport'])
            ->name('inspections.preview');
    });

    Route::post('/inspections/{encryptedIds}/send-email', [InspectionController::class, 'sendEmail'])
        ->name('inspections.send.email')
        ->middleware('permission:send email reports');

    // ========================= CAR ROUTES =========================
    Route::middleware('permission:view cars')->group(function () {
        Route::get('/cars', [CarController::class, 'index'])->name('cars');

        Route::middleware('permission:create cars')->group(function () {
            Route::get('/cars/create', [CarController::class, 'create'])->name('car.create');
            Route::post('/api/car-details', [CarController::class, 'storeCarDetail'])->name('car-details.store');
        });

        // API Routes
        Route::get('/api/brands', [CarController::class, 'getBrands']);
        Route::get('/api/models', [CarController::class, 'getModels']);
        Route::get('/api/types', [CarController::class, 'getTypes']);

        Route::middleware('permission:manage brands')->group(function () {
            Route::post('/api/brands/check-duplicate', [CarController::class, 'checkBrandDuplicate']);
            Route::post('/api/brands', [CarController::class, 'storeBrand']);
        });

        Route::middleware('permission:manage models')->group(function () {
            Route::post('/api/models/check-duplicate', [CarController::class, 'checkModelDuplicate']);
            Route::post('/api/models', [CarController::class, 'storeModel']);
        });

        Route::middleware('permission:manage types')->group(function () {
            Route::post('/types/check-duplicate', [CarController::class, 'checkTypeDuplicate']);
            Route::post('/api/types', [CarController::class, 'storeType']);
        });

        Route::middleware('permission:manage car details')->group(function () {
            Route::post('/api/car-details/check-duplicate', [CarController::class, 'checkDuplicateCarDetail']);
        });
    });

    // ========================= BANTUAN =========================
    Route::get('/bantuan', [HomeController::class, 'bantuan'])
        ->name('bantuan.index')
        ->middleware('permission:view bantuan');

    // ========================= COORDINATOR ROUTES =========================
    Route::middleware(['permission:view coordinator dashboard', 'role_spatie:Admin|coordinator'])->group(function () {
        Route::get('/coordinator/inspections', [CoordinatorController::class, 'index'])
            ->name('coordinator.inspections.index');

        Route::get('/coordinator/inspections/{inspection}', [CoordinatorController::class, 'show'])
            ->name('coordinator.inspections.show');

        Route::middleware('permission:assign inspections')->group(function () {
            Route::post('/coordinator/inspections/{inspection}/assign', [CoordinatorController::class, 'assign'])
                ->name('coordinator.inspections.assign');
        });

        Route::middleware('permission:update inspection status')->group(function () {
            Route::post('/coordinator/inspections/{inspection}/update-status', [CoordinatorController::class, 'updateStatus'])
                ->name('coordinator.inspections.update-status');
        });
    });
});

