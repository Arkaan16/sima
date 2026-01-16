<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Category
 *
 * Model yang menangani data master Kategori.
 * Digunakan untuk mengelompokkan jenis-jenis model aset (misalnya: Elektronik, Kendaraan, Furniture)
 * guna memudahkan pengorganisasian dan pelaporan inventaris.
 *
 * @package App\Models
 */
class Category extends Model
{
    use HasFactory;
    
    /**
     * Daftar atribut yang diizinkan untuk pengisian massal (Mass Assignment).
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * Mendapatkan daftar model aset yang terhubung dengan kategori ini.
     * Relasi: HasMany (Satu kategori dapat menaungi banyak model aset).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assetModels()
    {
        return $this->hasMany(AssetModel::class);
    }
}