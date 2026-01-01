<?php

namespace Tests\Feature\Livewire\Admin\Master;

use App\Livewire\Admin\Master\AssetModelManager;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Manufacturer;
use Livewire\Livewire;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// Import Global Functions
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Asset Model Manager - Complete Suite', function () {

    // --- 1. RENDER & AKSES ---
    test('halaman render dengan sukses', function () {
        Livewire::test(AssetModelManager::class)
            ->assertStatus(200)
            ->assertSee('Manajemen Model Aset');
    });

    // --- 2. PAGINATION & SORTING (BARU DITAMBAHKAN) ---
    test('pagination reset ke halaman 1 saat mengetik search', function () {
        // Simulasi: User sedang berada di halaman 5
        Livewire::test(AssetModelManager::class)
            ->set('paginators.page', 5) 
            ->set('search', 'Cari Sesuatu') // User mengetik
            ->assertSet('paginators.page', 1); // Harusnya otomatis reset ke 1
    });

    test('data ditampilkan maksimal 10 per halaman', function () {
        // Kita buat 15 data dummy
        AssetModel::factory()->count(15)->create();

        // Di halaman 1, harusnya cuma muncul 10 data terbaru
        $component = Livewire::test(AssetModelManager::class);
        
        // Kita cek view datanya (mengakses variabel public/computed 'assetModels' di view)
        // Karena Livewire mempassing data via view, cara test paling akurat adalah cek jumlah item di view
        $component->assertViewHas('assetModels', function ($models) {
            return $models->count() === 10;
        });
    });

    test('data diurutkan dari yang terbaru (latest)', function () {
        $modelLama = AssetModel::factory()->create(['created_at' => now()->subDay(), 'name' => 'Model Lama']);
        $modelBaru = AssetModel::factory()->create(['created_at' => now(), 'name' => 'Model Baru']);

        Livewire::test(AssetModelManager::class)
            ->assertSeeInOrder(['Model Baru', 'Model Lama']); // Pastikan Baru muncul duluan sebelum Lama
    });

    // --- 3. VALIDASI UNIK YANG KOMPLEKS ---
    test('gagal membuat data jika nama atau nomor model sudah ada', function () {
        AssetModel::factory()->create([
            'name' => 'Macbook Pro M1', 
            'model_number' => 'MBP-2020'
        ]);

        Livewire::test(AssetModelManager::class)
            ->call('create')
            ->set('name', 'Macbook Pro M1') 
            ->set('model_number', 'MBP-2020')
            ->call('store')
            ->assertHasErrors(['name', 'model_number']);
    });

    test('validasi unik mengabaikan diri sendiri saat update', function () {
        $model = AssetModel::factory()->create([
            'name' => 'Macbook Air', 
            'model_number' => 'MBA-2020'
        ]);

        Livewire::test(AssetModelManager::class)
            ->call('edit', $model->id)
            ->set('name', 'Macbook Air') 
            ->set('model_number', 'MBA-2020') 
            ->call('store')
            ->assertHasNoErrors();
    });

    test('validasi unik tetap memblokir duplikat punya orang lain saat update', function () {
        $modelA = AssetModel::factory()->create(['name' => 'Laptop A', 'model_number' => 'A-001']);
        $modelB = AssetModel::factory()->create(['name' => 'Laptop B', 'model_number' => 'B-002']);

        Livewire::test(AssetModelManager::class)
            ->call('edit', $modelA->id)
            ->set('name', 'Laptop B') 
            ->set('model_number', 'B-002') 
            ->call('store')
            ->assertHasErrors(['name', 'model_number']);
    });

    // --- 4. VALIDASI FOREIGN KEY (Security) ---
    test('gagal simpan jika ID kategori atau pabrikan tidak valid', function () {
        Livewire::test(AssetModelManager::class)
            ->call('create')
            ->set('name', 'Hacker Attempt')
            ->set('category_id', 999999) 
            ->set('manufacturer_id', 999999)
            ->call('store')
            ->assertHasErrors(['category_id', 'manufacturer_id']);
    });

    // --- 5. LOGIKA PRESERVASI GAMBAR ---
    test('update data TANPA upload gambar baru tidak menghapus gambar lama', function () {
        Storage::fake('public');
        
        $oldFile = UploadedFile::fake()->image('original.jpg');
        $oldPath = $oldFile->store('asset-models', 'public');
        
        $model = AssetModel::factory()->create(['image' => $oldPath]);

        Livewire::test(AssetModelManager::class)
            ->call('edit', $model->id)
            ->set('name', 'Nama Baru')
            ->set('newImage', null) 
            ->call('store');

        $model->refresh();

        expect($model->image)->toBe($oldPath);
        expect(Storage::disk('public')->exists($oldPath))->toBeTrue();
    });

    // --- 6. STATE MANAGEMENT & UX HELPER ---
    test('saat tombol edit ditekan, nama kategori dan pabrikan harus muncul di input', function () {
        $cat = Category::factory()->create(['name' => 'Kendaraan']);
        $man = Manufacturer::factory()->create(['name' => 'Toyota']);
        
        $model = AssetModel::factory()->create([
            'category_id' => $cat->id, 
            'manufacturer_id' => $man->id
        ]);

        Livewire::test(AssetModelManager::class)
            ->call('edit', $model->id)
            ->assertSet('selectedCategoryName', 'Kendaraan')
            ->assertSet('selectedManufacturerName', 'Toyota');
    });

    test('reset input fields bekerja saat modal ditutup', function () {
        Livewire::test(AssetModelManager::class)
            ->call('create')
            ->set('name', 'Data Sampah')
            ->set('category_id', 5)
            ->call('closeModal')
            ->assertSet('name', '')
            ->assertSet('category_id', null)
            ->assertSet('showFormModal', false);
    });

    // --- 7. PENCARIAN MENYELURUH ---
    test('search bisa mencari berdasarkan NOMOR MODEL', function () {
        AssetModel::factory()->create(['name' => 'A', 'model_number' => 'XYZ-999']);
        AssetModel::factory()->create(['name' => 'B', 'model_number' => 'ABC-123']);

        Livewire::test(AssetModelManager::class)
            ->set('search', 'XYZ-999')
            ->assertSee('XYZ-999') 
            ->assertDontSee('ABC-123');
    });

    test('search bisa mencari berdasarkan NAMA PABRIKAN (Relasi)', function () {
        $asus = Manufacturer::factory()->create(['name' => 'Asus']);
        $acer = Manufacturer::factory()->create(['name' => 'Acer']);

        AssetModel::factory()->create(['name' => 'ROG', 'manufacturer_id' => $asus->id]);
        AssetModel::factory()->create(['name' => 'Predator', 'manufacturer_id' => $acer->id]);

        Livewire::test(AssetModelManager::class)
            ->set('search', 'Asus') 
            ->assertSee('ROG') 
            ->assertDontSee('Predator');
    });

    // --- 8. FULL CYCLE CREATE ---
    test('full cycle: create model dengan gambar dan relasi', function () {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('final.jpg');
        $cat = Category::factory()->create();
        $man = Manufacturer::factory()->create();

        Livewire::test(AssetModelManager::class)
            ->call('create')
            ->set('name', 'Final Test')
            ->set('model_number', 'FINAL-01')
            ->call('selectCategory', $cat->id, $cat->name)
            ->call('selectManufacturer', $man->id, $man->name)
            ->set('newImage', $file)
            ->call('store')
            ->assertHasNoErrors();

        assertDatabaseHas('asset_models', [
            'name' => 'Final Test',
            'category_id' => $cat->id
        ]);
        
        $model = AssetModel::where('name', 'Final Test')->first();
        expect(Storage::disk('public')->exists($model->image))->toBeTrue();
    });

});