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
        Schema::create('dmarket_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title'); // Назва предмета
            $table->unsignedBigInteger('price'); // В копійках (наприклад 1415000 = 14150.00 USD)
            $table->unsignedInteger('amount')->default(1); // Кількість
            $table->string('float_part_value')->nullable(); // 'any' або значення
            $table->string('paint_seed')->nullable(); // 'any' або конкретний seed
            $table->string('phase')->nullable(); // Наприклад 'ruby'
            $table->boolean('is_active')->default(true); // Чи таргет активний
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dmarket_targets');
    }
};
