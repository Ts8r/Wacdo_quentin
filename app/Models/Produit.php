<?php

declare(strict_types=1);

namespace App\Models;

use App\Exceptions\ValidationException;

final class Produit
{
    public function __construct(
        public int $idProduit = 0,
        public string $nom = '',
        public string $description = '',
        public float $prixUnitaire = 0.0,
        public bool $disponibilite = true,
        public int $quantite = 0,
    ) {
    }

    public function estDisponible(int $qte): bool
    {
        return $qte > 0 && $this->disponibilite && $this->quantite >= $qte;
    }

    public function reserverStock(int $qte): void
    {
        if ($qte <= 0) {
            throw ValidationException::forField('qte', 'quantity must be greater than zero');
        }

        if (!$this->estDisponible($qte)) {
            throw ValidationException::forRule('insufficient product stock');
        }

        $this->quantite -= $qte;
    }

    public function restituerStock(int $qte): void
    {
        if ($qte <= 0) {
            throw ValidationException::forField('qte', 'quantity must be greater than zero');
        }

        $this->quantite += $qte;
    }
}
