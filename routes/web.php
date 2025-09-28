<?php

use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\DataCar\CarController;
use App\Http\Controllers\Finance\TransactionController;
use App\Http\Controllers\Inspection\InspectionController;
use App\Http\Controllers\Menu\Home\CoordinatorController;
use App\Http\Controllers\Menu\Home\HomeController;
use App\Http\Controllers\Menu\Job\JobController;
use App\Http\Controllers\Menu\Team\TeamController;
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
        ->middleware('permission:FrontEnd.access dashboard');

    Route::get('/welcome', function () {
        return Inertia::render('Welcome');
    })->name('welcome');

    // ========================= INSPECTION VIEW =========================
    Route::middleware('permission:FrontEnd.view inspections')->group(function () {
        Route::get('/job', [JobController::class, 'index'])->name('job.index');
        
        Route::get('/job/history', [JobController::class, 'history'])->name('inspections.history')
        ->middleware('permission:FrontEnd.history inspections');

        Route::get('/inspection/{inspection}/log', [InspectionController::class, 'show'])
            ->name('inspection.log')
             ->middleware('permission:FrontEnd.log inspections');

        Route::post('/inspections/{inspection}/cancel', [InspectionController::class, 'cancel'])
            ->name('inspections.cancel')
            ->middleware('permission:FrontEnd.cancel inspections');
    });

     // ========================= INSPECTION CREATE =========================
    Route::middleware('permission:FrontEnd.create inspections')->group(function () {
        Route::get('/inspections/create/new', [InspectionController::class, 'create'])
            ->name('inspections.create.new');
    
        Route::post('/inspections', [InspectionController::class, 'store'])
            ->name('inspections.store');
    });

    // ========================= INSPECTION START =========================
    Route::middleware('permission:FrontEnd.start inspections')->group(function () {
        Route::get('/inspections/{inspection}/start', [InspectionController::class, 'start'])
            ->name('inspections.start');

        Route::post('/inspections/{inspection}/store-results', [InspectionController::class, 'storeResults'])
            ->name('inspections.store-results');

        Route::post('/inspections/save-result', [InspectionController::class, 'saveResult'])
            ->name('inspections.save-result');

        Route::post('/inspections/delete-result-image', [InspectionController::class, 'deleteResultImage'])
            ->name('inspections.delete-result');

        Route::post('/inspections/{inspection}/vehicle-details', [InspectionController::class, 'updateVehicleDetails'])
            ->name('inspections.updateVehicleDetails');

        Route::post('/inspections/{inspection}/conclusion', [InspectionController::class, 'updateConclusion'])
            ->name('inspections.updateConclusion');

        Route::post('/inspections/upload-image', [InspectionController::class, 'uploadImage'])
            ->name('inspections.upload-image');

        Route::delete('/inspections/delete-image', [InspectionController::class, 'deleteImage'])
            ->name('inspections.delete-image');

        Route::get('/inspections/{id}/pending', [InspectionController::class, 'pending'])
            ->name('inspections.pending');

        Route::post('/inspections/{id}/final-submit', [InspectionController::class, 'finalSubmit'])
            ->name('inspections.final-submit')
            ->middleware('permission:FrontEnd.final submit inspections');

        Route::post('/inspections/{id}/final-submit-local', [InspectionController::class, 'finalSubmitAll'])
            ->name('inspections.final-submit-local');
    });

    // ========================= INSPECTION REVIEW =========================
    Route::middleware('permission:FrontEnd.review inspections')->group(function () {
        Route::get('/inspections/{id}/review', [InspectionController::class, 'review'])
            ->name('inspections.review');

        Route::get('/inspections/{id}/revisi', [InspectionController::class, 'revisi'])
            ->name('inspections.revisi')
            ->middleware('permission:FrontEnd.revisi inspections');
    });

    Route::middleware('permission:FrontEnd.review inspection report')->group(function () {
        Route::get('/inspections/{id}/review-pdf', [InspectionController::class, 'reviewPdf'])
            ->name('inspections.review.pdf');

        // routes/web.php untuk realtim pooling
        Route::get('/inspections/{id}/detail', [InspectionController::class, 'detail'])
            ->name('inspections.detail');


        Route::get('/inspections/{id}/approved-pdf', [InspectionController::class, 'approvePdf'])
            ->name('inspections.download.pdf')
            ->middleware('permission:FrontEnd.approve inspections report');

        Route::get('/inspections/{id}/download-approve-pdf', [InspectionController::class, 'downloadApprovePdf'])
            ->name('inspections.download.approved.pdf')
            ->middleware('permission:FrontEnd.download pdf');

        Route::get('/inspections/{id}/preview', [InspectionController::class, 'previewReport'])
            ->name('inspections.preview');

        Route::get('/inspections/{id}/coba', [InspectionController::class, 'download'])
            ->name('inspections.coba');
    });

    Route::post('/inspections/{encryptedIds}/send-email', [InspectionController::class, 'sendEmail'])
        ->name('inspections.send.email')
        ->middleware('permission:FrontEnd.send email reports');

  // ========================= CAR ROUTES =========================
    Route::middleware('permission:FrontEnd.send whatsapp reports')->group(function () {
        Route::get('/inspections/{encryptedIds}/whatsapp', [InspectionController::class, 'whatsapp'])
        ->name('inspections.whatsapp');

        // routes/web.php
    Route::get('/inspections/{inspection}/whatsapp/send', [InspectionController::class, 'send'])
        ->name('inspections.whatsapp.send');


    });

    // ========================= CAR ROUTES =========================
    Route::middleware('permission:FrontEnd.view cars')->group(function () {
        Route::get('/cars', [CarController::class, 'index'])->name('cars');

        Route::middleware('permission:FrontEnd.create cars')->group(function () {
            Route::get('/cars/create', [CarController::class, 'create'])->name('car.create');
            Route::post('/api/car-details', [CarController::class, 'storeCarDetail'])->name('car-details.store');
        });

        // API Routes
        Route::get('/api/brands', [CarController::class, 'getBrands']);
        Route::get('/api/models', [CarController::class, 'getModels']);
        Route::get('/api/types', [CarController::class, 'getTypes']);

        Route::middleware('permission:FrontEnd.manage brands')->group(function () {
            Route::post('/api/brands/check-duplicate', [CarController::class, 'checkBrandDuplicate']);
            Route::post('/api/brands', [CarController::class, 'storeBrand']);
        });

        Route::middleware('permission:FrontEnd.manage models')->group(function () {
            Route::post('/api/models/check-duplicate', [CarController::class, 'checkModelDuplicate']);
            Route::post('/api/models', [CarController::class, 'storeModel']);
        });

        Route::middleware('permission:FrontEnd.manage types')->group(function () {
            Route::post('/types/check-duplicate', [CarController::class, 'checkTypeDuplicate']);
            Route::post('/api/types', [CarController::class, 'storeType']);
        });

        Route::middleware('permission:FrontEnd.manage car details')->group(function () {
            Route::post('/api/car-details/check-duplicate', [CarController::class, 'checkDuplicateCarDetail']);
        });
    });

    // ========================= BANTUAN =========================
    Route::get('/bantuan', [HomeController::class, 'bantuan'])
        ->name('bantuan.index')
        ->middleware('permission:FrontEnd.view bantuan');

    // ========================= COORDINATOR ROUTES =========================
    Route::middleware(['permission:FrontEnd.view coordinator dashboard', 'role_spatie:Admin|coordinator'])->group(function () {
        Route::get('/coordinator/inspections', [CoordinatorController::class, 'index'])
            ->name('coordinator.inspections.index');
            
        Route::get('/api/region/{id}/users', function($id) {
            return \App\Models\User::whereIn('id', \App\Models\Team\RegionTeam::where('region_id', $id)->pluck('user_id'))->get(['id','name']);
        })->name('api.region.users');

            Route::get('/api/users/all', function() {
            return \App\Models\User::whereIn('id', \App\Models\Team\RegionTeam::pluck('user_id'))
                ->get(['id','name']);
        })->name('api.users.all');

        Route::get('/coordinator/inspections/{inspection}', [CoordinatorController::class, 'show'])
            ->name('coordinator.inspections.show');

        Route::middleware('permission:FrontEnd.coordinator assign inspections')->group(function () {
            Route::post('/coordinator/inspections/{inspection}/assign', [CoordinatorController::class, 'assign'])
                ->name('coordinator.inspections.assign');
        });

        Route::middleware('permission:FrontEnd.coordinator update inspection status')->group(function () {
            Route::post('/coordinator/inspections/{inspection}/update-status', [CoordinatorController::class, 'updateStatus'])
                ->name('coordinator.inspections.update-status');
        });
    });

        // ========================= CAR ROUTES =========================
    Route::middleware('permission:FrontEnd.view teams')->group(function () {

      Route::get('/team', [TeamController::class, 'index'])->name('team.index')
      ;
        Route::get('/team/region/{regionId}', [TeamController::class, 'getByRegion'])->name('team.by-region')
        ;
        Route::post('/team/add-user', [TeamController::class, 'addUser'])->name('team.add-user')
         ->middleware('permission:FrontEnd.add Team');
        ;
        Route::get('/team-settings/{id}/users', [TeamController::class, 'setting_team'])->name('setting.team')
        ;
        Route::put('/team-settings/{id}', [TeamController::class, 'updateettingTeam'])->name('team-settings.update')
         ->middleware('permission:FrontEnd.settings Team');
        ;
    });

        // ======================TRANSAKSI & FINANCE ROUTES==============================
    Route::post('/inspections/{inspection}/update-unified', [TransactionController::class, 'updateUnified'])
        ->name('inspections.updateUnified')
        ->middleware('permission:FrontEnd.update transaction');

    Route::middleware('permission:FrontEnd.view finance')->group(function () {
         Route::get('/finance/report', [TransactionController::class, 'index'])
        ->name('report.index');
    
        Route::get('/finance/report/export', [TransactionController::class, 'export'])
            ->name('report.export')
            ->middleware('permission:FrontEnd.finance report');

        Route::get('/finance/distributions/{id}', [TransactionController::class, 'show'])->name('finance.show');
        Route::get('/finance/tagihan', [TransactionController::class, 'tagihan'])->name('tagihan.show');
        Route::put('/finance/distributions/{id}/release', [TransactionController::class, 'updateRelease'])->name('distributions.release')
         ->middleware('permission:FrontEnd.finance setor');

    });

    

        
});

