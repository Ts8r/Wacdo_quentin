<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Produit;
use BadMethodCallException;
use PDO;

final class DbProduitRepository implements ProductRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findAll(): array
    {
        throw new BadMethodCallException('DbProduitRepository::findAll() must be implemented with PDO queries.');
    }

    public function findById(int $id): Produit
    {
        throw new BadMethodCallException('DbProduitRepository::findById() must be implemented with PDO queries.');
    }

    public function save(Produit $produit): void
    {
        throw new BadMethodCallException('DbProduitRepository::save() must be implemented with PDO queries.');
    }

    public function delete(int $id): bool
    {
        throw new BadMethodCallException('DbProduitRepository::delete() must be implemented with PDO queries.');
    }

    public function findByCategorie(int $idCat): array
    {
        throw new BadMethodCallException('DbProduitRepository::findByCategorie() must be implemented with PDO queries.');
    }
}
