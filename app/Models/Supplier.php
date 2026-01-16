<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Supplier
 *
 * Model yang menangani data master Pemasok (Supplier).
 * Menyimpan informasi lengkap mengenai vendor atau pihak ketiga penyedia aset,
 * termasuk detail kontak dan alamat perusahaan.
 *
 * @package App\Models
 */
class Supplier extends Model
{
    use HasFactory; 

    /**
     * Daftar atribut yang diizinkan untuk pengisian massal (Mass Assignment).
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'image',
        'contact_name',
        'email',
        'phone',
        'address',
        'url',
    ];

    /**
     * Mendapatkan daftar aset yang disediakan oleh pemasok ini.
     * Relasi: HasMany (Satu pemasok dapat menyediakan banyak unit aset).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}