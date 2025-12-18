<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = ['name', 'parent_id'];

    // Relasi untuk mendapatkan anak (sub-lokasi)
    public function children(): HasMany
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    // Relasi untuk mendapatkan induk (lokasi utama)
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }
}