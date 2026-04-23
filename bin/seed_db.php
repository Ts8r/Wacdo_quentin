<?php

declare(strict_types=1);

/**
 * Seed MVP WACDO.
 *
 * Execution attendue dans le conteneur wacdo_php :
 *   php bin/seed_db.php
 */

$projectRoot = dirname(__DIR__);

require $projectRoot . '/autoload.php';

$databaseFactory = require $projectRoot . '/config/database.php';
$pdo = $databaseFactory();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$columnExists = static function (PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = :table_name
           AND COLUMN_NAME = :column_name'
    );
    $stmt->execute([
        'table_name' => $table,
        'column_name' => $column,
    ]);

    return (int) $stmt->fetchColumn() > 0;
};

foreach (['categories', 'produits', 'menus'] as $table) {
    if (!$columnExists($pdo, $table, 'image')) {
        $pdo->exec(sprintf('ALTER TABLE `%s` ADD COLUMN image VARCHAR(255) NULL', $table));
    }
}

$categoriesPath = $projectRoot . '/wacdo/categories.json';
$productsPath = $projectRoot . '/wacdo/produits.json';

if (!is_file($categoriesPath)) {
    fwrite(STDERR, "Fichier categories introuvable: {$categoriesPath}\n");
    exit(1);
}

if (!is_file($productsPath)) {
    fwrite(STDERR, "Fichier produits introuvable: {$productsPath}\n");
    exit(1);
}

$categories = json_decode((string) file_get_contents($categoriesPath), true);
$catalog = json_decode((string) file_get_contents($productsPath), true);

if (!is_array($categories) || !is_array($catalog)) {
    fwrite(STDERR, "JSON invalide dans les donnees de seed.\n");
    exit(1);
}

$report = [
    'roles' => 0,
    'statuts_commandes' => 0,
    'canaux' => 0,
    'categories' => 0,
    'produits' => 0,
    'menus' => 0,
    'produits_categories' => 0,
    'menu_produit' => 0,
    'ingredients' => 0,
    'ingredients_produits' => 0,
    'images_corrigees' => [],
    'images_introuvables' => [],
    'menus_sans_produit' => [],
];

$normalizeImage = static function (string $image) use ($projectRoot, &$report): string {
    $image = trim($image);

    if ($image === '') {
        return '';
    }

    $candidates = [$image];

    if (str_ends_with($image, '.png.png')) {
        $candidates[] = substr($image, 0, -4);
    }

    if (str_ends_with($image, '.jpg.png')) {
        $candidates[] = substr($image, 0, -8) . '.png';
    }

    foreach (array_unique($candidates) as $candidate) {
        if (is_file($projectRoot . '/wacdo' . $candidate)) {
            if ($candidate !== $image) {
                $report['images_corrigees'][] = "{$image} -> {$candidate}";
            }

            return $candidate;
        }
    }

    $report['images_introuvables'][] = $image;

    return $image;
};

$fetchId = static function (PDO $pdo, string $sql, array $params): int {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $value = $stmt->fetchColumn();

    return $value === false ? 0 : (int) $value;
};

