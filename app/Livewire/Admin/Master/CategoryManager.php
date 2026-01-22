<?php

namespace App\Livewire\Admin\Master;

use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.admin')]
#[Title('Kelola Kategori')]
class CategoryManager extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind';

    // ==========================================
    // PROPERTIES
    // ==========================================

    public $categoryId;
    public $name = '';
    
    // UI State
    public $isEditMode = false;
    public $deleteName = ''; // Untuk menampilkan nama di modal delete

    // Search
    public $search = '';

    protected $queryString = ['search' => ['except' => '']];

    // ==========================================
    // VALIDATION & LIFECYCLE
    // ==========================================

    public function updatingSearch()
    {
        $this->resetPage();
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:categories,name,' . $this->categoryId,
        ];
    }

    protected $messages = [
        'name.required' => 'Nama kategori harus diisi.',
        'name.max'      => 'Nama kategori maksimal 255 karakter.',
        'name.unique'   => 'Nama kategori ini sudah terdaftar di sistem.',
    ];

    // ==========================================
    // ACTIONS (Server-Side Logic)
    // ==========================================

    // 1. Persiapan Create
    public function create()
    {
        $this->resetInputFields();
        // Kirim sinyal ke browser untuk buka modal
        $this->dispatch('open-modal-form');
    }

    // 2. Persiapan Edit
    public function edit($id)
    {
        $category = Category::find($id);
        if (!$category) return;

        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->isEditMode = true;

        $this->resetValidation(); // Bersihkan error lama
        
        // Kirim sinyal ke browser
        $this->dispatch('open-modal-form');
    }

    // 3. Persiapan Delete
    public function confirmDelete($id)
    {
        $category = Category::find($id);
        if (!$category) return;

        $this->categoryId = $category->id;
        $this->deleteName = $category->name; // Simpan nama untuk UI Confirm

        $this->dispatch('open-modal-delete');
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->categoryId = null;
        $this->deleteName = '';
        $this->isEditMode = false;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function store()
    {
        $this->validate();

        if ($this->isEditMode && $this->categoryId) {
            $category = Category::findOrFail($this->categoryId);
            $category->update(['name' => $this->name]);
            $message = 'Kategori berhasil diperbarui.';
        } else {
            Category::create(['name' => $this->name]);
            $message = 'Kategori berhasil ditambahkan.';
        }

        session()->flash('message', $message);
        
        // Tutup semua modal dan reset
        $this->dispatch('close-all-modals');
        $this->resetInputFields();
    }

    public function delete()
    {
        if (!$this->categoryId) return;

        try {
            Category::findOrFail($this->categoryId)->delete();
            session()->flash('message', 'Kategori berhasil dihapus.');
        
        } catch (\Exception $e) {
            // Jika error (misal Foreign Key constraint)
            session()->flash('error', 'Gagal menghapus kategori. Kategori ini mungkin sedang digunakan oleh data aset.');
        }

        // Apapun hasilnya (Sukses/Gagal), tutup modal dan reset state
        $this->dispatch('close-all-modals');
        $this->resetInputFields();
    }

    public function render()
    {
        return view('livewire.admin.master.category-manager', [
            'categories' => Category::query()
                ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                ->latest() 
                ->paginate(10), 
        ]);
    }
}