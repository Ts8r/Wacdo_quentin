<?php

declare(strict_types=1);

namespace App\Models;

use App\Exceptions\ValidationException;

final class CommandeMenu
{
    public function __construct(
        public int $idCmd = 0,
        public int $idMenu = 0,
        public int $quantite = 0,
        public string $taille = '',
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

    public function changerTaille(string $taille): void
    {
        $taille = trim($taille);

        if ($taille === '') {
            throw ValidationException::forField('taille', 'size cannot be empty');
        }

        $this->taille = $taille;
    }
}
