<?php

declare(strict_types=1);

namespace App\Controllers;

abstract class Controller
{
    protected function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        require __DIR__ . '/../Views/' . $view . '.php';
    }
}
