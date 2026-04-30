<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\NotFoundException;
use App\Models\Produit;
use PDO;

final class DbProduitRepository implements ProductRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id_produit, nom, description, prix_unitaire, image, image_mime, disponibilite, quantite
             FROM produits
             ORDER BY id_produit'
        );

        return array_map(
            fn (array $row): Produit => $this->hydrate($row),
            $stmt->fetchAll(),
        );
    }

    public function findById(int $id): Produit
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_produit, nom, description, prix_unitaire, image, image_mime, disponibilite, quantite
             FROM produits
             WHERE id_produit = :id_produit'
        );
        $stmt->execute(['id_produit' => $id]);
        $row = $stmt->fetch();

        if ($row === false) {
            throw NotFoundException::forId('produit', $id);
        }

        return $this->hydrate($row);
    }

    public function save(Produit $produit): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO produits (id_produit, nom, description, prix_unitaire, image, image_mime, disponibilite, quantite)
             VALUES (:id_produit, :nom, :description, :prix_unitaire, :image, NULL, :disponibilite, :quantite)
             ON DUPLICATE KEY UPDATE
               nom = VALUES(nom),
               description = VALUES(description),
               prix_unitaire = VALUES(prix_unitaire),
               image = VALUES(image),
               disponibilite = VALUES(disponibilite),
               quantite = VALUES(quantite)'
        );

        $stmt->execute([
            'id_produit' => $produit->idProduit === 0 ? null : $produit->idProduit,
            'nom' => $produit->nom,
            'description' => $produit->description,
            'prix_unitaire' => $produit->prixUnitaire,
            'image' => $produit->image,
            'disponibilite' => $produit->disponibilite ? 1 : 0,
            'quantite' => $produit->quantite,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM produits WHERE id_produit = :id_produit');
        $stmt->execute(['id_produit' => $id]);

        return $stmt->rowCount() > 0;
    }

    public function findByCategorie(int $idCat): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.id_produit, p.nom, p.description, p.prix_unitaire, p.image, p.image_mime, p.disponibilite, p.quantite
             FROM produits p
             INNER JOIN produits_categories pc ON pc.id_produit = p.id_produit
             WHERE pc.id_cat = :id_cat
             ORDER BY p.id_produit'
        );
        $stmt->execute(['id_cat' => $idCat]);

        return array_map(
            fn (array $row): Produit => $this->hydrate($row),
            $stmt->fetchAll(),
        );
    }

    public function findAllForApi(): array
    {
        $stmt = $this->pdo->query(
            'SELECT
                p.id_produit AS id,
                p.nom,
                p.description,
                p.prix_unitaire,
                p.image,
                p.image_mime,
                p.disponibilite,
                p.quantite,
                c.id_cat AS id_categorie,
                c.type AS categorie
             FROM produits p
             LEFT JOIN produits_categories pc ON pc.id_produit = p.id_produit
             LEFT JOIN categories c ON c.id_cat = pc.id_cat
             ORDER BY p.id_produit'
        );

        return array_map([$this, 'formatProductForApi'], $stmt->fetchAll());
    }

    public function findOneForApi(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                p.id_produit AS id,
                p.nom,
                p.description,
                p.prix_unitaire,
                p.image,
                p.image_mime,
                p.disponibilite,
                p.quantite,
                c.id_cat AS id_categorie,
                c.type AS categorie
             FROM produits p
             LEFT JOIN produits_categories pc ON pc.id_produit = p.id_produit
             LEFT JOIN categories c ON c.id_cat = pc.id_cat
             WHERE p.id_produit = :id_produit'
        );
        $stmt->execute(['id_produit' => $id]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        $row = $this->formatProductForApi($row);
        $row['ingredients'] = $this->findIngredientsForProduct($id);

        return $row;
    }

    public function updateForApi(int $idProduit, array $fields): ?array
    {
        if ($this->findOneForApi($idProduit) === null) {
            return null;
        }

        $allowedFields = [
            'description' => 'description',
            'prix_unitaire' => 'prix_unitaire',
            'disponibilite' => 'disponibilite',
            'quantite' => 'quantite',
        ];

        $sets = [];
        $params = ['id_produit' => $idProduit];

        foreach ($fields as $field => $value) {
            if (!array_key_exists($field, $allowedFields)) {
                continue;
            }

            $sets[] = $allowedFields[$field] . ' = :' . $field;
            $params[$field] = $value;
        }

        if ($sets === []) {
            return $this->findOneForApi($idProduit);
        }

        $stmt = $this->pdo->prepare(
            'UPDATE produits
             SET ' . implode(', ', $sets) . '
             WHERE id_produit = :id_produit'
        );
        $stmt->execute($params);

        return $this->findOneForApi($idProduit);
    }

    private function findIngredientsForProduct(int $idProduit): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                i.id_ingredient AS id,
                i.nom,
                ip.quantite
             FROM ingredients_produits ip
             INNER JOIN ingredients i ON i.id_ingredient = ip.id_ingredient
             WHERE ip.id_produit = :id_produit
             ORDER BY i.nom'
        );
        $stmt->execute(['id_produit' => $idProduit]);

        return array_map(
            static fn (array $row): array => [
                'id' => (int) $row['id'],
                'nom' => (string) $row['nom'],
                'quantite' => (int) $row['quantite'],
            ],
            $stmt->fetchAll(),
        );
    }

    private function formatProductForApi(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'nom' => (string) $row['nom'],
            'description' => $row['description'] === null ? null : (string) $row['description'],
            'prix_unitaire' => (string) $row['prix_unitaire'],
            'image' => ImageData::dataUri($row['image'] ?? null, $row['image_mime'] ?? null),
            'disponibilite' => (int) $row['disponibilite'],
            'quantite' => (int) $row['quantite'],
            'id_categorie' => $row['id_categorie'] === null ? null : (int) $row['id_categorie'],
            'categorie' => $row['categorie'] === null ? null : (string) $row['categorie'],
        ];
    }

    private function hydrate(array $row): Produit
    {
        return new Produit(
            idProduit: (int) $row['id_produit'],
            nom: (string) $row['nom'],
            description: (string) ($row['description'] ?? ''),
            prixUnitaire: (float) $row['prix_unitaire'],
            image: ImageData::dataUri($row['image'] ?? null, $row['image_mime'] ?? null) ?? '',
            disponibilite: (bool) $row['disponibilite'],
            quantite: (int) $row['quantite'],
        );
    }
}
