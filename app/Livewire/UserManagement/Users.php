<?php

namespace App\Livewire\UserManagement;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

#[Title('Kelola Data Pengguna')]
class Users extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind';

    // ==========================================
    // PROPERTIES
    // ==========================================

    public $userId;
    public $name = '';
    public $email = '';
    public $password = '';
    public $role = 'employee';

    // UI State
    public $isEditMode = false;
    public $deleteName = ''; // Untuk UI Modal Delete
    public $search = '';

    // Static Data
    public $roles = [
        'admin'    => 'Administrator',
        'employee' => 'Karyawan',
    ];

    // ==========================================
    // LIFECYCLE
    // ==========================================

    public function updatingSearch()
    {
        $this->resetPage();
    }

    protected function rules()
    {
        $rules = [
            'name'  => 'required|string|max:255',
            'role'  => 'required|in:admin,employee',
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->userId)
            ],
        ];

        if ($this->isEditMode) {
            $rules['password'] = 'nullable|string|min:8';
        } else {
            $rules['password'] = 'required|string|min:8';
        }

        return $rules;
    }

    // ==========================================
    // ACTIONS
    // ==========================================

    public function create()
    {
        $this->resetInputFields();
        $this->dispatch('open-modal-form');
    }

    public function edit($id)
    {
        $user = User::find($id);
        if (!$user) return;

        $this->userId     = $user->id;
        $this->name       = $user->name;
        $this->email      = $user->email;
        $this->role       = $user->role;
        $this->password   = ''; // Reset password field
        
        $this->isEditMode = true;
        $this->resetValidation();

        $this->dispatch('open-modal-form');
    }

    public function confirmDelete($id)
    {
        $user = User::find($id);
        if (!$user) return;

        $this->userId     = $user->id;
        $this->deleteName = $user->name;

        $this->dispatch('open-modal-delete');
    }

    public function store()
    {
        $this->validate();

        $data = [
            'name'  => $this->name,
            'email' => $this->email,
            'role'  => $this->role,
        ];

        if ($this->isEditMode) {
            // Update logic
            if (!empty($this->password)) {
                $data['password'] = Hash::make($this->password);
            }
            User::findOrFail($this->userId)->update($data);
            $message = 'Data pengguna berhasil diperbarui.';
        } else {
            // Create logic
            $data['password'] = Hash::make($this->password);
            User::create($data);
            $message = 'Pengguna baru berhasil ditambahkan.';
        }

        session()->flash('message', $message);
        
        $this->dispatch('close-all-modals');
        $this->resetInputFields();
    }

    public function delete()
    {
        if (!$this->userId) return;

        if ($this->userId == Auth::id()) {
            session()->flash('error', 'Anda tidak dapat menghapus akun sendiri.');
        } else {
            try {
                User::findOrFail($this->userId)->delete();
                session()->flash('message', 'Pengguna berhasil dihapus.');
            } catch (\Exception $e) {
                session()->flash('error', 'Gagal menghapus pengguna.');
            }
        }

        $this->dispatch('close-all-modals');
        $this->resetInputFields();
    }

    public function resetInputFields()
    {
        $this->userId     = null;
        $this->name       = '';
        $this->email      = '';
        $this->password   = '';
        $this->role       = 'employee';
        $this->deleteName = '';
        $this->isEditMode = false;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->orderBy('role', 'asc')
            ->latest()
            ->paginate(10);

        // Path view diperbarui ke folder user-management
        return view('livewire.user-management.users', [
            'users' => $users
        ]);
    }
}