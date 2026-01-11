<?php

namespace Tests\Feature\Livewire\Admin\Master;

use App\Livewire\Admin\Master\EmployeeManager;
use App\Models\Employee;
use Livewire\Livewire;

// Import Global Functions (Agar VS Code Hijau)
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Employee Manager Component', function () {

    // --- 1. RENDER & AKSES ---
    test('halaman render dengan sukses', function () {
        Livewire::test(EmployeeManager::class)
            ->assertStatus(200)
            ->assertSee('Kelola Karyawan');
    });

    // --- 2. CREATE (Happy Path) ---
    test('bisa menambah karyawan baru', function () {
        Livewire::test(EmployeeManager::class)
            ->call('create')
            ->set('name', 'Budi Santoso')
            ->set('email', 'budi@example.com')
            ->call('store')
            ->assertHasNoErrors()
            ->assertSet('showFormModal', false);

        assertDatabaseHas('employees', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
        ]);
    });

    // --- 3. VALIDASI INPUT (Unhappy Path) ---
    test('validasi gagal jika input kosong', function () {
        Livewire::test(EmployeeManager::class)
            ->call('create')
            ->set('name', '') // Kosong
            ->set('email', '') // Kosong
            ->call('store')
            ->assertHasErrors(['name', 'email']);
    });

    test('validasi gagal jika format email salah', function () {
        Livewire::test(EmployeeManager::class)
            ->call('create')
            ->set('name', 'Andi')
            ->set('email', 'bukan-email') // Salah
            ->call('store')
            ->assertHasErrors(['email']);
    });

    // --- 4. VALIDASI UNIK KOMPLEKS ---
    test('gagal membuat data jika email sudah terdaftar', function () {
        // Buat data A
        Employee::factory()->create(['email' => 'ada@example.com']);

        // Coba buat data baru dengan email sama
        Livewire::test(EmployeeManager::class)
            ->call('create')
            ->set('name', 'Orang Baru')
            ->set('email', 'ada@example.com') // Duplikat
            ->call('store')
            ->assertHasErrors(['email']);
    });

    test('gagal membuat data jika nama sudah terdaftar', function () {
        Employee::factory()->create(['name' => 'Siti Aminah']);

        Livewire::test(EmployeeManager::class)
            ->call('create')
            ->set('name', 'Siti Aminah') // Duplikat
            ->set('email', 'baru@example.com')
            ->call('store')
            ->assertHasErrors(['name']);
    });

    test('validasi unik mengabaikan diri sendiri saat update (Edit)', function () {
        // Kasus: Edit "Budi", tidak ubah email, lalu simpan. Harusnya LOLOS.
        $employee = Employee::factory()->create([
            'name' => 'Budi',
            'email' => 'budi@example.com'
        ]);

        Livewire::test(EmployeeManager::class)
            ->call('edit', $employee->id)
            ->set('name', 'Budi') // Nama tetap
            ->set('email', 'budi@example.com') // Email tetap
            ->call('store')
            ->assertHasNoErrors();
    });

    test('validasi unik tetap memblokir duplikat punya orang lain saat update', function () {
        // Kasus: Edit "Budi", ubah email jadi emailnya "Ani". Harusnya ERROR.
        $budi = Employee::factory()->create(['email' => 'budi@example.com']);
        $ani = Employee::factory()->create(['email' => 'ani@example.com']);

        Livewire::test(EmployeeManager::class)
            ->call('edit', $budi->id)
            ->set('email', 'ani@example.com') // Pakai email Ani
            ->call('store')
            ->assertHasErrors(['email']);
    });

    // --- 5. UPDATE ---
    test('bisa mengupdate data karyawan', function () {
        $employee = Employee::factory()->create([
            'name' => 'Lama',
            'email' => 'lama@example.com'
        ]);

        Livewire::test(EmployeeManager::class)
            ->call('edit', $employee->id)
            ->set('name', 'Baru')
            ->set('email', 'baru@example.com')
            ->call('store')
            ->assertHasNoErrors();

        assertDatabaseHas('employees', [
            'id' => $employee->id,
            'name' => 'Baru',
            'email' => 'baru@example.com'
        ]);
    });

    // --- 6. DELETE ---
    test('bisa menghapus karyawan', function () {
        $employee = Employee::factory()->create();

        Livewire::test(EmployeeManager::class)
            ->call('confirmDelete', $employee->id)
            ->assertSet('showDeleteModal', true)
            ->call('delete');

        assertDatabaseMissing('employees', ['id' => $employee->id]);
    });

    // --- 7. PENCARIAN & PAGINATION ---
    test('pencarian berfungsi berdasarkan nama atau email', function () {
        Employee::factory()->create(['name' => 'Joko', 'email' => 'joko@test.com']);
        Employee::factory()->create(['name' => 'Bambang', 'email' => 'bambang@test.com']);

        // Cari Nama
        Livewire::test(EmployeeManager::class)
            ->set('search', 'Joko')
            ->assertSee('Joko')
            ->assertDontSee('Bambang');

        // Cari Email
        Livewire::test(EmployeeManager::class)
            ->set('search', 'bambang@test.com')
            ->assertSee('Bambang')
            ->assertDontSee('Joko');
    });

    test('pagination reset ke halaman 1 saat mengetik search', function () {
        Livewire::test(EmployeeManager::class)
            ->set('paginators.page', 5)
            ->set('search', 'Cari')
            ->assertSet('paginators.page', 1);
    });

    // --- 8. RESET STATE ---
    test('modal form reset bersih saat ditutup', function () {
        Livewire::test(EmployeeManager::class)
            ->call('create')
            ->set('name', 'Sampah')
            ->call('closeModal')
            ->assertSet('name', '') // Harus kosong lagi
            ->assertSet('showFormModal', false);
    });

});