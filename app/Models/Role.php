<?php

declare(strict_types=1);

namespace App\Models;

final class Role
{
    public function __construct(
        public int $idRole = 0,
        public string $codeRole = '',
        public string $libelle = '',
    ) {
    }
}
