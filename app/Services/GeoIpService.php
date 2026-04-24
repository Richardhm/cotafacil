<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeoIpService
{
    private const PRIVATE_PREFIXES = ['192.168.', '10.', '172.16.', '172.17.', '172.18.',
        '172.19.', '172.20.', '172.21.', '172.22.', '172.23.', '172.24.', '172.25.',
        '172.26.', '172.27.', '172.28.', '172.29.', '172.30.', '172.31.'];

    public function resolve(string $ip): array
    {
        if ($this->isPrivateIp($ip)) {
            return ['city' => 'Local', 'country' => 'Dev'];
        }

        $cached = Cache::get("geoip_{$ip}");
        if ($cached !== null) {
            return $cached;
        }

        try {
            $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}", [
                'lang'   => 'pt-BR',
                'fields' => 'status,city,regionName,country',
            ]);

            if ($response->successful() && $response->json('status') === 'success') {
                $city    = $response->json('city');
                $region  = $response->json('regionName');
                $country = $response->json('country');

                $result = [
                    'city'    => $city ? "{$city} / {$region}" : $region,
                    'country' => $country,
                ];

                Cache::put("geoip_{$ip}", $result, now()->addDay());

                return $result;
            }
        } catch (\Throwable) {
            // falha silenciosa — geolocalização não é crítica
        }

        return ['city' => null, 'country' => null];
    }

    private function isPrivateIp(string $ip): bool
    {
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'], true)) {
            return true;
        }

        foreach (self::PRIVATE_PREFIXES as $prefix) {
            if (str_starts_with($ip, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
