<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DMarketService;

class CheckWatchlistPrices extends Command
{
    protected $signature = 'watchlist:check-prices';
    protected $description = 'Check market comparisons and calculate profit between target max and market min';

    public function handle(DMarketService $dmarket): int
    {
        $this->info('🔎 Початок перевірки Watchlist...');

        $comparisons = $dmarket->getMarketComparisons();
        $this->line("🔍 Знайдено записів для порівняння: " . count($comparisons));

        foreach ($comparisons as $cmp) {
            $title = $cmp['title'] ?? 'unknown';
            $marketPrices = $cmp['market_min_prices_usd'] ?? [];
            $targetMax = $cmp['target_max_price_usd'] ?? null;

            sort($marketPrices);
            $minMarketPrice = $marketPrices[0] ?? null;

            $profit = ($minMarketPrice && $targetMax)
                ? (($minMarketPrice - $targetMax) / $targetMax) * 100
                : null;

            $this->info("🎯 {$title}");
            $this->line("    🎯 Target Max: " . ($targetMax ?? 'N/A'));
            $this->line("    💲 Market Min: " . ($minMarketPrice ?? 'N/A'));
            $this->line("    📈 Profit (target - market): " . ($profit !== null ? round($profit, 2) : 'N/A') . '%');
            $this->line(str_repeat('─', 40));
        }

        $this->info('🏁 Перевірка завершена успішно.');
        return Command::SUCCESS;
    }
}
