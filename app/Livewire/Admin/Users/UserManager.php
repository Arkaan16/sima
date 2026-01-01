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
 * Lokasi: app/Livewire/Admin/Users/UserManager.php
 */
#[Layout('components.layouts.admin')]
#[Title('Kelola Data Pengguna')]
class UserManager extends Component
{
    use WithPagination;

    // Tema Pagination Tailwind
    protected $paginationTheme = 'tailwind';

    // ==========================================
    // PROPERTIES
    // ==========================================

    public $userId;
    public $name;
    public $email;
    public $password;
    
    // Default role sesuai migration database Anda
    public $role = 'employee'; 

    // Opsi Role (Sesuai Enum di Database)
    // Key (kiri) = Value di database
    // Value (kanan) = Label yang tampil di layar
    public $roles = [
        'admin'    => 'Administrator',
        'employee' => 'Karyawan (Employee)',
    ];

    // State UI
    public $search = '';
    public $showFormModal = false;
    public $showDeleteModal = false;
    public $isEditMode = false;

    // ==========================================
    // VALIDASI
    // ==========================================

    protected function rules()
    {
        // Aturan validasi dasar
        $rules = [
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,employee', // Wajib salah satu dari enum
        ];

        // Validasi Email:
        // Unik di tabel users, KECUALI untuk user yang sedang diedit (ignore id)
        $rules['email'] = [
            'required', 
            'email', 
            'max:255', 
            Rule::unique('users', 'email')->ignore($this->userId)
        ];

        // Validasi Password:
        // Create: Wajib. Edit: Boleh kosong (opsional).
        if ($this->isEditMode) {
            $rules['password'] = 'nullable|string|min:8';
        } else {
            $rules['password'] = 'required|string|min:8';
        }

        return $rules;
    }

    protected $messages = [
        'name.required' => 'Nama wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.unique' => 'Email ini sudah terdaftar.',
        'password.required' => 'Password wajib diisi untuk user baru.',
        'password.min' => 'Password minimal 8 karakter.',
        'role.required' => 'Silakan pilih role.',
    ];

    // ==========================================
    // LOGIKA SEARCH
    // ==========================================

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // ==========================================
    // RENDER & CRUD
    // ==========================================

    public function render()
    {
        $users = User::query()
            ->when($this->search, function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            // Urutkan: Admin di atas, lalu Employee terbaru
            ->orderBy('role', 'asc') 
            ->latest()
            ->paginate(10);

        return view('livewire.admin.users.user-manager', [
            'users' => $users
        ]);
    }

    public function resetInputFields()
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'employee'; // Reset ke default
        $this->isEditMode = false;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetInputFields();
        $this->showFormModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $user = User::findOrFail($id);

        $this->userId = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->password = ''; // Kosongkan demi keamanan

        $this->isEditMode = true;
        $this->showFormModal = true;
    }

    public function store()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];

        // Logic Hashing Password
        if ($this->isEditMode) {
            // Jika Edit: Hanya update password kalau user mengisi input
            if (!empty($this->password)) {
                $data['password'] = Hash::make($this->password);
            }
            User::findOrFail($this->userId)->update($data);
            session()->flash('message', 'Data pengguna berhasil diperbarui.');
        } else {
            // Jika Create: Password wajib di-hash
            $data['password'] = Hash::make($this->password);
            User::create($data);
            session()->flash('message', 'Pengguna baru berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->userId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->userId) {
            // Security: Cegah user menghapus akunnya sendiri saat login
            if ($this->userId == Auth::id()) {
                session()->flash('error', 'Anda tidak dapat menghapus akun sendiri yang sedang aktif.');
            } else {
                User::findOrFail($this->userId)->delete();
                session()->flash('message', 'Pengguna berhasil dihapus.');
            }
        }
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->showFormModal = false;
        $this->showDeleteModal = false;
        $this->resetInputFields();
    }
}