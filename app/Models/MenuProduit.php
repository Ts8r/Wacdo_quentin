<?php

declare(strict_types=1);

namespace App\Models;

final class MenuProduit
{
    public function __construct(
        public int $idMenu = 0,
        public int $idProduit = 0,
        public int $quantite = 0,
    ) {
    }
}
