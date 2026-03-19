<?php

declare(strict_types=1);

namespace App\Models;

use App\Exceptions\ValidationException;

final class Ingredient
{
    public function __construct(
        public int $idIngredient = 0,
        public string $nom = '',
        public float $coutUnitaire = 0.0,
        public float $quantite = 0.0,
    ) {
    }

    public function debiter(float $qte): void
    {
        if ($qte <= 0) {
            throw ValidationException::forField('qte', 'quantity must be greater than zero');
        }

        if (!$this->estDisponible($qte)) {
            throw ValidationException::forRule('insufficient ingredient stock');
        }

        $this->quantite -= $qte;
    }

    public function crediter(float $qte): void
    {
        if ($qte <= 0) {
            throw ValidationException::forField('qte', 'quantity must be greater than zero');
        }

        $this->quantite += $qte;
    }

    public function estDisponible(float $qte): bool
    {
        return $qte > 0 && $this->quantite >= $qte;
    }
}
