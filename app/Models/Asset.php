<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode; 

/**
 * Class Asset
 *
 * Model yang merepresentasikan entitas Aset dalam sistem.
 * Menangani logika bisnis terkait siklus hidup aset, termasuk pembuatan
 * Asset Tag otomatis, generasi QR Code, serta pengelolaan relasi data.
 *
 * @package App\Models
 */
class Asset extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'purchase_date' => 'date',
        'eol_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'warranty_months' => 'integer',
    ];

    /**
     * The "booted" method of the model.
     * Mendaftarkan event listener untuk lifecycle model Eloquent.
     */
    protected static function booted(): void
    {
        // 1. Event CREATING: Menangani pembuatan Asset Tag sebelum data disimpan
        static::creating(function (Asset $asset) {
            // Jika tag kosong, generate angka unik secara otomatis
            if (empty($asset->asset_tag)) {
                $asset->asset_tag = static::generateUniqueAssetTag();
            }
        });

        // 2. Event CREATED: Menangani proses lanjutan setelah aset berhasil disimpan
        static::created(function (Asset $asset) {
            // Generate QR Code fisik untuk aset ini
            $asset->generateQrCode();
        });
    }

    /**
     * Membuat file QR Code (format SVG) dan menyimpannya ke storage.
     * QR Code berisi URL yang mengarah ke halaman detail aset.
     */
    public function generateQrCode()
    {
        // 1. Definisikan URL Target (Halaman Detail Aset)
        $url = route('admin.assets.show', ['asset' => $this->asset_tag]);

        // 2. Tentukan Nama File dan Path Penyimpanan
        $filename = 'qr-' . $this->asset_tag . '.svg'; 
        $path = 'qrcodes/' . $filename;

        // 3. Generate Konten QR Code (Format SVG)
        // Menggunakan format SVG untuk kompatibilitas native PHP tanpa ketergantungan Imagick
        $qrContent = QrCode::format('svg') 
                        ->size(300)
                        ->margin(1)
                        ->generate($url);

        // 4. Simpan File ke Storage Publik
        Storage::disk('public')->put($path, $qrContent);

        // 5. Perbarui Path QR di Database
        // Menggunakan saveQuietly() untuk mencegah infinite loop trigger event updated
        $this->qr_code_path = $path;
        $this->saveQuietly();
    }

    /**
     * Menghasilkan Asset Tag unik berupa 10 digit angka acak.
     * Memastikan tidak ada duplikasi di database.
     *
     * @return string
     */
    protected static function generateUniqueAssetTag()
    {
        do {
            // Generate 10 digit angka acak
            $number = mt_rand(1000000000, 9999999999);
        } while (static::where('asset_tag', (string)$number)->exists()); // Ulangi jika sudah ada

        return (string)$number;
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Accessor untuk mendapatkan URL lengkap file QR Code.
     *
     * @return string|null
     */
    public function getQrCodeUrlAttribute()
    {
        if ($this->qr_code_path && Storage::disk('public')->exists($this->qr_code_path)) {
            return asset('storage/' . $this->qr_code_path);
        }
        return null;
    }

    /**
     * Accessor untuk mendapatkan URL gambar aset.
     * Menggunakan fallback ke gambar model atau gambar default jika kosong.
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        // Cek gambar spesifik aset
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return asset('storage/' . $this->image);
        }
        // Cek gambar dari master model (fallback 1)
        if ($this->model && $this->model->image && Storage::disk('public')->exists($this->model->image)) {
            return asset('storage/' . $this->model->image);
        }
        // Gambar default (fallback 2)
        return asset('images/no-image.png'); 
    }

    // ==========================================
    // RELATIONS
    // ==========================================

    /**
     * Relasi ke model master aset (Jenis/Tipe).
     */
    public function model() { return $this->belongsTo(AssetModel::class, 'asset_model_id'); }

    /**
     * Relasi ke status aset.
     */
    public function status() { return $this->belongsTo(AssetStatus::class, 'asset_status_id'); }

    /**
     * Relasi ke data supplier.
     */
    public function supplier() { return $this->belongsTo(Supplier::class, 'supplier_id'); }

    /**
     * Relasi ke lokasi default penyimpanan.
     */
    public function defaultLocation() { return $this->belongsTo(Location::class, 'location_id'); }

    /**
     * Relasi polimorfik untuk penugasan aset (Bisa ke User, Employee, atau Lokasi).
     */
    public function assignedTo() 
    { 
        return $this->morphTo(); 
    }

    /**
     * Relasi ke riwayat pemeliharaan.
     */
    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }
}