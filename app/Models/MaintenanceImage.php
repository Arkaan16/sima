<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MaintenanceImage
 *
 * Model yang menangani penyimpanan data gambar dokumentasi pemeliharaan.
 * Merepresentasikan file foto bukti (evidence) yang terlampir pada sebuah 
 * aktivitas maintenance tertentu.
 *
 * @package App\Models
 */
class MaintenanceImage extends Model
{
    use HasFactory;

    /**
     * Daftar atribut yang diizinkan untuk pengisian massal (Mass Assignment).
     *
     * @var array
     */
    protected $fillable = [
        'maintenance_id',
        'photo_path',
    ];

    /**
     * Mendapatkan data pemeliharaan (induk) yang memiliki gambar ini.
     * Relasi: BelongsTo (Setiap gambar milik satu record maintenance).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class);
    }
}