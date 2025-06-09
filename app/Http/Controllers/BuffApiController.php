<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BuffApiService;
use App\Models\UserWatchlistItem;

class BuffApiController extends Controller
{
    /**
     * Отримати ціни для першого активного айтему з watchlist.
     */
    public function index(Request $request, BuffApiService $buffApiService)
    {
        $itemId = $request->input('item_id');

        if (!$itemId) {
            // Якщо item_id не передано — беремо перший активний айтем
            $item = UserWatchlistItem::where('active', 1)->first();

            if (!$item) {
                return response()->json(['error' => 'No active items found.'], 404);
            }

            $itemId = $item->item_id;
        }

        $response = $buffApiService->getSkinPrices($itemId);

        return response()->json($response);
    }

    public function buyOrders(Request $request, BuffApiService $buffApiService)
    {
        $title = $request->input('title');
        $floatPartValue = $request->input('float_part_value');

        if (!$title) {
            return response()->json(['error' => 'Title is required.'], 422);
        }

        $item = UserWatchlistItem::where('title', $title)->first();
        if (!$item) {
            return response()->json(['error' => 'Item not found.'], 404);
        }

        $range = $this->getPaintwearRange($floatPartValue);

        $cookie = $request->header('Buff-Cookie');

        $response = $buffApiService->getBuyOrders(
            $item->item_id,
            $range['min'],
            $range['max'],
            $cookie
        );

        return response()->json($response);
    }

    public function sellOrders(Request $request, BuffApiService $buffApiService)
    {
        $title = $request->input('title');
        $floatPartValue = $request->input('float_part_value');
        $phase = $request->input('phase');

        if (!$title) {
            return response()->json(['error' => 'Title is required.'], 422);
        }

        $item = UserWatchlistItem::where('title', $title)->first();
        if (!$item) {
            return response()->json(['error' => 'Item not found.'], 404);
        }

        $range = $this->getPaintwearRange($floatPartValue);

        $cookie = $request->header('Buff-Cookie');

        $response = $buffApiService->getSellOrders(
            $item->item_id,
            $range['min'],
            $range['max'],
            $cookie
        );

        if (isset($response['data']['items']) && $phase) {
            $response['data']['items'] = array_values(array_filter(
                $response['data']['items'],
                fn ($i) => ($i['asset_info']['info']['phase_data']['name'] ?? null) === $phase
            ));
        }

        if (isset($response['data']['items'])) {
            usort($response['data']['items'], fn ($a, $b) => floatval($a['price']) <=> floatval($b['price']));
        }

        return response()->json($response);
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
