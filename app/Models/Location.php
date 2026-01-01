<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_location_id',
    ];

    // Relasi: lokasi induk (Gedung / Lantai)
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_location_id');
    }

    // Relasi: sub-lokasi (Ruang)
    public function children(): HasMany
    {
        return $this->hasMany(Location::class, 'parent_location_id');
    }
}
