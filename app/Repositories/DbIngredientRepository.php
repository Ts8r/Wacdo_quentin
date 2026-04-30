<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\ValidationException;
use PDO;

final class DbIngredientRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findAllForApi(?string $search, int $limit, int $offset): array
    {
        $params = [];
        $whereSql = '';

        if ($search !== null && $search !== '') {
            $whereSql = 'WHERE i.nom LIKE :search';
            $params['search'] = '%' . $search . '%';
        }

        $stmt = $this->pdo->prepare(
            'SELECT
                i.id_ingredient AS id,
                i.nom,
                i.cout_unitaire,
                i.quantite
             FROM ingredients i
             ' . $whereSql . '
             ORDER BY i.nom
             LIMIT :limit OFFSET :offset'
        );

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return array_map([$this, 'formatIngredientForApi'], $stmt->fetchAll());
    }

    public function countForApi(?string $search): int
    {
        $params = [];
        $whereSql = '';

        if ($search !== null && $search !== '') {
            $whereSql = 'WHERE nom LIKE :search';
            $params['search'] = '%' . $search . '%';
        }

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM ingredients ' . $whereSql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function findOneForApi(int $idIngredient): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                i.id_ingredient AS id,
                i.nom,
                i.cout_unitaire,
                i.quantite
             FROM ingredients i
             WHERE i.id_ingredient = :id_ingredient'
        );
        $stmt->execute(['id_ingredient' => $idIngredient]);
        $ingredient = $stmt->fetch();

        return $ingredient === false ? null : $this->formatIngredientForApi($ingredient);
    }

    public function updateQuantityForApi(int $idIngredient, int $quantite): ?array
    {
        if ($quantite < 0) {
            throw ValidationException::forField('quantite', 'must be greater than or equal to zero');
        }

        $stmt = $this->pdo->prepare(
            'UPDATE ingredients
             SET quantite = :quantite
             WHERE id_ingredient = :id_ingredient'
        );
        $stmt->execute([
            'quantite' => $quantite,
            'id_ingredient' => $idIngredient,
        ]);

        if ($stmt->rowCount() === 0 && $this->findOneForApi($idIngredient) === null) {
            return null;
        }

        return $this->findOneForApi($idIngredient);
    }

    private function formatIngredientForApi(array $ingredient): array
    {
        return [
            'id' => (int) $ingredient['id'],
            'nom' => (string) $ingredient['nom'],
            'cout_unitaire' => (float) $ingredient['cout_unitaire'],
            'quantite' => (int) $ingredient['quantite'],
        ];
    }
}
