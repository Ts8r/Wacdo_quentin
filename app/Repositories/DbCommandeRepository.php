<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Commande;
use App\Models\CommandeMenu;
use App\Models\CommandeProduit;
use BadMethodCallException;
use PDO;

final class DbCommandeRepository implements CommandeRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): Commande
    {
        throw new BadMethodCallException('DbCommandeRepository::findById() must be implemented with PDO queries.');
    }

    public function findByTicket(string $ticket): Commande
    {
        throw new BadMethodCallException('DbCommandeRepository::findByTicket() must be implemented with PDO queries.');
    }

    public function save(Commande $commande): void
    {
        throw new BadMethodCallException('DbCommandeRepository::save() must be implemented with PDO queries.');
    }

    public function addLigneProduit(int $idCmd, CommandeProduit $ligne): void
    {
        throw new BadMethodCallException('DbCommandeRepository::addLigneProduit() must be implemented with PDO queries.');
    }

    public function addLigneMenu(int $idCmd, CommandeMenu $ligne): void
    {
        throw new BadMethodCallException('DbCommandeRepository::addLigneMenu() must be implemented with PDO queries.');
    }

    public function updateStatut(int $idCmd, int $idStatut): bool
    {
        throw new BadMethodCallException('DbCommandeRepository::updateStatut() must be implemented with PDO queries.');
    }
}
