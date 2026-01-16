<?php

namespace App\Livewire\Admin\Master;

use App\Models\AssetStatus;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule; 

/**
 * Class AssetStatusManager
 * * Komponen Livewire untuk mengelola data master Status Aset.
 * Menangani operasi CRUD (Create, Read, Update, Delete) untuk status aset
 * yang digunakan sebagai referensi pada manajemen aset utama.
 */
#[Layout('components.layouts.admin')]
#[Title('Kelola Status Aset')]
class AssetStatusManager extends Component
{
    use WithPagination;

    // ==========================================
    // PROPERTIES
    // ==========================================

    /** @var int|null ID status aset yang sedang diproses (Edit/Delete) */
    public $assetStatusId;

    /** @var string Nama status aset (State Input) */
    public $name;

    /** @var string Keyword untuk pencarian data */
    public $search = '';

    // --- UI State Flags ---
    public $showFormModal = false;   
    public $showDeleteModal = false; 
    public $isEditMode = false;      

    /** @var array Konfigurasi agar parameter pencarian tetap ada di URL */
    protected $queryString = ['search' => ['except' => '']];

    // ==========================================
    // VALIDATION
    // ==========================================

    /**
     * Mendefinisikan aturan validasi input.
     * Mengatur validasi unik pada kolom nama dengan pengecualian untuk ID saat ini
     * agar tidak terjadi konflik saat proses update.
     * @return array
     */
    protected function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:asset_statuses,name,' . $this->assetStatusId,
        ];
    }

    /**
     * Pesan error kustom untuk validasi.
     * @var array
     */
    protected $messages = [
        'name.required' => 'Nama status aset harus diisi.',
        'name.max' => 'Nama status aset maksimal 255 karakter.',
        'name.unique' => 'Nama status aset sudah ada.',
    ];

    // ==========================================
    // RENDER & SEARCH LOGIC
    // ==========================================

    /**
     * Reset pagination ke halaman 1 saat query pencarian berubah.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Merender view komponen.
     * Mengambil data status aset dengan filter pencarian dan pagination.
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.admin.master.asset-status-manager', [
            'assetStatuses' => AssetStatus::query()
                ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                ->latest() 
                ->paginate(10), 
        ]);
    }

    // ==========================================
    // CRUD LOGIC
    // ==========================================

    /**
     * Mereset seluruh state input dan validasi ke kondisi awal.
     */
    public function resetInputFields()
    {
        $this->name = '';
        $this->assetStatusId = null; 
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
        
        $status = AssetStatus::findOrFail($id);

        $this->assetStatusId = $id;
        $this->name = $status->name;
        
        $this->isEditMode = true;
        $this->showFormModal = true;
    }

    /**
     * Menyimpan data ke database (Create atau Update).
     * Logika ditentukan berdasarkan status flag $isEditMode.
     */
    public function store()
    {
        $this->validate();

        if ($this->isEditMode && $this->assetStatusId) {
            $status = AssetStatus::findOrFail($this->assetStatusId);
            $status->update(['name' => $this->name]);
            
            session()->flash('message', 'Status aset berhasil diperbarui.');
        } else {
            AssetStatus::create(['name' => $this->name]);
            
            session()->flash('message', 'Status aset berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    /**
     * Menampilkan modal konfirmasi hapus.
     * @param int $id
     */
    public function confirmDelete($id)
    {
        $this->assetStatusId = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Menghapus data secara permanen.
     * Menangani exception jika data sedang digunakan di tabel lain (Foreign Key Constraint).
     */
    public function delete()
    {
        if ($this->assetStatusId) {
            try {
                AssetStatus::findOrFail($this->assetStatusId)->delete();
                session()->flash('message', 'Status aset berhasil dihapus.');
            } catch (\Exception $e) {
                session()->flash('error', 'Gagal menghapus status. Mungkin sedang digunakan pada data aset.');
            }
        }
        
        $this->closeModal();
    }

    /**
     * Menutup modal dan membersihkan state form.
     */
    public function closeModal()
    {
        $this->showFormModal = false;
        $this->showDeleteModal = false;
        $this->resetInputFields();
    }
}