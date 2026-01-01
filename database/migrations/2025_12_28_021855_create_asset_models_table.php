<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_models', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Asset Model Name
            $table->string('model_number')->nullable(); // Model No.
            
            // Relasi ke Categories
            // Pastikan tabel categories sudah ada sebelum migrasi ini dijalankan
            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->onDelete('cascade'); 
            
            // Relasi ke Manufacturers
            $table->foreignId('manufacturer_id')
                  ->constrained('manufacturers')
                  ->onDelete('cascade'); 

            $table->string('image')->nullable(); // Upload Image (Logo) - menyimpan path file
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_models');
    }
};
