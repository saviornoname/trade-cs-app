<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Масове оновлення всіх записів
        DB::table('user_watchlist_items')->update(['active' => 0]);
    }

    public function down(): void
    {
        // Якщо потрібно повернути — наприклад, активувати всі назад
        DB::table('user_watchlist_items')->update(['active' => 1]);
    }
};
