<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manufacturers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Name (Nama Manufacture)
            $table->string('url')->nullable(); // URL Website Resmi
            $table->string('support_url')->nullable(); // URL Support
            $table->string('support_phone')->nullable(); // No Telp Support
            $table->string('support_email')->nullable(); // Email Support
            $table->string('image')->nullable(); // Upload Image (Logo) - menyimpan path file
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manufacturers');
    }
};
