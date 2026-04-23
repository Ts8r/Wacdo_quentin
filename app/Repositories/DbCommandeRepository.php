<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\ValidationException;
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

    public function createForApi(?int $idUser, string $canal, array $produits, array $menus): array
    {
        if ($produits === [] && $menus === []) {
            throw ValidationException::forRule('a command must contain at least one product or menu');
        }

        $this->pdo->beginTransaction();

        try {
            if ($idUser !== null) {
                $this->assertUserExists($idUser);
            }

            $canalRow = $this->findCanalByLibelle($canal);
            $statutRow = $this->findStatutByLibelle('en_attente');
            $ticket = $this->generateTicket();

            $stmt = $this->pdo->prepare(
                'INSERT INTO commandes (id_user, id_statut, id_canal, numero_ticket, total_ttc)
                 VALUES (:id_user, :id_statut, :id_canal, :numero_ticket, 0.00)'
            );
            $stmt->execute([
                'id_user' => $idUser,
                'id_statut' => $statutRow['id_statut'],
                'id_canal' => $canalRow['id_canal'],
                'numero_ticket' => $ticket,
            ]);

            $idCmd = (int) $this->pdo->lastInsertId();
            $productLines = $this->insertProductLines($idCmd, $produits);
            $menuLines = $this->insertMenuLines($idCmd, $menus);
            $this->debitIngredients($this->collectIngredientNeeds($produits, $menus));
            $total = $this->sumLines($productLines) + $this->sumLines($menuLines);

            $update = $this->pdo->prepare('UPDATE commandes SET total_ttc = :total_ttc WHERE id_cmd = :id_cmd');
            $update->execute([
                'total_ttc' => number_format($total, 2, '.', ''),
                'id_cmd' => $idCmd,
            ]);

            $commande = $this->findOneForApi($idCmd);
            $this->pdo->commit();

            return $commande;
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
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

    private function insertProductLines(int $idCmd, array $produits): array
    {
        $insert = $this->pdo->prepare(
            'INSERT INTO commande_produit (id_cmd, id_produit, quantite, prix_unitaire, prix_ligne)
             VALUES (:id_cmd, :id_produit, :quantite, :prix_unitaire, :prix_ligne)'
        );
        $lines = [];

        foreach ($produits as $line) {
            $produit = $this->findProductForOrder((int) $line['id']);
            $quantite = (int) $line['quantite'];
            $prixUnitaire = (float) $produit['prix_unitaire'];
            $prixLigne = round($prixUnitaire * $quantite, 2);

            $insert->execute([
                'id_cmd' => $idCmd,
                'id_produit' => $produit['id_produit'],
                'quantite' => $quantite,
                'prix_unitaire' => number_format($prixUnitaire, 2, '.', ''),
                'prix_ligne' => number_format($prixLigne, 2, '.', ''),
            ]);

            $lines[] = [
                'id' => (int) $produit['id_produit'],
                'nom' => (string) $produit['nom'],
                'quantite' => $quantite,
                'prix_unitaire' => $prixUnitaire,
                'prix_ligne' => $prixLigne,
            ];
        }

        return $lines;
    }

    private function insertMenuLines(int $idCmd, array $menus): array
    {
        $insert = $this->pdo->prepare(
            'INSERT INTO commande_menu (id_cmd, id_menu, quantite, taille, prix_unitaire, prix_ligne)
             VALUES (:id_cmd, :id_menu, :quantite, :taille, :prix_unitaire, :prix_ligne)'
        );
        $lines = [];

        foreach ($menus as $line) {
            $menu = $this->findMenuForOrder((int) $line['id']);
            $quantite = (int) $line['quantite'];
            $taille = (string) $line['taille'];
            $prixUnitaire = $this->priceForMenuSize((float) $menu['prix'], $taille);
            $prixLigne = round($prixUnitaire * $quantite, 2);

            $insert->execute([
                'id_cmd' => $idCmd,
                'id_menu' => $menu['id_menu'],
                'quantite' => $quantite,
                'taille' => $taille,
                'prix_unitaire' => number_format($prixUnitaire, 2, '.', ''),
                'prix_ligne' => number_format($prixLigne, 2, '.', ''),
            ]);

            $lines[] = [
                'id' => (int) $menu['id_menu'],
                'nom' => (string) $menu['nom'],
                'quantite' => $quantite,
                'taille' => $taille,
                'prix_unitaire' => $prixUnitaire,
                'prix_ligne' => $prixLigne,
            ];
        }

        return $lines;
    }

    private function collectIngredientNeeds(array $produits, array $menus): array
    {
        $needs = [];

        foreach ($produits as $line) {
            $this->addProductIngredientNeeds($needs, (int) $line['id'], (int) $line['quantite']);
        }

        foreach ($menus as $line) {
            $this->addMenuIngredientNeeds($needs, (int) $line['id'], (int) $line['quantite']);
        }

        return $needs;
    }

    private function addProductIngredientNeeds(array &$needs, int $idProduit, int $quantiteProduit): void
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                i.id_ingredient,
                i.nom,
                ip.quantite
             FROM ingredients_produits ip
             INNER JOIN ingredients i ON i.id_ingredient = ip.id_ingredient
             WHERE ip.id_produit = :id_produit'
        );
        $stmt->execute(['id_produit' => $idProduit]);

        foreach ($stmt->fetchAll() as $ingredient) {
            $this->addIngredientNeed(
                $needs,
                (int) $ingredient['id_ingredient'],
                (string) $ingredient['nom'],
                (int) $ingredient['quantite'] * $quantiteProduit,
            );
        }
    }

    private function addMenuIngredientNeeds(array &$needs, int $idMenu, int $quantiteMenu): void
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                i.id_ingredient,
                i.nom,
                ip.quantite AS quantite_ingredient,
                mp.quantite AS quantite_produit
             FROM menu_produit mp
             INNER JOIN ingredients_produits ip ON ip.id_produit = mp.id_produit
             INNER JOIN ingredients i ON i.id_ingredient = ip.id_ingredient
             WHERE mp.id_menu = :id_menu'
        );
        $stmt->execute(['id_menu' => $idMenu]);

        foreach ($stmt->fetchAll() as $ingredient) {
            $this->addIngredientNeed(
                $needs,
                (int) $ingredient['id_ingredient'],
                (string) $ingredient['nom'],
                (int) $ingredient['quantite_ingredient'] * (int) $ingredient['quantite_produit'] * $quantiteMenu,
            );
        }
    }

    private function addIngredientNeed(array &$needs, int $idIngredient, string $nom, int $quantite): void
    {
        if (!isset($needs[$idIngredient])) {
            $needs[$idIngredient] = [
                'id' => $idIngredient,
                'nom' => $nom,
                'quantite' => 0,
            ];
        }

        $needs[$idIngredient]['quantite'] += $quantite;
    }

    private function debitIngredients(array $needs): void
    {
        if ($needs === []) {
            return;
        }

        $select = $this->pdo->prepare(
            'SELECT nom, quantite
             FROM ingredients
             WHERE id_ingredient = :id_ingredient
             FOR UPDATE'
        );
        $update = $this->pdo->prepare(
            'UPDATE ingredients
             SET quantite = quantite - :quantite
             WHERE id_ingredient = :id_ingredient'
        );

        foreach ($needs as $need) {
            $select->execute(['id_ingredient' => $need['id']]);
            $ingredient = $select->fetch();

            if ($ingredient === false) {
                throw ValidationException::forField('ingredients', sprintf('ingredient %d does not exist', $need['id']));
            }

            $stock = (int) $ingredient['quantite'];
            $required = (int) $need['quantite'];

            if ($stock < $required) {
                throw ValidationException::forField(
                    'ingredients',
                    sprintf('stock insuffisant pour %s: %d requis, %d disponible', $need['nom'], $required, $stock),
                );
            }

            $update->execute([
                'id_ingredient' => $need['id'],
                'quantite' => $required,
            ]);
        }
    }

    private function findOneForApi(int $idCmd): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                c.id_cmd AS id,
                c.id_user,
                c.numero_ticket,
                c.date_cmd,
                c.total_ttc,
                s.id_statut,
                s.libelle AS statut,
                ca.id_canal,
                ca.libelle AS canal
             FROM commandes c
             INNER JOIN statuts_commandes s ON s.id_statut = c.id_statut
             INNER JOIN canaux ca ON ca.id_canal = c.id_canal
             WHERE c.id_cmd = :id_cmd'
        );
        $stmt->execute(['id_cmd' => $idCmd]);
        $commande = $stmt->fetch();

        if ($commande === false) {
            throw ValidationException::forField('commande', 'created command cannot be found');
        }

        return [
            'id' => (int) $commande['id'],
            'id_user' => $commande['id_user'] === null ? null : (int) $commande['id_user'],
            'numero_ticket' => (string) $commande['numero_ticket'],
            'date_cmd' => (string) $commande['date_cmd'],
            'total_ttc' => (float) $commande['total_ttc'],
            'statut' => [
                'id' => (int) $commande['id_statut'],
                'libelle' => (string) $commande['statut'],
            ],
            'canal' => [
                'id' => (int) $commande['id_canal'],
                'libelle' => (string) $commande['canal'],
            ],
            'produits' => $this->findProductLinesForApi($idCmd),
            'menus' => $this->findMenuLinesForApi($idCmd),
        ];
    }

    private function findProductLinesForApi(int $idCmd): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                p.id_produit AS id,
                p.nom,
                cp.quantite,
                cp.prix_unitaire,
                cp.prix_ligne
             FROM commande_produit cp
             INNER JOIN produits p ON p.id_produit = cp.id_produit
             WHERE cp.id_cmd = :id_cmd
             ORDER BY p.id_produit'
        );
        $stmt->execute(['id_cmd' => $idCmd]);

        return array_map([$this, 'formatLineForApi'], $stmt->fetchAll());
    }

    private function findMenuLinesForApi(int $idCmd): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                m.id_menu AS id,
                m.nom,
                cm.quantite,
                cm.taille,
                cm.prix_unitaire,
                cm.prix_ligne
             FROM commande_menu cm
             INNER JOIN menus m ON m.id_menu = cm.id_menu
             WHERE cm.id_cmd = :id_cmd
             ORDER BY m.id_menu'
        );
        $stmt->execute(['id_cmd' => $idCmd]);

        return array_map([$this, 'formatLineForApi'], $stmt->fetchAll());
    }

    private function formatLineForApi(array $row): array
    {
        $line = [
            'id' => (int) $row['id'],
            'nom' => (string) $row['nom'],
            'quantite' => (int) $row['quantite'],
            'prix_unitaire' => (float) $row['prix_unitaire'],
            'prix_ligne' => (float) $row['prix_ligne'],
        ];

        if (array_key_exists('taille', $row)) {
            $line['taille'] = (string) $row['taille'];
        }

        return $line;
    }

    private function findProductForOrder(int $idProduit): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_produit, nom, prix_unitaire, disponibilite
             FROM produits
             WHERE id_produit = :id_produit'
        );
        $stmt->execute(['id_produit' => $idProduit]);
        $produit = $stmt->fetch();

        if ($produit === false) {
            throw ValidationException::forField('produits', sprintf('product %d does not exist', $idProduit));
        }

        if ((int) $produit['disponibilite'] !== 1) {
            throw ValidationException::forField('produits', sprintf('product %d is unavailable', $idProduit));
        }

        return $produit;
    }

    private function findMenuForOrder(int $idMenu): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_menu, nom, prix, disponibilite
             FROM menus
             WHERE id_menu = :id_menu'
        );
        $stmt->execute(['id_menu' => $idMenu]);
        $menu = $stmt->fetch();

        if ($menu === false) {
            throw ValidationException::forField('menus', sprintf('menu %d does not exist', $idMenu));
        }

        if ((int) $menu['disponibilite'] !== 1) {
            throw ValidationException::forField('menus', sprintf('menu %d is unavailable', $idMenu));
        }

        return $menu;
    }

    private function priceForMenuSize(float $basePrice, string $taille): float
    {
        $price = match ($taille) {
            'S' => $basePrice - 1.00,
            'M' => $basePrice,
            'L' => $basePrice + 1.00,
            default => throw ValidationException::forField('taille', 'must be S, M or L'),
        };

        return max(0.01, round($price, 2));
    }

    private function findCanalByLibelle(string $libelle): array
    {
        $stmt = $this->pdo->prepare('SELECT id_canal, libelle FROM canaux WHERE libelle = :libelle');
        $stmt->execute(['libelle' => strtolower(trim($libelle))]);
        $canal = $stmt->fetch();

        if ($canal === false) {
            throw ValidationException::forField('canal', 'unknown channel');
        }

        return $canal;
    }

    private function findStatutByLibelle(string $libelle): array
    {
        $stmt = $this->pdo->prepare('SELECT id_statut, libelle FROM statuts_commandes WHERE libelle = :libelle');
        $stmt->execute(['libelle' => $libelle]);
        $statut = $stmt->fetch();

        if ($statut === false) {
            throw ValidationException::forField('statut', 'unknown status');
        }

        return $statut;
    }

    private function assertUserExists(int $idUser): void
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM utilisateurs WHERE id_user = :id_user');
        $stmt->execute(['id_user' => $idUser]);

        if ($stmt->fetchColumn() === false) {
            throw ValidationException::forField('id_user', 'unknown user');
        }
    }

    private function generateTicket(): string
    {
        do {
            $ticket = 'WAC' . date('ymdHis') . random_int(100, 999);
            $stmt = $this->pdo->prepare('SELECT 1 FROM commandes WHERE numero_ticket = :numero_ticket');
            $stmt->execute(['numero_ticket' => $ticket]);
        } while ($stmt->fetchColumn() !== false);

        return $ticket;
    }

    private function sumLines(array $lines): float
    {
        return array_reduce(
            $lines,
            static fn (float $total, array $line): float => $total + (float) $line['prix_ligne'],
            0.0,
        );
    }
}
