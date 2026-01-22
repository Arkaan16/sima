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
            $table->string('name')->index();
            $table->string('model_number')->nullable()->index(); 
            
            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->onDelete('cascade'); 
            
            $table->foreignId('manufacturer_id')
                  ->constrained('manufacturers')
                  ->onDelete('cascade');

            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_models');
    }
};
