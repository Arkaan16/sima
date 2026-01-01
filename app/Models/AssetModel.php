<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetModel extends Model
{
    use HasFactory;

    protected $table = 'asset_models'; // Mendefinisikan nama tabel secara eksplisit

    protected $fillable = [
        'name',
        'model_number',
        'category_id',
        'manufacturer_id',
        'image',
    ];

    // Relasi ke Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relasi ke Manufacturer
    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }
}
