<?php

declare(strict_types=1);

namespace App\Http;

final class Cors
{
    private const DEFAULT_FRONT_URL = 'front-quentin-wacdo.stark.a3n.fr';

    public static function isApiRequest(?string $uri = null): bool
    {
        $path = parse_url($uri ?? ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';

        return str_starts_with($path, '/api/');
    }

    public static function sendApiHeaders(): void
    {
        header('Access-Control-Allow-Origin: ' . self::allowedOrigin());
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Accept');
        header('Vary: Origin');
    }

    public static function handlePreflight(string $method, string $uri): bool
    {
        if ($method !== 'OPTIONS' || !self::isApiRequest($uri)) {
            return false;
        }

        self::sendApiHeaders();
        http_response_code(204);

        return true;
    }

    private static function allowedOrigin(): string
    {
        $frontUrl = getenv('FRONT_URL') ?: self::DEFAULT_FRONT_URL;

        return str_starts_with($frontUrl, 'http')
            ? $frontUrl
            : 'https://' . $frontUrl;
    }
}
