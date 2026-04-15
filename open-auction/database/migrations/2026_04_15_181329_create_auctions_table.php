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
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('starting_price', 15, 2); // Açılış fiyatı
            $table->decimal('current_price', 15, 2);  // O anki en yüksek teklif
            
            $table->dateTime('start_time'); // Ön gösterim için başlangıç tarihi
            $table->dateTime('end_time');   // Bitiş tarihi
            
            $table->integer('extension_count')->default(0); // Anti-Snipe ile kaç kez uzadı?
            $table->enum('status', ['scheduled', 'active', 'ended', 'cancelled'])->default('scheduled');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
