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
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            
            // Satış Tipi: Sadece açık artırma, sadece hemen al veya her ikisi
            $table->enum('listing_type', ['auction', 'direct', 'hybrid'])->default('direct');
            
            $table->decimal('buy_now_price', 15, 2)->nullable(); // Hemen al fiyatı
            $table->integer('stock_quantity')->default(1);
            
            $table->string('status')->default('active'); // active, sold_out, draft
            $table->softDeletes(); // Veritabanından silinmesin, arşive alınsın
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
