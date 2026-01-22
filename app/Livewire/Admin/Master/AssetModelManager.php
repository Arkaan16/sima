<?php

namespace App\Livewire\Admin\Master;

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Manufacturer;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

#[Layout('components.layouts.admin')]
#[Title('Kelola Model Aset')]
class AssetModelManager extends Component
{
    use WithPagination;
    use WithFileUploads;

    protected $paginationTheme = 'tailwind';

    // ==========================================
    // PROPERTIES
    // ==========================================

    public $assetModelId;
    public $name = '';
    public $model_number = '';
    public $category_id;
    public $manufacturer_id;
    
    // Image Handling
    public $newImage;
    public $oldImage;

    // UI Helper (Dropdown & Search)
    public $categorySearch = ''; 
    public $manufacturerSearch = ''; 
    public $selectedCategoryName = ''; 
    public $selectedManufacturerName = ''; 

    // UI State
    public $isEditMode = false; 
    public $deleteName = ''; // Untuk text konfirmasi hapus

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
            'name' => [
                'required', 'string', 'max:255', 
                Rule::unique('asset_models', 'name')->ignore($this->assetModelId)
            ],
            'model_number' => [
                'nullable', 'string', 'max:255',
                Rule::unique('asset_models', 'model_number')->ignore($this->assetModelId)
            ],
            'category_id' => 'required|exists:categories,id',
            'manufacturer_id' => 'required|exists:manufacturers,id',
            'newImage' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240', 
        ];
    }

    protected $messages = [
        'name.required' => 'Nama model aset wajib diisi.',
        'name.unique' => 'Nama model aset ini sudah terdaftar.',
        'model_number.unique' => 'Nomor model ini sudah digunakan.',
        'category_id.required' => 'Kategori wajib dipilih.',
        'manufacturer_id.required' => 'Pabrikan wajib dipilih.',
        'newImage.image' => 'File harus berupa gambar.',
        'newImage.max' => 'Ukuran gambar maksimal 10MB.',
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
        $model = AssetModel::with('category', 'manufacturer')->find($id);
        if (!$model) return;

        // Isi State
        $this->assetModelId = $model->id;
        $this->name = $model->name;
        $this->model_number = $model->model_number;
        $this->category_id = $model->category_id;
        $this->manufacturer_id = $model->manufacturer_id;
        $this->oldImage = $model->image; // Simpan path gambar lama
        $this->newImage = null; // Reset upload baru
        
        // Isi UI Text untuk Dropdown
        $this->selectedCategoryName = $model->category->name ?? '';
        $this->selectedManufacturerName = $model->manufacturer->name ?? '';

        $this->isEditMode = true;
        $this->resetValidation();

        // Kirim sinyal ke browser
        $this->dispatch('open-modal-form');
    }

    // 3. Persiapan Hapus
    public function confirmDelete($id)
    {
        $model = AssetModel::find($id);
        if (!$model) return;

        $this->assetModelId = $model->id;
        $this->deleteName = $model->name;

        $this->dispatch('open-modal-delete');
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->model_number = '';
        $this->category_id = null;
        $this->manufacturer_id = null;
        $this->newImage = null;
        $this->oldImage = null;

        $this->categorySearch = '';
        $this->manufacturerSearch = '';
        $this->selectedCategoryName = '';
        $this->selectedManufacturerName = '';
        $this->deleteName = '';

        $this->assetModelId = null; 
        $this->isEditMode = false;
        $this->resetErrorBag(); 
        $this->resetValidation();
    }

    public function store()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'model_number' => $this->model_number,
            'category_id' => $this->category_id,
            'manufacturer_id' => $this->manufacturer_id,
        ];

        // Logic Gambar
        if ($this->newImage) {
            // Hapus gambar lama jika ada dan sedang mode edit
            if ($this->isEditMode && $this->oldImage) {
                if(Storage::disk('public')->exists($this->oldImage)) {
                    Storage::disk('public')->delete($this->oldImage);
                }
            }
            // Upload gambar baru
            $data['image'] = $this->compressAndStore($this->newImage);
        }

        if ($this->isEditMode && $this->assetModelId) {
            AssetModel::findOrFail($this->assetModelId)->update($data);
            $message = 'Data model aset berhasil diperbarui.';
        } else {
            AssetModel::create($data);
            $message = 'Data model aset berhasil ditambahkan.';
        }

        session()->flash('message', $message);
        $this->dispatch('close-all-modals');
        $this->resetInputFields();
    }

    public function delete()
    {
        if (!$this->assetModelId) return;

        try {
            $model = AssetModel::findOrFail($this->assetModelId);
            
            // Hapus file fisik gambar jika ada
            if ($model->image && Storage::disk('public')->exists($model->image)) {
                Storage::disk('public')->delete($model->image);
            }

            $model->delete();
            session()->flash('message', 'Data model aset berhasil dihapus.');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus data. Data mungkin sedang digunakan oleh aset lain.');
        }

        // Apapun hasilnya, tutup modal & reset
        $this->dispatch('close-all-modals');
        $this->resetInputFields();
    }

    // ==========================================
    // HELPERS
    // ==========================================

    public function getCategoriesProperty()
    {
        return Category::query()
            ->when($this->categorySearch, fn($q) => $q->where('name', 'like', '%' . $this->categorySearch . '%'))
            ->limit(5)->get();
    }

    public function getManufacturersProperty()
    {
        return Manufacturer::query()
            ->when($this->manufacturerSearch, fn($q) => $q->where('name', 'like', '%' . $this->manufacturerSearch . '%'))
            ->limit(5)->get();
    }

    protected function compressAndStore($file)
    {
        $filename = md5($file->getClientOriginalName() . microtime()) . '.jpg';
        $path = 'asset-models/' . $filename; 

        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());
            $image->scaleDown(width: 1200);
            $encoded = $image->toJpeg(quality: 80);
            Storage::disk('public')->put($path, $encoded);
        } catch (\Exception $e) {
            $path = $file->storeAs('asset-models', $filename, 'public');
        }

        return $path;
    }

    public function render()
    {
        $assetModels = AssetModel::with(['category:id,name', 'manufacturer:id,name,image'])
            ->when($this->search, function($q) {
                $q->where(function($subQ) {
                    $subQ->where('name', 'like', '%' . $this->search . '%') 
                           ->orWhere('model_number', 'like', '%' . $this->search . '%')
                           ->orWhereHas('manufacturer', fn($m) => $m->where('name', 'like', '%' . $this->search . '%'))
                           ->orWhereHas('category', fn($c) => $c->where('name', 'like', '%' . $this->search . '%'));
                });
            })
            ->latest() 
            ->paginate(10); 

        return view('livewire.admin.master.asset-model-manager', [
            'assetModels' => $assetModels,
        ]);
    }
}