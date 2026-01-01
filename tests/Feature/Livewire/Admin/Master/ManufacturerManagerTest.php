<?php

namespace Tests\Feature\Livewire\Admin\Master;

use App\Livewire\Admin\Master\ManufacturerManager;
use App\Models\Manufacturer;
use Livewire\Livewire;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// --- IMPORT GLOBAL FUNCTION (DATABASE) ---
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Manufacturer Manager Component', function () {

    // --- 1. TEST RENDER ---
    test('halaman bisa diakses dan judul muncul', function () {
        Livewire::test(ManufacturerManager::class)
            ->assertStatus(200)
            ->assertSee('Manajemen Pabrikan');
    });

    // --- 2. TEST CREATE (TANPA GAMBAR) ---
    test('bisa membuat pabrikan baru tanpa gambar', function () {
        Livewire::test(ManufacturerManager::class)
            ->call('create')
            ->set('name', 'Dell')
            ->set('url', 'https://dell.com')
            ->set('support_email', 'help@dell.com')
            ->call('store')
            ->assertHasNoErrors()
            ->assertSet('showFormModal', false);

        // Global Function (Database)
        assertDatabaseHas('manufacturers', [
            'name' => 'Dell', 
            'url' => 'https://dell.com',
            'support_email' => 'help@dell.com',
            'image' => null
        ]);
    });

    // --- 3. TEST CREATE DENGAN GAMBAR (UPLOAD) ---
    test('bisa upload logo pabrikan', function () {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('logo.png');

        Livewire::test(ManufacturerManager::class)
            ->call('create')
            ->set('name', 'Asus')
            ->set('newImage', $file)
            ->call('store')
            ->assertHasNoErrors();

        $manufacturer = Manufacturer::where('name', 'Asus')->first();
        
        // Pengecekan Object
        expect($manufacturer->image)->not->toBeNull();

        // --- PERBAIKAN: Ganti assertExists menjadi expect()->toBeTrue() ---
        // VS Code mengenali fungsi exists() standar, jadi tidak akan merah.
        expect(Storage::disk('public')->exists($manufacturer->image))->toBeTrue();
    });

    // --- 4. TEST VALIDASI ---
    test('validasi gagal jika url atau email tidak valid', function () {
        Livewire::test(ManufacturerManager::class)
            ->call('create')
            ->set('name', 'Lenovo')
            ->set('url', 'bukan-url')
            ->set('support_email', 'bukan-email')
            ->call('store')
            ->assertHasErrors(['url', 'support_email']);
    });

    test('validasi gagal jika file bukan gambar', function () {
        Storage::fake('public');
        
        // Menggunakan Size Besar untuk memancing error tanpa crash preview PDF
        $file = UploadedFile::fake()->image('logo_besar.jpg')->size(5000); // 5MB

        Livewire::test(ManufacturerManager::class)
            ->call('create')
            ->set('name', 'HP')
            ->set('newImage', $file)
            ->call('store')
            ->assertHasErrors(['newImage']); 
    });

    // --- 5. TEST AUTO FORMAT (Title Case) ---
    test('nama otomatis diubah menjadi title case', function () {
        Livewire::test(ManufacturerManager::class)
            ->call('create')
            ->set('name', 'acer indonesia') 
            ->call('store');

        assertDatabaseHas('manufacturers', [
            'name' => 'Acer Indonesia'
        ]);
    });

    // --- 6. TEST UPDATE & GANTI GAMBAR ---
    test('bisa update data dan mengganti gambar lama', function () {
        Storage::fake('public');

        // Setup Data Awal
        $oldFile = UploadedFile::fake()->image('old.png');
        $oldPath = $oldFile->store('manufacturers', 'public');
        
        $manufacturer = Manufacturer::factory()->create([
            'name' => 'Samsung',
            'image' => $oldPath
        ]);

        // Setup Gambar Baru
        $newFile = UploadedFile::fake()->image('new.png');

        Livewire::test(ManufacturerManager::class)
            ->call('edit', $manufacturer->id)
            ->assertSet('oldImage', $oldPath)
            ->set('name', 'Samsung Electronics')
            ->set('newImage', $newFile)
            ->call('store');

        $manufacturer->refresh();

        // --- PERBAIKAN STORAGE ASSERTION ---
        // Gambar lama harus hilang (exists -> False)
        expect(Storage::disk('public')->exists($oldPath))->toBeFalse();
        
        // Gambar baru harus ada (exists -> True)
        expect(Storage::disk('public')->exists($manufacturer->image))->toBeTrue();
        
        // Cek Database
        assertDatabaseHas('manufacturers', [
            'id' => $manufacturer->id,
            'name' => 'Samsung Electronics',
        ]);
    });

    // --- 7. TEST DELETE (CLEANUP FILE) ---
    test('menghapus data juga menghapus file gambar fisiknya', function () {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('logo-hapus.jpg');
        $path = $file->store('manufacturers', 'public');

        $manufacturer = Manufacturer::factory()->create([
            'image' => $path
        ]);

        // Pastikan file ada dulu
        expect(Storage::disk('public')->exists($path))->toBeTrue();

        // Hapus
        Livewire::test(ManufacturerManager::class)
            ->call('confirmDelete', $manufacturer->id)
            ->call('delete');

        // Cek DB Hilang
        assertDatabaseMissing('manufacturers', ['id' => $manufacturer->id]);

        // --- PERBAIKAN STORAGE ASSERTION ---
        // Cek File Fisik Hilang
        expect(Storage::disk('public')->exists($path))->toBeFalse();
    });

    // --- 8. TEST SEARCH ---
    test('pencarian berfungsi', function () {
        Manufacturer::factory()->create(['name' => 'Toshiba']);
        Manufacturer::factory()->create(['name' => 'Zyrex']);

        Livewire::test(ManufacturerManager::class)
            ->set('search', 'Toshiba')
            ->assertSee('Toshiba')
            ->assertDontSee('Zyrex');
    });

});