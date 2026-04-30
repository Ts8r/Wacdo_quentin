<?php

declare(strict_types=1);

namespace App\Http;

final class JsonResponse
{
    public static function send(mixed $data, int $status = 200): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';

        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Accept');

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
