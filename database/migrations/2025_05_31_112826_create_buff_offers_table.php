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
        Schema::create('buff_offers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('goods_id'); // Buff goods ID
            $table->decimal('price_cny', 10, 2); // Ціна в юанях
            $table->decimal('price_usd', 10, 2)->nullable(); // Розрахована ціна в доларах (по курсу)
            $table->unsignedInteger('amount'); // Кількість ордерів
            $table->string('buff_user_id')->nullable(); // ID юзера (опційно)

            $table->decimal('min_float', 6, 5)->nullable(); // Мін. float
            $table->decimal('max_float', 6, 5)->nullable(); // Макс. float

            $table->timestamp('buff_created_at')->nullable(); // Коли створено ордер
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buff_offers');
    }
};
