<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class DbCategorieRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findAllForApi(): array
    {
        $stmt = $this->pdo->query(
            'SELECT
                id_cat AS id,
                type,
                image,
                description
             FROM categories
             ORDER BY id_cat'
        );

        return $stmt->fetchAll();
    }
}
