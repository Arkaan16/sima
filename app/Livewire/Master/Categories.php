<?php

namespace App\Livewire\Master;

use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

#[Title('Kelola Kategori')]
class Categories extends Component
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
    public $deleteName = ''; 

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
    // ACTIONS
    // ==========================================

    public function create()
    {
        $this->resetInputFields();
        $this->dispatch('open-modal-form');
    }

    public function edit($id)
    {
        $category = Category::find($id);
        if (!$category) return;

        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->isEditMode = true;

        $this->resetValidation();
        $this->dispatch('open-modal-form');
    }

    public function confirmDelete($id)
    {
        $category = Category::find($id);
        if (!$category) return;

        $this->categoryId = $category->id;
        $this->deleteName = $category->name; 

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
        
        $this->dispatch('close-all-modals');
        $this->resetInputFields();
    }

    public function delete()
    {
        if (!$this->categoryId) return;

        try {
            $category = Category::findOrFail($this->categoryId);
            
            // Cek apakah kategori masih digunakan
            if ($category->assetModels()->count() > 0) {
                session()->flash('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh ' . $category->assetModels()->count() . ' aset.');
                $this->dispatch('close-all-modals');
                return;
            }
            
            $category->delete();
            session()->flash('message', 'Kategori berhasil dihapus.');
        
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus kategori: ' . $e->getMessage());
        }

        $this->dispatch('close-all-modals');
        $this->resetInputFields();
    }

    public function render()
    {
        // View path diarahkan ke folder master
        return view('livewire.master.categories', [
            'categories' => Category::query()
                ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                ->latest() 
                ->paginate(10), 
        ]);
    }
}