<?php
namespace App\Console\Commands;

use App\Models\ParseLog;
use App\Models\UserWatchlistItem;
use App\Models\WatchlistPriceCheck;
use App\Services\DMarketService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncDMarketOrders extends Command
{
    protected $signature = 'dmarket:sync-orders';
    protected $description = 'Sync DMarket market data and check watchlist items';

    public function handle(DMarketService $dmarket): int
    {
        $log = ParseLog::create([
            'source' => 'dmarket',
            'status' => 'started',
            'started_at' => now(),
        ]);

        try {
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

            $log->update([
                'status' => 'success',
                'ended_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('SyncDMarketOrders failed', [
                'exception' => $e->getMessage(),
            ]);
            $log->update([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'ended_at' => now(),
            ]);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
