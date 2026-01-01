<?php

namespace Tests\Feature\Livewire\Admin\Master;

use App\Livewire\Admin\Master\LocationManager;
use App\Models\Location;
use Livewire\Livewire;

// --- IMPORT GLOBAL FUNCTION BIAR GA MERAH DI VSCODE ---
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Location Manager Component', function () {

    // --- 1. RENDER & AKSES ---
    test('halaman bisa diakses', function () {
        Livewire::test(LocationManager::class)
            ->assertStatus(200)
            // Pastikan teks ini sesuai dengan Judul H1 di file blade kamu
            ->assertSee('Manajemen Lokasi');
    });

    // --- 2. CREATE (TAMBAH DATA) ---
    test('bisa membuat lokasi root (tanpa parent)', function () {
        Livewire::test(LocationManager::class)
            ->call('create')
            ->set('name', 'Gedung Serbaguna')
            ->call('store')
            ->assertHasNoErrors()
            ->assertSet('showFormModal', false);

        // CLEAN CODE: Tanpa $this->
        assertDatabaseHas('locations', [
            'name' => 'Gedung Serbaguna',
            'parent_location_id' => null
        ]);
    });

    test('bisa membuat sub-lokasi (dengan parent)', function () {
        $parent = Location::factory()->create(['name' => 'Gedung A']);

        Livewire::test(LocationManager::class)
            ->call('create')
            ->set('name', 'Lantai 1')
            ->set('parent_location_id', $parent->id) // Set Parent ID
            ->call('store');

        // CLEAN CODE: Tanpa $this->
        assertDatabaseHas('locations', [
            'name' => 'Lantai 1',
            'parent_location_id' => $parent->id
        ]);
    });

    // --- 3. VALIDASI KOMPLEKS (SOP UNIK BERSYARAT) ---
    test('gagal membuat nama duplikat di dalam gedung (parent) yang sama', function () {
        $parent = Location::factory()->create(['name' => 'Gedung A']);
        // Sudah ada "Toilet" di Gedung A
        Location::factory()->create(['name' => 'Toilet', 'parent_location_id' => $parent->id]);

        Livewire::test(LocationManager::class)
            ->call('create')
            ->set('parent_location_id', $parent->id)
            ->set('name', 'Toilet') // Coba buat "Toilet" lagi di Gedung A
            ->call('store')
            ->assertHasErrors(['name']); // Harus Error
    });

    test('sukses membuat nama sama asalkan beda gedung (parent)', function () {
        $gedungA = Location::factory()->create(['name' => 'Gedung A']);
        $gedungB = Location::factory()->create(['name' => 'Gedung B']);
        
        // Sudah ada "Toilet" di Gedung A
        Location::factory()->create(['name' => 'Toilet', 'parent_location_id' => $gedungA->id]);

        // Kita buat "Toilet" di Gedung B -> Harusnya Boleh
        Livewire::test(LocationManager::class)
            ->call('create')
            ->set('parent_location_id', $gedungB->id) 
            ->set('name', 'Toilet') 
            ->call('store')
            ->assertHasNoErrors(); // Tidak boleh Error
    });

    // --- 4. UI HELPER (SELECT PARENT) ---
    test('fungsi selectParent mengisi data dengan benar', function () {
        Livewire::test(LocationManager::class)
            ->call('selectParent', 99, 'Gedung X') // Simulasi klik dropdown
            ->assertSet('parent_location_id', 99)
            ->assertSet('parentSearch', 'Gedung X');
    });

    test('fungsi clearParent mereset data parent', function () {
        Livewire::test(LocationManager::class)
            ->set('parent_location_id', 99)
            ->set('parentSearch', 'Gedung X')
            ->call('clearParent') // Simulasi klik tombol X
            ->assertSet('parent_location_id', null)
            ->assertSet('parentSearch', '');
    });

    test('ketika mengetik manual di search parent, id parent ter-reset', function () {
        // Ini fitur safety agar user tidak mengetik nama sembarangan tanpa memilih dropdown
        Livewire::test(LocationManager::class)
            ->set('parent_location_id', 99)
            ->set('parentSearch', 'Gedung') // User mengetik...
            ->assertSet('parent_location_id', null); // ID harus hilang
    });

    // --- 5. EDIT DATA ---
    test('modal edit terbuka dan memuat data parent dengan benar', function () {
        $parent = Location::factory()->create(['name' => 'Kantor Pusat']);
        $child = Location::factory()->create(['name' => 'Ruang Rapat', 'parent_location_id' => $parent->id]);

        Livewire::test(LocationManager::class)
            ->call('edit', $child->id)
            ->assertSet('isEditMode', true)
            ->assertSet('locationId', $child->id)
            ->assertSet('name', 'Ruang Rapat')
            ->assertSet('parent_location_id', $parent->id)
            ->assertSet('parentSearch', 'Kantor Pusat'); // Input text harus terisi nama parent
    });

    // --- 6. DELETE (SOP INTEGRITAS DATA) ---
    test('berhasil menghapus lokasi yang tidak punya anak (leaf node)', function () {
        $location = Location::factory()->create(['name' => 'Gudang Terpencil']);

        Livewire::test(LocationManager::class)
            ->call('confirmDelete', $location->id)
            ->call('delete');

        // CLEAN CODE: Tanpa $this->
        assertDatabaseMissing('locations', ['id' => $location->id]);
    });

    test('GAGAL menghapus lokasi jika masih punya sub-lokasi (anak)', function () {
        $parent = Location::factory()->create(['name' => 'Lantai Utama']);
        // Buat anak di dalamnya
        Location::factory()->create(['parent_location_id' => $parent->id]);

        Livewire::test(LocationManager::class)
            ->call('confirmDelete', $parent->id)
            ->call('delete')
            // Cek apakah muncul pesan error flash
            ->assertSee('Gagal menghapus! Lokasi ini memiliki sub-lokasi');

        // Pastikan Parent MASIH ADA di database (Gagal hapus)
        // CLEAN CODE: Tanpa $this->
        assertDatabaseHas('locations', ['id' => $parent->id]);
    });

    // --- 7. SEARCH TABLE ---
    test('pencarian bisa menemukan lokasi berdasarkan nama parentnya', function () {
        $parent = Location::factory()->create(['name' => 'Gedung Z']);
        $child = Location::factory()->create(['name' => 'Kantin', 'parent_location_id' => $parent->id]);
        
        // Kita cari "Gedung Z", harusnya "Kantin" muncul di hasil karena dia ada di Gedung Z
        Livewire::test(LocationManager::class)
            ->set('search', 'Gedung Z')
            ->assertSee('Kantin');
    });

});