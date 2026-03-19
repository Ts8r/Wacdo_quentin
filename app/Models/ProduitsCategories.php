<?php

declare(strict_types=1);

namespace App\Models;

final class ProduitsCategories
{
    public function __construct(
        public int $idProduit = 0,
        public int $idCat = 0,
    ) {
    }
}
