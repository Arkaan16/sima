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
 * * Komponen Livewire untuk menangani CRUD (Create, Read, Update, Delete)
 * pada data Master Status Aset.
 * Contoh data: "Baik", "Rusak Ringan", "Rusak Berat", "Hilang", "Disewakan".
 */
#[Layout('components.layouts.admin')]
#[Title('Kelola Status Aset')]
class AssetStatusManager extends Component
{
    use WithPagination;

    // ==========================================
    // PROPERTIES (VARIABEL UTAMA)
    // ==========================================

    /** @var int|null ID dari status yang sedang diedit atau akan dihapus */
    public $assetStatusId;

    /** @var string Nama status aset (Input Form) */
    public $name;

    /** @var string Keyword pencarian */
    public $search = '';

    // --- State UI (Kondisi Tampilan) ---
    public $showFormModal = false;   // Kontrol modal Tambah/Edit
    public $showDeleteModal = false; // Kontrol modal Hapus
    public $isEditMode = false;      // Penanda mode Edit vs Create

    // Query string agar pencarian tetap tersimpan di URL browser
    protected $queryString = ['search' => ['except' => '']];

    // ==========================================
    // VALIDASI & ATURAN
    // ==========================================

    /**
     * Menentukan aturan validasi input.
     * Dijalankan saat $this->validate().
     */
    protected function rules()
    {
        return [
            // Validasi Name:
            // 1. Wajib diisi (required)
            // 2. Maksimal 255 karakter
            // 3. Unik di tabel 'asset_statuses', kolom 'name', KECUALI id saat ini ($this->assetStatusId)
            //    Ini mencegah error "Name already taken" saat kita update data tanpa ganti nama.
            'name' => 'required|string|max:255|unique:asset_statuses,name,' . $this->assetStatusId,
        ];
    }

    /**
     * Pesan error kustom dalam Bahasa Indonesia.
     */
    protected $messages = [
        'name.required' => 'Nama status aset harus diisi.',
        'name.max' => 'Nama status aset maksimal 255 karakter.',
        'name.unique' => 'Nama status aset sudah ada.',
    ];

    // ==========================================
    // LOGIKA RENDER & PENCARIAN
    // ==========================================

    /**
     * Dijalankan otomatis saat user mengetik di kolom pencarian.
     * Mereset halaman ke 1 agar hasil search yang sedikit tidak error di pagination.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Render tampilan blade.
     * Mengambil data dari database dengan filter pencarian dan pagination.
     */
    public function render()
    {
        return view('livewire.admin.master.asset-status-manager', [
            'assetStatuses' => AssetStatus::query()
                // Jika ada search, filter berdasarkan nama
                ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                ->latest() // Urutkan dari data terbaru
                ->paginate(10), // Batasi 10 baris per halaman
        ]);
    }

    // ==========================================
    // LOGIKA CRUD (CREATE, UPDATE, DELETE)
    // ==========================================

    /**
     * Mereset semua field form menjadi kosong.
     * Dipanggil sebelum membuka modal atau setelah simpan.
     */
    public function resetInputFields()
    {
        $this->name = '';
        $this->assetStatusId = null; // Penting: Reset ID agar tidak dianggap mode Edit
        $this->isEditMode = false;
        $this->resetErrorBag();      // Hapus pesan error validasi
        $this->resetValidation();
    }

    /**
     * Membuka modal untuk Tambah Data Baru.
     */
    public function create()
    {
        $this->resetInputFields();
        $this->showFormModal = true;
    }

    /**
     * Membuka modal untuk Edit Data.
     * @param int $id ID data yang dipilih dari tabel.
     */
    public function edit($id)
    {
        $this->resetValidation();
        
        // Cari data di database
        $status = AssetStatus::findOrFail($id);

        // Isi property komponen dengan data lama
        $this->assetStatusId = $id;
        $this->name = $status->name;
        
        // Set flag Edit Mode
        $this->isEditMode = true;
        $this->showFormModal = true;
    }

    /**
     * Fungsi Simpan (Menangani Create & Update sekaligus).
     */
    public function store()
    {
        // 1. Jalankan Validasi
        $this->validate();

        // 2. Cek Logika Simpan
        if ($this->isEditMode && $this->assetStatusId) {
            // -- LOGIKA UPDATE --
            $status = AssetStatus::findOrFail($this->assetStatusId);
            $status->update(['name' => $this->name]);
            
            session()->flash('message', 'Status aset berhasil diperbarui.');
        } else {
            // -- LOGIKA CREATE --
            AssetStatus::create(['name' => $this->name]);
            
            session()->flash('message', 'Status aset berhasil ditambahkan.');
        }

        // 3. Tutup Modal
        $this->closeModal();
    }

    /**
     * Konfirmasi sebelum menghapus (Mencegah hapus tidak sengaja).
     */
    public function confirmDelete($id)
    {
        $this->assetStatusId = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Eksekusi penghapusan data.
     */
    public function delete()
    {
        if ($this->assetStatusId) {
            try {
                // Coba hapus data
                AssetStatus::findOrFail($this->assetStatusId)->delete();
                session()->flash('message', 'Status aset berhasil dihapus.');
            } catch (\Exception $e) {
                // Tangkap error jika data gagal dihapus 
                // (Biasanya karena ID status ini masih dipakai di tabel Aset/Foreign Key Constraint)
                session()->flash('error', 'Gagal menghapus status. Mungkin sedang digunakan pada data aset.');
            }
        }
        
        $this->closeModal();
    }

    /**
     * Menutup semua modal dan membersihkan input.
     */
    public function closeModal()
    {
        $this->showFormModal = false;
        $this->showDeleteModal = false;
        $this->resetInputFields();
    }
}