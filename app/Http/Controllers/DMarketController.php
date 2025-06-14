<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\DMarketService;
use App\Services\BuffApiService;
use App\Models\UserWatchlistItem;
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

        if (!empty($targets['Items'])) {
            foreach ($targets['Items'] as $target) {
                $title = $target['Title'] ?? '';
                $floatPartValue = $this->getAttr($target, 'floatPartValue');
                $phase = $this->getAttr($target, 'phase');

                $watchItem = UserWatchlistItem::where('title', $title)->first();
                if (!$watchItem || !$watchItem->item_id) {
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
                'buff_target_price_usd' => $item->max_price_usd ? $item->max_price_usd / 100 : null,
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

            foreach ($orders as $order) {
                if (!isset($order['price'])) {
                    continue;
                }

                $float = $order['attributes']['floatPartValue'] ?? 'any';
                $seed = $order['attributes']['paintSeed'] ?? 'any';
                $phase = $order['attributes']['phase'] ?? 'any';

                $price = intval($order['price']) / 100;
                $key = $float . '|' . $seed . '|' . $phase;

                if (!isset($groups[$key]) || $price > $groups[$key]['target_max_price_usd']) {
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
                    $filters[] = 'paintSeed[]=' . $group['paintSeed'];
                }
                if ($group['phase'] !== 'any') {
                    $filters[] = 'phase[]=' . $group['phase'];
                }

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
        $map = [
            'FN-0' => ['min' => 0.00, 'max' => 0.01],
            'FN-1' => ['min' => 0.01, 'max' => 0.02],
            'FN-2' => ['min' => 0.02, 'max' => 0.03],
            'FN-3' => ['min' => 0.03, 'max' => 0.04],
            'FN-4' => ['min' => 0.04, 'max' => 0.05],
            'FN-5' => ['min' => 0.05, 'max' => 0.06],
            'FN-6' => ['min' => 0.06, 'max' => 0.07],
            'MW-0' => ['min' => 0.07, 'max' => 0.08],
            'MW-1' => ['min' => 0.08, 'max' => 0.09],
            'MW-2' => ['min' => 0.09, 'max' => 0.10],
            'MW-3' => ['min' => 0.10, 'max' => 0.11],
            'MW-4' => ['min' => 0.11, 'max' => 0.15],
            'FT-0' => ['min' => 0.15, 'max' => 0.18],
            'FT-1' => ['min' => 0.18, 'max' => 0.21],
            'FT-2' => ['min' => 0.21, 'max' => 0.24],
            'FT-3' => ['min' => 0.24, 'max' => 0.27],
            'FT-4' => ['min' => 0.27, 'max' => 0.38],
            'WW-0' => ['min' => 0.38, 'max' => 0.39],
            'WW-1' => ['min' => 0.39, 'max' => 0.40],
            'WW-2' => ['min' => 0.40, 'max' => 0.41],
            'WW-3' => ['min' => 0.41, 'max' => 0.42],
            'WW-4' => ['min' => 0.42, 'max' => 0.45],
            'BS-0' => ['min' => 0.45, 'max' => 0.50],
            'BS-1' => ['min' => 0.50, 'max' => 0.63],
            'BS-2' => ['min' => 0.63, 'max' => 0.76],
            'BS-3' => ['min' => 0.76, 'max' => 0.80],
            'BS-4' => ['min' => 0.80, 'max' => 1.00],
        ];

        return $map[$floatPartValue] ?? ['min' => null, 'max' => null];
    }
}
