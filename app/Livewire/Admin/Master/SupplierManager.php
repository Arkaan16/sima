<?php

namespace App\Livewire\Admin\Master;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

// Import Library Kompresi
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * Class SupplierManager
 *
 * Komponen Livewire untuk menangani manajemen data Master Supplier (Pemasok).
 * Mencakup fitur CRUD lengkap, pencarian multi-kolom, manajemen upload gambar,
 * serta penanganan validasi integritas database.
 *
 * @package App\Livewire\Admin\Master
 */
#[Layout('components.layouts.admin')]
#[Title('Kelola Supplier')]
class SupplierManager extends Component
{
    use WithPagination;
    use WithFileUploads;

    protected $paginationTheme = 'tailwind';

    // ==========================================
    // PROPERTIES
    // ==========================================

    /** @var int|null ID Supplier untuk operasi Edit/Delete */
    public $supplierId;

    // --- Form Inputs ---
    public $name;
    public $contact_name;
    public $email;
    public $phone;
    public $address;
    public $url;

    // --- Manajemen File Gambar ---
    /** @var mixed Objek file upload sementara */
    public $newImage;
    /** @var stringPath Path gambar lama untuk referensi penghapusan */
    public $oldImage;

    // --- UI State Flags ---
    public $search = '';
    public $showFormModal = false;
    public $showDeleteModal = false;
    public $isEditMode = false;

    /** @var array Konfigurasi Query String untuk mempertahankan state pencarian di URL */
    protected $queryString = ['search' => ['except' => '']];

    // ==========================================
    // VALIDATION
    // ==========================================

    /**
     * Mendefinisikan aturan validasi untuk input form.
     * Mengatur validasi unik pada nama supplier dengan pengecualian untuk record yang sedang diedit.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'name' => [
                'required', 
                'string', 
                'max:255', 
                'unique:suppliers,name,' . $this->supplierId
            ],
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^([0-9\s\-\+\(\)]*)$/'],
            'address' => 'nullable|string|max:1000',
            'url' => 'nullable|url|max:255',
            'newImage' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240', 
        ];
    }

    /**
     * Menyediakan pesan error kustom untuk validasi.
     *
     * @var array
     */
    protected $messages = [
        'name.required' => 'Nama supplier wajib diisi.',
        'name.unique' => 'Supplier ini sudah terdaftar.',
        'email.email' => 'Format email tidak valid.',
        'url.url' => 'Format URL harus valid (diawali http:// atau https://).',
        'phone.numeric' => 'Nomor telepon harus berupa angka.',
        'phone.regex' => 'Format nomor telepon tidak valid (hanya boleh angka, +, -).',
        'phone.max'   => 'Nomor telepon terlalu panjang (maksimal 20 karakter).',
        'newImage.image' => 'File harus berupa gambar.',
        'newImage.max' => 'Ukuran gambar maksimal 10MB.',
    ];

    // ==========================================
    // LOGIC & LIFECYCLE
    // ==========================================

