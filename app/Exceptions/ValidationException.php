<?php

declare(strict_types=1);

namespace App\Exceptions;

final class ValidationException extends DomainException
{
    public static function forField(string $field, string $reason): self
    {
        return new self(sprintf('Validation failed for "%s": %s.', $field, $reason));
    }

    public static function forRule(string $rule): self
    {
        return new self(sprintf('Validation rule failed: %s.', $rule));
    }
}
