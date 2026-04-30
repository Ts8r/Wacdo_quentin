<?php

declare(strict_types=1);

namespace App\Http;

final class JsonResponse
{
    public static function send(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        if (Cors::isApiRequest()) {
            Cors::sendApiHeaders();
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
