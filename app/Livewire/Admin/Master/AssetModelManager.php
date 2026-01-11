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

/**
 * Class AssetModelManager
 * * Komponen Livewire untuk menangani CRUD (Create, Read, Update, Delete)
 * pada data Model Aset.
 */
#[Layout('components.layouts.admin')]
#[Title('Kelola Model Aset')]
class AssetModelManager extends Component
{
    // Menggunakan trait untuk Pagination dan Upload File
    use WithPagination;
    use WithFileUploads;

    // Mengatur tema pagination agar sesuai dengan Tailwind CSS
    protected $paginationTheme = 'tailwind';

    // ==========================================
    // PROPERTIES (VARIABEL)
    // ==========================================

    /** @var int|null ID dari model aset yang sedang diedit (untuk pengecualian validasi unique) */
    public $assetModelId;

    /** @var string Nama model aset (Input Form) */
    public $name;

    /** @var string|null Nomor model aset (Input Form) */
    public $model_number;

    /** @var int|null Foreign Key untuk Kategori */
    public $category_id;

    /** @var int|null Foreign Key untuk Pabrikan */
    public $manufacturer_id;
    
    // --- Properties Gambar ---
    /** @var mixed File gambar baru yang diupload (Temporary) */
    public $newImage;

    /** @var stringPath Path gambar lama untuk preview saat edit */
    public $oldImage;

    // --- Properties Helper untuk UI Dropdown ---
    public $categorySearch = ''; // Keyword pencarian dropdown kategori
    public $manufacturerSearch = ''; // Keyword pencarian dropdown pabrikan
    public $selectedCategoryName = ''; // Nama kategori terpilih (tampil di input)
    public $selectedManufacturerName = ''; // Nama pabrikan terpilih (tampil di input)

    // --- State UI (Kondisi Tampilan) ---
    public $search = ''; // Pencarian tabel utama
    public $showFormModal = false; // Kontrol tampil/sembunyi modal form
    public $showDeleteModal = false; // Kontrol tampil/sembunyi modal hapus
    public $isEditMode = false; // Penanda apakah sedang mode Edit atau Tambah Baru

    // Memastikan parameter search tetap ada di URL browser
    protected $queryString = ['search' => ['except' => '']];

    // ==========================================
    // VALIDASI & PESAN
    // ==========================================

    /**
     * Menentukan aturan validasi input.
     * Dipanggil otomatis saat $this->validate().
     */
    protected function rules()
    {
        return [
            // Validasi Nama:
            // 1. Wajib diisi (required)
            // 2. Berupa text (string)
            // 3. Unik di tabel asset_models kolom name, KECUALI id yang sedang diedit ($this->assetModelId)
            'name' => [
                'required', 
                'string', 
                'max:255', 
                'unique:asset_models,name,' . $this->assetModelId
            ],

            // Validasi No Model:
            // Boleh kosong (nullable), tapi jika diisi harus unik (kecuali dirinya sendiri)
            'model_number' => [
                'nullable', 
                'string', 
                'max:255',
                'unique:asset_models,model_number,' . $this->assetModelId
            ],

            // Validasi Relasi:
            // Harus ada id-nya di tabel categories dan manufacturers
            'category_id' => 'required|exists:categories,id',
            'manufacturer_id' => 'required|exists:manufacturers,id',
            
            // Validasi Upload Gambar:
            // Boleh kosong, harus file gambar, format tertentu, max 2MB
            'newImage' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', 
        ];
    }

    /**
     * Custom pesan error dalam Bahasa Indonesia.
     */
    protected $messages = [
        'name.required' => 'Nama model aset wajib diisi.',
        'name.unique' => 'Nama model aset ini sudah terdaftar. Gunakan nama lain.',
        'model_number.unique' => 'Nomor model ini sudah digunakan oleh aset lain.',
        'category_id.required' => 'Kategori wajib dipilih.',
        'manufacturer_id.required' => 'Pabrikan wajib dipilih.',
        'newImage.image' => 'File harus berupa gambar.',
        'newImage.max' => 'Ukuran gambar maksimal 2MB.',
        'newImage.mimes' => 'Format gambar harus jpg, jpeg, png, atau webp.',
    ];

    // ==========================================
    // LOGIKA PENCARIAN & DROPDOWN
    // ==========================================

