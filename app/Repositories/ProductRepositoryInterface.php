<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Produit;

interface ProductRepositoryInterface
{
    /**
     * @return Produit[]
     */
    public function findAll(): array;

    public function findById(int $id): Produit;

    public function save(Produit $produit): void;

    public function delete(int $id): bool;

    /**
     * @return Produit[]
     */
    public function findByCategorie(int $idCat): array;
}
