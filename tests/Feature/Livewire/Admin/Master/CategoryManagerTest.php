<?php

namespace Tests\Feature\Livewire\Admin\Master;

use App\Livewire\Admin\Master\CategoryManager;
use App\Models\Category;
use Livewire\Livewire;

// --- IMPORT GLOBAL FUNCTION BIAR GA MERAH DI VSCODE ---
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Category Manager Component', function () {

    // --- 1. RENDER ---
    test('halaman bisa diakses dan komponen dirender', function () {
        Livewire::test(CategoryManager::class)
            ->assertStatus(200)
            // Pastikan teks ini sesuai dengan <h1 ...> di file Blade kamu
            // Kalau di blade "Manajemen Kategori", di sini juga harus sama.
            ->assertSee('Manajemen Kategori'); 
    });

    // --- 2. CREATE ---
    test('bisa membuka modal tambah data', function () {
        Livewire::test(CategoryManager::class)
            ->call('create')
            ->assertSet('showFormModal', true)
            ->assertSet('isEditMode', false)
            ->assertSet('name', '');
    });

    test('bisa menyimpan kategori baru yang valid', function () {
        Livewire::test(CategoryManager::class)
            ->call('create')
            ->set('name', 'Komputer')
            ->call('store')
            ->assertHasNoErrors()
            ->assertSet('showFormModal', false);

        // CLEAN CODE: Pakai fungsi global, tanpa $this->
        assertDatabaseHas('categories', [
            'name' => 'Komputer',
        ]);
    });

    test('gagal menyimpan jika nama kosong (Validasi Required)', function () {
        Livewire::test(CategoryManager::class)
            ->call('create')
            ->set('name', '')
            ->call('store')
            ->assertHasErrors(['name' => 'required']);
    });

    test('gagal menyimpan jika nama duplikat (Validasi Unique)', function () {
        Category::factory()->create(['name' => 'Laptop']);

        Livewire::test(CategoryManager::class)
            ->call('create')
            ->set('name', 'Laptop')
            ->call('store')
            ->assertHasErrors(['name' => 'unique']);
    });

    // --- 3. UPDATE ---
    test('bisa membuka modal edit dan data terisi', function () {
        $category = Category::factory()->create(['name' => 'Mouse']);

        Livewire::test(CategoryManager::class)
            ->call('edit', $category->id)
            ->assertSet('showFormModal', true)
            ->assertSet('isEditMode', true)
            ->assertSet('categoryId', $category->id)
            ->assertSet('name', 'Mouse');
    });

    test('bisa mengupdate kategori', function () {
        $category = Category::factory()->create(['name' => 'Mouse']);

        Livewire::test(CategoryManager::class)
            ->call('edit', $category->id)
            ->set('name', 'Mouse Gaming')
            ->call('store')
            ->assertHasNoErrors()
            ->assertSet('showFormModal', false);

        // CLEAN CODE: Tanpa $this->
        assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Mouse Gaming',
        ]);
    });

    test('bisa update dengan nama sendiri (Ignore Unique ID)', function () {
        $category = Category::factory()->create(['name' => 'Laptop']);

        Livewire::test(CategoryManager::class)
            ->call('edit', $category->id)
            ->set('name', 'Laptop')
            ->call('store')
            ->assertHasNoErrors();
    });

    // --- 4. DELETE ---
    test('bisa membuka konfirmasi hapus', function () {
        $category = Category::factory()->create();

        Livewire::test(CategoryManager::class)
            ->call('confirmDelete', $category->id)
            ->assertSet('showDeleteModal', true)
            ->assertSet('categoryId', $category->id);
    });

    test('bisa menghapus kategori', function () {
        $category = Category::factory()->create(['name' => 'Sampah']);

        Livewire::test(CategoryManager::class)
            ->call('confirmDelete', $category->id)
            ->call('delete');

        // CLEAN CODE: Tanpa $this->
        assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    });
    
    // --- 5. SEARCH & PAGINATION ---
    test('pencarian berfungsi memfilter data', function () {
        Category::factory()->create(['name' => 'Iphone 15']);
        Category::factory()->create(['name' => 'Samsung S24']);

        Livewire::test(CategoryManager::class)
            ->set('search', 'Samsung')
            ->assertSee('Samsung S24')
            ->assertDontSee('Iphone 15');
    });

    test('pagination ter-reset saat melakukan pencarian', function () {
        Livewire::test(CategoryManager::class)
            ->set('paginators.page', 2)
            ->set('search', 'Cari')
            ->assertSet('paginators.page', 1);
    });
});