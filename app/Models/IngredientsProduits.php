<?php

declare(strict_types=1);

namespace App\Models;

final class IngredientsProduits
{
    public function __construct(
        public int $idIngredient = 0,
        public int $idProduit = 0,
        public int $quantite = 0,
    ) {
    }
}
