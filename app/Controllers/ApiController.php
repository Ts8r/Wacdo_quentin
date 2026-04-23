<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\JsonResponse;
use App\Repositories\DbCategorieRepository;
use App\Repositories\DbMenuRepository;
use App\Repositories\DbProduitRepository;
use PDO;
use Throwable;

final class ApiController
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly DbCategorieRepository $categories,
        private readonly DbProduitRepository $produits,
        private readonly DbMenuRepository $menus,
    ) {
    }

    public function health(): void
    {
        try {
            JsonResponse::send([
                'status' => 'ok',
                'database' => (string) $this->pdo->query('SELECT DATABASE()')->fetchColumn(),
            ]);
        } catch (Throwable $exception) {
            JsonResponse::send([
                'status' => 'failed',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    public function categories(): void
    {
        JsonResponse::send([
            'data' => $this->categories->findAllForApi(),
        ]);
    }

    public function produits(): void
    {
        JsonResponse::send([
            'data' => $this->produits->findAllForApi(),
        ]);
    }

    public function produit(string $id): void
    {
        $produit = $this->produits->findOneForApi((int) $id);

        if ($produit === null) {
            JsonResponse::send([
                'error' => 'not_found',
                'message' => 'Produit introuvable.',
            ], 404);
            return;
        }

        JsonResponse::send([
            'data' => $produit,
        ]);
    }

    public function menus(): void
    {
        JsonResponse::send([
            'data' => $this->menus->findAllForApi(),
        ]);
    }

    public function catalogue(): void
    {
        JsonResponse::send([
            'data' => [
                'categories' => $this->categories->findAllForApi(),
                'produits' => $this->produits->findAllForApi(),
                'menus' => $this->menus->findAllForApi(),
            ],
        ]);
    }
}
