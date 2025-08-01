<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('watchlist_item_filters', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('phase');
        });
    }

    public function down(): void
    {
        Schema::table('watchlist_item_filters', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
};
