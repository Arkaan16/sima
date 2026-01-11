<?php

namespace App\Livewire\Admin\Asset;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Livewire\Forms\AssetForm;
use App\Models\Asset; // Pastikan Model Asset diimport

// Models Relasi
use App\Models\AssetModel;
use App\Models\AssetStatus;
use App\Models\Location;
use App\Models\Supplier;
use App\Models\Employee;

#[Layout('components.layouts.admin')]
#[Title('Edit Aset')]
class AssetEdit extends Component
{
    use WithFileUploads;

    public AssetForm $form;

    // =================================================================
    // SEARCH & UI STATE PROPERTIES (SAMA DENGAN CREATE)
    // =================================================================
    
    // 1. Model
    public $searchModel = '';
    public $selectedModelName = null;
    public $selectedModelImage = null;

    // 2. Status
    public $searchStatus = '';
    public $selectedStatusName = null;

    // 3. Location
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

    // 5. Employee
    public $searchEmployee = '';
    public $selectedEmployeeName = null;

    // =================================================================
    // MOUNT: ISI DATA SAAT EDIT
    // =================================================================
    public function mount(Asset $asset)
    {
        // 1. Isi Form Object
        $this->form->setAsset($asset);

        // 2. POPULASI UI STATE (Dropdown Text & Images)
        
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

        // E. Location (LOGIKA PARENT/CHILD)
        if ($asset->defaultLocation) {
            $location = $asset->defaultLocation;

            if ($location->parent_location_id) {
                // Jika lokasi aset adalah Ruangan (punya parent)
                $this->selectedChildId = $location->id;
                $this->selectedChildName = $location->name;
                
                // Set Parentnya juga
                $this->selectedParentId = $location->parent_location_id;
                $this->selectedParentName = $location->parent->name;
            } else {
                // Jika lokasi aset adalah Gedung/Parent langsung
                $this->selectedParentId = $location->id;
                $this->selectedParentName = $location->name;
            }
        }
    }

    // =================================================================
    // METHODS UI HANDLING (COPY DARI CREATE)
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
        
        // Jika ID kosong (opsi "Umum/Lobby"), kembalikan ke Parent ID
        if (empty($id)) {
            $this->form->location_id = $this->selectedParentId;
        } else {
            $this->form->location_id = $id; 
        }
        
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
        $this->form->update(); // Panggil Method Update
        session()->flash('message', 'Data aset berhasil diperbarui.');
        return $this->redirectRoute('admin.assets.index', navigate: true);
    }

    public function render()
    {
        // Copy Paste Logic Render dari Create
        $models = AssetModel::with(['manufacturer', 'category']) 
            ->when($this->searchModel, function($q) {
                $q->where('name', 'like', '%'.$this->searchModel.'%')
                ->orWhere('model_number', 'like', '%'.$this->searchModel.'%');
            })
            ->orderBy('name')->take(10)->get();

        $statuses = AssetStatus::query()
            ->when($this->searchStatus, fn($q) => $q->where('name', 'like', '%'.$this->searchStatus.'%'))
            ->orderBy('name')->take(10)->get();

        $parents = Location::whereNull('parent_location_id')
            ->when($this->searchParent, fn($q) => $q->where('name', 'like', '%'.$this->searchParent.'%'))
            ->orderBy('name')->take(10)->get();

        $children = [];
        if ($this->selectedParentId) {
            $children = Location::where('parent_location_id', $this->selectedParentId)
                ->when($this->searchChild, fn($q) => $q->where('name', 'like', '%'.$this->searchChild.'%'))
                ->orderBy('name')->take(10)->get();
        }

        $suppliers = Supplier::query()
            ->when($this->searchSupplier, fn($q) => $q->where('name', 'like', '%'.$this->searchSupplier.'%'))
            ->orderBy('name')->take(10)->get();

        $employees = Employee::query()
            ->when($this->searchEmployee, function($q) {
                $q->where('name', 'like', '%'.$this->searchEmployee.'%')
                  ->orWhere('email', 'like', '%'.$this->searchEmployee.'%');
            })
            ->orderBy('name')->take(10)->get();

        return view('livewire.admin.asset.asset-edit', [ // View Edit
            'models' => $models,
            'statuses' => $statuses,
            'parentLocations' => $parents,
            'childLocations' => $children,
            'suppliers' => $suppliers,
            'employees' => $employees,
        ]);
    }
}