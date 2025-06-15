<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watchlist_price_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_watchlist_item_id')->constrained()->onDelete('cascade');
            $table->decimal('min_market_price_usd', 10, 2)->nullable();
            $table->unsignedBigInteger('target_max_price_usd')->nullable();
            $table->decimal('profit_percent', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watchlist_price_checks');
    }
};
