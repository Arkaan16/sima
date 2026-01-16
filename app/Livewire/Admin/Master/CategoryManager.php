<?php

namespace App\Livewire\Admin\Master;

use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

/**
 * Class CategoryManager
 * * Komponen Livewire untuk mengelola data master Kategori Aset.
 * Menangani operasi CRUD (Create, Read, Update, Delete) untuk kategori aset
 * yang berfungsi sebagai pengelompokan utama dalam sistem manajemen aset.
 */
#[Layout('components.layouts.admin')]
#[Title('Kelola Kategori')]
class CategoryManager extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'tailwind';

    // ==========================================
    // PROPERTIES
    // ==========================================

    /** @var int|null ID kategori yang sedang diproses (Edit/Delete) */
    public $categoryId;

    /** @var string Nama kategori (State Input) */
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
     * (unique ignore) untuk mencegah konflik saat proses update data.
     * @return array
     */
    protected function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:categories,name,' . $this->categoryId,
        ];
    }

    /**
     * Pesan error kustom untuk validasi.
     * @var array
     */
    protected $messages = [
        'name.required' => 'Nama kategori harus diisi.',
        'name.max' => 'Nama kategori maksimal 255 karakter.',
        'name.unique' => 'Nama kategori ini sudah terdaftar di sistem.',
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
     * Mengambil data kategori dengan filter pencarian dan pagination.
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.admin.master.category-manager', [
            'categories' => Category::query()
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
        $this->categoryId = null; 
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
        
        $category = Category::findOrFail($id);

        $this->categoryId = $id;
        $this->name = $category->name;
        
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

        if ($this->isEditMode && $this->categoryId) {
            $category = Category::findOrFail($this->categoryId);
            $category->update(['name' => $this->name]);
            
            session()->flash('message', 'Kategori berhasil diperbarui.');
        } else {
            Category::create(['name' => $this->name]);
            
            session()->flash('message', 'Kategori berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    /**
     * Menampilkan modal konfirmasi hapus.
     * @param int $id
     */
    public function confirmDelete($id)
    {
        $this->categoryId = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Menghapus data secara permanen.
     * Menangani exception jika data sedang digunakan di tabel lain (Foreign Key Constraint).
     */
    public function delete()
    {
        if ($this->categoryId) {
            try {
                Category::findOrFail($this->categoryId)->delete();
                session()->flash('message', 'Kategori berhasil dihapus.');
            } catch (\Exception $e) {
                session()->flash('error', 'Gagal menghapus kategori. Kategori ini mungkin sedang digunakan oleh data aset.');
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