<?php

namespace App\Livewire\Admin\Asset;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Livewire\Forms\AssetForm;
use App\Models\Asset; 

// Models Relasi
use App\Models\AssetModel;
use App\Models\AssetStatus;
use App\Models\Location;
use App\Models\Supplier;
use App\Models\Employee;

/**
 * Class AssetEdit
 *
 * Komponen Livewire untuk menangani proses penyuntingan (update) data aset.
 * Bertanggung jawab untuk menginisialisasi form dengan data yang ada,
 * mengelola state UI untuk dropdown pencarian, dan menangani logika hierarki lokasi.
 *
 * @package App\Livewire\Admin\Asset
 */
#[Layout('components.layouts.admin')]
#[Title('Edit Aset')]
class AssetEdit extends Component
{
    use WithFileUploads;

    /**
     * Objek Form Livewire yang menangani validasi dan logika update data.
     * @var AssetForm
     */
    public AssetForm $form;

    // =================================================================
    // SEARCH & UI STATE PROPERTIES
    // =================================================================
    
    // Variabel state di bawah ini digunakan untuk menangani tampilan UI 
    // (Label nama yang terpilih & Input pencarian) pada dropdown custom.

    // --- 1. Model Aset ---
    public $searchModel = '';
    public $selectedModelName = null;
    public $selectedModelImage = null;

    // --- 2. Status Aset ---
    public $searchStatus = '';
    public $selectedStatusName = null;

    // --- 3. Lokasi (Hierarki: Gedung -> Ruangan) ---
    public $selectedParentId = ''; 
    public $searchParent = '';
    public $selectedParentName = null;

    public $selectedChildId = '';
    public $searchChild = '';
    public $selectedChildName = null;

    // --- 4. Supplier ---
    public $searchSupplier = '';
    public $selectedSupplierName = null;
    public $selectedSupplierImage = null;

    // --- 5. Karyawan (Penanggung Jawab) ---
    public $searchEmployee = '';
    public $selectedEmployeeName = null;

    // =================================================================
    // LIFECYCLE: INITIALIZATION
    // =================================================================

    /**
     * Lifecycle Method: Mount
     * Dijalankan saat komponen pertama kali dimuat. Mengisi form dan state UI
     * berdasarkan data aset yang sedang diedit.
     *
     * @param Asset $asset Model aset yang di-inject (Route Model Binding)
     */
    public function mount(Asset $asset)
    {
        // 1. Isi Form Object dengan data dari database
        $this->form->setAsset($asset);

        // 2. Populasi UI State (Label & Gambar) agar dropdown menampilkan data yang benar
        
        // A. Model
        if ($asset->model) {
            $this->selectedModelName = $asset->model->category->name . ' - ' . $asset->model->name . ' - (' . $asset->model->model_number . ')';
            $this->selectedModelImage = $asset->model->image;
        }

        // B. Status
        if ($asset->status) {
            $this->selectedStatusName = $asset->status->name;
        }

        // C. Supplier
        if ($asset->supplier) {
            $this->selectedSupplierName = $asset->supplier->name;
            $this->selectedSupplierImage = $asset->supplier->image;
        }

        // D. Employee (Assignment)
        if ($asset->assigned_to_type === 'App\Models\Employee' && $asset->assignedTo) {
            $this->selectedEmployeeName = $asset->assignedTo->name;
        }

        // 3. Logika Hierarki Lokasi (Gedung vs Ruangan)
        if ($asset->defaultLocation) {
            $location = $asset->defaultLocation;

            if ($location->parent_location_id) {
                // Kasus: Lokasi adalah Ruangan (Punya Parent)
                // Set Ruangan (Child)
                $this->selectedChildId = $location->id;
                $this->selectedChildName = $location->name;
                
                // Set Gedung (Parent) secara otomatis dari relasi
                $this->selectedParentId = $location->parent_location_id;
                $this->selectedParentName = $location->parent->name;
            } else {
                // Kasus: Lokasi adalah Gedung (Root Level)
                $this->selectedParentId = $location->id;
                $this->selectedParentName = $location->name;
            }
        }
    }

    // =================================================================
    // UI EVENT HANDLERS
    // =================================================================

    /**
     * Menangani pemilihan Lokasi Induk (Gedung).
     *
     * @param int|string $id ID Gedung
     * @param string $name Nama Gedung
     */
    public function selectParentLocation($id, $name)
    {
        // 1. Set State Lokasi Induk
        $this->selectedParentId = $id;
        $this->selectedParentName = $name;
        
        // 2. Update Form: Set default lokasi ke Gedung
        $this->form->location_id = $id; 
        $this->searchParent = ''; 

        // 3. Reset State Lokasi Anak (Ruangan)
        // Mencegah inkonsistensi data ruangan dari gedung sebelumnya
        $this->selectedChildId = '';
        $this->selectedChildName = null;
        $this->searchChild = '';
    }

