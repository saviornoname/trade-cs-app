<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->enum('marketplace', ['dmarket', 'buff']);
            $table->decimal('price_usd', 10, 2);
            $table->unsignedInteger('quantity')->nullable();
            $table->timestamp('snapshot_at');
            $table->timestamps();

            $table->index(['item_id', 'marketplace', 'snapshot_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_snapshots');
    }
};
