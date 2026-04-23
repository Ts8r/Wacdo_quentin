<?php

declare(strict_types=1);

namespace App\Http;

use App\Exceptions\ValidationException;

final class JsonRequest
{
    public static function body(): array
    {
        $raw = file_get_contents('php://input');

        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $data = json_decode($raw, true);

        if (!is_array($data)) {
            throw ValidationException::forField('body', 'invalid JSON payload');
        }

        return $data;
    }
}
