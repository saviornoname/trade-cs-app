<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BuffApiService
{
    private function httpGet(string $url, array $params, ?string $cookie = null)
    {
        sleep(random_int(2, 15));

        $client = Http::withHeaders([
            'Cookie' => $cookie ?? env('BUFF_COOKIE'),
        ]);

        return $client->get($url, $params);
    }
    public function getSkinPrices(string $skinId)
    {
        $url = "https://buff.163.com/api/market/goods/sell_order";
        $params = [
            'game' => 'csgo',
            'goods_id' => $skinId,
        ];

        $response = $this->httpGet($url, $params);

        if ($response->successful()) {
            return $response->json(); // повертає структуру з цінами
        }

        return [
            'error' => 'Failed to fetch data from Buff API',
            'status' => $response->status(),
            'body' => $response->body()
        ];
    }

    public function getBuyOrders(string $goodsId, ?float $minPaintwear = null, ?float $maxPaintwear = null, ?string $cookie = null)
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

        $response = $this->httpGet($url, $params, $cookie);

        if ($response->successful()) {
            return $response->json();
        }

        return [
            'error' => 'Failed to fetch data from Buff API',
            'status' => $response->status(),
            'body' => $response->body(),
        ];
    }

    public function getSellOrders(string $goodsId, ?float $minPaintwear = null, ?float $maxPaintwear = null, ?string $cookie = null)
    {
        $url = "https://buff.163.com/api/market/goods/sell_order";
        $params = [
            'game' => 'csgo',
            'goods_id' => $goodsId,
            'page_size' => 1000,
            'page_num' => 1,
            'sort_by' => 'default',
            'mode' => '',
            'allow_tradable_cooldown' => 1,
        ];

        if ($minPaintwear !== null) {
            $params['min_paintwear'] = $minPaintwear;
        }
        if ($maxPaintwear !== null) {
            $params['max_paintwear'] = $maxPaintwear;
        }

        $response = $this->httpGet($url, $params, $cookie);

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
