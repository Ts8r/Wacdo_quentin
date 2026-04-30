<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class DbMenuRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findAllForApi(): array
    {
        $stmt = $this->pdo->query(
            'SELECT
                m.id_menu AS id,
                m.nom,
                m.prix,
                m.image,
                m.image_mime,
                m.disponibilite
             FROM menus m
             ORDER BY m.id_menu'
        );

        $menus = $stmt->fetchAll();
        $components = $this->findComponentsByMenu();

        foreach ($menus as &$menu) {
            $this->formatMenuImage($menu);
            $menu['produits'] = $components[(int) $menu['id']] ?? [];
        }
        unset($menu);

        return $menus;
    }

    public function findOneForApi(int $idMenu): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                m.id_menu AS id,
                m.nom,
                m.prix,
                m.image,
                m.image_mime,
                m.disponibilite
             FROM menus m
             WHERE m.id_menu = :id_menu'
        );
        $stmt->execute(['id_menu' => $idMenu]);
        $menu = $stmt->fetch();

        if ($menu === false) {
            return null;
        }

        $components = $this->findComponentsByMenu();
        $this->formatMenuImage($menu);
        $menu['produits'] = $components[$idMenu] ?? [];

        return $menu;
    }

    public function updateForApi(int $idMenu, array $fields): ?array
    {
        if ($this->findOneForApi($idMenu) === null) {
            return null;
        }

        $allowedFields = [
            'prix' => 'prix',
            'disponibilite' => 'disponibilite',
        ];

        $sets = [];
        $params = ['id_menu' => $idMenu];

        foreach ($fields as $field => $value) {
            if (!array_key_exists($field, $allowedFields)) {
                continue;
            }

            $sets[] = $allowedFields[$field] . ' = :' . $field;
            $params[$field] = $value;
        }

        if ($sets === []) {
            return $this->findOneForApi($idMenu);
        }

        $stmt = $this->pdo->prepare(
            'UPDATE menus
             SET ' . implode(', ', $sets) . '
             WHERE id_menu = :id_menu'
        );
        $stmt->execute($params);

        return $this->findOneForApi($idMenu);
    }

    private function findComponentsByMenu(): array
    {
        $stmt = $this->pdo->query(
            'SELECT
                mp.id_menu,
                p.id_produit AS id,
                p.nom,
                p.image,
                p.image_mime,
                mp.quantite
             FROM menu_produit mp
             INNER JOIN produits p ON p.id_produit = mp.id_produit
             ORDER BY mp.id_menu, p.id_produit'
        );

        $components = [];

        foreach ($stmt->fetchAll() as $row) {
            $idMenu = (int) $row['id_menu'];
            unset($row['id_menu']);
            $row['image'] = ImageData::dataUri($row['image'] ?? null, $row['image_mime'] ?? null);
            unset($row['image_mime']);
            $components[$idMenu][] = $row;
        }

        return $components;
    }

    private function formatMenuImage(array &$menu): void
    {
        $menu['prix'] = (float) $menu['prix'];
        $menu['prix_tailles'] = $this->pricesBySize($menu['prix']);
        $menu['image'] = ImageData::dataUri($menu['image'] ?? null, $menu['image_mime'] ?? null);
        unset($menu['image_mime']);
    }

    private function pricesBySize(float $basePrice): array
    {
        return [
            'S' => max(0.01, round($basePrice - 1.00, 2)),
            'M' => round($basePrice, 2),
            'L' => round($basePrice + 1.00, 2),
        ];
    }
}
