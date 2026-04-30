<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Http\JsonRequest;
use App\Http\JsonResponse;
use App\Models\Utilisateur;
use App\Repositories\DbUtilisateurRepository;
use App\Security\SessionAuthGuard;
use Throwable;

final class UtilisateurController
{
    private const DEFAULT_LIMIT = 50;
    private const MAX_LIMIT = 100;
    private const BACK_OFFICE_ROLES = ['EMPLOYE', 'MANAGER', 'ADMIN'];

    public function __construct(
        private readonly DbUtilisateurRepository $utilisateurs,
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
            $role = $this->optionalRole($_GET['role'] ?? null);
            $limit = $this->boundedInteger($_GET['limit'] ?? self::DEFAULT_LIMIT, 'limit', 1, self::MAX_LIMIT);
            $offset = $this->boundedInteger($_GET['offset'] ?? 0, 'offset', 0, PHP_INT_MAX);

            JsonResponse::send([
                'data' => $this->utilisateurs->findAllForApi($search, $role, $limit, $offset),
                'meta' => [
                    'total' => $this->utilisateurs->countForApi($search, $role),
                    'limit' => $limit,
                    'offset' => $offset,
                    'filters' => [
                        'search' => $search,
                        'role' => $role,
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

            $nom = $this->requiredString($data, 'nom');
            $prenom = $this->requiredString($data, 'prenom');
            $email = $this->requiredEmail($data);
            $password = $this->requiredPassword($data);
            $numTel = trim((string) ($data['num_tel'] ?? ''));
            $role = trim((string) ($data['role'] ?? 'CLIENT'));

            $utilisateur = new Utilisateur(
                idRole: $this->utilisateurs->findRoleIdByCode($role),
                nom: $nom,
                prenom: $prenom,
                email: $email,
                motDePasseHash: password_hash($password, PASSWORD_DEFAULT),
                numTel: $numTel,
            );

            $created = $this->utilisateurs->create($utilisateur);

            JsonResponse::send([
                'data' => [
                    'id' => $created->idUser,
                    'id_role' => $created->idRole,
                    'nom' => $created->nom,
                    'prenom' => $created->prenom,
                    'email' => $created->email,
                    'num_tel' => $created->numTel,
                ],
            ], 201);
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

    private function requiredString(array $data, string $field): string
    {
        $value = trim((string) ($data[$field] ?? ''));

        if ($value === '') {
            throw ValidationException::forField($field, 'field is required');
        }

        return $value;
    }

    private function requiredEmail(array $data): string
    {
        $email = $this->requiredString($data, 'email');

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw ValidationException::forField('email', 'invalid email');
        }

        return strtolower($email);
    }

    private function requiredPassword(array $data): string
    {
        $password = (string) ($data['mot_de_passe'] ?? $data['password'] ?? '');

        if (strlen($password) < 8) {
            throw ValidationException::forField('mot_de_passe', 'password must contain at least 8 characters');
        }

        return $password;
    }

    private function optionalFilter(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $filter = trim((string) $value);

        return $filter === '' ? null : $filter;
    }

    private function optionalRole(mixed $value): ?string
    {
        $role = $this->optionalFilter($value);

        return $role === null ? null : strtoupper($role);
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
