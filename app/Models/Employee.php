<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Employee
 *
 * Model yang merepresentasikan data Karyawan.
 * Bertindak sebagai salah satu entitas penerima penugasan aset (Assignee)
 * dalam skema relasi polimorfik.
 *
 * @package App\Models
 */
class Employee extends Model
{
    use HasFactory;
    
    /**
     * Daftar atribut yang diizinkan untuk pengisian massal (Mass Assignment).
     *
     * @var array
     */
    protected $fillable = ['name', 'email'];

    /**
     * Mendapatkan daftar aset yang saat ini ditugaskan kepada karyawan ini.
     * Menggunakan relasi One-to-Many Polimorfik.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function assets()
    {
        // Mengambil data dari tabel assets dimana assigned_to_type = Employee dan assigned_to_id = ID karyawan ini
        return $this->morphMany(Asset::class, 'assigned_to');
    }
}