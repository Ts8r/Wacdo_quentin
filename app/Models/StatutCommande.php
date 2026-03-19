<?php

declare(strict_types=1);

namespace App\Models;

final class StatutCommande
{
    public function __construct(
        public int $idStatut = 0,
        public string $libelle = '',
    ) {
    }
}
