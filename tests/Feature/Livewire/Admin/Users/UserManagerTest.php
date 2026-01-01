<?php

namespace Tests\Feature\Livewire\Admin\Users;

use App\Livewire\Admin\Users\UserManager;
use App\Models\User;
use Livewire\Livewire;
use Illuminate\Support\Facades\Hash;

// --- IMPORT GLOBAL FUNCTION ---
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('User Manager Component', function () {

    // ==========================================
    // 1. RENDER & AKSES
    // ==========================================
    
    test('halaman bisa diakses oleh admin dan komponen dirender', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        actingAs($admin);
        
        Livewire::test(UserManager::class)
            ->assertStatus(200)
            ->assertSee('Manajemen Pengguna');
    });

    // TEST TAMBAHAN: Security Authorization
    // Jika test ini MERAH (Gagal), berarti Anda belum pasang Middleware/Gate di Component/Route.
    test('karyawan biasa (non-admin) DILARANG mengakses halaman admin via Route', function () {
        $employee = User::factory()->create(['role' => 'employee']);

        actingAs($employee)
            ->get(route('admin.users')) // <--- Panggil Rute Asli, bukan Component langsung
            ->assertForbidden(); // Cek status 403
    });

    // ==========================================
    // 2. CREATE (TAMBAH DATA)
    // ==========================================

    test('bisa membuka modal tambah user', function () {
        Livewire::test(UserManager::class)
            ->call('create')
            ->assertSet('showFormModal', true)
            ->assertSet('isEditMode', false)
            ->assertSet('name', '')
            ->assertSet('role', 'employee');
    });

    test('bisa menyimpan user baru yang valid', function () {
        Livewire::test(UserManager::class)
            ->call('create')
            ->set('name', 'Budi Baru')
            ->set('email', 'budi@test.com')
            ->set('role', 'admin')
            ->set('password', 'rahasia123')
            ->call('store')
            ->assertHasNoErrors()
            ->assertSet('showFormModal', false)
            ->assertSee('Pengguna baru berhasil ditambahkan');

        assertDatabaseHas('users', [
            'name' => 'Budi Baru',
            'email' => 'budi@test.com',
            'role' => 'admin',
        ]);
    });

    test('gagal menyimpan jika validasi error (Required Fields)', function () {
        Livewire::test(UserManager::class)
            ->call('create')
            ->set('name', '')
            ->set('email', '')
            ->set('password', '')
            ->call('store')
            ->assertHasErrors([
                'name' => 'required',
                'email' => 'required',
                'password' => 'required',
            ]);
    });

    test('gagal menyimpan jika email duplikat', function () {
        User::factory()->create(['email' => 'ada@test.com']);

        Livewire::test(UserManager::class)
            ->call('create')
            ->set('name', 'User B')
            ->set('email', 'ada@test.com')
            ->set('password', 'password123')
            ->call('store')
            ->assertHasErrors(['email' => 'unique']);
    });

    test('gagal jika password kurang dari 8 karakter', function () {
        Livewire::test(UserManager::class)
            ->call('create')
            ->set('name', 'User Pendek')
            ->set('email', 'pendek@test.com')
            ->set('password', '123')
            ->call('store')
            ->assertHasErrors(['password' => 'min']);
    });

    // TEST TAMBAHAN: Validasi Enum Role
    test('gagal menyimpan jika role dimanipulasi (value tidak valid)', function () {
        Livewire::test(UserManager::class)
            ->call('create')
            ->set('name', 'Hacker')
            ->set('email', 'hack@test.com')
            ->set('password', 'password123')
            ->set('role', 'super-god-mode') // Value ngawur
            ->call('store')
            ->assertHasErrors(['role' => 'in']); // Harus error validation rule "in:admin,employee"
    });

    // ==========================================
    // 3. UPDATE (EDIT DATA)
    // ==========================================

    test('bisa membuka modal edit dan data terisi', function () {
        $user = User::factory()->create();

        Livewire::test(UserManager::class)
            ->call('edit', $user->id)
            ->assertSet('showFormModal', true)
            ->assertSet('isEditMode', true)
            ->assertSet('userId', $user->id)
            ->assertSet('name', $user->name)
            ->assertSet('email', $user->email)
            ->assertSet('password', '');
    });

    test('bisa update data user TANPA mengubah password', function () {
        $user = User::factory()->create(['name' => 'Nama Lama']);
        $oldPasswordHash = $user->password;

        Livewire::test(UserManager::class)
            ->call('edit', $user->id)
            ->set('name', 'Nama Baru')
            ->set('password', '')
            ->call('store')
            ->assertHasNoErrors();

        assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Baru',
        ]);

        $updatedUser = User::find($user->id);
        expect($updatedUser->password)->toBe($oldPasswordHash);
    });

    test('bisa update data user DAN mengubah password', function () {
        $user = User::factory()->create(['password' => Hash::make('lama123')]);

        Livewire::test(UserManager::class)
            ->call('edit', $user->id)
            ->set('password', 'baru12345')
            ->call('store');

        $updatedUser = User::find($user->id);
        expect(Hash::check('baru12345', $updatedUser->password))->toBeTrue();
    });

    test('validasi unique email mengabaikan diri sendiri saat edit', function () {
        $user = User::factory()->create(['email' => 'saya@test.com']);

        Livewire::test(UserManager::class)
            ->call('edit', $user->id)
            ->set('email', 'saya@test.com') 
            ->call('store')
            ->assertHasNoErrors();
    });

    // ==========================================
    // 4. RESET & UI UX
    // ==========================================

    // TEST TAMBAHAN: Reset Form
    test('input form di-reset bersih saat modal ditutup', function () {
        Livewire::test(UserManager::class)
            ->call('create')
            ->set('name', 'Isian Yang Batal Disimpan')
            ->set('email', 'batal@test.com')
            ->call('closeModal') // Klik tombol Batal
            ->assertSet('showFormModal', false)
            ->assertSet('name', '') // Harus kembali kosong
            ->assertSet('email', '');
    });

    // ==========================================
    // 5. DELETE (HAPUS DATA)
    // ==========================================

    test('bisa menghapus user lain', function () {
        $admin = User::factory()->create();
        $targetUser = User::factory()->create();

        actingAs($admin);
        
        Livewire::test(UserManager::class)
            ->call('confirmDelete', $targetUser->id)
            ->assertSet('showDeleteModal', true)
            ->call('delete')
            ->assertSet('showDeleteModal', false);

        assertDatabaseMissing('users', ['id' => $targetUser->id]);
    });

    test('TIDAK BISA menghapus akun sendiri (Security)', function () {
        $admin = User::factory()->create();

        actingAs($admin);
        
        Livewire::test(UserManager::class)
            ->call('confirmDelete', $admin->id)
            ->call('delete')
            ->assertSee('Anda tidak dapat menghapus akun sendiri');

        assertDatabaseHas('users', ['id' => $admin->id]);
    });

    // ==========================================
    // 6. SEARCH & PAGINATION
    // ==========================================

    test('pencarian berfungsi memfilter nama atau email', function () {
        User::factory()->create(['name' => 'Joko Widodo', 'email' => 'jokowi@indo.com']);
        User::factory()->create(['name' => 'Prabowo', 'email' => 'prabowo@indo.com']);

        Livewire::test(UserManager::class)
            ->set('search', 'Joko')
            ->assertSee('Joko Widodo')
            ->assertDontSee('Prabowo');

        Livewire::test(UserManager::class)
            ->set('search', 'prabowo@indo.com')
            ->assertSee('Prabowo')
            ->assertDontSee('Joko Widodo');
    });

    test('pagination reset ke halaman 1 saat mengetik search', function () {
        Livewire::test(UserManager::class)
            ->set('paginators.page', 5)
            ->set('search', 'A')
            ->assertSet('paginators.page', 1);
    });

});