$ingredientsSeed = [
    'Pain burger' => ['cout' => 0.35, 'stock' => 500],
    'Pain wrap' => ['cout' => 0.28, 'stock' => 350],
    'Steak boeuf' => ['cout' => 1.20, 'stock' => 300],
    'Poulet pane' => ['cout' => 1.10, 'stock' => 260],
    'Poisson pane' => ['cout' => 1.05, 'stock' => 180],
    'Bacon' => ['cout' => 0.45, 'stock' => 220],
    'Cheddar' => ['cout' => 0.30, 'stock' => 350],
    'Chevre' => ['cout' => 0.45, 'stock' => 180],
    'Mozzarella' => ['cout' => 0.45, 'stock' => 180],
    'Salade' => ['cout' => 0.20, 'stock' => 250],
    'Tomate' => ['cout' => 0.18, 'stock' => 250],
    'Oignon' => ['cout' => 0.12, 'stock' => 250],
    'Cornichon' => ['cout' => 0.10, 'stock' => 250],
    'Galette pommes de terre' => ['cout' => 0.70, 'stock' => 220],
    'Frites' => ['cout' => 0.60, 'stock' => 500],
    'Potatoes' => ['cout' => 0.70, 'stock' => 350],
    'Nuggets' => ['cout' => 0.35, 'stock' => 600],
    'Tortilla' => ['cout' => 0.25, 'stock' => 300],
    'Sauce burger' => ['cout' => 0.15, 'stock' => 300],
    'Sauce barbecue' => ['cout' => 0.12, 'stock' => 250],
    'Sauce moutarde' => ['cout' => 0.12, 'stock' => 250],
    'Sauce creamy deluxe' => ['cout' => 0.12, 'stock' => 250],
    'Sauce ketchup' => ['cout' => 0.10, 'stock' => 300],
    'Sauce chinoise' => ['cout' => 0.12, 'stock' => 250],
    'Sauce curry' => ['cout' => 0.12, 'stock' => 250],
    'Sauce pommes frites' => ['cout' => 0.12, 'stock' => 250],
    'Boisson cola' => ['cout' => 0.45, 'stock' => 400],
    'Boisson sans sucres' => ['cout' => 0.45, 'stock' => 250],
    'Eau bouteille' => ['cout' => 0.25, 'stock' => 300],
    'Boisson orange' => ['cout' => 0.45, 'stock' => 250],
    'Ice tea peche' => ['cout' => 0.45, 'stock' => 250],
    'Ice tea citron' => ['cout' => 0.45, 'stock' => 250],
    'Jus orange' => ['cout' => 0.55, 'stock' => 200],
    'Jus pomme' => ['cout' => 0.55, 'stock' => 200],
    'Brownie' => ['cout' => 0.75, 'stock' => 140],
    'Cheesecake' => ['cout' => 0.95, 'stock' => 120],
    'Cookie' => ['cout' => 0.65, 'stock' => 160],
    'Donut' => ['cout' => 0.70, 'stock' => 140],
    'Macaron' => ['cout' => 0.40, 'stock' => 260],
    'Glace' => ['cout' => 0.80, 'stock' => 150],
    'Muffin' => ['cout' => 0.85, 'stock' => 130],
    'Sundae' => ['cout' => 0.55, 'stock' => 140],
];