    /**
     * Dijalankan otomatis saat variable $search berubah.
     * Reset halaman ke 1 agar hasil pencarian tidak kosong jika ada di halaman jauh.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Computed Property untuk mengambil daftar Kategori.
     * Digunakan untuk dropdown pencarian dinamis.
     */
    public function getCategoriesProperty()
    {
        // Query: Cari kategori berdasarkan nama yang diketik user, limit 5 hasil
        return Category::query()
            ->when($this->categorySearch, fn($q) => $q->where('name', 'like', '%' . $this->categorySearch . '%'))
            ->limit(5)->get();
    }

    /**
     * Computed Property untuk mengambil daftar Pabrikan.
     */
    public function getManufacturersProperty()
    {
        // Query: Cari pabrikan berdasarkan nama yang diketik user, limit 5 hasil
        return Manufacturer::query()
            ->when($this->manufacturerSearch, fn($q) => $q->where('name', 'like', '%' . $this->manufacturerSearch . '%'))
            ->limit(5)->get();
    }

    /**
     * Event saat user memilih item dari dropdown Kategori.
     */
    public function selectCategory($id, $name)
    {
        $this->category_id = $id;          // Set ID untuk disimpan ke DB
        $this->selectedCategoryName = $name; // Set Nama untuk ditampilkan di Input
        $this->categorySearch = '';        // Bersihkan pencarian dropdown
    }

    /**
     * Event saat user memilih item dari dropdown Pabrikan.
     */
    public function selectManufacturer($id, $name)
    {
        $this->manufacturer_id = $id;
        $this->selectedManufacturerName = $name;
        $this->manufacturerSearch = '';
    }

    // ==========================================
    // LOGIKA UTAMA (RENDER & CRUD)
    // ==========================================

    /**
     * Render tampilan blade.
     * Mengambil data dari database dengan filter dan pagination.
     */
    public function render()
    {
        $assetModels = AssetModel::with([
                // 1. OPTIMASI MEMORY:
                // Hanya ambil kolom 'id' dan 'name' dari tabel categories.
                // (Kolom lain yang tidak dipakai tidak perlu ditarik ke RAM)
                'category:id,name', 
                
                // Hanya ambil 'id', 'name', dan 'image' dari manufacturers.
                'manufacturer:id,name,image' 
            ])
            // 2. LOGIC PENCARIAN (SEARCH):
            ->when($this->search, function($q) {
                // Kita bungkus dalam where(function(...)) agar logic OR tidak bocor.
                // Ini penting supaya query-nya jadi: (Kondisi A ATAU B ATAU C) AND (Data Tidak Terhapus)
                $q->where(function($subQ) {
                    $subQ->where('name', 'like', '%' . $this->search . '%') // Cari Nama Model
                         ->orWhere('model_number', 'like', '%' . $this->search . '%') // Cari No. Model
                         
                         // Cari berdasarkan Nama Pabrikan (Relasi)
                         ->orWhereHas('manufacturer', function($m) {
                             $m->where('name', 'like', '%' . $this->search . '%');
                         })
                         
                         // (Opsional) Cari berdasarkan Nama Kategori (Relasi)
                         // Saya tambahkan ini agar user bisa mencari "Laptop" dan muncul semua model laptop.
                         ->orWhereHas('category', function($c) {
                             $c->where('name', 'like', '%' . $this->search . '%');
                         });
                });
            })
            ->latest() // Urutkan dari yang terbaru
            ->paginate(10); // Batasi 10 data per halaman

        return view('livewire.admin.master.asset-model-manager', [
            'assetModels' => $assetModels,
        ]);
    }

    /**
     * Reset semua form input menjadi kosong.
     * Dipanggil sebelum membuka modal Create atau setelah Simpan.
     */
    public function resetInputFields()
    {
        // Reset data text
        $this->name = '';
        $this->model_number = '';
        $this->category_id = null;
        $this->manufacturer_id = null;
        
        // Reset data gambar
        $this->newImage = null;
        $this->oldImage = null;

        // Reset state dropdown
        $this->categorySearch = '';
        $this->manufacturerSearch = '';
        $this->selectedCategoryName = '';
        $this->selectedManufacturerName = '';

        // Reset state modal dan error
        $this->assetModelId = null; // Penting: Hapus ID agar tidak dianggap Edit Mode
        $this->isEditMode = false;
        $this->resetErrorBag(); // Hapus pesan error validasi merah-merah
        $this->resetValidation();
    }

