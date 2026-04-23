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
                m.disponibilite
             FROM menus m
             ORDER BY m.id_menu'
        );

        $menus = $stmt->fetchAll();
        $components = $this->findComponentsByMenu();

        foreach ($menus as &$menu) {
            $menu['produits'] = $components[(int) $menu['id']] ?? [];
        }

        return $menus;
    }

    private function findComponentsByMenu(): array
    {
        $stmt = $this->pdo->query(
            'SELECT
                mp.id_menu,
                p.id_produit AS id,
                p.nom,
                p.image,
                mp.quantite
             FROM menu_produit mp
             INNER JOIN produits p ON p.id_produit = mp.id_produit
             ORDER BY mp.id_menu, p.id_produit'
        );

        $components = [];

        foreach ($stmt->fetchAll() as $row) {
            $idMenu = (int) $row['id_menu'];
            unset($row['id_menu']);
            $components[$idMenu][] = $row;
        }

        return $components;
    }
}
