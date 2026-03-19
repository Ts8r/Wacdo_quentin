<?php

declare(strict_types=1);

namespace App\Models;

use App\Exceptions\ValidationException;

final class CommandeProduit
{
    public function __construct(
        public int $idCmd = 0,
        public int $idProduit = 0,
        public int $quantite = 0,
        public float $prixUnitaire = 0.0,
        public float $prixLigne = 0.0,
    ) {
        $this->recalculerPrixLigne();
    }

    public function recalculerPrixLigne(): float
    {
        $this->prixLigne = $this->quantite * $this->prixUnitaire;

        return $this->prixLigne;
    }

    public function changerQuantite(int $qte): void
    {
        if ($qte <= 0) {
            throw ValidationException::forField('qte', 'quantity must be greater than zero');
        }

        $this->quantite = $qte;
        $this->recalculerPrixLigne();
    }
}
