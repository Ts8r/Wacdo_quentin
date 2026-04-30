<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Http\JsonRequest;
use App\Http\JsonResponse;
use App\Repositories\DbCategorieRepository;
use App\Repositories\DbMenuRepository;
use App\Repositories\DbProduitRepository;
use App\Security\SessionAuthGuard;
use PDO;
use Throwable;

final class ApiController
{
    private const BACK_OFFICE_ROLES = ['EMPLOYE', 'MANAGER', 'ADMIN'];

    public function __construct(
        private readonly PDO $pdo,
        private readonly DbCategorieRepository $categories,
        private readonly DbProduitRepository $produits,
        private readonly DbMenuRepository $menus,
        private readonly ?SessionAuthGuard $authGuard = null,
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

    public function updateProduit(string $id): void
    {
        if (!$this->canAccessBackOffice()) {
            return;
        }

        $idProduit = $this->positiveId($id);

        if ($idProduit === null) {
            return;
        }

        try {
            $fields = $this->productUpdateFields(JsonRequest::body());
            $produit = $this->produits->updateForApi($idProduit, $fields);

            if ($produit === null) {
                JsonResponse::send([
                    'error' => 'not_found',
                    'message' => 'Produit introuvable.',
                ], 404);
                return;
            }

            JsonResponse::send(['data' => $produit]);
        } catch (ValidationException $exception) {
            JsonResponse::send([
                'error' => 'validation_failed',
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            JsonResponse::send([
                'error' => 'server_error',
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function updateMenu(string $id): void
    {
        if (!$this->canAccessBackOffice()) {
            return;
        }

        $idMenu = $this->positiveId($id);

        if ($idMenu === null) {
            return;
        }

        try {
            $fields = $this->menuUpdateFields(JsonRequest::body());
            $menu = $this->menus->updateForApi($idMenu, $fields);

            if ($menu === null) {
                JsonResponse::send([
                    'error' => 'not_found',
                    'message' => 'Menu introuvable.',
                ], 404);
                return;
            }

            JsonResponse::send(['data' => $menu]);
        } catch (ValidationException $exception) {
            JsonResponse::send([
                'error' => 'validation_failed',
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            JsonResponse::send([
                'error' => 'server_error',
                'message' => $exception->getMessage(),
            ], 500);
        }
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

    private function productUpdateFields(array $data): array
    {
        $fields = [];

        if (array_key_exists('prix', $data)) {
            $data['prix_unitaire'] = $data['prix'];
        }

        if (array_key_exists('prix_unitaire', $data)) {
            $fields['prix_unitaire'] = $this->nonNegativeDecimal($data['prix_unitaire'], 'prix_unitaire');
        }

        if (array_key_exists('disponibilite', $data)) {
            $fields['disponibilite'] = $this->booleanAsInteger($data['disponibilite'], 'disponibilite');
        }

        if (array_key_exists('quantite', $data)) {
            $fields['quantite'] = $this->nonNegativeInteger($data['quantite'], 'quantite');
        }

        if (array_key_exists('description', $data)) {
            $fields['description'] = $this->nullableString($data['description'], 'description');
        }

        if ($fields === []) {
            throw ValidationException::forRule('at least one editable product field is required');
        }

        return $fields;
    }

    private function menuUpdateFields(array $data): array
    {
        $fields = [];
        $sizePrices = [];

        if (array_key_exists('prix', $data)) {
            $fields['prix'] = $this->nonNegativeDecimal($data['prix'], 'prix');
        }

        foreach (['prix_s' => 'S', 'prix_m' => 'M', 'prix_l' => 'L'] as $field => $size) {
            if (array_key_exists($field, $data)) {
                $sizePrices[$size] = $this->nonNegativeDecimal($data[$field], $field);
            }
        }

        if ($sizePrices !== []) {
            $fields['prix'] = $this->baseMenuPriceFromSizePrices($sizePrices);
        }

        if (array_key_exists('disponibilite', $data)) {
            $fields['disponibilite'] = $this->booleanAsInteger($data['disponibilite'], 'disponibilite');
        }

        if ($fields === []) {
            throw ValidationException::forRule('at least one editable menu field is required');
        }

        return $fields;
    }

    private function baseMenuPriceFromSizePrices(array $prices): float
    {
        $basePrice = match (true) {
            array_key_exists('M', $prices) => $prices['M'],
            array_key_exists('S', $prices) => $prices['S'] + 1.00,
            array_key_exists('L', $prices) => $prices['L'] - 1.00,
        };

        $expectedPrices = [
            'S' => max(0.01, round($basePrice - 1.00, 2)),
            'M' => round($basePrice, 2),
            'L' => round($basePrice + 1.00, 2),
        ];

        foreach ($prices as $size => $price) {
            if (abs($price - $expectedPrices[$size]) > 0.001) {
                throw ValidationException::forField(
                    'prix_' . strtolower($size),
                    'menu sizes must keep a 1 euro difference',
                );
            }
        }

        return round($basePrice, 2);
    }

    private function positiveId(string $id): ?int
    {
        if (filter_var($id, FILTER_VALIDATE_INT) === false || (int) $id <= 0) {
            JsonResponse::send([
                'error' => 'validation_failed',
                'message' => 'Validation failed for "id": must be a positive integer.',
            ], 422);
            return null;
        }

        return (int) $id;
    }

    private function nonNegativeDecimal(mixed $value, string $field): float
    {
        if (!is_int($value) && !is_float($value) && !is_string($value)) {
            throw ValidationException::forField($field, 'must be a number');
        }

        if (!is_numeric($value)) {
            throw ValidationException::forField($field, 'must be a number');
        }

        $number = (float) $value;

        if ($number < 0) {
            throw ValidationException::forField($field, 'must be greater than or equal to zero');
        }

        return $number;
    }

    private function nonNegativeInteger(mixed $value, string $field): int
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw ValidationException::forField($field, 'must be an integer');
        }

        $integer = (int) $value;

        if ($integer < 0) {
            throw ValidationException::forField($field, 'must be greater than or equal to zero');
        }

        return $integer;
    }

    private function booleanAsInteger(mixed $value, string $field): int
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if ($value === 0 || $value === 1 || $value === '0' || $value === '1') {
            return (int) $value;
        }

        throw ValidationException::forField($field, 'must be a boolean');
    }

    private function nullableString(mixed $value, string $field, ?int $maxLength = null): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw ValidationException::forField($field, 'must be a string');
        }

        $value = trim($value);

        if ($maxLength !== null && strlen($value) > $maxLength) {
            throw ValidationException::forField($field, sprintf('must contain at most %d characters', $maxLength));
        }

        return $value === '' ? null : $value;
    }

    private function canAccessBackOffice(): bool
    {
        if ($this->authGuard === null) {
            JsonResponse::send([
                'error' => 'server_error',
                'message' => 'Auth guard is not configured.',
            ], 500);
            return false;
        }

        return $this->authGuard->requireRoles(self::BACK_OFFICE_ROLES) !== null;
    }
}
