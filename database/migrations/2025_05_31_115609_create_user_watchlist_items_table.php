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
        Schema::create('user_watchlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->unsignedBigInteger('max_price_usd')->nullable(); // $14.00 → 1400
            $table->decimal('min_float', 6, 5)->nullable();
            $table->decimal('max_float', 6, 5)->nullable();
            $table->string('item_id')->nullable();
            $table->string('phase')->nullable();
            $table->string('paint_seed')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_watchlist_items');
    }
};
