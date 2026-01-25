<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Dashboard as AdminDashboard; 
use App\Livewire\Admin\Scan\Scan;
use App\Livewire\Admin\Asset\AssetEdit;
use App\Livewire\Admin\Asset\AssetShow;
use App\Livewire\Admin\Asset\AssetIndex;
use App\Livewire\Admin\Asset\AssetCreate;
use App\Livewire\Admin\Users\UserManager;
use App\Http\Controllers\ProfileController;
use App\Livewire\Admin\Report\ExportReport;
use App\Livewire\Admin\Master\CategoryManager;
use App\Livewire\Admin\Master\EmployeeManager;
use App\Livewire\Admin\Master\LocationManager;
use App\Livewire\Admin\Master\SupplierManager;
use App\Livewire\Admin\Master\AssetModelManager;
use App\Livewire\Admin\Master\AssetStatusManager;
use App\Livewire\Admin\Master\ManufacturerManager;
use App\Livewire\Admin\Maintenance\MaintenanceEdit;
use App\Livewire\Admin\Maintenance\MaintenanceShow;
use App\Livewire\Admin\Maintenance\MaintenanceIndex;
use App\Livewire\Admin\Maintenance\MaintenanceCreate;
use App\Livewire\Employee\Dashboard as EmployeeDashboard;
use App\Livewire\Employee\Asset\AssetIndex as EmployeeAssetIndex;
use App\Livewire\Employee\Asset\AssetShow as EmployeeAssetShow;
use App\Livewire\Employee\Scan\Scan as EmployeeScan;
use App\Livewire\Employee\Maintenance\MaintenanceIndex as EmployeeMaintenanceIndex;
use App\Livewire\Employee\Maintenance\MaintenanceCreate as EmployeeMaintenanceCreate;
use App\Livewire\Employee\Maintenance\MaintenanceShow as EmployeeMaintenanceShow;
use App\Livewire\Employee\Maintenance\MaintenanceEdit as EmployeeMaintenanceEdit;

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

    // Dashboard
    Route::get('/dashboard', AdminDashboard::class)->name('dashboard');

    // User Management
    Route::get('/users', UserManager::class)->name('users');

    Route::get('/scan', Scan::class)->name('scan');

    // GROUP MASTER DATA
    Route::prefix('master')->name('master.')->group(function () {
        Route::get('/category', CategoryManager::class)->name('category');
        Route::get('/location', LocationManager::class)->name('location');
        Route::get('/asset-status', AssetStatusManager::class)->name('asset-status');
        Route::get('/manufacturer', ManufacturerManager::class)->name('manufacturer');
        Route::get('/asset-model', AssetModelManager::class)->name('asset-model');
        Route::get('/employee', EmployeeManager::class)->name('employee');
        Route::get('/supplier', SupplierManager::class)->name('supplier');
    });

    Route::prefix('assets')->name('assets.')->group(function () {
        Route::get('/', AssetIndex::class)->name('index');
        Route::get('/create', AssetCreate::class)->name('create');
        Route::get('/{asset:asset_tag}', AssetShow::class)->name('show');
        Route::get('/{asset}/edit', AssetEdit::class)->name('edit');

        // // 5. Aksi Download PDF / QR Code (Opsional)
        // // Karena ini download file, biasanya pakai Controller biasa (bukan Livewire Component)
        // // Route Name: admin.assets.qrcodes.pdf
        // Route::post('/qrcodes/download', [AssetController::class, 'downloadQrCodes'])->name('qrcodes.pdf');
    });

    Route::prefix('maintenances')->name('maintenances.')->group(function () {
        // 1. Halaman Index
        Route::get('/', MaintenanceIndex::class)->name('index');
        
        // 2. Halaman Create (WAJIB sebelum show/edit)
        Route::get('/create', MaintenanceCreate::class)->name('create');
        
        // 3. Halaman Show (Detail)
        // Menggunakan {maintenance} agar otomatis connect ke model di Livewire (Route Model Binding)
        Route::get('/{maintenance}', MaintenanceShow::class)->name('show');
        
        // 4. Halaman Edit
        Route::get('/{maintenance}/edit', MaintenanceEdit::class)->name('edit');
    });

    Route::get('/reports', ExportReport::class)->name('reports');
});

// Employee Area
Route::middleware(['auth', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    
    Route::get('/dashboard', EmployeeDashboard::class)->name('dashboard');

    // Route Scan
    Route::get('/scan', EmployeeScan::class)->name('scan');

    // Group Assets (READ ONLY)
    Route::prefix('assets')->name('assets.')->group(function () {
        Route::get('/', EmployeeAssetIndex::class)->name('index');    
        Route::get('/{asset:asset_tag}', EmployeeAssetShow::class)->name('show');
    });

    Route::prefix('maintenances')->name('maintenances.')->group(function () {
        // 1. Halaman Index
        Route::get('/', EmployeeMaintenanceIndex::class)->name('index');
        
        // 2. Halaman Create (WAJIB sebelum show/edit)
        Route::get('/create', EmployeeMaintenanceCreate::class)->name('create');
        
        // // 3. Halaman Show (Detail)
        // // Menggunakan {maintenance} agar otomatis connect ke model di Livewire (Route Model Binding)
        Route::get('/{maintenance}', EmployeeMaintenanceShow::class)->name('show');
        
        // // 4. Halaman Edit
        Route::get('/{maintenance}/edit', EmployeeMaintenanceEdit::class)->name('edit');
    });
});

require __DIR__.'/auth.php';
