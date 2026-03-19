<?php

declare(strict_types=1);

namespace App\Models;

final class Canal
{
    public function __construct(
        public int $idCanal = 0,
        public string $libelle = '',
    ) {
    }
}
