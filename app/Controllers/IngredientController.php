<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Http\JsonRequest;
use App\Http\JsonResponse;
use App\Repositories\DbIngredientRepository;
use App\Security\SessionAuthGuard;
use Throwable;

final class IngredientController
{
    private const DEFAULT_LIMIT = 50;
    private const MAX_LIMIT = 100;
    private const BACK_OFFICE_ROLES = ['EMPLOYE', 'MANAGER', 'ADMIN'];

    public function __construct(
        private readonly DbIngredientRepository $ingredients,
        private readonly ?SessionAuthGuard $authGuard = null,
    ) {
    }

    public function index(): void
    {
        if (!$this->canAccessBackOffice()) {
            return;
        }

        try {
            $search = $this->optionalFilter($_GET['search'] ?? null);
            $limit = $this->boundedInteger($_GET['limit'] ?? self::DEFAULT_LIMIT, 'limit', 1, self::MAX_LIMIT);
            $offset = $this->boundedInteger($_GET['offset'] ?? 0, 'offset', 0, PHP_INT_MAX);

            JsonResponse::send([
                'data' => $this->ingredients->findAllForApi($search, $limit, $offset),
                'meta' => [
                    'total' => $this->ingredients->countForApi($search),
                    'limit' => $limit,
                    'offset' => $offset,
                    'filters' => [
                        'search' => $search,
                    ],
                ],
            ]);
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

    public function updateQuantity(string $id): void
    {
        if (!$this->canAccessBackOffice()) {
            return;
        }

        $idIngredient = (int) $id;

        if ($idIngredient <= 0) {
            JsonResponse::send([
                'error' => 'validation_failed',
                'message' => 'Validation failed for "id": must be a positive integer.',
            ], 422);
            return;
        }

        try {
            $data = JsonRequest::body();
            $quantite = $this->requiredQuantity($data);
            $ingredient = $this->ingredients->updateQuantityForApi($idIngredient, $quantite);

            if ($ingredient === null) {
                JsonResponse::send([
                    'error' => 'not_found',
                    'message' => 'Ingredient introuvable.',
                ], 404);
                return;
            }

            JsonResponse::send(['data' => $ingredient]);
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

    private function requiredQuantity(array $data): int
    {
        if (!array_key_exists('quantite', $data)) {
            throw ValidationException::forField('quantite', 'field is required');
        }

        if (filter_var($data['quantite'], FILTER_VALIDATE_INT) === false) {
            throw ValidationException::forField('quantite', 'must be an integer');
        }

        return (int) $data['quantite'];
    }

    private function optionalFilter(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $filter = trim((string) $value);

        return $filter === '' ? null : $filter;
    }

    private function boundedInteger(mixed $value, string $field, int $min, int $max): int
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw ValidationException::forField($field, 'must be an integer');
        }

        $integer = (int) $value;

        if ($integer < $min || $integer > $max) {
            throw ValidationException::forField($field, sprintf('must be between %d and %d', $min, $max));
        }

        return $integer;
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
