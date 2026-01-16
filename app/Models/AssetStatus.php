<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AssetStatus
 *
 * Model yang menangani data master Status Aset.
 * Merepresentasikan kondisi fisik atau status ketersediaan dari sebuah aset
 * (contoh: Tersedia, Sedang Digunakan, Rusak, atau Hilang).
 *
 * @package App\Models
 */
class AssetStatus extends Model
{
    use HasFactory;

    /**
     * Daftar atribut yang diizinkan untuk pengisian massal (Mass Assignment).
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * Mendapatkan daftar aset yang terasosiasi dengan status ini.
     * Relasi: HasMany (Satu jenis status dapat dimiliki oleh banyak unit aset).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}