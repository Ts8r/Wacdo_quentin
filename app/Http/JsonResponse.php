<?php

declare(strict_types=1);

namespace App\Http;

use Throwable;

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

    /**
     * Erreur 500 : le détail technique va dans le journal du serveur, le client reçoit un message générique.
     *
     * @param array<string, string> $body
     */
    public static function serverError(
        Throwable $exception,
        array $body = ['error' => 'server_error', 'message' => 'Erreur interne du serveur.'],
    ): void {
        error_log(sprintf(
            '[WACDO] %s %s -> %s: %s (%s:%d)',
            $_SERVER['REQUEST_METHOD'] ?? '-',
            $_SERVER['REQUEST_URI'] ?? '-',
            $exception::class,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
        ));
        self::send($body, 500);
    }
}
