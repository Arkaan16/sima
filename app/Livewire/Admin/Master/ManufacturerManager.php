<?php

namespace App\Livewire\Admin\Master;

use App\Models\Manufacturer;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Storage;

// Import Library Kompresi
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * Class ManufacturerManager
 * * Komponen Livewire untuk mengelola data master Pabrikan (Manufacturer).
 * Menyediakan fungsionalitas CRUD lengkap, validasi input URL/Email,
 * serta kompresi otomatis untuk logo pabrikan.
 */
#[Layout('components.layouts.admin')]
#[Title('Kelola Pabrikan')]
class ManufacturerManager extends Component
{
    use WithPagination;
    use WithFileUploads;

    protected $paginationTheme = 'tailwind';

    // ==========================================
    // DATA PROPERTIES
    // ==========================================

    /** @var int|null ID data yang sedang diproses */
    public $manufacturerId;

    // Field Database sesuai Model Manufacturer
    public $name;
    public $url;
    public $support_url;
    public $support_phone;
    public $support_email;

    // Image Handling
    public $newImage; // Upload baru (Temporary)
    public $oldImage; // Path gambar lama

    // UI State
    public $search = '';
    public $showFormModal = false;
    public $showDeleteModal = false;
    public $isEditMode = false;

    // ==========================================
    // VALIDATION RULES
    // ==========================================

    protected function rules()
    {
        return [
            'name' => [
                'required', 
                'string', 
                'max:255', 
                'unique:manufacturers,name,' . $this->manufacturerId
            ],
            'url' => 'nullable|url|max:255',
            'support_url' => 'nullable|url|max:255',
            'support_phone' => 'nullable|string|max:20',
            'support_email' => 'nullable|email|max:255',
            // VALIDASI FOTO: Max 10MB (10240 KB)
            'newImage' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama pabrikan wajib diisi.',
        'name.unique' => 'Nama pabrikan ini sudah terdaftar.',
        'url.url' => 'Format URL Website tidak valid (awali dengan http:// atau https://).',
        'support_url.url' => 'Format URL Support tidak valid.',
        'support_email.email' => 'Format email tidak valid.',
        'newImage.max' => 'Ukuran gambar maksimal 10MB.',
        'newImage.image' => 'File harus berupa gambar.',
    ];

    // ==========================================
    // MAIN LOGIC
    // ==========================================

    public function render()
    {
        $manufacturers = Manufacturer::query()
            ->when($this->search, function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('support_email', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.master.manufacturer-manager', [
            'manufacturers' => $manufacturers
        ]);
    }

    // Reset Form
    public function resetInputFields()
    {
        $this->name = '';
        $this->url = '';
        $this->support_url = '';
        $this->support_phone = '';
        $this->support_email = '';
        
        $this->newImage = null;
        $this->oldImage = null;
        
        $this->manufacturerId = null;
        $this->isEditMode = false;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    // Buka Modal Tambah
    public function create()
    {
        $this->resetInputFields();
        $this->showFormModal = true;
    }

    // Buka Modal Edit
    public function edit($id)
    {
        $this->resetValidation();
        $data = Manufacturer::findOrFail($id);

        $this->manufacturerId = $id;
        $this->name = $data->name;
        $this->url = $data->url;
        $this->support_url = $data->support_url;
        $this->support_phone = $data->support_phone;
        $this->support_email = $data->support_email;
        
        $this->oldImage = $data->image;

        $this->isEditMode = true;
        $this->showFormModal = true;
    }

    // Simpan Data (Create/Update) dengan Kompresi
    public function store()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'url' => $this->url,
            'support_url' => $this->support_url,
            'support_phone' => $this->support_phone,
            'support_email' => $this->support_email,
        ];

        // --- LOGIKA UPLOAD & KOMPRESI ---
        if ($this->newImage) {
            // Hapus gambar lama jika mode edit
            if ($this->isEditMode && $this->oldImage) {
                if(Storage::disk('public')->exists($this->oldImage)) {
                    Storage::disk('public')->delete($this->oldImage);
                }
            }
            
            // Simpan gambar baru dengan Kompresi Helper
            $data['image'] = $this->compressAndStore($this->newImage);

        } else {
            // Jika tidak ada upload baru, pertahankan yang lama
            if($this->isEditMode) {
                $data['image'] = $this->oldImage;
            }
        }
        // ---------------------------------

        if ($this->isEditMode && $this->manufacturerId) {
            Manufacturer::findOrFail($this->manufacturerId)->update($data);
            session()->flash('message', 'Data pabrikan berhasil diperbarui.');
        } else {
            Manufacturer::create($data);
            session()->flash('message', 'Data pabrikan berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->manufacturerId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->manufacturerId) {
            try {
                $data = Manufacturer::findOrFail($this->manufacturerId);
                
                // Hapus file fisik
                if ($data->image && Storage::disk('public')->exists($data->image)) {
                    Storage::disk('public')->delete($data->image);
                }

                $data->delete();
                session()->flash('message', 'Data pabrikan berhasil dihapus.');
            } catch (\Exception $e) {
                session()->flash('error', 'Gagal menghapus data. Kemungkinan data sedang digunakan pada Model Aset.');
            }
        }
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->showFormModal = false;
        $this->showDeleteModal = false;
        $this->resetInputFields();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // ==========================================
    // HELPERS (COMPRESSION)
    // ==========================================

    /**
     * Mengompresi gambar dan menyimpannya ke storage publik.
     * Logika sama persis dengan AssetModelManager.
     */
    protected function compressAndStore($file)
    {
        $filename = md5($file->getClientOriginalName() . microtime()) . '.jpg';
        // Folder penyimpanan khusus Manufacturers
        $path = 'manufacturers/' . $filename; 

        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());
            
            // Resize (Scale Down) ke lebar maksimal 1200px
            $image->scaleDown(width: 1200);
            
            // Encode ke JPG kualitas 80%
            $encoded = $image->toJpeg(quality: 80);
            
            Storage::disk('public')->put($path, $encoded);

        } catch (\Exception $e) {
            // Fallback: Simpan file asli jika gagal
            $path = $file->storeAs('manufacturers', $filename, 'public');
        }

        return $path;
    }
}