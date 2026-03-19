<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Commande;
use App\Models\CommandeMenu;
use App\Models\CommandeProduit;

interface CommandeRepositoryInterface
{
    public function findById(int $id): Commande;

    public function findByTicket(string $ticket): Commande;

    public function save(Commande $commande): void;

    public function addLigneProduit(int $idCmd, CommandeProduit $ligne): void;

    public function addLigneMenu(int $idCmd, CommandeMenu $ligne): void;

    public function updateStatut(int $idCmd, int $idStatut): bool;
}