$recipeFor = static function (string $category, string $name): array {
    $lowerName = strtolower($name);

    if ($category === 'boissons') {
        return match (true) {
            str_contains($lowerName, 'sans sucres') => ['Boisson sans sucres' => 1],
            str_contains($lowerName, 'eau') => ['Eau bouteille' => 1],
            str_contains($lowerName, 'fanta') => ['Boisson orange' => 1],
            str_contains($lowerName, 'pêche') || str_contains($lowerName, 'peche') => ['Ice tea peche' => 1],
            str_contains($lowerName, 'citron') => ['Ice tea citron' => 1],
            str_contains($lowerName, 'orange') => ['Jus orange' => 1],
            str_contains($lowerName, 'pomme') => ['Jus pomme' => 1],
            default => ['Boisson cola' => 1],
        };
    }

    if ($category === 'frites') {
        return str_contains($lowerName, 'potatoes') ? ['Potatoes' => 1] : ['Frites' => 1];
    }

    if ($category === 'sauces') {
        return match (true) {
            str_contains($lowerName, 'barbecue') => ['Sauce barbecue' => 1],
            str_contains($lowerName, 'moutarde') => ['Sauce moutarde' => 1],
            str_contains($lowerName, 'deluxe') => ['Sauce creamy deluxe' => 1],
            str_contains($lowerName, 'ketchup') => ['Sauce ketchup' => 1],
            str_contains($lowerName, 'chinoise') => ['Sauce chinoise' => 1],
            str_contains($lowerName, 'curry') => ['Sauce curry' => 1],
            default => ['Sauce pommes frites' => 1],
        };
    }

    if ($category === 'desserts') {
        return match (true) {
            str_contains($lowerName, 'brownie') => ['Brownie' => 1],
            str_contains($lowerName, 'cheesecake') => ['Cheesecake' => 1],
            str_contains($lowerName, 'cookie') => ['Cookie' => 1],
            str_contains($lowerName, 'donut') => ['Donut' => 1],
            str_contains($lowerName, 'macaron') => ['Macaron' => 4],
            str_contains($lowerName, 'fleury') => ['Glace' => 1],
            str_contains($lowerName, 'muffin') => ['Muffin' => 1],
            default => ['Sundae' => 1],
        };
    }

    if ($category === 'salades') {
        $recipe = ['Salade' => 1, 'Tomate' => 1];
        if (str_contains($lowerName, 'cesar') || str_contains($lowerName, 'caesar')) {
            $recipe['Poulet pane'] = 1;
            $recipe['Sauce creamy deluxe'] = 1;
        }
        if (str_contains($lowerName, 'mozza')) {
            $recipe['Mozzarella'] = 1;
        }

        return $recipe;
    }

    if ($category === 'wraps') {
        $recipe = ['Tortilla' => 1, 'Salade' => 1, 'Tomate' => 1];
        $recipe[str_contains($lowerName, 'chevre') ? 'Chevre' : 'Poulet pane'] = 1;
        if (str_contains($lowerName, 'bacon')) {
            $recipe['Bacon'] = 1;
        }
        $recipe['Sauce creamy deluxe'] = 1;

        return $recipe;
    }

    if ($category === 'encas') {
        if (str_contains($lowerName, 'nuggets x20')) {
            return ['Nuggets' => 20];
        }
        if (str_contains($lowerName, 'nuggets')) {
            return ['Nuggets' => 4];
        }
        if (str_contains($lowerName, 'croc')) {
            return ['Pain burger' => 1, 'Cheddar' => 1, 'Sauce burger' => 1];
        }

        return ['Pain burger' => 1, 'Steak boeuf' => 1, 'Cheddar' => 1, 'Sauce burger' => 1];
    }

    $recipe = ['Pain burger' => 1, 'Sauce burger' => 1];

    if (str_contains($lowerName, 'chicken') || str_contains($lowerName, 'cbo') || str_contains($lowerName, 'crispy')) {
        $recipe['Poulet pane'] = 1;
    } elseif (str_contains($lowerName, 'fish')) {
        $recipe['Poisson pane'] = 1;
    } else {
        $recipe['Steak boeuf'] = str_contains($lowerName, '2 viandes') ? 2 : 1;
    }

    if (str_contains($lowerName, 'bacon')) {
        $recipe['Bacon'] = 1;
    }

    if (str_contains($lowerName, 'cheese') || str_contains($lowerName, 'royal') || str_contains($lowerName, '280')) {
        $recipe['Cheddar'] = 1;
    }

    if (str_contains($lowerName, 'cbo')) {
        $recipe['Bacon'] = 1;
        $recipe['Cheddar'] = 1;
    }

    $recipe['Salade'] = 1;
    $recipe['Tomate'] = 1;
    $recipe['Oignon'] = 1;

    if (str_contains($lowerName, 'deluxe') || str_contains($lowerName, 'big mac')) {
        $recipe['Cornichon'] = 1;
    }

    if (str_contains($lowerName, 'bbq')) {
        $recipe['Sauce barbecue'] = 1;
    }

    return $recipe;
};

$upsertRole = $pdo->prepare(
    'INSERT INTO roles (code_role, libelle)
     VALUES (:code_role, :libelle)
     ON DUPLICATE KEY UPDATE libelle = VALUES(libelle)'
);

$upsertStatut = $pdo->prepare(
    'INSERT INTO statuts_commandes (libelle)
     VALUES (:libelle)
     ON DUPLICATE KEY UPDATE libelle = VALUES(libelle)'
);

$upsertCanal = $pdo->prepare(
    'INSERT INTO canaux (libelle)
     VALUES (:libelle)
     ON DUPLICATE KEY UPDATE libelle = VALUES(libelle)'
);

$upsertCategorie = $pdo->prepare(
    'INSERT INTO categories (id_cat, type, image, description)
     VALUES (:id_cat, :type, :image, :description)
     ON DUPLICATE KEY UPDATE
       type = VALUES(type),
       image = VALUES(image),
       description = VALUES(description)'
);

$upsertProduit = $pdo->prepare(
    'INSERT INTO produits (id_produit, nom, description, prix_unitaire, image, disponibilite, quantite)
     VALUES (:id_produit, :nom, :description, :prix_unitaire, :image, :disponibilite, :quantite)
     ON DUPLICATE KEY UPDATE
       nom = VALUES(nom),
       description = VALUES(description),
       prix_unitaire = VALUES(prix_unitaire),
       image = VALUES(image),
       disponibilite = VALUES(disponibilite),
       quantite = VALUES(quantite)'
);