    /**
     * Menangani pemilihan Lokasi Anak (Ruangan).
     *
     * @param int|string $id ID Ruangan
     * @param string $name Nama Ruangan
     */
    public function selectChildLocation($id, $name)
    {
        // 1. Set State Lokasi Anak
        $this->selectedChildId = $id;
        $this->selectedChildName = $name;
        
        // 2. Logika Fallback Lokasi
        if (empty($id)) {
            // Jika memilih opsi kosong (misal "Umum"), kembalikan ke Parent ID
            $this->form->location_id = $this->selectedParentId;
        } else {
            // Jika memilih ruangan valid, set ID ruangan
            $this->form->location_id = $id; 
        }
        
        $this->searchChild = ''; 
    }

    /**
     * Handler generik untuk memilih opsi dari dropdown (Model, Status, Supplier, dll).
     * Mengupdate ID pada Form dan Label Tampilan pada UI.
     *
     * @param string $field Nama properti di AssetForm
     * @param int|string $id ID data yang dipilih
     * @param string $displayName Label nama untuk UI
     * @param string $searchProp Properti pencarian yang harus di-reset
     * @param string|null $image URL gambar (opsional)
     */
    public function selectOption($field, $id, $displayName, $searchProp, $image = null)
    {
        // 1. Update ID ke Form
        $this->form->$field = $id;

        // 2. Update UI State (Nama Label)
        $type = str_replace('search', '', $searchProp); 
        $propName = 'selected' . $type . 'Name'; 
        $this->$propName = $displayName;

        // 3. Update UI State (Gambar - Opsional)
        if ($searchProp === 'searchModel') {
            $this->selectedModelImage = $image;
        } elseif ($searchProp === 'searchSupplier') {
            $this->selectedSupplierImage = $image;
        }

        // 4. Reset Keyword Pencarian
        $this->$searchProp = ''; 
    }

    /**
     * Mengeksekusi proses update data ke database.
     *
     * @return \Illuminate\Routing\Redirector
     */
    public function save()
    {
        // 1. Delegasikan ke Form Object untuk validasi dan update
        $this->form->update(); 
        
        // 2. Berikan Feedback User
        session()->flash('message', 'Data aset berhasil diperbarui.');
        
        // 3. Redirect ke halaman index
        return $this->redirectRoute('admin.assets.index', navigate: true);
    }

    /**
     * Merender tampilan komponen.
     * Melakukan query data referensi berdasarkan keyword pencarian (Live Search).
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // 1. Query Master Model (Live Search)
        $models = AssetModel::with(['manufacturer', 'category']) 
            ->when($this->searchModel, function($q) {
                $q->where('name', 'like', '%'.$this->searchModel.'%')
                ->orWhere('model_number', 'like', '%'.$this->searchModel.'%');
            })
            ->orderBy('name')->take(10)->get();

        // 2. Query Master Status
        $statuses = AssetStatus::query()
            ->when($this->searchStatus, fn($q) => $q->where('name', 'like', '%'.$this->searchStatus.'%'))
            ->orderBy('name')->take(10)->get();

        // 3. Query Lokasi (Hierarki)
        // A. Parent (Gedung)
        $parents = Location::whereNull('parent_location_id')
            ->when($this->searchParent, fn($q) => $q->where('name', 'like', '%'.$this->searchParent.'%'))
            ->orderBy('name')->take(10)->get();

        // B. Child (Ruangan) - Hanya jika Parent terpilih
        $children = [];
        if ($this->selectedParentId) {
            $children = Location::where('parent_location_id', $this->selectedParentId)
                ->when($this->searchChild, fn($q) => $q->where('name', 'like', '%'.$this->searchChild.'%'))
                ->orderBy('name')->take(10)->get();
        }

        // 4. Query Master Supplier
        $suppliers = Supplier::query()
            ->when($this->searchSupplier, fn($q) => $q->where('name', 'like', '%'.$this->searchSupplier.'%'))
            ->orderBy('name')->take(10)->get();

        // 5. Query Master Employee
        $employees = Employee::query()
            ->when($this->searchEmployee, function($q) {
                $q->where('name', 'like', '%'.$this->searchEmployee.'%')
                  ->orWhere('email', 'like', '%'.$this->searchEmployee.'%');
            })
            ->orderBy('name')->take(10)->get();

        // 6. Return View
        return view('livewire.admin.asset.asset-edit', [ 
            'models' => $models,
            'statuses' => $statuses,
            'parentLocations' => $parents,
            'childLocations' => $children,
            'suppliers' => $suppliers,
            'employees' => $employees,
        ]);
    }
}