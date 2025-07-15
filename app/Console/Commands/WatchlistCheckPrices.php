<?php

namespace App\Console\Commands;

use App\Models\ParseLog;
use App\Models\UserWatchlistItem;
use App\Models\WatchlistPriceCheck;
use App\Services\DMarketService;
use Illuminate\Console\Command;

class WatchlistCheckPrices extends Command
{
    protected $signature = 'watchlist:check-prices';

    protected $description = 'Scan watchlist items and log potential profit';

    public function handle(DMarketService $dmarket): int
    {
        $start = now();
        $log = ParseLog::create([
            'source' => 'watchlist:check-prices',
            'status' => 'started',
            'started_at' => $start,
        ]);

        try {
            $items = UserWatchlistItem::where('active', true)
                ->with(['filters'])
                ->get();
            $matched = 0;

            foreach ($items as $item) {
                $targetsData = $dmarket->getMarketTargetsByTitle($item->title);
                $orders = $targetsData['orders'] ?? [];

                $groups = [];

                foreach ($orders as $order) {
                    if (!isset($order['price'])) {
                        continue;
                    }

                    $float = $dmarket->normalizeFloat($order['attributes']['floatPartValue'] ?? null);
                    $seed = $order['attributes']['paintSeed'] ?? null;
                    $phase = $order['attributes']['phase'] ?? null;

                    if (!is_null($item->filters) && $item->filters->isNotEmpty()) {
                        $allow = false;
                        foreach ($item->filters as $filter) {
                            $ok = true;
                            if ($filter->phase !== null && $phase !== $filter->phase) {
                                $ok = false;
                            }
                            if ($filter->paint_seed !== null && $seed !== $filter->paint_seed) {
                                $ok = false;
                            }
                            if ($filter->min_float !== null && ($float === null || $float <= (float) $filter->min_float)) {
                                $ok = false;
                            }
                            if ($filter->max_float !== null && ($float === null || $float >= (float) $filter->max_float)) {
                                $ok = false;
                            }

                            if ($ok) {
                                $allow = true;
                                break;
                            }
                        }

                        if (!$allow) {
                            continue;
                        }
                    }

                    $key = ($float ?? 'any').'|'.($seed ?? 'any').'|'.($phase ?? 'any');
                    $price = ((int) $order['price']) / 100;

                    if (!isset($groups[$key]) || $price > $groups[$key]['target_max_price_usd']) {
                        $groups[$key] = [
                            'floatPartValue' => $float,
                            'paintSeed' => $seed,
                            'phase' => $phase,
                            'target_max_price_usd' => $price,
                        ];
                    }
                }

                $best = null;

                foreach ($groups as $group) {
                    $filterStrings = [];
                    if ($group['floatPartValue'] !== 'any') {
                        $range = $dmarket->getPaintwearRange($group['floatPartValue']);
                        if ($range['min'] !== null) {
                            $filterStrings[] = sprintf('floatValueFrom[]=%s,floatValueTo[]=%s', $range['min'], $range['max']);
                        }
                    }
                    if ($group['paintSeed'] !== 'any') {
                        $filterStrings[] = 'paintSeed[]='.$group['paintSeed'];
                    }
                    if ($group['phase'] !== 'any') {
                        $filterStrings[] = 'phase[]='.$group['phase'];
                    }
                    $filterStrings[] = 'category_0[]=not_stattrak_tm';

                    $marketData = $dmarket->getMarketItemsByTitle($item->title, [
                        'treeFilters' => $filterStrings ? implode(',', $filterStrings) : '',
                    ]);

                    $prices = [];
                    foreach ($marketData['objects'] ?? [] as $obj) {
                        if (isset($obj['price']['USD'])) {
                            $prices[] = ((int) $obj['price']['USD']) / 100;
                        }
                    }

                    sort($prices);
                    $marketMin = $prices[0] ?? null;
                    $targetMax = $group['target_max_price_usd'];
                    $profit = ($marketMin !== null && $marketMin > 0)
                        ? ($targetMax - $marketMin) / $marketMin * 100
                        : null;

                    if ($profit !== null && $profit >= 5) {
                        if ($best === null || $profit > $best['profit_percent']) {
                            $best = [
                                'min_market_price_usd' => $marketMin,
                                'target_max_price_usd' => $targetMax,
                                'profit_percent' => $profit,
                            ];
                        }
                    }
                }

                if ($best) {
                    $matched++;
                    WatchlistPriceCheck::updateOrCreate(
                        ['user_watchlist_item_id' => $item->id],
                        $best
                    );
                }
            }

            $log->update([
                'status' => 'success',
                'message' => "Скановано {$items->count()} айтемів, знайдено {$matched} вигідних",
                'ended_at' => now(),
            ]);

            $this->info('Completed successfully');
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $log->update([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'ended_at' => now(),
            ]);

            $this->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
