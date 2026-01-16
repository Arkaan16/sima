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

// Tambahan Import untuk Kompresi Gambar
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * Class AssetModelManager
 * * Komponen Livewire untuk mengelola data master Model Aset (Asset Models).
 * Menyediakan fungsionalitas CRUD lengkap, termasuk:
 * - Pagination dan pencarian data.
 * - Form modal untuk Tambah dan Edit data.
 * - Validasi input unik.
 * - Pengelolaan upload gambar (termasuk hapus file fisik saat update/delete).
 * - Fitur dropdown pencarian untuk Kategori dan Pabrikan.
 */
#[Layout('components.layouts.admin')]
#[Title('Kelola Model Aset')]
class AssetModelManager extends Component
{
    use WithPagination;
    use WithFileUploads;

    protected $paginationTheme = 'tailwind';

    // ==========================================
    // DATA PROPERTIES
    // ==========================================

    /** @var int|null ID data yang sedang diproses (untuk Edit/Delete) */
    public $assetModelId;

    /** @var string Nama model aset */
    public $name;

    /** @var string|null Nomor seri/model aset */
    public $model_number;

    /** @var int|null ID Kategori terpilih */
    public $category_id;

    /** @var int|null ID Pabrikan terpilih */
    public $manufacturer_id;
    
    // --- Image Handling ---
    /** @var mixed File upload baru (Temporary) */
    public $newImage;

    /** @var string|null Path gambar lama (untuk referensi saat update/delete) */
    public $oldImage;

    // --- Dropdown UI Helper ---
    public $categorySearch = ''; 
    public $manufacturerSearch = ''; 
    public $selectedCategoryName = ''; 
    public $selectedManufacturerName = ''; 

    // --- UI State ---
    public $search = ''; 
    public $showFormModal = false; 
    public $showDeleteModal = false; 
    public $isEditMode = false; 

    protected $queryString = ['search' => ['except' => '']];

    // ==========================================
    // VALIDATION LOGIC
    // ==========================================

    /**
     * Mendefinisikan aturan validasi.
     * Mengatur validasi unik pada nama dan nomor model dengan pengecualian ID saat update.
     * @return array Rules validasi Laravel.
     */
    protected function rules()
    {
        return [
            'name' => [
                'required', 
                'string', 
                'max:255', 
                'unique:asset_models,name,' . $this->assetModelId
            ],
            'model_number' => [
                'nullable', 
                'string', 
                'max:255',
                'unique:asset_models,model_number,' . $this->assetModelId
            ],
            'category_id' => 'required|exists:categories,id',
            'manufacturer_id' => 'required|exists:manufacturers,id',
            'newImage' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240', 
        ];
    }

    /**
     * Pesan error kustom untuk validasi.
     * @var array
     */
    protected $messages = [
        'name.required' => 'Nama model aset wajib diisi.',
        'name.unique' => 'Nama model aset ini sudah terdaftar. Gunakan nama lain.',
        'model_number.unique' => 'Nomor model ini sudah digunakan oleh aset lain.',
        'category_id.required' => 'Kategori wajib dipilih.',
        'manufacturer_id.required' => 'Pabrikan wajib dipilih.',
        'newImage.image' => 'File harus berupa gambar.',
        'newImage.max' => 'Ukuran gambar maksimal 10MB.',
        'newImage.mimes' => 'Format gambar harus jpg, jpeg, png, atau webp.',
    ];

    // ==========================================
    // SEARCH & DROPDOWN LOGIC
    // ==========================================

    /**
     * Reset pagination saat query pencarian utama berubah.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Mengambil daftar Kategori berdasarkan keyword pencarian dropdown.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCategoriesProperty()
    {
        return Category::query()
            ->when($this->categorySearch, fn($q) => $q->where('name', 'like', '%' . $this->categorySearch . '%'))
            ->limit(5)->get();
    }

    /**
     * Mengambil daftar Pabrikan berdasarkan keyword pencarian dropdown.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getManufacturersProperty()
    {
        return Manufacturer::query()
            ->when($this->manufacturerSearch, fn($q) => $q->where('name', 'like', '%' . $this->manufacturerSearch . '%'))
            ->limit(5)->get();
    }

    /**
     * Handler seleksi item dropdown Kategori.
     */
    public function selectCategory($id, $name)
    {
        $this->category_id = $id;          
        $this->selectedCategoryName = $name; 
        $this->categorySearch = '';        
    }

    /**
     * Handler seleksi item dropdown Pabrikan.
     */
    public function selectManufacturer($id, $name)
    {
        $this->manufacturer_id = $id;
        $this->selectedManufacturerName = $name;
        $this->manufacturerSearch = '';
    }

    // ==========================================
    // MAIN LOGIC (CRUD & RENDER)
    // ==========================================

