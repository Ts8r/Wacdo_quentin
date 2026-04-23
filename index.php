<?php
declare(strict_types=1);

require __DIR__ . '/autoload.php';

use App\Controllers\ApiController;
use App\Controllers\HomeController;
use App\Http\Router;
use App\Repositories\DbCategorieRepository;
use App\Repositories\DbMenuRepository;
use App\Repositories\DbProduitRepository;

$databaseFactory = require __DIR__ . '/config/database.php';
$pdo = $databaseFactory();

$router = new Router($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
$apiController = new ApiController(
    $pdo,
    new DbCategorieRepository($pdo),
    new DbProduitRepository($pdo),
    new DbMenuRepository($pdo),
);

$router->get('/api/health', [$apiController, 'health']);
$router->get('/api/categories', [$apiController, 'categories']);
$router->get('/api/produits', [$apiController, 'produits']);
$router->get('/api/produits/{id}', [$apiController, 'produit']);
$router->get('/api/menus', [$apiController, 'menus']);
$router->get('/api/catalogue', [$apiController, 'catalogue']);
$router->get('/', [new HomeController($pdo), 'index']);

$router->dispatch();
