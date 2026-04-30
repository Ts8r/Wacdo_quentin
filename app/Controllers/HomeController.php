<?php

declare(strict_types=1);

namespace App\Controllers;

final class HomeController extends Controller
{
    public function __construct()
    {
    }

    public function backOffice(): void
    {
        $this->render('back_office');
    }
}
