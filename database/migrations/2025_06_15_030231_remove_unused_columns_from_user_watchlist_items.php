<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_watchlist_items', function (Blueprint $table) {
            $table->dropColumn(['max_price_usd', 'min_float', 'max_float', 'phase', 'paint_seed']);
        });
    }

    public function down(): void
    {
        Schema::table('user_watchlist_items', function (Blueprint $table) {
            $table->unsignedBigInteger('max_price_usd')->nullable();
            $table->decimal('min_float', 6, 5)->nullable();
            $table->decimal('max_float', 6, 5)->nullable();
            $table->string('phase')->nullable();
            $table->string('paint_seed')->nullable();
        });
    }
};
