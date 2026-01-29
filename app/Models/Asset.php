<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode; 

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
     */
    protected static function booted(): void
    {
        // 1. Event CREATING
        static::creating(function (Asset $asset) {
            // Jika tag kosong, generate otomatis
            if (empty($asset->asset_tag)) {
                
                // A. Cari ID Kategori dari AssetModel yang dipilih
                // Kita perlu load manual karena relasi belum tentu tersedia saat 'creating'
                $categoryId = 0; // Default jika gagal ambil
                
                if ($asset->asset_model_id) {
                    $model = \App\Models\AssetModel::find($asset->asset_model_id);
                    if ($model) {
                        $categoryId = $model->category_id;
                    }
                }

                // B. Panggil generator dengan parameter category_id
                $asset->asset_tag = static::generateUniqueAssetTag($categoryId);
            }
        });

        // 2. Event CREATED
        static::created(function (Asset $asset) {
            $asset->generateQrCode();
        });
    }

    /**
     * Membuat file QR Code
     */
    public function generateQrCode()
    {
        $url = route('assets.show', ['asset' => $this->asset_tag]);
        $filename = 'qr-' . $this->asset_tag . '.svg'; 
        $path = 'qrcodes/' . $filename;

        $qrContent = QrCode::format('svg') 
                        ->size(300)
                        ->margin(1)
                        ->generate($url);

        Storage::disk('public')->put($path, $qrContent);

        $this->qr_code_path = $path;
        $this->saveQuietly();
    }

    /**
     * Menghasilkan Asset Tag unik (Smart Tag).
     * Format: YY (Tahun) + CC (Kategori) + RRRRRR (Acak)
     * Contoh: 2605123456 (Tahun 2026, Kategori 05, ID 123456)
     *
     * @param int $categoryId
     * @return string
     */
    protected static function generateUniqueAssetTag($categoryId)
    {
        // 1. Ambil 2 digit Tahun (misal: 2026 -> "26")
        $year = date('y'); 

        // 2. Format Kategori jadi 2 digit (misal: 5 -> "05", 12 -> "12")
        // str_pad memastikan angka selalu minimal 2 digit dengan menambahkan 0 di kiri
        $catCode = str_pad($categoryId, 2, '0', STR_PAD_LEFT);
        
        // (Opsional) Jika ID kategori > 99, kita ambil 2 digit terakhir saja agar panjang tag tetap 10
        $catCode = substr($catCode, -2);

        do {
            // 3. Generate 6 digit angka acak sisa
            // (10 digit total - 2 digit tahun - 2 digit kategori = 6 digit random)
            // Range: 100.000 s/d 999.999
            $random = mt_rand(100000, 999999);

            // 4. Gabungkan
            $number = $year . $catCode . $random;

        } while (static::where('asset_tag', (string)$number)->exists());

        return (string)$number;
    }

    // ... (Sisa kode Accessors dan Relations DI BAWAH SAMA PERSIS seperti sebelumnya) ...
    
    public function getQrCodeUrlAttribute()
    {
        if ($this->qr_code_path && Storage::disk('public')->exists($this->qr_code_path)) {
            return asset('storage/' . $this->qr_code_path);
        }
        return null;
    }

    public function getImageUrlAttribute()
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return asset('storage/' . $this->image);
        }
        if ($this->model && $this->model->image && Storage::disk('public')->exists($this->model->image)) {
            return asset('storage/' . $this->model->image);
        }
        return asset('images/no-image.png'); 
    }

    public function model() { return $this->belongsTo(AssetModel::class, 'asset_model_id'); }
    public function status() { return $this->belongsTo(AssetStatus::class, 'asset_status_id'); }
    public function supplier() { return $this->belongsTo(Supplier::class, 'supplier_id'); }
    public function defaultLocation() { return $this->belongsTo(Location::class, 'location_id'); }
    public function assignedTo() { return $this->morphTo(); }
    public function maintenances() { return $this->hasMany(Maintenance::class); }
}