    /**
     * Membuka modal untuk Tambah Data Baru.
     */
    public function create()
    {
        $this->resetInputFields(); // Pastikan form bersih
        $this->showFormModal = true; // Tampilkan modal
    }

    /**
     * Membuka modal untuk Edit Data.
     * @param int $id ID dari data yang akan diedit.
     */
    public function edit($id)
    {
        $this->resetValidation();
        // Cari data, jika tidak ketemu tampilkan 404
        $data = AssetModel::with(['category', 'manufacturer'])->findOrFail($id);

        // Isi property komponen dengan data dari database
        $this->assetModelId = $id; // Set ID (kunci untuk update dan validasi unique)
        $this->name = $data->name;
        $this->model_number = $data->model_number;
        $this->category_id = $data->category_id;
        $this->manufacturer_id = $data->manufacturer_id;
        
        // Simpan path gambar lama untuk preview
        $this->oldImage = $data->image;

        // Set nama untuk dropdown (agar user melihat nama yang tersimpan)
        $this->selectedCategoryName = $data->category->name ?? '';
        $this->selectedManufacturerName = $data->manufacturer->name ?? '';

        $this->isEditMode = true; // Set flag Edit Mode aktif
        $this->showFormModal = true;
    }

    /**
     * Fungsi Simpan (Bisa Create atau Update).
     */
    public function store()
    {
        // 1. Jalankan Validasi sesuai rules()
        // Code akan berhenti di sini jika validasi gagal
        $this->validate();

        // 2. Siapkan data array untuk disimpan
        $data = [
            'name' => $this->name,
            'model_number' => $this->model_number,
            'category_id' => $this->category_id,
            'manufacturer_id' => $this->manufacturer_id,
        ];

        // 3. Logika Penanganan Upload Gambar
        if ($this->newImage) {
            // A. Jika ada upload gambar baru...
            
            // Cek: Jika ini Edit Mode DAN ada gambar lama di server, Hapus dulu fisiknya
            if ($this->isEditMode && $this->oldImage) {
                Storage::disk('public')->delete($this->oldImage);
            }
            // Simpan gambar baru ke folder 'asset-models' dan ambil path-nya
            $data['image'] = $this->newImage->store('asset-models', 'public');
        } else {
            // B. Jika tidak ada upload baru...
            
            // Jika Edit Mode, pastikan path gambar tetap pakai yang lama (jangan dikosongkan)
            if($this->isEditMode) {
                $data['image'] = $this->oldImage;
            }
        }

        // 4. Simpan ke Database
        if ($this->isEditMode && $this->assetModelId) {
            // Update Data
            AssetModel::findOrFail($this->assetModelId)->update($data);
            session()->flash('message', 'Data model aset berhasil diperbarui.');
        } else {
            // Create Data Baru
            AssetModel::create($data);
            session()->flash('message', 'Data model aset berhasil ditambahkan.');
        }

        // 5. Tutup Modal
        $this->closeModal();
    }

    /**
     * Konfirmasi sebelum menghapus data.
     */
    public function confirmDelete($id)
    {
        $this->assetModelId = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Eksekusi Hapus Data.
     */
    public function delete()
    {
        if ($this->assetModelId) {
            try {
                $model = AssetModel::findOrFail($this->assetModelId);
                
                // Hapus file gambar fisik dari storage jika ada
                if ($model->image) {
                    Storage::disk('public')->delete($model->image);
                }

                // Hapus record dari database
                $model->delete();
                session()->flash('message', 'Data model aset berhasil dihapus.');
            } catch (\Exception $e) {
                // Tangani error jika data sedang digunakan di tabel lain (Foreign Key Constraint)
                session()->flash('error', 'Gagal menghapus data. Data mungkin sedang digunakan pada aset lain.');
            }
        }
        $this->closeModal();
    }

    /**
     * Menutup semua modal dan mereset input.
     */
    public function closeModal()
    {
        $this->showFormModal = false;
        $this->showDeleteModal = false;
        $this->resetInputFields();
    }
}