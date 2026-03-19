<?php
declare(strict_types=1);

require __DIR__ . '/autoload.php';

use App\Controllers\HomeController;

$databaseFactory = require __DIR__ . '/config/database.php';
$pdo = $databaseFactory();

$controller = new HomeController($pdo);
$controller->index();
