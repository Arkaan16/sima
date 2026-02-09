<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Livewire Components Imports
|--------------------------------------------------------------------------
*/
// General
use App\Livewire\Scan;
use App\Livewire\Dashboard;
use App\Livewire\Report;

// User Management
use App\Livewire\UserManagement\Users;

// Maintenance
use App\Livewire\Maintenance\Index as MaintenanceIndex;
use App\Livewire\Maintenance\Form as MaintenanceForm;
use App\Livewire\Maintenance\Show as MaintenanceShow;

// Assets
use App\Livewire\Assets\Index as AssetIndex;
use App\Livewire\Assets\Form as AssetForm;
use App\Livewire\Assets\Show as AssetShow;

// Master Data
use App\Livewire\Master\Employees;
use App\Livewire\Master\Manufacturers;
use App\Livewire\Master\Locations;
use App\Livewire\Master\Suppliers;
use App\Livewire\Master\Categories;
use App\Livewire\Master\AssetModels;
use App\Livewire\Master\AssetStatus;

/*
|--------------------------------------------------------------------------
| ROOT & AUTH LOGIC
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD & UTILITIES (Admin | Employee)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin|employee')->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('/scan', Scan::class)->name('scan');
        Route::get('/laporan', Report::class)->name('reports');
    });

    /*
    |--------------------------------------------------------------------------
    | MAINTENANCE (Admin | Employee)
    |--------------------------------------------------------------------------
    */
    Route::prefix('maintenances')
        ->name('maintenances.')
        ->middleware('role:admin|employee') // Middleware langsung di chain disini
        ->group(function () {
            Route::get('/', MaintenanceIndex::class)->name('index');
            Route::get('/create', MaintenanceForm::class)->name('create');
            Route::get('/{maintenance}/edit', MaintenanceForm::class)->name('edit');
            Route::get('/{maintenance}', MaintenanceShow::class)->name('show');
        });

    /*
    |--------------------------------------------------------------------------
    | ASSETS (Hybrid Access)
    |--------------------------------------------------------------------------
    */
    Route::prefix('assets')->name('assets.')->group(function () {
    
        Route::middleware('role:admin')->group(function () {
            Route::get('/create', AssetForm::class)->name('create'); 
            Route::get('/{asset}/edit', AssetForm::class)->name('edit');
        });

        Route::middleware('role:admin|employee')->group(function () {
            Route::get('/', AssetIndex::class)->name('index');
            Route::get('/{asset:asset_tag}', AssetShow::class)->name('show');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | ADMIN ONLY MODULES
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {
        
        // User Management
        Route::get('/users', Users::class)->name('users');

        // Master Data
        Route::prefix('master')->name('master.')->group(function () {
            Route::get('/categories', Categories::class)->name('categories');
            Route::get('/locations', Locations::class)->name('locations');
            Route::get('/manufacturers', Manufacturers::class)->name('manufacturers');
            Route::get('/suppliers', Suppliers::class)->name('suppliers');
            Route::get('/asset-status', AssetStatus::class)->name('asset-status');
            Route::get('/asset-models', AssetModels::class)->name('asset-models');
            Route::get('/employees', Employees::class)->name('employees');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | PROFILE (All Authenticated Users)
    |--------------------------------------------------------------------------
    */
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });
});

require __DIR__.'/auth.php';