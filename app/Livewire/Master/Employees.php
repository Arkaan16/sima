<?php

namespace App\Livewire\Master;

use App\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Illuminate\Validation\Rule;

#[Title('Kelola Karyawan')]
class Employees extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // ==========================================
    // PROPERTIES
    // ==========================================

    public $employeeId;
    public $name = '';
    public $email = '';

    // UI State
    public $isEditMode = false;
    public $deleteName = ''; // Untuk menampilkan nama di modal delete

    #[Url(except: '')]
    public $search = '';

    // ==========================================
    // VALIDATION
    // ==========================================

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
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

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // ==========================================
    // ACTIONS (Server-Side Logic)
    // ==========================================

    // 1. Persiapan Create
    public function create()
    {
        $this->resetInputFields();
        // Kirim perintah buka modal form
        $this->dispatch('open-modal-form');
    }

    // 2. Persiapan Edit
    public function edit($id)
    {
        $employee = Employee::find($id);
        if (!$employee) return;

        $this->employeeId = $employee->id;
        $this->name = $employee->name;
        $this->email = $employee->email;
        $this->isEditMode = true;

        $this->resetValidation(); // Bersihkan error lama
        
        // Kirim perintah buka modal form
        $this->dispatch('open-modal-form');
    }

    // 3. Persiapan Hapus
    public function confirmDelete($id)
    {
        $employee = Employee::find($id);
        if (!$employee) return;

        $this->employeeId = $employee->id;
        $this->deleteName = $employee->name;

        // Kirim perintah buka modal delete
        $this->dispatch('open-modal-delete');
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->email = '';
        $this->employeeId = null;
        $this->deleteName = '';
        $this->isEditMode = false;
        $this->resetErrorBag();
        $this->resetValidation();
    }

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
            $message = 'Data karyawan berhasil diperbarui.';
        } else {
            Employee::create($data);
            $message = 'Karyawan baru berhasil ditambahkan.';
        }

        session()->flash('message', $message);
        
        $this->dispatch('close-all-modals');
        $this->resetInputFields();
    }

    public function delete()
    {
        if (!$this->employeeId) return;

        try {
            $employee = Employee::findOrFail($this->employeeId);
            $employee->delete();
            session()->flash('message', 'Data karyawan berhasil dihapus.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus data. Data mungkin terhubung dengan data lain.');
        }
        
        // Tutup modal apapun hasilnya
        $this->dispatch('close-all-modals');
        $this->resetInputFields();
    }

    public function render()
    {
        $employees = Employee::query()
            ->when($this->search, function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.master.employees', [
            'employees' => $employees
        ]);
    }
}