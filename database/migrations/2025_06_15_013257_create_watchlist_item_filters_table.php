<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watchlist_item_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_watchlist_item_id')->constrained()->onDelete('cascade');
            $table->decimal('min_float', 6, 5)->nullable();
            $table->decimal('max_float', 6, 5)->nullable();
            $table->string('paint_seed')->nullable();
            $table->string('phase')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watchlist_item_filters');
    }
};
