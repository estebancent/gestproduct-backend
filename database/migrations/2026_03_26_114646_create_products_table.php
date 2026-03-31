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
       Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image_path', 300)->nullable();
            
            // Precios base (se pueden sobreescribir en la variante
            $table->decimal('purchase_price', 12, 2)->default(0); // Costo
            $table->decimal('sale_price', 12, 2)->default(0);    // Venta
            $table->decimal('profit_margin', 5, 2)->default(0);  // % ganancia
            //seguridad total: restrict
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->foreignId('brand_id')->constrained()->onDelete('restrict');
            
            $table->boolean('is_active')->default(true);

            $table->softDeletes();
            $table->timestamps();
        });
     
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
