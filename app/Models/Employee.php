<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'email'];

    // Opsional: Relasi ke aset (jika nanti tabel aset sudah ada foreign key employee_id)
    // public function assets() {
    //     return $this->hasMany(Asset::class);
    // }
}
