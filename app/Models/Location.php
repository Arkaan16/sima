<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Location
 *
 * Model yang menangani data master Lokasi.
 * Mendukung struktur hierarki (Parent-Child) untuk merepresentasikan Gedung, Lantai,
 * atau Ruangan, serta mengelola relasi aset yang tersimpan atau ditugaskan di lokasi tersebut.
 *
 * @package App\Models
 */
class Location extends Model
{
    use HasFactory;

    /**
     * Daftar atribut yang diizinkan untuk pengisian massal (Mass Assignment).
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'parent_location_id',
    ];

    /**
     * Mendapatkan lokasi induk (Parent) dari lokasi ini.
     * Relasi: Self-referential BelongsTo.
     * Contoh: Jika ini adalah 'Ruang 101', parent-nya mungkin 'Gedung A'.
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_location_id');
    }

    /**
     * Mendapatkan daftar sub-lokasi (Children) yang berada di bawah lokasi ini.
     * Relasi: Self-referential HasMany.
     * Contoh: Jika ini adalah 'Gedung A', children-nya adalah daftar ruangan di dalamnya.
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Location::class, 'parent_location_id');
    }

    /**
     * Mendapatkan daftar aset yang menjadikan lokasi ini sebagai lokasi penyimpanan utama (Default Location).
     * Relasi ini mengacu pada kolom 'location_id' di tabel assets.
     *
     * @return HasMany
     */
    public function defaultAssets()
    {
        return $this->hasMany(Asset::class, 'location_id');
    }

    /**
     * [TAMBAHAN UNTUK FITUR DASHBOARD]
     * Alias standar untuk relasi ke aset. 
     * Diperlukan untuk mengatasi error "Call to undefined method... assets()".
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'location_id');
    }

    /**
     * Mendapatkan daftar aset yang saat ini sedang ditugaskan atau berada secara fisik di lokasi ini.
     * Menggunakan mekanisme Polymorphic Relationship (assigned_to), di mana aset bisa
     * ditugaskan ke User, Employee, atau Location.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function assignedAssets()
    {
        return $this->morphMany(Asset::class, 'assigned_to');
    }
}