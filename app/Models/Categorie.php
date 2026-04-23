<?php

declare(strict_types=1);

namespace App\Models;

final class Categorie
{
    public function __construct(
        public int $idCat = 0,
        public string $type = '',
        public string $image = '',
        public string $description = '',
    ) {
    }
}
