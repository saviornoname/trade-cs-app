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
        Schema::create('user_active_targets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('title'); // Назва скіна
            $table->unsignedBigInteger('price'); // Ціна в копійках (наприклад 1415000 = $14150.00)
            $table->unsignedInteger('amount')->default(1); // Скільки скінів таргетиться

            // атрибути предмета
            $table->string('float_part_value')->nullable(); // "any" або значення
            $table->string('paint_seed')->nullable();       // "any" або значення
            $table->string('phase')->nullable();            // Наприклад, "ruby"

            $table->enum('marketplace', ['dmarket', 'buff'])->default('dmarket'); // З якого маркету таргет
            $table->boolean('is_active')->default(true); // Чи увімкнений таргет
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_active_targets');
    }
};
