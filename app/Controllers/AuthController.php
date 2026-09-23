<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Http\JsonRequest;
use App\Http\JsonResponse;
use App\Models\Utilisateur;
use App\Repositories\DbUtilisateurRepository;
use App\Security\SessionCookieConfig;
use Throwable;

final class AuthController
{
    public function __construct(private readonly DbUtilisateurRepository $utilisateurs)
    {
    }

    public function login(): void
    {
        $this->authenticate(false);
    }

    public function clientLogin(): void
    {
        $this->authenticate(true);
    }

    public function register(): void
    {
        try {
            $data = JsonRequest::body();
            $utilisateur = new Utilisateur(
                idRole: $this->utilisateurs->findRoleIdByCode('CLIENT'),
                nom: $this->requiredString($data, 'nom'),
                prenom: $this->requiredString($data, 'prenom'),
                email: $this->requiredEmail($data),
                motDePasseHash: password_hash($this->requiredPassword($data), PASSWORD_DEFAULT),
                numTel: trim((string) ($data['num_tel'] ?? '')),
            );
            $created = $this->utilisateurs->create($utilisateur);

            $this->startSession();
            session_regenerate_id(true);
            $_SESSION['user_id'] = $created->idUser;

            JsonResponse::send([
                'data' => ['user' => $this->utilisateurs->findOneForApi($created->idUser)],
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

    private function authenticate(bool $clientOnly): void
    {
        try {
            $data = JsonRequest::body();
            $email = $this->requiredEmail($data);
            $password = (string) ($data['mot_de_passe'] ?? $data['password'] ?? '');

            if ($password === '') {
                throw ValidationException::forField('mot_de_passe', 'field is required');
            }

            $user = $this->utilisateurs->findByEmailForAuth($email);

            if ($user === null || !password_verify($password, (string) $user['mot_de_passe_hash'])) {
                JsonResponse::send([
                    'error' => 'invalid_credentials',
                    'message' => 'Email ou mot de passe incorrect.',
                ], 401);
                return;
            }

            if ($clientOnly && strtoupper((string) $user['code_role']) !== 'CLIENT') {
                JsonResponse::send([
                    'error' => 'forbidden',
                    'message' => 'Cette connexion est réservée aux comptes clients.',
                ], 403);
                return;
            }

            $this->startSession();
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];

            JsonResponse::send([
                'data' => [
                    'user' => $this->utilisateurs->formatUserForApi($user),
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

    public function logout(): void
    {
        $this->startSession();
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 3600,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'] ?? 'Lax',
                ],
            );
            session_destroy();
        }

        JsonResponse::send(['data' => ['logged_out' => true]]);
    }

    public function me(): void
    {
        $this->startSession();
        $idUser = (int) ($_SESSION['user_id'] ?? 0);

        if ($idUser <= 0) {
            JsonResponse::send([
                'error' => 'unauthenticated',
                'message' => 'Utilisateur non connecté.',
            ], 401);
            return;
        }

        try {
            $user = $this->utilisateurs->findOneForApi($idUser);

            if ($user === null) {
                $_SESSION = [];
                session_destroy();
                JsonResponse::send([
                    'error' => 'unauthenticated',
                    'message' => 'Session invalide.',
                ], 401);
                return;
            }

            JsonResponse::send(['data' => ['user' => $user]]);
        } catch (Throwable $exception) {
            JsonResponse::send([
                'error' => 'server_error',
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    private function requiredEmail(array $data): string
    {
        $email = strtolower(trim((string) ($data['email'] ?? '')));

        if ($email === '') {
            throw ValidationException::forField('email', 'field is required');
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw ValidationException::forField('email', 'invalid email');
        }

        return $email;
    }

    private function requiredString(array $data, string $field): string
    {
        $value = trim((string) ($data[$field] ?? ''));

        if ($value === '') {
            throw ValidationException::forField($field, 'field is required');
        }

        return $value;
    }

    private function requiredPassword(array $data): string
    {
        $password = (string) ($data['mot_de_passe'] ?? $data['password'] ?? '');

        if (strlen($password) < 8) {
            throw ValidationException::forField('mot_de_passe', 'password must contain at least 8 characters');
        }

        return $password;
    }

    private function startSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            SessionCookieConfig::apply();
            session_start();
        }
    }
}
