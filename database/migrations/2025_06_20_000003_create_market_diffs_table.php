<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_diffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->decimal('dmarket_price_usd', 10, 2);
            $table->decimal('buff_price_usd', 10, 2);
            $table->decimal('diff_percent', 8, 2);
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->index('item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_diffs');
    }
};
