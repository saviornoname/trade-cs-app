<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_watchlist_items', function (Blueprint $table) {
            if (!Schema::hasColumn('user_watchlist_items', 'item_id')) {
                $table->string('item_id')->nullable()->after('max_float');
            }

            // Додаємо унікальний індекс на пару user_id + item_id
            $table->unique(['user_id', 'item_id'], 'user_item_unique');
        });
    }

    public function down(): void
    {
        Schema::table('user_watchlist_items', function (Blueprint $table) {
            $table->dropUnique('user_item_unique');
            $table->dropColumn('item_id');
        });
    }
};
