<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Maintenance
 *
 * Model yang merepresentasikan data riwayat pemeliharaan (Maintenance) pada aset.
 * Mengelola informasi detail aktivitas perbaikan atau perawatan, jadwal pelaksanaan,
 * teknisi yang bertugas, serta dokumentasi visual terkait.
 *
 * @package App\Models
 */
class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'title',
        'description',
        'maintenance_type',
        'execution_date',
    ];

    protected $casts = [
        'execution_date' => 'date',
    ];

    // =================================================================
    // HELPER & ACCESSOR
    // =================================================================

    /**
     * Menyediakan daftar tipe pemeliharaan yang tersedia beserta label terjemahannya.
     *
     * @return array Array asosiatif [Key Internal => Label Tampilan]
     */
    public static function getTypes()
    {
        return [
            'Preventive' => 'Pencegahan',
            'Corrective' => 'Perbaikan',
            'Calibration' => 'Kalibrasi',
            'Predictive' => 'Prediksi',
            'Routine Inspection' => 'Inspeksi Rutin',
            'Emergency Repair' => 'Perbaikan Darurat',
            'Parts Replacement' => 'Penggantian Suku Cadang',
            'Software Update' => 'Pembaruan Perangkat Lunak',
            'Cleaning' => 'Pembersihan',
        ];
    }

    /**
     * Accessor: Mendapatkan label tipe pemeliharaan (Bahasa Indonesia).
     * Cara panggil di Blade: $maintenance->type_label
     *
     * @return string
     */
    public function getTypeLabelAttribute()
    {
        $types = self::getTypes();
        return $types[$this->maintenance_type] ?? $this->maintenance_type;
    }

    /**
     * Accessor: Mendapatkan kelas warna badge (Tailwind CSS) berdasarkan tipe.
     * Cara panggil di Blade: $maintenance->badge_class
     *
     * @return string
     */
    public function getBadgeClassAttribute()
    {
        return match($this->maintenance_type) {
            'Preventive' => 'bg-blue-100 text-blue-800 border-blue-200',
            'Corrective' => 'bg-red-100 text-red-800 border-red-200',
            'Calibration' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'Predictive' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'Routine Inspection' => 'bg-green-100 text-green-800 border-green-200',
            'Emergency Repair' => 'bg-red-100 text-red-800 border-red-200 ring-1 ring-red-300', 
            'Parts Replacement' => 'bg-orange-100 text-orange-800 border-orange-200',
            'Software Update' => 'bg-sky-100 text-sky-800 border-sky-200',
            'Cleaning' => 'bg-teal-100 text-teal-800 border-teal-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200'
        };
    }

    // =================================================================
    // RELATIONS
    // =================================================================

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function technicians()
    {
        return $this->belongsToMany(User::class, 'maintenance_technician');
    }

    public function images()
    {
        return $this->hasMany(MaintenanceImage::class);
    }
}