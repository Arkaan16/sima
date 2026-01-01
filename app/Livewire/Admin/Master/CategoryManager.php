<?php

namespace App\Livewire\Admin\Master;

use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

/**
 * Class CategoryManager
 * * Komponen Livewire untuk menangani CRUD (Create, Read, Update, Delete)
 * pada data Master Kategori Aset.
 * Contoh data: "Laptop", "Kendaraan", "Furniture", "Elektronik".
 */
#[Layout('components.layouts.admin')]
#[Title('Manajemen Kategori')]
class CategoryManager extends Component
{
    use WithPagination;
    
    // Mengatur tema pagination agar sesuai dengan framework CSS Tailwind
    protected $paginationTheme = 'tailwind';

    // ==========================================
    // PROPERTIES (VARIABEL UTAMA)
    // ==========================================

    /** @var int|null ID kategori yang sedang diedit atau akan dihapus */
    public $categoryId;

    /** @var string Nama kategori (Input Form) */
    public $name;

    /** @var string Keyword pencarian data */
    public $search = '';

    // --- State UI (Kondisi Tampilan) ---
    public $showFormModal = false;   // Kontrol tampil/sembunyi modal Form
    public $showDeleteModal = false; // Kontrol tampil/sembunyi modal Hapus
    public $isEditMode = false;      // Penanda apakah sedang Edit atau Tambah Baru

    // Memastikan parameter search tetap ada di URL browser saat halaman direfresh
    protected $queryString = ['search' => ['except' => '']];

    // ==========================================
    // VALIDASI & ATURAN
    // ==========================================

    /**
     * Menentukan aturan validasi input.
     * Dijalankan otomatis saat $this->validate().
     */
    protected function rules()
    {
        return [
            // Validasi Name:
            // 1. Wajib diisi (required)
            // 2. Maksimal 255 karakter
            // 3. Unik di tabel 'categories' kolom 'name'.
            //    Bagian ",' . $this->categoryId" berfungsi untuk MENGECUALIKAN ID ini saat cek unik.
            //    Ini penting agar saat Edit data, nama yang sama tidak dianggap duplikat.
            'name' => 'required|string|max:255|unique:categories,name,' . $this->categoryId,
        ];
    }

    /**
     * Pesan error kustom dalam Bahasa Indonesia.
     */
    protected $messages = [
        'name.required' => 'Nama kategori harus diisi.',
        'name.max' => 'Nama kategori maksimal 255 karakter.',
        'name.unique' => 'Nama kategori ini sudah terdaftar di sistem.',
    ];

    // ==========================================
    // LOGIKA RENDER & PENCARIAN
    // ==========================================

    /**
     * Dijalankan saat properti $search berubah (user mengetik).
     * Reset pagination ke halaman 1 agar hasil pencarian terlihat benar.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Render view blade.
     * Mengambil data dari database, memfilter berdasarkan pencarian, dan membuat pagination.
     */
    public function render()
    {
        return view('livewire.admin.master.category-manager', [
            'categories' => Category::query()
                // Jika $this->search ada isinya, filter berdasarkan nama
                ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                ->latest() // Urutkan dari yang paling baru dibuat
                ->paginate(10), // Batasi 10 data per halaman
        ]);
    }

    // ==========================================
    // LOGIKA CRUD (CREATE, UPDATE, DELETE)
    // ==========================================

    /**
     * Reset semua input form kembali bersih.
     * Dipanggil sebelum membuka modal Create atau setelah selesai Simpan.
     */
    public function resetInputFields()
    {
        $this->name = '';
        $this->categoryId = null; // Penting: Reset ID agar logika Edit tidak jalan
        $this->isEditMode = false;
        $this->resetErrorBag();   // Hapus pesan error validasi (merah-merah)
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
     * @param int $id ID data yang akan diedit.
     */
    public function edit($id)
    {
        $this->resetValidation();
        
        // Cari data kategori berdasarkan ID
        $category = Category::findOrFail($id);

        // Isi variabel komponen dengan data dari database
        $this->categoryId = $id;
        $this->name = $category->name;
        
        // Aktifkan mode edit & buka modal
        $this->isEditMode = true;
        $this->showFormModal = true;
    }

    /**
     * Fungsi Utama untuk Menyimpan Data.
     * Menangani logika Create (Baru) dan Update (Edit) sekaligus.
     */
    public function store()
    {
        // 1. Jalankan Validasi
        $this->validate();

        // 2. Cek apakah ini mode Edit atau Baru
        if ($this->isEditMode && $this->categoryId) {
            // -- Update Data Lama --
            $category = Category::findOrFail($this->categoryId);
            $category->update(['name' => $this->name]);
            
            session()->flash('message', 'Kategori berhasil diperbarui.');
        } else {
            // -- Buat Data Baru --
            Category::create(['name' => $this->name]);
            
            session()->flash('message', 'Kategori berhasil ditambahkan.');
        }

        // 3. Tutup Modal setelah selesai
        $this->closeModal();
    }

    /**
     * Konfirmasi hapus data (Mencegah penghapusan tidak sengaja).
     * Hanya menampilkan modal konfirmasi, belum menghapus data.
     */
    public function confirmDelete($id)
    {
        $this->categoryId = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Eksekusi penghapusan data dari database.
     */
    public function delete()
    {
        if ($this->categoryId) {
            try {
                // Coba hapus data
                Category::findOrFail($this->categoryId)->delete();
                session()->flash('message', 'Kategori berhasil dihapus.');
            } catch (\Exception $e) {
                // Tangkap Error (Biasanya Foreign Key Constraint)
                // Jika Kategori ini sudah dipakai oleh Aset, maka tidak boleh dihapus.
                session()->flash('error', 'Gagal menghapus kategori. Kategori ini mungkin sedang digunakan oleh data aset.');
            }
        }
        
        $this->closeModal();
    }

    /**
     * Menutup semua modal (Form & Delete) dan mereset input.
     */
    public function closeModal()
    {
        $this->showFormModal = false;
        $this->showDeleteModal = false;
        $this->resetInputFields();
    }
}