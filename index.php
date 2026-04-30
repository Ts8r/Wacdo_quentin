<?php
declare(strict_types=1);

require __DIR__ . '/autoload.php';

use App\Controllers\ApiController;
use App\Controllers\AuthController;
use App\Controllers\CommandeController;
use App\Controllers\HomeController;
use App\Controllers\IngredientController;
use App\Controllers\UtilisateurController;
use App\Http\Cors;
use App\Http\Router;
use App\Repositories\DbCategorieRepository;
use App\Repositories\DbCommandeRepository;
use App\Repositories\DbIngredientRepository;
use App\Repositories\DbMenuRepository;
use App\Repositories\DbProduitRepository;
use App\Repositories\DbUtilisateurRepository;
use App\Security\SessionAuthGuard;

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';

if (Cors::handlePreflight($method, $uri)) {
    return;
}

$databaseFactory = require __DIR__ . '/config/database.php';
$pdo = $databaseFactory();

$router = new Router($method, $uri);
$utilisateurRepository = new DbUtilisateurRepository($pdo);
$authGuard = new SessionAuthGuard($utilisateurRepository);
$apiController = new ApiController(
    $pdo,
    new DbCategorieRepository($pdo),
    new DbProduitRepository($pdo),
    new DbMenuRepository($pdo),
    $authGuard,
);
$utilisateurController = new UtilisateurController($utilisateurRepository, $authGuard);
$commandeController = new CommandeController(new DbCommandeRepository($pdo), $authGuard);
$ingredientController = new IngredientController(new DbIngredientRepository($pdo), $authGuard);
$authController = new AuthController($utilisateurRepository);

$router->get('/api/health', [$apiController, 'health']);
$router->get('/api/categories', [$apiController, 'categories']);
$router->get('/api/produits', [$apiController, 'produits']);
$router->get('/api/produits/{id}', [$apiController, 'produit']);
$router->patch('/api/produits/{id}', [$apiController, 'updateProduit']);
$router->get('/api/menus', [$apiController, 'menus']);
$router->patch('/api/menus/{id}', [$apiController, 'updateMenu']);
$router->get('/api/catalogue', [$apiController, 'catalogue']);
$router->post('/api/auth/login', [$authController, 'login']);
$router->post('/api/auth/logout', [$authController, 'logout']);
$router->get('/api/auth/me', [$authController, 'me']);
$router->get('/api/utilisateurs', [$utilisateurController, 'index']);
$router->post('/api/utilisateurs', [$utilisateurController, 'create']);
$router->post('/api/commandes', [$commandeController, 'create']);
$router->get('/api/commandes', [$commandeController, 'index']);
$router->get('/api/commandes/{id}', [$commandeController, 'show']);
$router->patch('/api/commandes/{id}/statut', [$commandeController, 'updateStatus']);
$router->get('/api/ingredients', [$ingredientController, 'index']);
$router->patch('/api/ingredients/{id}', [$ingredientController, 'updateQuantity']);
$homeController = new HomeController();
$router->get('/', [$homeController, 'backOffice']);

$router->dispatch();
