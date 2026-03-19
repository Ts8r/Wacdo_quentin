<?php

declare(strict_types=1);

namespace App\Models;

final class Commande
{
    /** @var CommandeProduit[] */
    private array $lignesProduit = [];

    /** @var CommandeMenu[] */
    private array $lignesMenu = [];

    public function __construct(
        public int $idCmd = 0,
        public ?int $idUser = null,
        public int $idStatut = 0,
        public int $idCanal = 0,
        public string $numeroTicket = '',
        public string $dateCmd = '',
        public float $totalTtc = 0.0,
    ) {
    }

    public function ajouterLigneProduit(CommandeProduit $ligne): void
    {
        $this->lignesProduit[] = $ligne;
        $this->calculerTotal();
    }

    public function ajouterLigneMenu(CommandeMenu $ligne): void
    {
        $this->lignesMenu[] = $ligne;
        $this->calculerTotal();
    }

    public function calculerTotal(): float
    {
        if ($this->lignesProduit === [] && $this->lignesMenu === []) {
            return $this->totalTtc;
        }

        $produits = array_reduce(
            $this->lignesProduit,
            static fn (float $carry, CommandeProduit $ligne): float => $carry + $ligne->prixLigne,
            0.0,
        );

        $menus = array_reduce(
            $this->lignesMenu,
            static fn (float $carry, CommandeMenu $ligne): float => $carry + $ligne->prixLigne,
            0.0,
        );

        $this->totalTtc = $produits + $menus;

        return $this->totalTtc;
    }

    public function changerStatut(StatutCommande $statut): void
    {
        $this->idStatut = $statut->idStatut;
    }

    /**
     * @return CommandeProduit[]
     */
    public function getLignesProduit(): array
    {
        return $this->lignesProduit;
    }

    /**
     * @return CommandeMenu[]
     */
    public function getLignesMenu(): array
    {
        return $this->lignesMenu;
    }
}
