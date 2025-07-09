<?php

namespace App\Services;

use App\Models\ApiCredential;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\PaintwearRange;
use App\Models\UserWatchlistItem;
class DMarketService
{
    protected string $baseUrl = 'https://api.dmarket.com/';
    protected string $publicKey;
    protected string $secretKey;

    public function __construct()
    {
        $cred = ApiCredential::first();
        $this->publicKey = $cred->dmarket_public_key ?? config('services.dmarket.public_key');
        $this->secretKey = $cred->dmarket_secret_key ?? config('services.dmarket.secret_key');
    }

    protected function buildUrl(string $endpoint): string
    {
        return rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
    }

    protected function buildSignedHeaders(string $method, string $route, string $body = ''): array
    {
        $timestamp = (string)time();
        $message = $method . $route . $body . $timestamp;

        $signature = sodium_bin2hex(
            sodium_crypto_sign_detached($message, sodium_hex2bin($this->secretKey))
        );

        return [
            'X-Api-Key' => $this->publicKey,
            'X-Request-Sign' => 'dmar ed25519 ' . $signature,
            'X-Sign-Date' => $timestamp,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function sendRequest(
        string  $method,
        string  $routeForUrl,
        array   $query = [],
        array   $body = [],
        ?string $routeForSign = null
    ): ?array
    {
        $url = $this->buildUrl($routeForUrl);
        $bodyJson = empty($body) ? '' : json_encode($body, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        // Побудова query-рядка (вже енкоджений)
        $queryString = http_build_query($query);

        // Формування route для підпису
        $signingRoute = $routeForSign ?? $routeForUrl;
        if (!empty($queryString)) {
            $signingRoute .= '?' . $queryString;
        }

        $headers = $this->buildSignedHeaders(strtoupper($method), $signingRoute, $bodyJson);

        try {
            $response = match (strtolower($method)) {
                'get' => Http::withHeaders($headers)->get($url, $query),
                'post' => Http::withHeaders($headers)->post($url, $body),
                default => throw new \InvalidArgumentException("Unsupported HTTP method: $method"),
            };

            if ($response->failed()) {
                Log::error("DMarket API error [{$url}]: {$response->status()}", $response->json());
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('DMarket request failed', ['exception' => $e->getMessage()]);
            return null;
        }
    }


    // ------------------------------------
    // 👇 Публічні API методи
    // ------------------------------------

    public function getMarketTargets(string $gameId, string $title): ?array
    {
        $query = [
            'limit' => 1000,
        ];
        // 1. route для реального URL (треба ENCODED title)
        $encodedTitle = rawurlencode($title);
        $routeForUrl = "/marketplace-api/v1/targets-by-title/{$gameId}/{$encodedTitle}";

        // 2. route для ПІДПИСУ (НЕенкоджений title)
        $routeForSign = "/marketplace-api/v1/targets-by-title/{$gameId}/{$title}";

        return $this->sendRequest('GET', $routeForUrl, $query, [], $routeForSign);
    }


//    public function getUserTargets(string $status = 'TargetStatusActive'): ?array
    public function getUserTargets(string $status = 'TargetStatusInactive'): ?array
    {
        $query = [
            'BasicFilters.Status' => $status,
            'Limit' => 100,
        ];
        return $this->sendRequest('GET', '/marketplace-api/v1/user-targets', $query);
    }


    public function createTarget(array $targetData): ?array
    {
        return $this->sendRequest('POST', '/marketplace-api/v1/user-targets/create', [], $targetData);
    }

    public function getMarketItems(array $params): ?array
    {

        try {
            $url = $this->buildUrl('/exchange/v1/market/items');
            $response = Http::acceptJson()->get($url, $params);
            if ($response->failed()) {
                Log::error("DMarket market items error [{$url}]: {$response->status()}", [
                    'body' => $response->body(),
                ]);
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('DMarket market items request failed', ['exception' => $e->getMessage()]);
            return null;
        }
    }

    public function compareWithBuff(array $filters = []): ?array
    {
        $dmarketItems = $this->getMarketTargets($filters['gameId'] ?? 'a8db', $filters['title'] ?? '');
        // TODO: реалізувати логіку порівняння
        return $dmarketItems;
    }
    public function getMarketComparisons(string $gameId = 'a8db'): array
    {
        $watchItems = UserWatchlistItem::where('active', true)->get();

        $result = [];

        foreach ($watchItems as $item) {
            $targetsData = $this->getMarketTargets($gameId, $item->title);
            $orders = $targetsData['orders'] ?? [];
            $groups = [];
            $filterSets = $item->filters()->get();

            foreach ($orders as $order) {
                if (!isset($order['price'])) {
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
                    'gameId' => $gameId,
                    'types' => 'dmarket',
                    'myFavorites' => 'false',
                    'limit' => 3,
                    'currency' => 'USD',
                    'category_0'=>["not_stattrak_tm"]
                ];

                $params['treeFilters'] = $filters ? implode(',', $filters) : '';

                $marketData = $this->getMarketItems($params);
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

        return $result;
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
