<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\StockController;


  
// ============================================================================
// PUBLIC ROUTES
// ============================================================================
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// ============================================================================
// AUTHENTICATED ROUTES (All users must be logged in)
// ============================================================================
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [AuthController::class, 'profile'])->name('index');
        Route::get('/password', [AuthController::class, 'showChangePasswordForm'])->name('password');
        Route::post('/password', [AuthController::class, 'changePassword'])->name('password.update');
    });
    
    Route::resource('employes', EmployeController::class);
    Route::prefix('employes/{employe}')->name('employes.')->group(function () {
        Route::post('/assign-project', [EmployeController::class, 'assignProject'])->name('assign-project');
        Route::get('/presences', [EmployeController::class, 'getPresences'])->name('presences');
    });
    
    Route::resource('projets', ProjetController::class);    
    Route::prefix('projets')->name('projets.')->group(function () {
        Route::get('/projets-stats', [ProjetController::class, 'getStats'])->name('stats');
        Route::post('/{projet}/phases', [ProjetController::class, 'addPhase'])->name('phases.store');
    });
    Route::prefix('projets/{projet}/stocks')->name('stocks.')->group(function () {
        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::post('/receive', [StockController::class, 'receive'])->name('receive');
        Route::post('/transfer', [StockController::class, 'transfer'])->name('transfer');
        Route::post('/consume', [StockController::class, 'consume'])->name('consume');
        Route::get('/export', [StockController::class, 'export'])->name('export');
        Route::get('/materiaux/{materiau}/history', [StockController::class, 'history'])->name('history');
    });
    Route::prefix('projets/{projet}/presences')->name('presences.')->group(function () {
        Route::get('/', [PresenceController::class, 'index'])->name('index');
        Route::post('/', [PresenceController::class, 'store'])->name('store');
        Route::get('/history', [PresenceController::class, 'history'])->name('history');
        Route::get('/export', [PresenceController::class, 'export'])->name('export');
    });
    
    Route::prefix('presences/{presence}')->name('presences.')->group(function () {
        Route::put('/', [PresenceController::class, 'update'])->name('update');
        Route::delete('/', [PresenceController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('phases')->name('phases.')->group(function () {
        Route::put('/{phase}/progression', [ProjetController::class, 'updatePhaseProgression'])->name('progression.update');
        Route::post('/{phase}/remarques', [ProjetController::class, 'addRemark'])->name('remarques.store');
    });

    // ------------------------------------------------------------------------
    // ROLE-SPECIFIC DASHBOARDS
    // Each dashboard is protected by specific permission gates
    // ------------------------------------------------------------------------
    
    Route::get('/admin/dashboard', function () {
        return view('dashboards.admin');
    })->name('admin.dashboard')->middleware('can:access-admin-dashboard');
    
    Route::get('/dt/dashboard', function () {
        return view('dashboards.directeur-technique');
    })->name('dt.dashboard')->middleware('can:access-dt-dashboard');
    
    Route::get('/controleur/dashboard', function () {
        return view('dashboards.controleur');
    })->name('controleur.dashboard')->middleware('can:access-controleur-dashboard');
    
    Route::get('/stock/dashboard', function () {
        return view('dashboards.gestionnaire-stock');
    })->name('stock.dashboard')->middleware('can:access-stock-dashboard');
});