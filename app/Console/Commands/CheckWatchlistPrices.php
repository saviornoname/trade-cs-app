<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserWatchlistItem;
use App\Models\WatchlistPriceCheck;
use App\Services\DMarketService;
use Illuminate\Support\Facades\DB;

class CheckWatchlistPrices extends Command
{
    protected $signature = 'watchlist:check-prices';
    protected $description = 'Check market prices for active watchlist items';

    public function handle(DMarketService $dmarket): int
    {
        $this->info('🔎 Початок перевірки Watchlist...');

        $items = UserWatchlistItem::where('active', true)->get();
        $this->line("🔍 Знайдено активних записів у Watchlist: " . $items->count());

        foreach ($items as $item) {
            $this->line("➡️ Перевіряю: {$item->title}");

            $data = $dmarket->getMarketTargets('a8db', $item->title);
//            $this->line(json_encode($data, JSON_PRETTY_PRINT));

            $orders = $data['orders'] ?? [];

            $min = null;
            foreach ($orders as $order) {
                $this->line(json_encode($order, JSON_PRETTY_PRINT));

                if (!isset($order['price'])) {
                    continue;
                }

                $price = $order['price'];
                if ($min === null || $price < $min) {
                    $min = $price;
                }
            }

            $this->line("💲 Мінімальна ринкова ціна: " . ($min ?? 'N/A'));

            $targetMax = DB::table('user_active_targets')
                ->where('title', $item->title)
                ->max('price');

            $this->line("🎯 Максимальна цільова ціна: " . ($targetMax ?? 'N/A'));

            $profit = null;
            if ($min !== null && $targetMax !== null) {
                $profit = ($targetMax - $min) / $min * 100;
                $this->line("📈 Потенційний профіт: " . round($profit, 2) . "%");
            } else {
                $this->warn("⚠️ Неможливо обчислити профіт для {$item->title}");
            }

            WatchlistPriceCheck::create([
                'user_watchlist_item_id' => $item->id,
                'min_market_price_usd' => $min,
                'target_max_price_usd' => $targetMax,
                'profit_percent' => $profit,
            ]);

            $this->info("✅ Запис створено для {$item->title}\n");
        }

        $this->info('🏁 Перевірка завершена успішно.');

        return Command::SUCCESS;
    }
}
