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
        $items = UserWatchlistItem::where('active', true)->get();

        foreach ($items as $item) {
            $data = $dmarket->getMarketTargets('a8db', $item->title);
            $orders = $data['orders'] ?? [];
            $min = null;
            foreach ($orders as $order) {
                if (!isset($order['price'])) {
                    continue;
                }
                $price = $order['price'];
                if ($min === null || $price < $min) {
                    $min = $price;
                }
            }

            $targetMax = DB::table('user_active_targets')
                ->where('title', $item->title)
                ->max('price');

            $profit = null;
            if ($min !== null && $targetMax !== null) {
                $profit = ($targetMax - $min) / $min * 100;
            }

            WatchlistPriceCheck::create([
                'user_watchlist_item_id' => $item->id,
                'min_market_price_usd' => $min,
                'target_max_price_usd' => $targetMax,
                'profit_percent' => $profit,
            ]);
        }

        return Command::SUCCESS;
    }
}
