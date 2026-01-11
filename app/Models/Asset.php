<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode; // Pastikan import ini ada

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

    protected static function booted(): void
    {
        // 1. Event CREATING: Generate Asset Tag jika kosong
        static::creating(function (Asset $asset) {
            if (empty($asset->asset_tag)) {
                $asset->asset_tag = static::generateUniqueAssetTag();
            }
        });

        // 2. Event CREATED: Generate QR Code otomatis setelah aset tersimpan
        static::created(function (Asset $asset) {
            $asset->generateQrCode();
        });
    }

    /**
     * Logic Generate QR Code dipisah agar rapi
     */
    public function generateQrCode()
    {
        // A. URL Target
        $url = route('admin.assets.show', ['asset' => $this->asset_tag]);

        // ============================================================
        // UBAH BAGIAN INI (Ganti PNG ke SVG)
        // ============================================================
        
        // 1. Ganti ekstensi file jadi .svg
        $filename = 'qr-' . $this->asset_tag . '.svg'; 
        $path = 'qrcodes/' . $filename;

        // 2. Ganti format render jadi 'svg'
        // Format SVG tidak butuh Imagick, jalan native di PHP
        $qrContent = QrCode::format('svg') 
                        ->size(300)
                        ->margin(1)
                        ->generate($url);

        // ============================================================

        // D. Simpan File ke Storage
        Storage::disk('public')->put($path, $qrContent);

        // E. Update Kolom database
        $this->qr_code_path = $path;
        $this->saveQuietly();
    }

    protected static function generateUniqueAssetTag()
    {
        do {
            $number = mt_rand(1000000000, 9999999999);
        } while (static::where('asset_tag', (string)$number)->exists());

        return (string)$number;
    }

    // ==========================================
    // ACCESSOR (Untuk mempermudah ambil URL QR)
    // ==========================================
    public function getQrCodeUrlAttribute()
    {
        if ($this->qr_code_path && Storage::disk('public')->exists($this->qr_code_path)) {
            return asset('storage/' . $this->qr_code_path);
        }
        return null;
    }

    // ... (Sisa method getImageUrlAttribute dan Relasi biarkan sama seperti sebelumnya) ...
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
    public function assignedTo() { 
        return $this->morphTo('assigned_to'); 
    }
}