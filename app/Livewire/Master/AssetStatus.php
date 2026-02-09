<?php

namespace App\Livewire\Master;

use App\Models\AssetStatus as AssetStatusModel; 
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

#[Title('Kelola Status Aset')]
class AssetStatus extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind';

    // ==========================================
    // PROPERTIES
    // ==========================================

    public $assetStatusId;
    public $name = '';

    // UI State
    public $isEditMode = false;
    public $deleteName = ''; 

    // Search
    public $search = '';

    protected $queryString = ['search' => ['except' => '']];

    // ==========================================
    // LIFECYCLE & VALIDATION
    // ==========================================

    public function updatingSearch()
    {
        $this->resetPage();
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:asset_statuses,name,' . $this->assetStatusId,
        ];
    }

    protected $messages = [
        'name.required' => 'Nama status aset harus diisi.',
        'name.max'      => 'Nama status aset maksimal 255 karakter.',
        'name.unique'   => 'Nama status aset sudah ada.',
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
        $status = AssetStatusModel::find($id);
        if (!$status) return;

        $this->assetStatusId = $status->id;
        $this->name = $status->name;
        $this->isEditMode = true;

        $this->resetValidation();
        $this->dispatch('open-modal-form');
    }

    public function confirmDelete($id)
    {
        $status = AssetStatusModel::find($id);
        if (!$status) return;

        $this->assetStatusId = $status->id;
        $this->deleteName = $status->name;

        $this->dispatch('open-modal-delete');
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->assetStatusId = null;
        $this->deleteName = '';
        $this->isEditMode = false;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function store()
    {
        $this->validate();

        if ($this->isEditMode && $this->assetStatusId) {
            $status = AssetStatusModel::findOrFail($this->assetStatusId);
            $status->update(['name' => $this->name]);
            $message = 'Status aset berhasil diperbarui.';
        } else {
            AssetStatusModel::create(['name' => $this->name]);
            $message = 'Status aset berhasil ditambahkan.';
        }

        session()->flash('message', $message);
        
        $this->dispatch('close-all-modals');
        $this->resetInputFields();
    }

    public function delete()
    {
        if (!$this->assetStatusId) return;

        try {
            AssetStatusModel::findOrFail($this->assetStatusId)->delete();
            session()->flash('message', 'Status aset berhasil dihapus.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus status. Mungkin sedang digunakan pada data aset.');
        }

        $this->dispatch('close-all-modals');
        $this->resetInputFields();
    }

    public function render()
    {
        return view('livewire.master.asset-status', [
            'assetStatuses' => AssetStatusModel::query()
                ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                ->latest()
                ->paginate(10),
        ]);
    }
}