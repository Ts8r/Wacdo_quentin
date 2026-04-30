<?php
declare(strict_types=1);

require __DIR__ . '/autoload.php';

use App\Controllers\ApiController;
use App\Controllers\AuthController;
use App\Controllers\CommandeController;
use App\Controllers\HomeController;
use App\Controllers\IngredientController;
use App\Controllers\UtilisateurController;
use App\Http\Router;
use App\Repositories\DbCategorieRepository;
use App\Repositories\DbCommandeRepository;
use App\Repositories\DbIngredientRepository;
use App\Repositories\DbMenuRepository;
use App\Repositories\DbProduitRepository;
use App\Repositories\DbUtilisateurRepository;
use App\Security\SessionAuthGuard;

$databaseFactory = require __DIR__ . '/config/database.php';
$pdo = $databaseFactory();

$router = new Router($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
$apiController = new ApiController(
    $pdo,
    new DbCategorieRepository($pdo),
    new DbProduitRepository($pdo),
    new DbMenuRepository($pdo),
);
$utilisateurRepository = new DbUtilisateurRepository($pdo);
$authGuard = new SessionAuthGuard($utilisateurRepository);
$utilisateurController = new UtilisateurController($utilisateurRepository);
$commandeController = new CommandeController(new DbCommandeRepository($pdo), $authGuard);
$ingredientController = new IngredientController(new DbIngredientRepository($pdo), $authGuard);
$authController = new AuthController($utilisateurRepository);

$router->get('/api/health', [$apiController, 'health']);
$router->get('/api/categories', [$apiController, 'categories']);
$router->get('/api/produits', [$apiController, 'produits']);
$router->get('/api/produits/{id}', [$apiController, 'produit']);
$router->get('/api/menus', [$apiController, 'menus']);
$router->get('/api/catalogue', [$apiController, 'catalogue']);
$router->post('/api/auth/login', [$authController, 'login']);
$router->post('/api/auth/logout', [$authController, 'logout']);
$router->get('/api/auth/me', [$authController, 'me']);
$router->post('/api/utilisateurs', [$utilisateurController, 'create']);
$router->post('/api/commandes', [$commandeController, 'create']);
$router->get('/api/commandes', [$commandeController, 'index']);
$router->get('/api/commandes/{id}', [$commandeController, 'show']);
$router->patch('/api/commandes/{id}/statut', [$commandeController, 'updateStatus']);
$router->get('/api/ingredients', [$ingredientController, 'index']);
$router->patch('/api/ingredients/{id}', [$ingredientController, 'updateQuantity']);
$router->get('/', [new HomeController($pdo), 'index']);

$router->dispatch();
