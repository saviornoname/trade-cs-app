<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DMarketService
{
    protected string $baseUrl = 'https://api.dmarket.com/';
    protected string $publicKey;
    protected string $secretKey;

    public function __construct()
    {
        $this->publicKey = config('services.dmarket.public_key');
        $this->secretKey = config('services.dmarket.secret_key');
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
        // 1. route для реального URL (треба ENCODED title)
        $encodedTitle = rawurlencode($title);
        $routeForUrl = "/marketplace-api/v1/targets-by-title/{$gameId}/{$encodedTitle}";

        // 2. route для ПІДПИСУ (НЕенкоджений title)
        $routeForSign = "/marketplace-api/v1/targets-by-title/{$gameId}/{$title}";

        return $this->sendRequest('GET', $routeForUrl, [], [], $routeForSign);
    }


//    public function getUserTargets(string $status = 'TargetStatusActive'): ?array
    public function getUserTargets(string $status = 'TargetStatusInactive'): ?array
    {
        $query = [
            'BasicFilters.Status' => $status,
            'Limit' => 2,
        ];
        return $this->sendRequest('GET', '/marketplace-api/v1/user-targets', $query);
    }


    public function createTarget(array $targetData): ?array
    {
        return $this->sendRequest('POST', '/marketplace-api/v1/user-targets/create', [], $targetData);
    }

    public function compareWithBuff(array $filters = []): ?array
    {
        $dmarketItems = $this->getMarketTargets($filters['gameId'] ?? 'a8db', $filters['title'] ?? '');
        // TODO: реалізувати логіку порівняння
        return $dmarketItems;
    }
}
