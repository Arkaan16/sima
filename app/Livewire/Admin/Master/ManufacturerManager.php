<?php

namespace App\Livewire\Admin\Master;

use App\Models\Manufacturer;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; 
use Illuminate\Validation\Rule; 

/**
 * Class ManufacturerManager
 * * Komponen Livewire untuk menangani CRUD (Create, Read, Update, Delete)
 * pada data Master Pabrikan (Manufacturer).
 * * Fitur Utama:
 * 1. Kelola data kontak support (URL, Email, Telepon).
 * 2. Upload logo pabrikan dengan fitur preview dan hapus file fisik.
 * 3. Validasi nama unik (case-insensitive).
 */
#[Layout('components.layouts.admin')]
#[Title('Kelola Pabrikan')]
class ManufacturerManager extends Component
{
    use WithPagination;
    use WithFileUploads; // Trait wajib untuk menangani file upload
    
    protected $paginationTheme = 'tailwind';

    // ==========================================
    // PROPERTIES (DATA & STATE)
    // ==========================================

    /** @var int|null ID Pabrikan (Primary Key) untuk Edit/Delete */
    public $manufacturerId;

    /** @var string Nama Pabrikan */
    public $name;

    /** @var string|null Website Utama Pabrikan */
    public $url;

    /** @var string|null URL Halaman Support/Driver */
    public $support_url;

    /** @var string|null Nomor Telepon Support */
    public $support_phone;

    /** @var string|null Email Support */
    public $support_email;
    
    // --- Properties Gambar ---
    /** @var mixed File object untuk gambar BARU yang sedang diupload */
    public $newImage; 
    
    /** @var string|null Path string ke gambar LAMA (dari database) */
    public $oldImage; 

    // --- Pencarian & UI States ---
    public $search = '';
    public $showFormModal = false;
    public $showDeleteModal = false;
    public $isEditMode = false;

    // Memastikan parameter search tetap ada di URL browser
    protected $queryString = ['search' => ['except' => '']];

    // ==========================================
    // VALIDASI
    // ==========================================

