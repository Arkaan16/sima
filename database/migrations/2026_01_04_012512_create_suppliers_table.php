<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();

            // 1. Identitas Utama
            $table->string('name')->index(); // Wajib diisi & di-index untuk pencarian cepat
            $table->string('image')->nullable(); // Logo supplier
            
            // 2. Kontak Person (CP)
            $table->string('contact_name')->nullable(); // Nama sales/pemilik toko
            
            // 3. Kontak Detail
            $table->string('email')->nullable();
            $table->string('phone')->nullable(); // String, agar bisa input "+62" atau "08xx"
            
            // 4. Lokasi & Web
            $table->text('address')->nullable(); // Pakai Text, alamat biasanya panjang
            $table->string('url')->nullable();   // Website toko
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};