$upsertMenu = $pdo->prepare(
    'INSERT INTO menus (id_menu, nom, prix, image, disponibilite)
     VALUES (:id_menu, :nom, :prix, :image, :disponibilite)
     ON DUPLICATE KEY UPDATE
       nom = VALUES(nom),
       prix = VALUES(prix),
       image = VALUES(image),
       disponibilite = VALUES(disponibilite)'
);

$upsertProduitCategorie = $pdo->prepare(
    'INSERT INTO produits_categories (id_produit, id_cat)
     VALUES (:id_produit, :id_cat)
     ON DUPLICATE KEY UPDATE id_cat = VALUES(id_cat)'
);

$upsertMenuProduit = $pdo->prepare(
    'INSERT INTO menu_produit (id_menu, id_produit, quantite)
     VALUES (:id_menu, :id_produit, :quantite)
     ON DUPLICATE KEY UPDATE quantite = VALUES(quantite)'
);

$upsertIngredient = $pdo->prepare(
    'INSERT INTO ingredients (nom, cout_unitaire, quantite)
     VALUES (:nom, :cout_unitaire, :quantite)
     ON DUPLICATE KEY UPDATE
       cout_unitaire = VALUES(cout_unitaire),
       quantite = VALUES(quantite)'
);

$upsertIngredientProduit = $pdo->prepare(
    'INSERT INTO ingredients_produits (id_ingredient, id_produit, quantite)
     VALUES (:id_ingredient, :id_produit, :quantite)
     ON DUPLICATE KEY UPDATE quantite = VALUES(quantite)'
);

$pdo->beginTransaction();