    /**
     * Menentukan aturan validasi input secara dinamis.
     */
    protected function rules()
    {
        return [
            'name' => [
                'required', 
                'string', 
                'max:255', 
                // Validasi Unik:
                // Cek tabel 'manufacturers', kolom 'name'.
                // Kecualikan ID saat ini ($this->manufacturerId) agar tidak error saat Edit.
                'unique:manufacturers,name,' . $this->manufacturerId 
            ],
            'url' => 'nullable|url|max:255',
            'support_url' => 'nullable|url|max:255',
            'support_phone' => 'nullable|string|max:50',
            'support_email' => 'nullable|email|max:255',
            
            // Validasi Gambar:
            // Hanya jalankan validasi format & size jika user mengupload gambar baru ($newImage)
            'newImage' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    /**
     * Custom pesan error dalam Bahasa Indonesia.
     */
    protected $messages = [
        'name.required' => 'Nama pabrikan wajib diisi.',
        'name.unique' => 'Nama pabrikan ini sudah terdaftar di sistem.',
        'url.url' => 'Format URL website tidak valid.',
        'support_url.url' => 'Format URL dukungan tidak valid.',
        'support_email.email' => 'Format email dukungan tidak valid.',
        
        'newImage.image' => 'File harus berupa gambar.',
        'newImage.mimes' => 'Format gambar harus JPG, JPEG, atau PNG.',
        'newImage.max' => 'Ukuran gambar maksimal 2MB.',
    ];

    // ==========================================
    // LOGIKA RENDER & EVENT
    // ==========================================

    /**
     * Reset pagination ke halaman 1 saat pencarian berubah.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Render view blade utama dengan data tabel.
     */
    public function render()
    {
        return view('livewire.admin.master.manufacturer-manager', [
            'manufacturers' => Manufacturer::query()
                ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                ->latest()
                ->paginate(10),
        ]);
    }

    // ==========================================
    // LOGIKA CRUD & FILE HANDLING
    // ==========================================

    /**
     * Reset seluruh input form kembali bersih.
     */
    public function resetInputFields()
    {
        $this->name = '';
        $this->url = '';
        $this->support_url = '';
        $this->support_phone = '';
        $this->support_email = '';
        
        $this->newImage = null; // Reset upload temporary
        $this->oldImage = null; // Reset path lama
        
        $this->manufacturerId = null; // Reset ID (Mode Create)
        $this->isEditMode = false;
        
        $this->resetErrorBag();
        $this->resetValidation();
    }

    /**
     * Buka modal Tambah Data.
     */
    public function create()
    {
        $this->resetInputFields();
        $this->showFormModal = true;
    }

    /**
     * Buka modal Edit Data.
     * @param int $id ID Pabrikan yang dipilih.
     */
    public function edit($id)
    {
        $this->resetValidation();
        
        // Ambil data dari DB
        $data = Manufacturer::findOrFail($id);

        // Isi properti form
        $this->manufacturerId = $id;
        $this->name = $data->name;
        $this->url = $data->url;
        $this->support_url = $data->support_url;
        $this->support_phone = $data->support_phone;
        $this->support_email = $data->support_email;
        
        // Simpan path gambar lama untuk ditampilkan di preview
        $this->oldImage = $data->image;
        
        $this->newImage = null; // Pastikan upload baru kosong saat awal buka edit
        $this->isEditMode = true;
        $this->showFormModal = true;
    }

    /**
     * Simpan Data (Create atau Update).
     */
    public function store()
    {
        // 1. Normalisasi Nama: Ubah menjadi Title Case (Huruf Besar di Awal Kata)
        // Contoh: "dell indonesia" -> "Dell Indonesia"
        $this->name = Str::title(trim($this->name));

        // 2. Jalankan Validasi
        $this->validate();

        // 3. Logika Upload Gambar
        // Defaultnya gunakan gambar lama (jika ada)
        $imagePath = $this->oldImage;

        // Jika user mengupload gambar baru...
        if ($this->newImage) {
            // A. Hapus gambar lama fisik dari server (clean up storage)
            if ($this->oldImage && Storage::disk('public')->exists($this->oldImage)) {
                Storage::disk('public')->delete($this->oldImage);
            }
            // B. Simpan gambar baru
            $imagePath = $this->newImage->store('manufacturers', 'public');
        }

        // 4. Siapkan Array Data
        $data = [
            'name' => $this->name,
            'url' => $this->url,
            'support_url' => $this->support_url,
            'support_phone' => $this->support_phone,
            'support_email' => $this->support_email,
            'image' => $imagePath, // Path gambar baru atau lama
        ];

        // 5. Simpan ke Database
        if ($this->isEditMode && $this->manufacturerId) {
            // Update
            Manufacturer::findOrFail($this->manufacturerId)->update($data);
            session()->flash('message', 'Data pabrikan berhasil diperbarui.');
        } else {
            // Create
            Manufacturer::create($data);
            session()->flash('message', 'Data pabrikan berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    /**
     * Buka modal konfirmasi hapus.
     */
    public function confirmDelete($id)
    {
        $this->manufacturerId = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Eksekusi Hapus Data.
     */
    public function delete()
    {
        if ($this->manufacturerId) {
            try {
                $manufacturer = Manufacturer::findOrFail($this->manufacturerId);
                
                // 1. Hapus file gambar fisik dari storage jika ada
                if ($manufacturer->image && Storage::disk('public')->exists($manufacturer->image)) {
                    Storage::disk('public')->delete($manufacturer->image);
                }

                // 2. Hapus record database
                $manufacturer->delete();
                session()->flash('message', 'Data pabrikan berhasil dihapus.');
            } catch (\Exception $e) {
                // 3. Tangani error jika data sedang digunakan (Foreign Key Constraint)
                // Contoh: Pabrikan "Dell" tidak bisa dihapus jika ada aset model "Latitude" yang menggunakannya.
                session()->flash('error', 'Gagal menghapus data. Pabrikan ini mungkin masih digunakan oleh aset lain.');
            }
        }
        
        $this->closeModal();
    }

    /**
     * Tutup semua modal & reset form.
     */
    public function closeModal()
    {
        $this->showFormModal = false;
        $this->showDeleteModal = false;
        $this->resetInputFields();
    }
}