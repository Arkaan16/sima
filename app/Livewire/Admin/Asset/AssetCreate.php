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

/**
 * Class AssetCreate
 *
 * Komponen Livewire untuk menangani proses pembuatan aset baru.
 * Komponen ini bertindak sebagai Controller yang menghubungkan UI (View)
 * dengan Logic Validasi (Form Object).
 * * Fitur Utama:
 * - Pencarian real-time (Live Search) untuk relasi data (Model, Lokasi, dll).
 * - Manajemen seleksi lokasi berjenjang (Gedung -> Ruangan).
 * - Penanganan state UI untuk pratinjau data yang dipilih.
 *
 * @package App\Livewire\Admin\Asset
 */
#[Layout('components.layouts.admin')]
#[Title('Tambah Aset Baru')]
class AssetCreate extends Component
{
    use WithFileUploads;

    /**
     * Objek Form Livewire yang menangani validasi dan penyimpanan data.
     * @var AssetForm
     */
    public AssetForm $form;

    // =================================================================
    // SEARCH & UI STATE PROPERTIES
    // =================================================================
    
    // --- Section 1: Model Aset ---

    /** @var string Keyword pencarian untuk Model Aset */
    public $searchModel = '';
    
    /** @var string|null Nama Model yang terpilih (untuk display UI) */
    public $selectedModelName = null;
    
    /** @var string|null URL gambar Model yang terpilih */
    public $selectedModelImage = null;

    // --- Section 2: Status Aset ---

    /** @var string Keyword pencarian untuk Status */
    public $searchStatus = '';
    
    /** @var string|null Nama Status yang terpilih */
    public $selectedStatusName = null;

    // --- Section 3: Lokasi (Hierarki: Gedung -> Ruangan) ---

    /** @var string ID Lokasi Induk (Gedung) yang aktif */
    public $selectedParentId = ''; 
    
    /** @var string Keyword pencarian Gedung */
    public $searchParent = '';      
    
    /** @var string|null Nama Gedung yang terpilih */
    public $selectedParentName = null;

    /** @var string ID Lokasi Anak (Ruangan) yang aktif */
    public $selectedChildId = '';
    
    /** @var string Keyword pencarian Ruangan */
    public $searchChild = '';       
    
    /** @var string|null Nama Ruangan yang terpilih */
    public $selectedChildName = null;

    // --- Section 4: Supplier ---

    /** @var string Keyword pencarian Supplier */
    public $searchSupplier = '';
    
    /** @var string|null Nama Supplier yang terpilih */
    public $selectedSupplierName = null;
    
    /** @var string|null URL Logo Supplier */
    public $selectedSupplierImage = null;

    // --- Section 5: Karyawan (Penanggung Jawab) ---

    /** @var string Keyword pencarian Karyawan */
    public $searchEmployee = '';
    
    /** @var string|null Nama Karyawan yang terpilih */
    public $selectedEmployeeName = null;

    // =================================================================
    // METHODS: UI EVENT HANDLERS
    // =================================================================

    /**
     * Menangani pemilihan Lokasi Induk (Gedung).
     * Saat gedung dipilih, data ruangan (child) harus di-reset untuk menjaga konsistensi.
     *
     * @param int|string $id ID Gedung
     * @param string $name Nama Gedung
     */
    public function selectParentLocation($id, $name)
    {
        // 1. Set State Lokasi Induk
        $this->selectedParentId = $id;
        $this->selectedParentName = $name;
        
        // 2. Update Form: Set location_id sementara ke Gedung
        $this->form->location_id = $id; 
        $this->searchParent = ''; 

        // 3. Reset State Lokasi Anak (Ruangan)
        // Mencegah user memilih Ruangan dari Gedung yang berbeda
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
        
        // 2. Update Form: Override location_id menjadi ID Ruangan (Lokasi Spesifik)
        $this->form->location_id = $id; 
        $this->searchChild = ''; 
    }

    /**
     * Handler generik untuk memilih opsi dari dropdown (Model, Status, Supplier, Employee).
     * Mengurangi duplikasi kode untuk logika seleksi yang serupa.
     *
     * @param string $field Nama properti di AssetForm (misal: 'asset_model_id')
     * @param int|string $id ID data yang dipilih
     * @param string $displayName Label nama untuk tampilan UI
     * @param string $searchProp Nama properti pencarian yang harus di-reset
     * @param string|null $image URL gambar (opsional)
     */
    public function selectOption($field, $id, $displayName, $searchProp, $image = null)
    {
        // 1. Update ID ke Form Object
        $this->form->$field = $id;

        // 2. Tentukan Properti UI secara Dinamis
        // Mengubah 'searchModel' menjadi 'selectedModelName'
        $type = str_replace('search', '', $searchProp); 
        $propName = 'selected' . $type . 'Name'; 
        $this->$propName = $displayName;

        // 3. Handle Kasus Khusus (Gambar)
        if ($searchProp === 'searchModel') {
            $this->selectedModelImage = $image;
        } elseif ($searchProp === 'searchSupplier') {
            $this->selectedSupplierImage = $image;
        }

        // 4. Reset Keyword Pencarian
        $this->$searchProp = ''; 
    }

    /**
     * Mengeksekusi penyimpanan data aset.
     *
     * @return \Illuminate\Routing\Redirector
     */
    public function save()
    {
        // 1. Delegasikan proses simpan ke Form Object
        $this->form->store();
        
        // 2. Berikan Feedback User
        session()->flash('message', 'Aset baru berhasil ditambahkan.');
        
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
        // 1. Query Master Model (dengan Eager Loading)
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

        // 3. Logika Query Lokasi Hierarkis
        
        // A. Query Parent (Gedung) - Hanya lokasi root (parent_id null)
        $parents = Location::whereNull('parent_location_id')
            ->when($this->searchParent, fn($q) => $q->where('name', 'like', '%'.$this->searchParent.'%'))
            ->orderBy('name')
            ->take(10)
            ->get();

        // B. Query Child (Ruangan) - Hanya jika Parent sudah dipilih
        $children = [];
        if ($this->selectedParentId) {
            $children = Location::where('parent_location_id', $this->selectedParentId)
                ->when($this->searchChild, fn($q) => $q->where('name', 'like', '%'.$this->searchChild.'%'))
                ->orderBy('name')
                ->take(10)
                ->get();
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
        return view('livewire.admin.asset.asset-create', [
            'models' => $models,
            'statuses' => $statuses,
            'parentLocations' => $parents, 
            'childLocations' => $children, 
            'suppliers' => $suppliers,
            'employees' => $employees,
        ]);
    }
}