try {
    foreach ([
        'CLIENT' => 'Client',
        'EMPLOYE' => 'Employe',
        'MANAGER' => 'Manager',
        'ADMIN' => 'Administrateur',
    ] as $code => $label) {
        $upsertRole->execute([
            'code_role' => $code,
            'libelle' => $label,
        ]);
        $report['roles']++;
    }

    foreach (['en_attente', 'en_preparation', 'prete', 'servie', 'annulee'] as $statut) {
        $upsertStatut->execute(['libelle' => $statut]);
        $report['statuts_commandes']++;
    }

    foreach (['borne', 'caisse', 'appli', 'drive'] as $canal) {
        $upsertCanal->execute(['libelle' => $canal]);
        $report['canaux']++;
    }

    $categoryIdsByKey = [];
    $productCategoriesById = [];

    foreach ($categories as $category) {
        $id = (int) ($category['id'] ?? 0);
        $type = trim((string) ($category['title'] ?? ''));

        if ($id <= 0 || $type === '') {
            continue;
        }

        $upsertCategorie->execute([
            'id_cat' => $id,
            'type' => $type,
            'image' => $normalizeImage((string) ($category['image'] ?? '')),
            'description' => ucfirst($type),
        ]);

        $categoryIdsByKey[$type] = $id;
        $report['categories']++;
    }

    $productIdsByName = [];

    foreach ($catalog as $categoryKey => $items) {
        if ($categoryKey === 'menus' || !is_array($items)) {
            continue;
        }

        $idCat = $categoryIdsByKey[$categoryKey] ?? 0;

        foreach ($items as $item) {
            $idProduit = (int) ($item['id'] ?? 0);
            $nom = trim((string) ($item['nom'] ?? ''));

            if ($idProduit <= 0 || $nom === '') {
                continue;
            }

            $upsertProduit->execute([
                'id_produit' => $idProduit,
                'nom' => $nom,
                'description' => ucfirst((string) $categoryKey),
                'prix_unitaire' => number_format((float) ($item['prix'] ?? 0), 2, '.', ''),
                'image' => $normalizeImage((string) ($item['image'] ?? '')),
                'disponibilite' => 1,
                'quantite' => 100,
            ]);

            $productIdsByName[strtolower($nom)] = $idProduit;
            $report['produits']++;

            if ($idCat > 0) {
                $upsertProduitCategorie->execute([
                    'id_produit' => $idProduit,
                    'id_cat' => $idCat,
                ]);
                $report['produits_categories']++;
            }

            $productCategoriesById[$idProduit] = (string) $categoryKey;
        }
    }

    $ingredientIdsByName = [];

    foreach ($ingredientsSeed as $name => $values) {
        $upsertIngredient->execute([
            'nom' => $name,
            'cout_unitaire' => number_format((float) $values['cout'], 2, '.', ''),
            'quantite' => (int) $values['stock'],
        ]);
        $ingredientIdsByName[$name] = $fetchId($pdo, 'SELECT id_ingredient FROM ingredients WHERE nom = :nom', ['nom' => $name]);
        $report['ingredients']++;
    }

    foreach ($productIdsByName as $productName => $idProduit) {
        $category = $productCategoriesById[$idProduit] ?? '';
        $recipe = $recipeFor($category, $productName);

        foreach ($recipe as $ingredientName => $quantity) {
            $idIngredient = $ingredientIdsByName[$ingredientName] ?? 0;

            if ($idIngredient <= 0) {
                continue;
            }

            $upsertIngredientProduit->execute([
                'id_ingredient' => $idIngredient,
                'id_produit' => $idProduit,
                'quantite' => (int) $quantity,
            ]);
            $report['ingredients_produits']++;
        }
    }

    foreach (($catalog['menus'] ?? []) as $menu) {
        $idMenu = (int) ($menu['id'] ?? 0);
        $nom = trim((string) ($menu['nom'] ?? ''));

        if ($idMenu <= 0 || $nom === '') {
            continue;
        }

        $upsertMenu->execute([
            'id_menu' => $idMenu,
            'nom' => $nom,
            'prix' => number_format((float) ($menu['prix'] ?? 0), 2, '.', ''),
            'image' => $normalizeImage((string) ($menu['image'] ?? '')),
            'disponibilite' => 1,
        ]);
        $report['menus']++;

        $productName = trim(preg_replace('/^Menu\s+/i', '', $nom) ?? $nom);
        $mainProductId = $productIdsByName[strtolower($productName)]
            ?? $fetchId($pdo, 'SELECT id_produit FROM produits WHERE LOWER(nom) = LOWER(:nom)', ['nom' => $productName]);

        if ($mainProductId > 0) {
            foreach ([
                $mainProductId,
                $productIdsByName[strtolower('Moyenne Frite')] ?? 0,
                $productIdsByName[strtolower('Coca Cola')] ?? 0,
            ] as $idProduit) {
                if ($idProduit <= 0) {
                    continue;
                }

                $upsertMenuProduit->execute([
                    'id_menu' => $idMenu,
                    'id_produit' => $idProduit,
                    'quantite' => 1,
                ]);
                $report['menu_produit']++;
            }

            continue;
        }

        foreach ([
            $productIdsByName[strtolower('Moyenne Frite')] ?? 0,
            $productIdsByName[strtolower('Coca Cola')] ?? 0,
        ] as $idProduit) {
            if ($idProduit <= 0) {
                continue;
            }

            $upsertMenuProduit->execute([
                'id_menu' => $idMenu,
                'id_produit' => $idProduit,
                'quantite' => 1,
            ]);
            $report['menu_produit']++;
        }

        $report['menus_sans_produit'][] = $nom;
    }

    $pdo->commit();
} catch (Throwable $exception) {
    $pdo->rollBack();
    fwrite(STDERR, "Seed annule: {$exception->getMessage()}\n");
    exit(1);
}

$report['images_corrigees'] = array_values(array_unique($report['images_corrigees']));
$report['images_introuvables'] = array_values(array_unique($report['images_introuvables']));
$report['menus_sans_produit'] = array_values(array_unique($report['menus_sans_produit']));

fwrite(STDOUT, "Seed WACDO termine.\n");
foreach ([
    'roles',
    'statuts_commandes',
    'canaux',
    'categories',
    'produits',
    'menus',
    'produits_categories',
    'menu_produit',
    'ingredients',
    'ingredients_produits',
] as $key) {
    fwrite(STDOUT, sprintf("- %s: %d\n", $key, $report[$key]));
}

if ($report['images_corrigees'] !== []) {
    fwrite(STDOUT, "\nImages corrigees:\n");
    foreach ($report['images_corrigees'] as $line) {
        fwrite(STDOUT, "- {$line}\n");
    }
}

if ($report['images_introuvables'] !== []) {
    fwrite(STDOUT, "\nImages introuvables:\n");
    foreach ($report['images_introuvables'] as $line) {
        fwrite(STDOUT, "- {$line}\n");
    }
}

if ($report['menus_sans_produit'] !== []) {
    fwrite(STDOUT, "\nMenus sans produit correspondant dans menu_produit:\n");
    foreach ($report['menus_sans_produit'] as $line) {
        fwrite(STDOUT, "- {$line}\n");
    }
}
