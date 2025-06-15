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
        Schema::create('paintwear_ranges', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('min', 6, 5);
            $table->decimal('max', 6, 5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paintwear_ranges');
    }
};
