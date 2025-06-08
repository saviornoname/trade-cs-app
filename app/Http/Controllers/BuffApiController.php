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

}
