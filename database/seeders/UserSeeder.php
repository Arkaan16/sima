<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Membuat Akun Admin
        User::create([
            'name' => 'Administrator SIMA',
            'email' => 'admin@ptba.co.id',
            'password' => 'password', // Otomatis ter-hash oleh model
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Membuat Akun Employee
        User::create([
            'name' => 'Employee SIMA',
            'email' => 'employee@ptba.co.id',
            'password' => 'password',
            'role' => 'employee',
            'email_verified_at' => now(),
        ]);
    }
}