<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_watchlist_items', function (Blueprint $table) {
            $table->json('filters')->nullable()->after('paint_seed');
        });
    }

    public function down(): void
    {
        Schema::table('user_watchlist_items', function (Blueprint $table) {
            $table->dropColumn('filters');
        });
    }
};
