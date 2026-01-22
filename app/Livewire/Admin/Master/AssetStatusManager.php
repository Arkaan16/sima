<?php

namespace App\Livewire\Admin\Master;

use App\Models\AssetStatus;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;

#[Layout('components.layouts.admin')]
#[Title('Kelola Status Aset')]
class AssetStatusManager extends Component
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
    public $deleteName = ''; // Untuk konfirmasi hapus

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
    // ACTIONS (Server-Side Logic)
    // ==========================================

    // 1. Persiapan Create
    public function create()
    {
        $this->resetInputFields();
        // Kirim sinyal ke browser
        $this->dispatch('open-modal-form');
    }

    // 2. Persiapan Edit
    public function edit($id)
    {
        $status = AssetStatus::find($id);
        if (!$status) return;

        $this->assetStatusId = $status->id;
        $this->name = $status->name;
        $this->isEditMode = true;

        $this->resetValidation(); // Bersihkan error lama
        
        // Kirim sinyal ke browser
        $this->dispatch('open-modal-form');
    }

    // 3. Persiapan Hapus
    public function confirmDelete($id)
    {
        $status = AssetStatus::find($id);
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
            $status = AssetStatus::findOrFail($this->assetStatusId);
            $status->update(['name' => $this->name]);
            $message = 'Status aset berhasil diperbarui.';
        } else {
            AssetStatus::create(['name' => $this->name]);
            $message = 'Status aset berhasil ditambahkan.';
        }

        session()->flash('message', $message);
        
        // Tutup modal & Reset
        $this->dispatch('close-all-modals');
        $this->resetInputFields();
    }

    public function delete()
    {
        if (!$this->assetStatusId) return;

        try {
            AssetStatus::findOrFail($this->assetStatusId)->delete();
            session()->flash('message', 'Status aset berhasil dihapus.');
        } catch (\Exception $e) {
            // Error Foreign Key (Data sedang digunakan)
            session()->flash('error', 'Gagal menghapus status. Mungkin sedang digunakan pada data aset.');
        }

        // Apapun hasilnya, tutup modal & reset
        $this->dispatch('close-all-modals');
        $this->resetInputFields();
    }

    public function render()
    {
        return view('livewire.admin.master.asset-status-manager', [
            'assetStatuses' => AssetStatus::query()
                ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                ->latest()
                ->paginate(10),
        ]);
    }
}