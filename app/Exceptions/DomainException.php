<?php

declare(strict_types=1);

namespace App\Exceptions;

class DomainException extends \DomainException
{
    public static function fromMessage(string $message): self
    {
        return new self($message);
    }
}
