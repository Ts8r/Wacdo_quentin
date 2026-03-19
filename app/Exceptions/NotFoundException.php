<?php

declare(strict_types=1);

namespace App\Exceptions;

final class NotFoundException extends DomainException
{
    public static function forId(string $entity, int $id): self
    {
        return new self(sprintf('%s with id %d was not found.', $entity, $id));
    }

    public static function forKey(string $entity, string $key): self
    {
        return new self(sprintf('%s with key "%s" was not found.', $entity, $key));
    }
}
