<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use Livewire\Attributes\Validate;
use App\Models\Maintenance;
use App\Models\MaintenanceImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * Class MaintenanceForm
 *
 * Objek Form Livewire yang menangani logika bisnis untuk modul Pemeliharaan (Maintenance).
 * Mencakup validasi input, manajemen transaksi database, pengelolaan relasi teknisi,
 * serta pemrosesan dan kompresi gambar bukti pemeliharaan.
 *
 * @package App\Livewire\Forms
 */
class MaintenanceForm extends Form
{
    // ==========================================
    // PROPERTIES
    // ==========================================
    
    public $asset_id = '';
    public $title = '';
    public $description = '';
    public $maintenance_type = '';
    public $execution_date = '';
    public $selected_technicians = [];
    
    /** @var Maintenance|null Instance model untuk mode edit */
    public ?Maintenance $maintenance = null;
    
    /** @var array Koleksi file foto sementara (upload) */
    public $photos = [];

    // ==========================================
    // VALIDATION RULES & MESSAGES
    // ==========================================

    /**
     * Mendefinisikan aturan validasi dasar untuk form.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'asset_id' => 'required|exists:assets,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'maintenance_type' => 'required|string',
            'execution_date' => 'required|date',
            'selected_technicians' => 'required|array|min:1',
            'selected_technicians.*' => 'exists:users,id',
            'photos.*' => 'image|max:10240', 
        ];
    }

    /**
     * Menyediakan pesan error kustom dalam Bahasa Indonesia.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'asset_id.required' => 'Aset wajib dipilih.',
            'asset_id.exists' => 'Aset yang dipilih tidak valid.',        
            'title.required' => 'Judul pemeliharaan wajib diisi.',        
            'description.required' => 'Deskripsi pengerjaan wajib diisi.',         
            'maintenance_type.required' => 'Jenis pemeliharaan wajib dipilih.',
            'execution_date.required' => 'Tanggal pelaksanaan wajib diisi.',
            'selected_technicians.required' => 'Harap pilih minimal satu teknisi.',
            'selected_technicians.min' => 'Harap pilih minimal satu teknisi.',
            'photos.required' => 'Bukti foto wajib diupload.',
            'photos.min' => 'Harap upload minimal 1 foto sebagai bukti.',
            'photos.max' => 'Maksimal hanya boleh 3 foto.',
            'photos.*.max' => 'Ukuran foto terlalu besar (Maks 10MB).',
        ];
    }

    // ==========================================
    // INITIALIZATION (EDIT MODE)
    // ==========================================

    /**
     * Mengisi properti form berdasarkan data pemeliharaan yang ada.
     * Digunakan untuk inisialisasi halaman Edit.
     *
     * @param Maintenance $maintenance
     */
    public function setMaintenance(Maintenance $maintenance)
    {
        $this->maintenance = $maintenance;
        
        $this->asset_id = $maintenance->asset_id;
        $this->title = $maintenance->title;
        $this->description = $maintenance->description;
        $this->maintenance_type = $maintenance->maintenance_type;
        
        $this->execution_date = $maintenance->execution_date instanceof \DateTime 
            ? $maintenance->execution_date->format('Y-m-d') 
            : $maintenance->execution_date;

        $this->selected_technicians = $maintenance->technicians->pluck('id')->map(fn($id) => (string) $id)->toArray();
    }

    // ==========================================
    // BUSINESS LOGIC: STORE
    // ==========================================

    /**
     * Menyimpan data pemeliharaan baru ke database.
     * Menangani transaksi database untuk integritas data antara record utama,
     * relasi teknisi, dan file gambar.
     *
     * @throws \Exception Jika terjadi kesalahan saat transaksi.
     */
    public function store()
    {
        // 1. Validasi Khusus: Pastikan ada foto saat pembuatan baru (Min 1, Max 3)
        $this->validate([
            'photos' => 'required|array|min:1|max:3',
        ]);

        // 2. Validasi Atribut Input Lainnya
        $this->validate();

        try {
            DB::transaction(function () {
                
                // 3. Simpan Data Utama Pemeliharaan
                $maintenance = Maintenance::create(
                    $this->except(['photos', 'selected_technicians', 'maintenance'])
                );

                // 4. Hubungkan Relasi Teknisi (Many-to-Many)
                $maintenance->technicians()->attach($this->selected_technicians);

                // 5. Proses Penyimpanan Foto
                if (!empty($this->photos)) {
                    // Buat direktori jika belum ada
                    if(!Storage::disk('public')->exists('maintenance-photos')) {
                        Storage::disk('public')->makeDirectory('maintenance-photos');
                    }

                    foreach ($this->photos as $photo) {
                        // Kompresi dan simpan fisik file
                        $path = $this->compressAndStore($photo);

                        // Simpan referensi path ke database
                        MaintenanceImage::create([
                            'maintenance_id' => $maintenance->id,
                            'photo_path' => $path,
                        ]);
                    }
                }
            });
            
            // 6. Reset state form setelah berhasil
            $this->reset();

        } catch (\Exception $e) {
            throw $e; 
        }
    }

