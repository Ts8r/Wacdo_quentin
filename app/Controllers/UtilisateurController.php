<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Http\JsonRequest;
use App\Http\JsonResponse;
use App\Models\Utilisateur;
use App\Repositories\DbUtilisateurRepository;
use Throwable;

final class UtilisateurController
{
    public function __construct(private readonly DbUtilisateurRepository $utilisateurs)
    {
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
}
