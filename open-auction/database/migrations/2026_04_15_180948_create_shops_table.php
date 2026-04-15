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
    Schema::create('shops', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Hangi kullanıcıya ait?
        $table->string('store_name');
        $table->string('iban')->nullable();
        $table->decimal('rating', 3, 2)->default(0); // 0.00 ile 5.00 arası puan
        $table->enum('status', ['pending', 'approved', 'suspended'])->default('pending');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
