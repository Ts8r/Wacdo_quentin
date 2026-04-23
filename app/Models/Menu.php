<?php

declare(strict_types=1);

namespace App\Models;

final class Menu
{
    public function __construct(
        public int $idMenu = 0,
        public string $nom = '',
        public float $prix = 0.0,
        public string $image = '',
        public bool $disponibilite = true,
    ) {
    }

    public function estDisponible(): bool
    {
        return $this->disponibilite;
    }
}