    /**
     * Hook lifecycle: Mereset pagination ke halaman 1 saat keyword pencarian berubah.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Merender tampilan komponen dengan data supplier yang terfilter.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // 1. Inisialisasi Query Builder
        $suppliers = Supplier::query()
            // 2. Terapkan Filter Pencarian (Grouped Where Clause)
            ->when($this->search, function($q) {
                $q->where(function($sub) {
                    $sub->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('contact_name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            // 3. Urutkan dan Pagination
            ->latest()
            ->paginate(10);

        return view('livewire.admin.master.supplier-manager', [
            'suppliers' => $suppliers
        ]);
    }

    /**
     * Mengembalikan seluruh state form input ke nilai awal (kosong).
     */
    public function resetInputFields()
    {
        $this->name = '';
        $this->contact_name = '';
        $this->email = '';
        $this->phone = '';
        $this->address = '';
        $this->url = '';
        
        $this->newImage = null;
        $this->oldImage = null;

        $this->supplierId = null;
        $this->isEditMode = false;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    /**
     * Menyiapkan dan menampilkan modal untuk pembuatan data baru.
     */
    public function create()
    {
        $this->resetInputFields();
        $this->showFormModal = true;
    }

    /**
     * Menyiapkan dan menampilkan modal edit dengan data existing.
     *
     * @param int $id ID Supplier yang akan diedit
     */
    public function edit($id)
    {
        $this->resetValidation();
        
        // 1. Ambil data dari database
        $data = Supplier::findOrFail($id);

        // 2. Isi state properti dengan data yang ditemukan
        $this->supplierId = $id;
        $this->name = $data->name;
        $this->contact_name = $data->contact_name;
        $this->email = $data->email;
        $this->phone = $data->phone;
        $this->address = $data->address;
        $this->url = $data->url;
        $this->oldImage = $data->image;

        // 3. Tampilkan modal dalam mode edit
        $this->isEditMode = true;
        $this->showFormModal = true;
    }

    /**
     * Menangani logika penyimpanan data (Create dan Update).
     * Termasuk penanganan upload file gambar dan pembersihan file lama.
     */
    public function store()
    {
        // 1. Validasi Input Form
        $this->validate();

        // 2. Persiapkan Payload Data Dasar
        $data = [
            'name' => $this->name,
            'contact_name' => $this->contact_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'url' => $this->url,
        ];

        // 3. Logika Upload Gambar & Kompresi
        if ($this->newImage) {
            // Hapus fisik gambar lama jika dalam mode edit
            if ($this->isEditMode && $this->oldImage) {
                if (Storage::disk('public')->exists($this->oldImage)) {
                    Storage::disk('public')->delete($this->oldImage);
                }
            }
            
            // Menggunakan helper compressAndStore alih-alih store biasa
            $data['image'] = $this->compressAndStore($this->newImage);

        } else {
            // Jika tidak ada gambar baru, pertahankan path lama (hanya saat edit)
            if ($this->isEditMode) {
                $data['image'] = $this->oldImage;
            }
        }

        // 4. Eksekusi Penyimpanan ke Database
        if ($this->isEditMode && $this->supplierId) {
            Supplier::findOrFail($this->supplierId)->update($data);
            session()->flash('message', 'Data pemasok berhasil diperbarui.');
        } else {
            Supplier::create($data);
            session()->flash('message', 'Pemasok baru berhasil ditambahkan.');
        }

        // 5. Tutup Modal & Reset Form
        $this->closeModal();
    }

    /**
     * Menampilkan konfirmasi sebelum menghapus data.
     *
     * @param int $id ID Supplier yang akan dihapus
     */
    public function confirmDelete($id)
    {
        $this->supplierId = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Mengeksekusi penghapusan data secara permanen.
     * Menangani penghapusan file fisik dan pengecekan foreign key constraint.
     */
    public function delete()
    {
        if ($this->supplierId) {
            try {
                // 1. Cari data supplier
                $supplier = Supplier::findOrFail($this->supplierId);
                
                // 2. Hapus file gambar fisik jika ada
                if ($supplier->image) {
                    Storage::disk('public')->delete($supplier->image);
                }

                // 3. Hapus record dari database
                $supplier->delete();
                session()->flash('message', 'Data pemasok berhasil dihapus.');

            } catch (QueryException $e) {
                // 4. Penanganan Error Constraint Database (Foreign Key)
                if ($e->getCode() == 23000) {
                    session()->flash('error', 'Gagal menghapus: Pemasok ini sedang digunakan oleh data aset/stok.');
                } else {
                    session()->flash('error', 'Terjadi kesalahan database saat menghapus data.');
                }
            } catch (\Exception $e) {
                // 5. Penanganan Error Umum
                session()->flash('error', 'Terjadi kesalahan sistem.');
            }
        }
        
        // 6. Tutup modal
        $this->closeModal();
    }

    /**
     * Menutup semua modal yang aktif dan membersihkan state.
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
     *
     * @param \Illuminate\Http\UploadedFile $file File gambar asli
     * @return string Path relatif file yang disimpan
     */
    protected function compressAndStore($file)
    {
        $filename = md5($file->getClientOriginalName() . microtime()) . '.jpg';
        $path = 'suppliers/' . $filename; // Disimpan di folder 'suppliers'

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
            $path = $file->storeAs('suppliers', $filename, 'public');
        }

        return $path;
    }
}