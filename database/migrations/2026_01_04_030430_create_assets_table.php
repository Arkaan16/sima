<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();

            // 1. IDENTITAS
            $table->string('asset_tag')->unique(); 
            $table->string('serial')->nullable()->index(); 
            $table->string('image')->nullable();
            $table->string('qr_code_path')->nullable();

            // 2. RELASI 
            $table->foreignId('asset_model_id')->constrained('asset_models')->onDelete('cascade');
            $table->foreignId('asset_status_id')->constrained('asset_statuses')->onDelete('restrict');
            $table->foreignId('location_id')->constrained('locations')->onDelete('restrict');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');

            // 3. CHECKOUT
            $table->nullableMorphs('assigned_to'); 

            // 4. INFO LAIN
            $table->string('order_number')->nullable()->index(); 
            $table->date('purchase_date')->nullable()->index();  
            $table->decimal('purchase_cost', 15, 2)->nullable();
            $table->integer('warranty_months')->nullable();
            $table->date('eol_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};