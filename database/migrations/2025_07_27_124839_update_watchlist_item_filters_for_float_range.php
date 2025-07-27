<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateWatchlistItemFiltersForFloatRange extends Migration
{
    public function up(): void
    {
        Schema::table('watchlist_item_filters', function (Blueprint $table) {
            $table->dropColumn(['min_float', 'max_float']);
            $table->foreignId('paintwear_range_id')->nullable()->constrained('paintwear_ranges')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('watchlist_item_filters', function (Blueprint $table) {
            $table->dropForeign(['paintwear_range_id']);
            $table->dropColumn('paintwear_range_id');
            $table->float('min_float')->nullable();
            $table->float('max_float')->nullable();
        });
    }
}
