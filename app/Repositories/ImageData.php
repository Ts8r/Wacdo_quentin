<?php

declare(strict_types=1);

namespace App\Repositories;

final class ImageData
{
    public static function dataUri(mixed $binary, mixed $mime): ?string
    {
        if (!is_string($binary) || $binary === '') {
            return null;
        }

        $mime = is_string($mime) && $mime !== '' ? $mime : 'image/png';

        return sprintf('data:%s;base64,%s', $mime, base64_encode($binary));
    }
}
