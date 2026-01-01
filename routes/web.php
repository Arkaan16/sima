<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Livewire\Admin\Master\CategoryManager;
use App\Livewire\Admin\Master\EmployeeManager;
use App\Livewire\Admin\Master\LocationManager;
use App\Livewire\Admin\Master\AssetModelManager;
use App\Livewire\Admin\Master\AssetStatusManager;
use App\Livewire\Admin\Master\ManufacturerManager;

Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->role === 'admin' 
            ? redirect()->route('admin.dashboard') 
            : redirect()->route('employee.dashboard');
    }

    return redirect()->route('login');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Area
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::get('/master/category', CategoryManager::class)->name('master.category');
    Route::get('/master/location', LocationManager::class)->name('master.location');
    Route::get('/master/asset-status', AssetStatusManager::class)->name('master.asset-status');
    Route::get('/master/manufacturer', ManufacturerManager::class)->name('master.manufacturer');
    Route::get('/master/asset-model', AssetModelManager::class)->name('master.asset-model');
    Route::get('/master/employee', EmployeeManager::class)->name('master.employee');
});

// Employee Area
Route::middleware(['auth', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', function () {
        return view('employee.dashboard');
    })->name('dashboard');
});

require __DIR__.'/auth.php';
