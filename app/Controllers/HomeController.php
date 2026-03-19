<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;

final class HomeController extends Controller
{
    public function __construct(private PDO $pdo)
    {
    }

    public function index(): void
    {
        $dbName = (string) $this->pdo->query('SELECT DATABASE()')->fetchColumn();

        $this->render('home', [
            'title' => 'WACDO Back',
            'message' => sprintf('Structure MVC explicite active. Connexion PDO OK sur la base %s.', $dbName),
        ]);
    }
}
