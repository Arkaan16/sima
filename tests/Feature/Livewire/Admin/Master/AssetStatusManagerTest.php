<?php

namespace Tests\Feature\Livewire\Admin\Master;

use App\Livewire\Admin\Master\AssetStatusManager;
use App\Models\AssetStatus;
use Livewire\Livewire;

// --- BAGIAN INI YANG MENGHILANGKAN GARIS MERAH DI VS CODE ---
// Kita mengimport fungsi assertion database secara langsung
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

// Menggunakan RefreshDatabase agar database bersih setiap kali test jalan
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Asset Status Manager Component', function () {

    // --- 1. TEST RENDER HALAMAN ---
    test('halaman bisa diakses dan judul muncul', function () {
        Livewire::test(AssetStatusManager::class)
            ->assertStatus(200)
            // Pastikan teks ini sesuai dengan Judul H1 di file blade kamu
            ->assertSee('Manajemen Status Aset'); 
    });

    // --- 2. TEST CREATE (TAMBAH DATA) ---
    test('bisa membuka modal tambah data', function () {
        Livewire::test(AssetStatusManager::class)
            ->call('create')
            ->assertSet('showFormModal', true)
            ->assertSet('isEditMode', false)
            ->assertSet('name', '');
    });

    test('bisa menyimpan status baru yang valid', function () {
        Livewire::test(AssetStatusManager::class)
            ->call('create')
            ->set('name', 'Rusak Ringan') // Input data
            ->call('store') // Simpan
            ->assertHasNoErrors()
            ->assertSet('showFormModal', false); // Modal tertutup

        // Cek Database (Tanpa $this->)
        assertDatabaseHas('asset_statuses', [
            'name' => 'Rusak Ringan'
        ]);
    });

    test('validasi gagal jika nama kosong', function () {
        Livewire::test(AssetStatusManager::class)
            ->call('create')
            ->set('name', '') // Kosongkan
            ->call('store')
            ->assertHasErrors(['name' => 'required']); // Harus error
    });

    test('validasi gagal jika nama duplikat', function () {
        // Buat data "Baik" duluan di database
        AssetStatus::factory()->create(['name' => 'Baik']);

        // Coba input "Baik" lagi
        Livewire::test(AssetStatusManager::class)
            ->call('create')
            ->set('name', 'Baik')
            ->call('store')
            ->assertHasErrors(['name' => 'unique']); // Harus error unik
    });

    // --- 3. TEST UPDATE (EDIT DATA) ---
    test('bisa membuka modal edit dan data terisi', function () {
        // Buat data dummy
        $status = AssetStatus::factory()->create(['name' => 'Hilang']);

        Livewire::test(AssetStatusManager::class)
            ->call('edit', $status->id)
            ->assertSet('showFormModal', true)
            ->assertSet('isEditMode', true)
            ->assertSet('assetStatusId', $status->id)
            ->assertSet('name', 'Hilang'); // Input harus terisi 'Hilang'
    });

    test('bisa mengupdate nama status', function () {
        $status = AssetStatus::factory()->create(['name' => 'Hilang']);

        Livewire::test(AssetStatusManager::class)
            ->call('edit', $status->id)
            ->set('name', 'Ditemukan') // Ubah nama
            ->call('store')
            ->assertHasNoErrors();

        // Cek Database pastikan berubah (Tanpa $this->)
        assertDatabaseHas('asset_statuses', [
            'id' => $status->id,
            'name' => 'Ditemukan'
        ]);
    });

    test('bisa simpan edit tanpa ganti nama (ignore unique rule)', function () {
        // Kasus: User buka edit, tidak ubah apa-apa, langsung simpan.
        // Seharusnya tidak error "Nama sudah dipakai".
        $status = AssetStatus::factory()->create(['name' => 'Baik']);

        Livewire::test(AssetStatusManager::class)
            ->call('edit', $status->id)
            ->set('name', 'Baik') // Nama tetap sama
            ->call('store')
            ->assertHasNoErrors(); // Harus sukses
    });

    // --- 4. TEST DELETE (HAPUS DATA) ---
    test('bisa menghapus status', function () {
        $status = AssetStatus::factory()->create(['name' => 'Salah Input']);

        Livewire::test(AssetStatusManager::class)
            ->call('confirmDelete', $status->id) // Buka modal konfirmasi
            ->assertSet('showDeleteModal', true)
            ->call('delete'); // Hapus

        // Cek Database pastikan hilang (Tanpa $this->)
        assertDatabaseMissing('asset_statuses', [
            'id' => $status->id
        ]);
    });

    // --- 5. TEST SEARCH & PAGINATION ---
    test('pencarian berfungsi memfilter data', function () {
        AssetStatus::factory()->create(['name' => 'Rusak Berat']);
        AssetStatus::factory()->create(['name' => 'Barang Baru']);

        Livewire::test(AssetStatusManager::class)
            ->set('search', 'Rusak') // Cari kata 'Rusak'
            ->assertSee('Rusak Berat') // Harusnya muncul
            ->assertDontSee('Barang Baru'); // Harusnya hilang
    });

    test('pagination reset ke halaman 1 saat mengetik search', function () {
        Livewire::test(AssetStatusManager::class)
            ->set('paginators.page', 5) // Pura-pura sedang di halaman 5
            ->set('search', 'A')        // User mengetik search
            ->assertSet('paginators.page', 1); // Otomatis reset ke hal 1
    });

});