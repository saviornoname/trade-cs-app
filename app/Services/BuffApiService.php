<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BuffApiService
{
    public function getSkinPrices(string $skinId)
    {
        $url = "https://buff.163.com/api/market/goods/sell_order";
        $params = [
            'game' => 'csgo',
            'goods_id' => $skinId,
        ];

        $response = Http::get($url, $params);

        if ($response->successful()) {
            return $response->json(); // повертає структуру з цінами
        }

        return [
            'error' => 'Failed to fetch data from Buff API',
            'status' => $response->status(),
            'body' => $response->body()
        ];
    }

    public function getBuyOrders(string $goodsId, ?float $minPaintwear = null, ?float $maxPaintwear = null)
    {
        $url = "https://buff.163.com/api/market/goods/buy_order";
        $params = [
            'game' => 'csgo',
            'goods_id' => $goodsId,
            'page_num' => 1,
        ];

        if ($minPaintwear !== null) {
            $params['min_paintwear'] = $minPaintwear;
        }
        if ($maxPaintwear !== null) {
            $params['max_paintwear'] = $maxPaintwear;
        }

        $response = Http::get($url, $params);

        if ($response->successful()) {
            return $response->json();
        }

        return [
            'error' => 'Failed to fetch data from Buff API',
            'status' => $response->status(),
            'body' => $response->body(),
        ];
    }
}
