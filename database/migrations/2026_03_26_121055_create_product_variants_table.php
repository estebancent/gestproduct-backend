<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            // Aquí sí cascade: si muere el producto, mueren sus talles
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            $table->string('sku', 50)->unique(); 
            $table->string('barcode', 50)->nullable()->unique();
            $table->string('size', 20); // S, M, L, XL, etc.
            $table->string('color', 50)->nullable(); 
            
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(5);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
