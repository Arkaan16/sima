<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Manufacturer
 *
 * Model yang menangani data master Pabrikan (Manufacturer).
 * Menyimpan informasi identitas dan kontak dukungan dari perusahaan pembuat aset,
 * yang digunakan sebagai referensi untuk Model Aset.
 *
 * @package App\Models
 */
class Manufacturer extends Model
{
    use HasFactory;

    /**
     * Daftar atribut yang diizinkan untuk pengisian massal (Mass Assignment).
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'url',
        'support_url',
        'support_phone',
        'support_email',
        'image',
    ];

    /**
     * Mendapatkan daftar model aset yang diproduksi oleh pabrikan ini.
     * Relasi: HasMany (Satu pabrikan dapat memproduksi banyak jenis model aset).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assetModels()
    {
        return $this->hasMany(AssetModel::class);
    }
}