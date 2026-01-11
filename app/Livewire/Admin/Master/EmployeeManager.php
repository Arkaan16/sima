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
 */
#[Layout('components.layouts.admin')]
#[Title('Kelola Karyawan')]
class EmployeeManager extends Component
{
    use WithPagination;

    // ==========================================
    // PROPERTIES
    // ==========================================

    public $employeeId;
    public $name;
    public $email;

    #[Url(except: '')]
    public $search = '';

    // UI States
    public $showFormModal = false;
    public $showDeleteModal = false;
    public $isEditMode = false;

    // ==========================================
    // VALIDASI
    // ==========================================

    protected function rules()
    {
        return [
            'name' => [
                'required', 
                'string', 
                'max:255',
                // PERUBAHAN: Validasi unique dihapus disini.
                // Nama boleh sama, yang penting email beda.
            ],
            'email' => [
                'required', 
                'email', 
                'max:255',
                // Email WAJIB unik. Abaikan ID saat mode Edit.
                Rule::unique('employees', 'email')->ignore($this->employeeId),
            ],
        ];
    }

    protected $messages = [
        'name.required' => 'Nama karyawan wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.unique' => 'Email ini sudah digunakan oleh karyawan lain.',
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
        // Validasi dijalankan sesuai rules() di atas
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
                
                // Karena di Model Employee sudah pakai 'use SoftDeletes',
                // perintah delete() ini otomatis melakukan Soft Delete (tidak hilang permanen).
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