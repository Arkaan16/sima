<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AssetModel
 *
 * Model yang merepresentasikan data master Model Aset.
 * Mengelola informasi spesifik mengenai tipe perangkat (seperti Nama Model,
 * Nomor Model) dan menghubungkannya dengan Kategori serta Pabrikan.
 *
 * @package App\Models
 */
class AssetModel extends Model
{
    use HasFactory;

    /** @var string Nama tabel database yang didefinisikan secara eksplisit */
    protected $table = 'asset_models';

    /**
     * Daftar atribut yang dapat diisi secara massal (Mass Assignment).
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'model_number',
        'category_id',
        'manufacturer_id',
        'image',
    ];

    /**
     * Mendapatkan data Kategori yang berasosiasi dengan model aset ini.
     * Relasi: BelongsTo (Setiap Model Aset memiliki satu Kategori).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Mendapatkan data Pabrikan (Manufacturer) yang memproduksi model ini.
     * Relasi: BelongsTo (Setiap Model Aset diproduksi oleh satu Pabrikan).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    /**
     * Mendapatkan daftar unit aset fisik yang menggunakan model ini.
     * Relasi: HasMany (Satu Model Aset bisa digunakan oleh banyak unit Aset fisik).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}