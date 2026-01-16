<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

/**
 * Class UserManager
 *
 * Komponen Livewire untuk mengelola data master Pengguna (Users).
 * Menangani operasi CRUD (Create, Read, Update, Delete), manajemen hak akses (Role),
 * serta validasi keamanan untuk akun pengguna.
 *
 * @package App\Livewire\Admin\Users
 */
#[Layout('components.layouts.admin')]
#[Title('Kelola Data Pengguna')]
class UserManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // ==========================================
    // PROPERTIES
    // ==========================================

    /** @var int|null ID Pengguna untuk operasi Edit/Delete */
    public $userId;

    // --- State Form Input ---
    public $name;
    public $email;
    public $password;
    
    /** @var string Role default untuk user baru */
    public $role = 'employee'; 

    /** * Daftar opsi Role yang tersedia.
     * Format: ['value_database' => 'Label Tampilan']
     * @var array 
     */
    public $roles = [
        'admin'    => 'Administrator',
        'employee' => 'Karyawan (Employee)',
    ];

    // --- State UI Control ---
    public $search = '';
    public $showFormModal = false;
    public $showDeleteModal = false;
    public $isEditMode = false;

    // ==========================================
    // VALIDATION
    // ==========================================

    /**
     * Mendefinisikan aturan validasi dinamis.
     * Mengatur validasi password secara kondisional (Wajib saat Create, Opsional saat Edit).
     *
     * @return array
     */
    protected function rules()
    {
        // 1. Aturan Validasi Dasar
        $rules = [
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,employee',
        ];

        // 2. Validasi Email (Unik dengan pengecualian user saat ini)
        $rules['email'] = [
            'required', 
            'email', 
            'max:255', 
            Rule::unique('users', 'email')->ignore($this->userId)
        ];

        // 3. Validasi Password Kondisional
        if ($this->isEditMode) {
            // Saat Edit: Boleh kosong (berarti password tidak diganti)
            $rules['password'] = 'nullable|string|min:8';
        } else {
            // Saat Create: Wajib diisi
            $rules['password'] = 'required|string|min:8';
        }

        return $rules;
    }

    /**
     * Pesan error kustom untuk validasi.
     * @var array
     */
    protected $messages = [
        'name.required' => 'Nama wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.unique' => 'Email ini sudah terdaftar.',
        'password.required' => 'Password wajib diisi untuk user baru.',
        'password.min' => 'Password minimal 8 karakter.',
        'role.required' => 'Silakan pilih role.',
    ];

    // ==========================================
    // SEARCH LOGIC
    // ==========================================

    /**
     * Reset pagination ke halaman pertama setiap kali keyword pencarian berubah.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // ==========================================
    // RENDER & CRUD LOGIC
    // ==========================================

    /**
     * Merender tampilan tabel manajemen pengguna.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // 1. Inisialisasi Query
        $users = User::query()
            // 2. Terapkan Filter Pencarian (Nama atau Email)
            ->when($this->search, function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            // 3. Pengurutan & Pagination
            ->orderBy('role', 'asc') 
            ->latest()
            ->paginate(10);

        return view('livewire.admin.users.user-manager', [
            'users' => $users
        ]);
    }

    /**
     * Mengembalikan seluruh input form ke nilai awal (kosong/default).
     */
    public function resetInputFields()
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'employee';
        $this->isEditMode = false;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    /**
     * Menyiapkan modal untuk penambahan pengguna baru.
     */
    public function create()
    {
        $this->resetInputFields();
        $this->showFormModal = true;
    }

    /**
     * Menyiapkan modal edit dan mengisi form dengan data pengguna yang dipilih.
     *
     * @param int $id ID User yang akan diedit
     */
    public function edit($id)
    {
        $this->resetValidation();
        
        // 1. Ambil data dari database
        $user = User::findOrFail($id);

        // 2. Isi state properti
        $this->userId = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->password = ''; // Kosongkan password demi keamanan

        // 3. Tampilkan modal
        $this->isEditMode = true;
        $this->showFormModal = true;
    }

    /**
     * Menyimpan data pengguna ke database (Create atau Update).
     * Menangani hashing password secara otomatis.
     */
    public function store()
    {
        // 1. Validasi Input
        $this->validate();

        // 2. Persiapkan Data Dasar
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];

        // 3. Logika Hashing Password & Penyimpanan
        if ($this->isEditMode) {
            // Update: Hanya update password jika field diisi
            if (!empty($this->password)) {
                $data['password'] = Hash::make($this->password);
            }
            User::findOrFail($this->userId)->update($data);
            session()->flash('message', 'Data pengguna berhasil diperbarui.');
        } else {
            // Create: Password wajib di-hash
            $data['password'] = Hash::make($this->password);
            User::create($data);
            session()->flash('message', 'Pengguna baru berhasil ditambahkan.');
        }

        // 4. Tutup modal dan reset
        $this->closeModal();
    }

    /**
     * Menampilkan konfirmasi hapus data.
     *
     * @param int $id
     */
    public function confirmDelete($id)
    {
        $this->userId = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Menghapus data pengguna dari database.
     * Memiliki proteksi agar admin tidak dapat menghapus akunnya sendiri yang sedang aktif.
     */
    public function delete()
    {
        if ($this->userId) {
            // 1. Validasi Keamanan: Cegah penghapusan akun sendiri
            if ($this->userId == Auth::id()) {
                session()->flash('error', 'Anda tidak dapat menghapus akun sendiri yang sedang aktif.');
            } else {
                // 2. Eksekusi Hapus
                User::findOrFail($this->userId)->delete();
                session()->flash('message', 'Pengguna berhasil dihapus.');
            }
        }
        $this->closeModal();
    }

    /**
     * Menutup semua modal dan membersihkan state.
     */
    public function closeModal()
    {
        $this->showFormModal = false;
        $this->showDeleteModal = false;
        $this->resetInputFields();
    }
}