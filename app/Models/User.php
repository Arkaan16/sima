<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 *
 * Model yang merepresentasikan entitas Pengguna dalam sistem otentikasi.
 * Mengelola data akun, peran (role), serta relasi operasional (sebagai teknisi)
 * terhadap aktivitas pemeliharaan.
 *
 * @package App\Models
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Daftar atribut yang diizinkan untuk pengisian massal (Mass Assignment).
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * Daftar atribut yang disembunyikan saat serialisasi data (misal: respons JSON).
     * Menjaga keamanan data sensitif seperti password dan token.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Mendefinisikan konversi tipe data otomatis (Type Casting).
     * Mengatur hashing password otomatis dan format tanggal verifikasi email.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Mendapatkan daftar pekerjaan pemeliharaan yang ditugaskan kepada pengguna ini.
     * Hanya relevan jika pengguna memiliki peran sebagai karyawan (teknisi).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function maintenances()
    {
        // Relasi: BelongsToMany (User/Teknisi bisa mengerjakan banyak pemeliharaan)
        // Menggunakan tabel pivot 'maintenance_technician'
        return $this->belongsToMany(Maintenance::class, 'maintenance_technician');
    }
}