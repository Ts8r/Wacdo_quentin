<?php

declare(strict_types=1);

namespace App\Security;

use App\Http\JsonResponse;
use App\Repositories\DbUtilisateurRepository;
use Throwable;

final class SessionAuthGuard
{
    public function __construct(private readonly DbUtilisateurRepository $utilisateurs)
    {
    }

    /**
     * @param string[] $allowedRoles
     */
    public function requireRoles(array $allowedRoles): ?array
    {
        $this->startSession();
        $idUser = (int) ($_SESSION['user_id'] ?? 0);

        if ($idUser <= 0) {
            JsonResponse::send([
                'error' => 'unauthenticated',
                'message' => 'Utilisateur non connecté.',
            ], 401);
            return null;
        }

        try {
            $user = $this->utilisateurs->findOneForApi($idUser);
        } catch (Throwable $exception) {
            JsonResponse::send([
                'error' => 'server_error',
                'message' => $exception->getMessage(),
            ], 500);
            return null;
        }

        if ($user === null) {
            $_SESSION = [];
            session_destroy();
            JsonResponse::send([
                'error' => 'unauthenticated',
                'message' => 'Session invalide.',
            ], 401);
            return null;
        }

        $role = strtoupper((string) ($user['role']['code'] ?? ''));
        $allowedRoles = array_map('strtoupper', $allowedRoles);

        if (!in_array($role, $allowedRoles, true)) {
            JsonResponse::send([
                'error' => 'forbidden',
                'message' => 'Accès réservé au back office.',
            ], 403);
            return null;
        }

        return $user;
    }

    private function startSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            SessionCookieConfig::apply();
            session_start();
        }
    }
}
