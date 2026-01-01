<?php

namespace App\Livewire\Admin\Master;

use App\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Illuminate\Validation\Rule;

/**
 * Class EmployeeManager
 * Komponen Livewire untuk mengelola Data Master Karyawan.
 * * Fitur Utama:
 * 1. CRUD Karyawan.
 * 2. Validasi Unik (Mencegah duplikasi Nama & Email).
 * 3. Pencarian Real-time.
 */
#[Layout('components.layouts.admin')]
#[Title('Kelola Karyawan')]
class EmployeeManager extends Component
{
    use WithPagination;

    // ==========================================
    // PROPERTIES
    // ==========================================

    /** @var int|null ID Karyawan yang sedang diedit/dihapus */
    public $employeeId;

    /** @var string Nama Karyawan */
    public $name;

    /** @var string Email Karyawan */
    public $email;

    // --- Pencarian ---
    #[Url(except: '')]
    public $search = '';

    // --- UI States ---
    public $showFormModal = false;
    public $showDeleteModal = false;
    public $isEditMode = false;

    // ==========================================
    // VALIDASI (ANTI DUPLIKASI)
    // ==========================================

    protected function rules()
    {
        return [
            'name' => [
                'required', 
                'string', 
                'max:255',
                // Cek unik di tabel employees kolom name, abaikan ID ini jika sedang Edit
                Rule::unique('employees', 'name')->ignore($this->employeeId),
            ],
            'email' => [
                'required', 
                'email', 
                'max:255',
                // Cek unik di tabel employees kolom email, abaikan ID ini jika sedang Edit
                Rule::unique('employees', 'email')->ignore($this->employeeId),
            ],
        ];
    }

    protected $messages = [
        'name.required' => 'Nama karyawan wajib diisi.',
        'name.unique' => 'Nama karyawan ini sudah terdaftar.', // Pesan error duplikasi
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.unique' => 'Email ini sudah digunakan oleh karyawan lain.', // Pesan error duplikasi
    ];

    // ==========================================
    // LIFECYCLE HOOKS
    // ==========================================

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // ==========================================
    // RENDER VIEW
    // ==========================================

    public function render()
    {
        $employees = Employee::query()
            ->when($this->search, function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.master.employee-manager', [
            'employees' => $employees
        ]);
    }

    // ==========================================
    // LOGIKA CRUD
    // ==========================================

    public function resetInputFields()
    {
        $this->name = '';
        $this->email = '';
        $this->employeeId = null;
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
        
        $employee = Employee::findOrFail($id);

        $this->employeeId = $id;
        $this->name = $employee->name;
        $this->email = $employee->email;
        
        $this->isEditMode = true;
        $this->showFormModal = true;
    }

    public function store()
    {
        // Jalankan validasi (Rules di atas akan mengecek duplikasi)
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if ($this->isEditMode && $this->employeeId) {
            $employee = Employee::findOrFail($this->employeeId);
            $employee->update($data);
            session()->flash('message', 'Data karyawan berhasil diperbarui.');
        } else {
            Employee::create($data);
            session()->flash('message', 'Karyawan baru berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->employeeId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->employeeId) {
            try {
                $employee = Employee::findOrFail($this->employeeId);
                $employee->delete();
                session()->flash('message', 'Data karyawan berhasil dihapus.');
            } catch (\Exception $e) {
                session()->flash('error', 'Gagal menghapus data. Terjadi kesalahan sistem.');
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