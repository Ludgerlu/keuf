<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile routes
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::get('/profile/password', [AuthController::class, 'showChangePasswordForm'])->name('profile.password');
    Route::post('/profile/password', [AuthController::class, 'changePassword'])->name('profile.password.update');
    
    // Role-specific dashboards (to be implemented)
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