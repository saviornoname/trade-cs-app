<?php

namespace App\Http\Controllers;

use App\Models\PaintwearRange;
use App\Models\UserWatchlistItem;
use App\Services\BuffApiService;
use App\Services\DMarketService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DMarketController extends Controller
{
    public function index()
    {
        return Inertia::render('DMarketTest');
    }

    public function getMarketItems(Request $request, DMarketService $dmarket)
    {
        // отримуємо дані з форми або з фронтенда
        $title = $request->input('title', '');
        $gameId = $request->input('gameId', 'a8db'); // CS:GO/CS2 — це DMarket Game ID

        $items = $dmarket->getMarketTargets($gameId, $title);

        return response()->json($items);
    }

    public function getUserTargets(DMarketService $dmarket)
    {
        return response()->json($dmarket->getUserTargets());
    }

    public function createTarget(Request $request, DMarketService $dmarket)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'price' => 'required|numeric',
        ]);

        $response = $dmarket->createTarget([
            'title' => $data['title'],
            'price' => $data['price'],
        ]);

        return response()->json($response);
    }

    public function compareWithBuff(DMarketService $dmarket, BuffApiService $buff)
    {
        $targets = $dmarket->getUserTargets('TargetStatusActive');

        $result = [];

        if (! empty($targets['Items'])) {
            foreach ($targets['Items'] as $target) {
                $title = $target['Title'] ?? '';
                $floatPartValue = $this->getAttr($target, 'floatPartValue');
                $phase = $this->getAttr($target, 'phase');

                $watchItem = UserWatchlistItem::where('title', $title)->first();
                if (! $watchItem || ! $watchItem->item_id) {
                    continue;
                }

                $range = $this->getPaintwearRange($floatPartValue);
                $buffData = $buff->getSellOrders($watchItem->item_id, $range['min'], $range['max']);

                $orders = $buffData['data']['items'] ?? [];
                if ($phase) {
                    $orders = array_values(array_filter($orders, fn ($o) => ($o['asset_info']['info']['phase_data']['name'] ?? null) === $phase));
                }

                if ($orders) {
                    usort($orders, fn ($a, $b) => floatval($a['price']) <=> floatval($b['price']));
                    $buffPrice = $orders[0]['price'];
                } else {
                    $buffPrice = null;
                }

                $result[] = [
                    'title' => $title,
                    'phase' => $phase,
                    'float' => $floatPartValue,
                    'dmarket_price_usd' => $target['Price']['Amount'] ?? null,
                    'best_buff_price_cny' => $buffPrice,
                ];
            }

            return response()->json($result);
        }

        $watchItems = UserWatchlistItem::where('active', true)->get();

        foreach ($watchItems as $item) {
            $dmData = $dmarket->getMarketItems([
                'gameId' => 'a8db',
                'title' => $item->title,
                'currency' => 'USD',
                'limit' => 1,
                'orderBy' => 'price',
                'orderDir' => 'asc',
            ]);

            $obj = $dmData['objects'][0] ?? null;

            $dmPrice = $obj['price']['USD'] ?? null;
            $phase = $obj['extra']['phase'] ?? null;
            $float = $obj['extra']['floatPartValue'] ?? null;

            $result[] = [
                'title' => $item->title,
                'phase' => $phase,
                'float' => $float,
                'dmarket_price_usd' => $dmPrice,
            ];
        }

        return response()->json($result);
    }
    public function compareTargetsMarket(DMarketService $dmarket)
    {
        $watchItems = UserWatchlistItem::where('active', true)->get();

        $result = [];

        foreach ($watchItems as $item) {
            $targetsData = $dmarket->getMarketTargets('a8db', $item->title);
            $orders = $targetsData['orders'] ?? [];
            $groups = [];
            $filterSets = $item->filters()->get();
            foreach ($orders as $order) {
                if (! isset($order['price'])) {
                    continue;
                }

                $float = $this->normalizeFloat($order['attributes']['floatPartValue'] ?? null);
                $seed = $order['attributes']['paintSeed'] ?? null;
                $phase = $order['attributes']['phase'] ?? null;

                if ($filterSets->isNotEmpty()) {
                    $matched = false;
                    foreach ($filterSets as $f) {

                        $ok = true;
                        if ($f->phase !== null && $phase !== $f->phase) {

                            $ok = false;
                        }

                        if ($f->min_float !== null) {
                            if ($float === null || $float <= floatval($f->min_float)) {
                                $ok = false;
                            }
                        }
                        if ($f->max_float !== null) {
                            if ($float === null || $float >= floatval($f->max_float)) {
                                $ok = false;
                            }
                        }

                        if ($ok) {
                            $matched = true;
                            break;
                        }
                    }

                    if (!$matched) {
                        continue;
                    }
                }

                $floatKey = $float ?? 'any';
                $seedKey = $seed ?? 'any';
                $phaseKey = $phase ?? 'any';

                $price = intval($order['price']) / 100;
                $key = $floatKey.'|'.$seedKey.'|'.$phaseKey;

                if (! isset($groups[$key]) || $price > $groups[$key]['target_max_price_usd']) {
                    $groups[$key] = [
                        'floatPartValue' => $float,
                        'paintSeed' => $seed,
                        'phase' => $phase,
                        'target_max_price_usd' => $price,
                    ];
                }
            }

            foreach ($groups as &$group) {
                $filters = [];
                if ($group['floatPartValue'] !== 'any') {
                    $range = $this->getPaintwearRange($group['floatPartValue']);
                    if ($range['min'] !== null) {
                        $filters[] = sprintf('floatValueFrom[]=%s,floatValueTo[]=%s', $range['min'], $range['max']);
                    }
                }
                if ($group['paintSeed'] !== 'any') {
                    $filters[] = 'paintSeed[]='.$group['paintSeed'];
                }
                if ($group['phase'] !== 'any') {
                    $filters[] = 'phase[]='.$group['phase'];
                }
                $filters[] = 'category_0[]=not_stattrak_tm';

                $params = [
                    'side' => 'market',
                    'orderBy' => 'price',
                    'orderDir' => 'asc',
                    'title' => $item->title,
                    'priceFrom' => 0,
                    'priceTo' => 0,
                    'gameId' => 'a8db',
                    'types' => 'dmarket',
                    'myFavorites' => 'false',
                    'limit' => 3,
                    'currency' => 'USD',
                    'category_0'=>["not_stattrak_tm"]
                ];

                $params['treeFilters'] = $filters ? implode(',', $filters) : '';

                $marketData = $dmarket->getMarketItems($params);
                $objects = $marketData['objects'] ?? [];

                $prices = [];
                foreach ($objects as $obj) {
                    if (isset($obj['price']['USD'])) {
                        $prices[] = intval($obj['price']['USD']) / 100;
                    }
                }

                $group['market_min_prices_usd'] = $prices;
                $group['title'] = $item->title;

                $result[] = $group;
            }
        }

        return response()->json($result);
    }

    private function getPaintwearRange(?string $floatPartValue): array
    {
        if ($floatPartValue === null) {
            return ['min' => null, 'max' => null];
        }

        $range = PaintwearRange::where('name', $floatPartValue)->first();

        return $range ? ['min' => $range->min, 'max' => $range->max] : ['min' => null, 'max' => null];
    }

    private function normalizeFloat(?string $floatPartValue): ?float
    {
        if ($floatPartValue === null) {
            return null;
        }

        if (is_numeric($floatPartValue)) {
            return floatval($floatPartValue);
        }

        $range = PaintwearRange::where('name', $floatPartValue)->first();

        return $range ? ($range->min + $range->max) / 2 : null;
    }
}
