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
                image_mime,
                description
             FROM categories
             ORDER BY id_cat'
        );

        return array_map(
            static fn (array $row): array => [
                'id' => (int) $row['id'],
                'type' => (string) $row['type'],
                'image' => ImageData::dataUri($row['image'] ?? null, $row['image_mime'] ?? null),
                'description' => $row['description'] === null ? null : (string) $row['description'],
            ],
            $stmt->fetchAll(),
        );
    }
}
