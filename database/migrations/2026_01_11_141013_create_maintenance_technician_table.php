<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_technician', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_id')->constrained('maintenances')->onDelete('cascade');
            
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); 
            
            $table->timestamps();

            $table->unique(['maintenance_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_technician');
    }
};