    /**
     * Merender view komponen.
     * Melakukan query data AssetModel dengan optimasi select kolom dan filter pencarian kompleks (OR WhereHas).
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $assetModels = AssetModel::with([
                'category:id,name', 
                'manufacturer:id,name,image' 
            ])
            ->when($this->search, function($q) {
                $q->where(function($subQ) {
                    $subQ->where('name', 'like', '%' . $this->search . '%') 
                           ->orWhere('model_number', 'like', '%' . $this->search . '%')
                           ->orWhereHas('manufacturer', function($m) {
                               $m->where('name', 'like', '%' . $this->search . '%');
                           })
                           ->orWhereHas('category', function($c) {
                               $c->where('name', 'like', '%' . $this->search . '%');
                           });
                });
            })
            ->latest() 
            ->paginate(10); 

        return view('livewire.admin.master.asset-model-manager', [
            'assetModels' => $assetModels,
        ]);
    }

    /**
     * Mereset seluruh state input form dan validasi ke kondisi awal.
     */
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

        $this->assetModelId = null; 
        $this->isEditMode = false;
        $this->resetErrorBag(); 
        $this->resetValidation();
    }

    /**
     * Inisialisasi modal untuk pembuatan data baru.
     */
    public function create()
    {
        $this->resetInputFields(); 
        $this->showFormModal = true; 
    }

    /**
     * Inisialisasi modal untuk pengeditan data.
     * Mengisi form dengan data existing dari database.
     * @param int $id
     */
    public function edit($id)
    {
        $this->resetValidation();
        $data = AssetModel::with(['category', 'manufacturer'])->findOrFail($id);

        $this->assetModelId = $id; 
        $this->name = $data->name;
        $this->model_number = $data->model_number;
        $this->category_id = $data->category_id;
        $this->manufacturer_id = $data->manufacturer_id;
        
        $this->oldImage = $data->image;

        $this->selectedCategoryName = $data->category->name ?? '';
        $this->selectedManufacturerName = $data->manufacturer->name ?? '';

        $this->isEditMode = true; 
        $this->showFormModal = true;
    }

    /**
     * Menyimpan data (Create atau Update).
     * Menangani logika validasi, upload/hapus gambar fisik, dan persistensi database.
     */
    public function store()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'model_number' => $this->model_number,
            'category_id' => $this->category_id,
            'manufacturer_id' => $this->manufacturer_id,
        ];

        // Logika Upload Gambar
        if ($this->newImage) {
            // Hapus gambar lama jika ada (saat update)
            if ($this->isEditMode && $this->oldImage) {
                if(Storage::disk('public')->exists($this->oldImage)) {
                    Storage::disk('public')->delete($this->oldImage);
                }
            }
            
            // Menggunakan helper compressAndStore alih-alih store biasa
            $data['image'] = $this->compressAndStore($this->newImage);

        } else {
            // Pertahankan gambar lama jika tidak ada upload baru saat update
            if($this->isEditMode) {
                $data['image'] = $this->oldImage;
            }
        }

        if ($this->isEditMode && $this->assetModelId) {
            AssetModel::findOrFail($this->assetModelId)->update($data);
            session()->flash('message', 'Data model aset berhasil diperbarui.');
        } else {
            AssetModel::create($data);
            session()->flash('message', 'Data model aset berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    /**
     * Menampilkan modal konfirmasi hapus.
     */
    public function confirmDelete($id)
    {
        $this->assetModelId = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Menghapus data secara permanen.
     * Menghapus file gambar fisik terkait sebelum menghapus record database.
     */
    public function delete()
    {
        if ($this->assetModelId) {
            try {
                $model = AssetModel::findOrFail($this->assetModelId);
                
                if ($model->image) {
                    Storage::disk('public')->delete($model->image);
                }

                $model->delete();
                session()->flash('message', 'Data model aset berhasil dihapus.');
            } catch (\Exception $e) {
                session()->flash('error', 'Gagal menghapus data. Data mungkin sedang digunakan pada aset lain.');
            }
        }
        $this->closeModal();
    }

    /**
     * Menutup modal dan membersihkan state form.
     */
    public function closeModal()
    {
        $this->showFormModal = false;
        $this->showDeleteModal = false;
        $this->resetInputFields();
    }

    // ==========================================
    // HELPERS (COMPRESSION)
    // ==========================================

    /**
     * Mengompresi gambar dan menyimpannya ke storage publik.
     * Menggunakan Intervention Image untuk resize dan optimasi JPEG.
     *
     * @param \Illuminate\Http\UploadedFile $file File gambar asli
     * @return string Path relatif file yang disimpan
     */
    protected function compressAndStore($file)
    {
        $filename = md5($file->getClientOriginalName() . microtime()) . '.jpg';
        $path = 'asset-models/' . $filename; // Folder disesuaikan untuk modul ini

        try {
            // Inisialisasi Image Manager (Driver GD)
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());
            
            // Resize (Scale Down) ke lebar maksimal 1200px
            $image->scaleDown(width: 1200);
            
            // Encode ke JPG kualitas 80%
            $encoded = $image->toJpeg(quality: 80);
            
            // Simpan ke disk
            Storage::disk('public')->put($path, $encoded);

        } catch (\Exception $e) {
            // Fallback: Simpan file asli jika kompresi gagal
            $path = $file->storeAs('asset-models', $filename, 'public');
        }

        return $path;
    }
}