    // ==========================================
    // BUSINESS LOGIC: UPDATE
    // ==========================================

    /**
     * Memperbarui data pemeliharaan yang sudah ada.
     * Menangani logika kompleks untuk validasi jumlah foto (gabungan foto lama dan baru)
     * serta penghapusan fisik file foto lama.
     *
     * @param array $photosToDelete Daftar ID foto lama yang akan dihapus.
     * @throws ValidationException Jika jumlah total foto tidak memenuhi syarat.
     * @throws \Exception Jika terjadi kesalahan sistem.
     */
    public function update(array $photosToDelete = [])
    {
        // 1. Validasi Atribut Input Teks
        $this->validate();

        // 2. Logika Validasi Foto: Hitung sisa foto setelah penghapusan + foto baru
        $existingCount = $this->maintenance->images()
            ->whereNotIn('id', $photosToDelete) // Abaikan foto yang ditandai hapus
            ->count();
            
        $newCount = count($this->photos);
        $totalPhotos = $existingCount + $newCount;

        // 3. Pengecekan Batasan Jumlah Foto
        if ($totalPhotos < 1) {
            throw ValidationException::withMessages([
                'photos' => 'Harus ada minimal 1 foto dokumentasi (Foto lama atau baru).',
            ]);
        }

        if ($totalPhotos > 3) {
            throw ValidationException::withMessages([
                'photos' => 'Maksimal hanya 3 foto dokumentasi.',
            ]);
        }

        try {
            DB::transaction(function () use ($photosToDelete) {
                // 4. Update Data Utama Record
                $this->maintenance->update(
                    $this->except(['photos', 'selected_technicians', 'maintenance', 'asset_id'])
                );

                // 5. Sinkronisasi Data Teknisi (Hapus yang tidak dipilih, tambah yang baru)
                $this->maintenance->technicians()->sync($this->selected_technicians);

                // 6. Proses Penghapusan Foto Lama
                if (!empty($photosToDelete)) {
                    $imagesToDelete = $this->maintenance->images()
                        ->whereIn('id', $photosToDelete)
                        ->get();

                    foreach ($imagesToDelete as $img) {
                        // Hapus file fisik dari storage
                        if ($img->photo_path && Storage::disk('public')->exists($img->photo_path)) {
                            Storage::disk('public')->delete($img->photo_path);
                        }
                        // Hapus record database
                        $img->delete();
                    }
                }

                // 7. Proses Penyimpanan Foto Baru (Jika ada)
                if (!empty($this->photos)) {
                    if(!Storage::disk('public')->exists('maintenance-photos')) {
                        Storage::disk('public')->makeDirectory('maintenance-photos');
                    }

                    foreach ($this->photos as $photo) {
                        $path = $this->compressAndStore($photo);
                        MaintenanceImage::create([
                            'maintenance_id' => $this->maintenance->id,
                            'photo_path' => $path,
                        ]);
                    }
                }
            });
            
            // 8. Bersihkan buffer foto baru
            $this->photos = [];
            
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // ==========================================
    // IMAGE MANAGEMENT ACTIONS
    // ==========================================

    /**
     * Menghapus satu foto spesifik secara langsung (biasanya via aksi klik di UI).
     *
     * @param int $imageId ID dari MaintenanceImage
     */
    public function deleteExistingImage($imageId)
    {
        $image = MaintenanceImage::find($imageId);
        
        // Pastikan gambar ada dan milik maintenance yang sedang diedit (Security Check)
        if ($image && $image->maintenance_id == $this->maintenance->id) {
            // Hapus file fisik
            if (Storage::disk('public')->exists($image->photo_path)) {
                Storage::disk('public')->delete($image->photo_path);
            }
            // Hapus record database
            $image->delete();
            
            // Reload relasi untuk memperbarui UI
            $this->maintenance->load('images');
        }
    }

    /**
     * Menghapus foto dari daftar antrian upload sementara (Draft).
     *
     * @param int $index Index array foto
     */
    public function removePhoto($index)
    {
        unset($this->photos[$index]);
        $this->photos = array_values($this->photos); 
    }

    // ==========================================
    // HELPERS
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
        $path = 'maintenance-photos/' . $filename;

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
            $path = $file->storeAs('maintenance-photos', $filename, 'public');
        }

        return $path;
    }
}