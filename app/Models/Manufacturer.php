<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manufacturer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'support_url',
        'support_phone',
        'support_email',
        'image',
    ];

    // Relasi: Satu Manufacturer punya banyak AssetModel
    public function assetModels()
    {
        return $this->hasMany(AssetModel::class);
    }
}
