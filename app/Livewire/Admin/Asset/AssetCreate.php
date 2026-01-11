<?php

namespace App\Livewire\Admin\Asset;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Livewire\Forms\AssetForm;

// Models
use App\Models\AssetModel;
use App\Models\AssetStatus;
use App\Models\Location;
use App\Models\Supplier;
use App\Models\Employee;

#[Layout('components.layouts.admin')]
#[Title('Tambah Aset Baru')]
class AssetCreate extends Component
{
    use WithFileUploads;

    public AssetForm $form;

    // =================================================================
    // SEARCH & UI STATE PROPERTIES
    // =================================================================
    
    // 1. Model
    public $searchModel = '';
    public $selectedModelName = null;
    public $selectedModelImage = null;

    // 2. Status
    public $searchStatus = '';
    public $selectedStatusName = null;

    // 3. Location (UBAH DISINI: Tambahkan Search & SelectedName)
    public $selectedParentId = ''; 
    public $searchParent = '';      // Untuk search Gedung
    public $selectedParentName = null; // Nama Gedung terpilih

    public $selectedChildId = '';
    public $searchChild = '';       // Untuk search Ruangan
    public $selectedChildName = null; // Nama Ruangan terpilih

    // 4. Supplier
    public $searchSupplier = '';
    public $selectedSupplierName = null;
    public $selectedSupplierImage = null;

    // 5. Employee
    public $searchEmployee = '';
    public $selectedEmployeeName = null;

    // =================================================================
    // METHODS UI HANDLING
    // =================================================================

    /**
     * Method Khusus untuk Memilih Gedung (Parent)
     * Karena saat Gedung berubah, Ruangan harus di-reset.
     */
    public function selectParentLocation($id, $name)
    {
        $this->selectedParentId = $id;
        $this->selectedParentName = $name;
        $this->form->location_id = $id; // Default lokasi = Gedung
        $this->searchParent = ''; // Reset search

        // Reset Child / Ruangan saat gedung ganti
        $this->selectedChildId = '';
        $this->selectedChildName = null;
        $this->searchChild = '';
    }

    /**
     * Method Khusus untuk Memilih Ruangan (Child)
     */
    public function selectChildLocation($id, $name)
    {
        $this->selectedChildId = $id;
        $this->selectedChildName = $name;
        $this->form->location_id = $id; // Lokasi spesifik = Ruangan
        $this->searchChild = ''; // Reset search
    }

    /**
     * Helper umum untuk Model, Status, Supplier, Employee
     */
    public function selectOption($field, $id, $displayName, $searchProp, $image = null)
    {
        $this->form->$field = $id;

        $type = str_replace('search', '', $searchProp); 
        $propName = 'selected' . $type . 'Name'; 
        $this->$propName = $displayName;

        if ($searchProp === 'searchModel') {
            $this->selectedModelImage = $image;
        } elseif ($searchProp === 'searchSupplier') {
            $this->selectedSupplierImage = $image;
        }

        $this->$searchProp = ''; 
    }

    public function save()
    {
        $this->form->store();
        session()->flash('message', 'Aset baru berhasil ditambahkan.');
        return $this->redirectRoute('admin.assets.index', navigate: true);
    }

    public function render()
    {
        // 1. Models
        $models = AssetModel::with(['manufacturer', 'category']) 
            ->when($this->searchModel, function($q) {
                $q->where('name', 'like', '%'.$this->searchModel.'%')
                ->orWhere('model_number', 'like', '%'.$this->searchModel.'%');
            })
            ->orderBy('name')->take(10)->get();

        // 2. Status
        $statuses = AssetStatus::query()
            ->when($this->searchStatus, fn($q) => $q->where('name', 'like', '%'.$this->searchStatus.'%'))
            ->orderBy('name')->take(10)->get();

        // 3. LOGIKA BARU LOCATION (Query di sini agar Live Search jalan)
        
        // A. Parent Locations (Gedung) - Cari berdasarkan searchParent
        $parents = Location::whereNull('parent_location_id')
            ->when($this->searchParent, fn($q) => $q->where('name', 'like', '%'.$this->searchParent.'%'))
            ->orderBy('name')
            ->take(10)
            ->get();

        // B. Child Locations (Ruangan) - Hanya muncul jika Parent dipilih
        $children = [];
        if ($this->selectedParentId) {
            $children = Location::where('parent_location_id', $this->selectedParentId)
                ->when($this->searchChild, fn($q) => $q->where('name', 'like', '%'.$this->searchChild.'%'))
                ->orderBy('name')
                ->take(10)
                ->get();
        }

        // 4. Supplier
        $suppliers = Supplier::query()
            ->when($this->searchSupplier, fn($q) => $q->where('name', 'like', '%'.$this->searchSupplier.'%'))
            ->orderBy('name')->take(10)->get();

        // 5. Employees
        $employees = Employee::query()
            ->when($this->searchEmployee, function($q) {
                $q->where('name', 'like', '%'.$this->searchEmployee.'%')
                  ->orWhere('email', 'like', '%'.$this->searchEmployee.'%');
            })
            ->orderBy('name')->take(10)->get();

        return view('livewire.admin.asset.asset-create', [
            'models' => $models,
            'statuses' => $statuses,
            'parentLocations' => $parents, // Variable baru
            'childLocations' => $children, // Variable baru
            'suppliers' => $suppliers,
            'employees' => $employees,
        ]);
    }
}