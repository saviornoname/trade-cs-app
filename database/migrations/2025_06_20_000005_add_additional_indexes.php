<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('watchlist_item_filters', function (Blueprint $table) {
            $table->index(['paint_seed', 'phase']);
        });

        Schema::table('deals', function (Blueprint $table) {
            $table->index('item_id');
        });

        Schema::table('user_active_targets', function (Blueprint $table) {
            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('watchlist_item_filters', function (Blueprint $table) {
            $table->dropIndex(['paint_seed', 'phase']);
        });

        Schema::table('deals', function (Blueprint $table) {
            $table->dropIndex(['item_id']);
        });

        Schema::table('user_active_targets', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_active']);
        });
    }
};
