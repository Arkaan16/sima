<?php

namespace App\Livewire\Assets;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use App\Livewire\Forms\AssetForm;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\AssetStatus;
use App\Models\Location;
use App\Models\Supplier;
use App\Models\Employee;

#[Title('Form Aset')]
class Form extends Component
{
    use WithFileUploads;

    public AssetForm $form;

    // State Mode
    public bool $isEdit = false;
    public string $pageTitle = 'Tambah Aset Baru';

    // =================================================================
    // SEARCH & UI STATE PROPERTIES (SAMA PERSIS DENGAN CREATE)
    // =================================================================
    
    // 1. Model
    public $searchModel = '';
    public $selectedModelName = null;
    public $selectedModelImage = null;

    // 2. Status
    public $searchStatus = '';
    public $selectedStatusName = null;

    // 3. Lokasi (Hierarki)
    public $selectedParentId = ''; 
    public $searchParent = '';      
    public $selectedParentName = null;
    public $selectedChildId = '';
    public $searchChild = '';       
    public $selectedChildName = null;

    // 4. Supplier
    public $searchSupplier = '';
    public $selectedSupplierName = null;
    public $selectedSupplierImage = null;

    // 5. Karyawan
    public $searchEmployee = '';
    public $selectedEmployeeName = null;

    // =================================================================
    // LIFECYCLE: MOUNT (Initialize Data)
    // =================================================================
    public function mount(Asset $asset = null)
    {
        if ($asset && $asset->exists) {
            // MODE EDIT
            $this->authorize('update', $asset);
            
            $this->isEdit = true;
            $this->pageTitle = 'Edit Aset: ' . $asset->asset_tag;
            
            // 1. Isi Data Form Object (ID, Tag, Serial, dll)
            $this->form->setAsset($asset);

            // 2. HYDRATE UI STATE (PENTING! Agar dropdown terisi nama, bukan kosong)
            $this->populateUiFromAsset($asset);

        } else {
            // MODE CREATE
            $this->authorize('create', Asset::class);
            $this->isEdit = false;
            $this->pageTitle = 'Tambah Aset Baru';
        }
    }

    /**
     * Mengisi variabel UI (Search/Selected Name) dari data Aset yang ada.
     */
    private function populateUiFromAsset(Asset $asset)
    {
        // Model
        if ($asset->model) {
            $this->selectedModelName = "{$asset->model->category->name} - {$asset->model->name} ({$asset->model->model_number})";
            $this->selectedModelImage = $asset->model->image;
        }

        // Status
        if ($asset->status) {
            $this->selectedStatusName = $asset->status->name;
        }

        // Lokasi (Hierarki)
        if ($asset->defaultLocation) {
            if ($asset->defaultLocation->parent) {
                // Jika lokasi adalah Child (Ruangan)
                $this->selectedParentId = $asset->defaultLocation->parent->id;
                $this->selectedParentName = $asset->defaultLocation->parent->name;
                
                $this->selectedChildId = $asset->defaultLocation->id;
                $this->selectedChildName = $asset->defaultLocation->name;
            } else {
                // Jika lokasi adalah Parent (Gedung/Utama)
                $this->selectedParentId = $asset->defaultLocation->id;
                $this->selectedParentName = $asset->defaultLocation->name;
            }
        }

        // Supplier
        if ($asset->supplier) {
            $this->selectedSupplierName = $asset->supplier->name;
            $this->selectedSupplierImage = $asset->supplier->image;
        }

        // Karyawan
        if ($asset->assignedTo && $asset->assigned_to_type === \App\Models\Employee::class) {
            $this->selectedEmployeeName = $asset->assignedTo->name;
        }
    }

    // =================================================================
    // METHODS: UI EVENT HANDLERS (COPY DARI CREATE ANDA)
    // =================================================================

    public function selectParentLocation($id, $name)
    {
        $this->selectedParentId = $id;
        $this->selectedParentName = $name;
        $this->form->location_id = $id; 
        $this->searchParent = ''; 

        // Reset Child
        $this->selectedChildId = '';
        $this->selectedChildName = null;
        $this->searchChild = '';
    }

    public function selectChildLocation($id, $name)
    {
        $this->selectedChildId = $id;
        $this->selectedChildName = $name;
        $this->form->location_id = $id; 
        $this->searchChild = ''; 
    }

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
        if ($this->isEdit) {
            $this->form->update();
            session()->flash('message', 'Data aset berhasil diperbarui.');
        } else {
            $this->form->store();
            session()->flash('message', 'Aset baru berhasil ditambahkan.');
        }
        
        return $this->redirectRoute('assets.index', navigate: true);
    }

    public function render()
    {
        // 1. Query Master Model
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

        // 3. Query Lokasi Parent
        $parents = Location::whereNull('parent_location_id')
            ->when($this->searchParent, fn($q) => $q->where('name', 'like', '%'.$this->searchParent.'%'))
            ->orderBy('name')->take(10)->get();

        // 4. Query Lokasi Child
        $children = [];
        if ($this->selectedParentId) {
            $children = Location::where('parent_location_id', $this->selectedParentId)
                ->when($this->searchChild, fn($q) => $q->where('name', 'like', '%'.$this->searchChild.'%'))
                ->orderBy('name')->take(10)->get();
        }

        // 5. Query Supplier
        $suppliers = Supplier::query()
            ->when($this->searchSupplier, fn($q) => $q->where('name', 'like', '%'.$this->searchSupplier.'%'))
            ->orderBy('name')->take(10)->get();

        // 6. Query Employee
        $employees = Employee::query()
            ->when($this->searchEmployee, function($q) {
                $q->where('name', 'like', '%'.$this->searchEmployee.'%')
                  ->orWhere('email', 'like', '%'.$this->searchEmployee.'%');
            })
            ->orderBy('name')->take(10)->get();

        return view('livewire.assets.form', [
            'models' => $models,
            'statuses' => $statuses,
            'parentLocations' => $parents, 
            'childLocations' => $children, 
            'suppliers' => $suppliers,
            'employees' => $employees,
        ]);
    }
}