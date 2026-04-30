<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Http\JsonRequest;
use App\Http\JsonResponse;
use App\Repositories\DbCommandeRepository;
use App\Security\SessionAuthGuard;
use Throwable;

final class CommandeController
{
    private const DEFAULT_LIMIT = 50;
    private const MAX_LIMIT = 100;
    private const BACK_OFFICE_ROLES = ['EMPLOYE', 'MANAGER', 'ADMIN'];

    public function __construct(
        private readonly DbCommandeRepository $commandes,
        private readonly ?SessionAuthGuard $authGuard = null,
    ) {
    }

    public function index(): void
    {
        if (!$this->canAccessBackOffice()) {
            return;
        }

        try {
            $statut = $this->optionalFilter($_GET['statut'] ?? null);
            $canal = $this->optionalFilter($_GET['canal'] ?? null);
            $limit = $this->boundedInteger($_GET['limit'] ?? self::DEFAULT_LIMIT, 'limit', 1, self::MAX_LIMIT);
            $offset = $this->boundedInteger($_GET['offset'] ?? 0, 'offset', 0, PHP_INT_MAX);

            JsonResponse::send([
                'data' => $this->commandes->findAllForApi($statut, $canal, $limit, $offset),
                'meta' => [
                    'total' => $this->commandes->countForApi($statut, $canal),
                    'limit' => $limit,
                    'offset' => $offset,
                    'filters' => [
                        'statut' => $statut,
                        'canal' => $canal,
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

    public function create(): void
    {
        try {
            $data = JsonRequest::body();

            $commande = $this->commandes->createForApi(
                idUser: $this->optionalIdUser($data),
                canal: $this->requiredCanal($data),
                produits: $this->normalizeLines($data['produits'] ?? [], 'produits'),
                menus: $this->normalizeLines($data['menus'] ?? [], 'menus'),
            );

            JsonResponse::send(['data' => $commande], 201);
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

    public function show(string $id): void
    {
        if (!$this->canAccessBackOffice()) {
            return;
        }

        $idCmd = (int) $id;

        if ($idCmd <= 0) {
            JsonResponse::send([
                'error' => 'validation_failed',
                'message' => 'Validation failed for "id": must be a positive integer.',
            ], 422);
            return;
        }

        try {
            $commande = $this->commandes->findOneForApi($idCmd);

            if ($commande === null) {
                JsonResponse::send([
                    'error' => 'not_found',
                    'message' => 'Commande introuvable.',
                ], 404);
                return;
            }

            JsonResponse::send(['data' => $commande]);
        } catch (Throwable $exception) {
            JsonResponse::send([
                'error' => 'server_error',
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function updateStatus(string $id): void
    {
        if (!$this->canAccessBackOffice()) {
            return;
        }

        $idCmd = (int) $id;

        if ($idCmd <= 0) {
            JsonResponse::send([
                'error' => 'validation_failed',
                'message' => 'Validation failed for "id": must be a positive integer.',
            ], 422);
            return;
        }

        try {
            $data = JsonRequest::body();
            $statut = $this->requiredStatus($data);
            $commande = $this->commandes->updateStatusForApi($idCmd, $statut);

            if ($commande === null) {
                JsonResponse::send([
                    'error' => 'not_found',
                    'message' => 'Commande introuvable.',
                ], 404);
                return;
            }

            JsonResponse::send(['data' => $commande]);
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

    private function optionalIdUser(array $data): ?int
    {
        if (!array_key_exists('id_user', $data) || $data['id_user'] === null || $data['id_user'] === '') {
            return null;
        }

        $idUser = (int) $data['id_user'];

        if ($idUser <= 0) {
            throw ValidationException::forField('id_user', 'must be a positive integer');
        }

        return $idUser;
    }

    private function requiredCanal(array $data): string
    {
        $canal = trim((string) ($data['canal'] ?? ''));

        if ($canal === '') {
            throw ValidationException::forField('canal', 'field is required');
        }

        return $canal;
    }

    private function requiredStatus(array $data): string
    {
        $statut = strtolower(trim((string) ($data['statut'] ?? '')));

        if ($statut === '') {
            throw ValidationException::forField('statut', 'field is required');
        }

        return $statut;
    }

    private function normalizeLines(mixed $lines, string $field): array
    {
        if (!is_array($lines)) {
            throw ValidationException::forField($field, 'must be an array');
        }

        $normalized = [];

        foreach ($lines as $index => $line) {
            if (!is_array($line)) {
                throw ValidationException::forField($field . '.' . $index, 'must be an object');
            }

            $id = (int) ($line['id'] ?? 0);
            $quantite = (int) ($line['quantite'] ?? 0);

            if ($id <= 0) {
                throw ValidationException::forField($field . '.' . $index . '.id', 'must be a positive integer');
            }

            if ($quantite <= 0) {
                throw ValidationException::forField($field . '.' . $index . '.quantite', 'must be a positive integer');
            }

            $taille = strtoupper(trim((string) ($line['taille'] ?? 'M')));

            if ($field === 'menus' && !in_array($taille, ['S', 'M', 'L'], true)) {
                throw ValidationException::forField($field . '.' . $index . '.taille', 'must be S, M or L');
            }

            if (isset($normalized[$id])) {
                if ($field === 'menus' && $normalized[$id]['taille'] !== $taille) {
                    throw ValidationException::forField($field . '.' . $index . '.taille', 'duplicate menu cannot use another size');
                }

                $normalized[$id]['quantite'] += $quantite;
                continue;
            }

            $normalized[$id] = [
                'id' => $id,
                'quantite' => $quantite,
                'taille' => $taille,
            ];
        }

        return array_values($normalized);
    }

    private function optionalFilter(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $filter = trim((string) $value);

        return $filter === '' ? null : strtolower($filter);
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
