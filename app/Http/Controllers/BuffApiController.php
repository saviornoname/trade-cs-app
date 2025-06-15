<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BuffApiService;
use App\Models\PaintwearRange;
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
        if ($floatPartValue === null) {
            return ['min' => null, 'max' => null];
        }

        $range = PaintwearRange::where('name', $floatPartValue)->first();

        return $range ? ['min' => $range->min, 'max' => $range->max] : ['min' => null, 'max' => null];
    }
}
