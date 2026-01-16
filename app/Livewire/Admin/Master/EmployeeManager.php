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
 * * Komponen Livewire untuk mengelola data master Karyawan.
 * Menangani operasi CRUD (Create, Read, Update, Delete) serta fitur pencarian
 * dan paginasi untuk data karyawan.
 */
#[Layout('components.layouts.admin')]
#[Title('Kelola Karyawan')]
class EmployeeManager extends Component
{
    use WithPagination;

    // ==========================================
    // PROPERTIES
    // ==========================================

    /** @var int|null ID karyawan yang sedang diproses (Edit/Delete) */
    public $employeeId;

    /** @var string Nama karyawan (State Input) */
    public $name;

    /** @var string Email karyawan (State Input) */
    public $email;

    /** @var string Keyword pencarian data */
    #[Url(except: '')]
    public $search = '';

    // --- UI State Flags ---
    public $showFormModal = false;   
    public $showDeleteModal = false; 
    public $isEditMode = false;      

    // ==========================================
    // VALIDATION
    // ==========================================

    /**
     * Mendefinisikan aturan validasi input.
     * Mengatur validasi unik pada kolom email dengan pengecualian ID saat ini
     * untuk mencegah konflik saat proses update data.
     * @return array
     */
    protected function rules()
    {
        return [
            'name' => [
                'required', 
                'string', 
                'max:255',
            ],
            'email' => [
                'required', 
                'email', 
                'max:255',
                Rule::unique('employees', 'email')->ignore($this->employeeId),
            ],
        ];
    }

    /**
     * Pesan error kustom untuk validasi.
     * @var array
     */
    protected $messages = [
        'name.required' => 'Nama karyawan wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.unique' => 'Email ini sudah digunakan oleh karyawan lain.',
    ];

    // ==========================================
    // LIFECYCLE HOOKS
    // ==========================================

    /**
     * Reset pagination ke halaman 1 saat query pencarian berubah.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // ==========================================
    // RENDER VIEW
    // ==========================================

    /**
     * Merender view komponen.
     * Mengambil data karyawan dengan filter pencarian (nama atau email) dan paginasi.
     * @return \Illuminate\View\View
     */
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

    /**
     * Mereset seluruh state input dan validasi ke kondisi awal.
     */
    public function resetInputFields()
    {
        $this->name = '';
        $this->email = '';
        $this->employeeId = null;
        $this->isEditMode = false;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    /**
     * Inisialisasi modal untuk pembuatan data baru.
     */
    public function create()
    {
        $this->resetInputFields();
        $this->showFormModal = true;
    }

    /**
     * Inisialisasi modal untuk pengeditan data.
     * Mengisi form dengan data existing berdasarkan ID.
     * @param int $id
     */
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

    /**
     * Menyimpan data ke database (Create atau Update).
     * Logika penyimpanan ditentukan berdasarkan status flag $isEditMode.
     */
    public function store()
    {
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

    /**
     * Menampilkan modal konfirmasi hapus.
     * @param int $id
     */
    public function confirmDelete($id)
    {
        $this->employeeId = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Mengeksekusi penghapusan data karyawan dari database.
     */
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

    /**
     * Menutup semua modal dan membersihkan state form.
     */
    public function closeModal()
    {
        $this->showFormModal = false;
        $this->showDeleteModal = false;
        $this->resetInputFields();
    }
}