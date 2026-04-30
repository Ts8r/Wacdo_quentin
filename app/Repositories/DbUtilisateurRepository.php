<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\ValidationException;
use App\Models\Utilisateur;
use PDO;
use PDOException;

final class DbUtilisateurRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(Utilisateur $utilisateur): Utilisateur
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO utilisateurs (id_role, nom, prenom, email, mot_de_passe_hash, num_tel)
             VALUES (:id_role, :nom, :prenom, :email, :mot_de_passe_hash, :num_tel)'
        );

        try {
            $stmt->execute([
                'id_role' => $utilisateur->idRole,
                'nom' => $utilisateur->nom,
                'prenom' => $utilisateur->prenom,
                'email' => $utilisateur->email,
                'mot_de_passe_hash' => $utilisateur->getMotDePasseHash(),
                'num_tel' => $utilisateur->numTel === '' ? null : $utilisateur->numTel,
            ]);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                throw ValidationException::forField('email', 'email already exists');
            }

            throw $exception;
        }

        $utilisateur->idUser = (int) $this->pdo->lastInsertId();

        return $utilisateur;
    }

    public function findRoleIdByCode(string $codeRole): int
    {
        $stmt = $this->pdo->prepare('SELECT id_role FROM roles WHERE code_role = :code_role');
        $stmt->execute(['code_role' => strtoupper($codeRole)]);
        $id = $stmt->fetchColumn();

        if ($id === false) {
            throw ValidationException::forField('role', 'unknown role');
        }

        return (int) $id;
    }

    public function findByEmailForAuth(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                u.id_user AS id,
                u.id_role,
                u.nom,
                u.prenom,
                u.email,
                u.mot_de_passe_hash,
                u.num_tel,
                r.code_role,
                r.libelle AS role
             FROM utilisateurs u
             INNER JOIN roles r ON r.id_role = u.id_role
             WHERE u.email = :email'
        );
        $stmt->execute(['email' => strtolower(trim($email))]);
        $user = $stmt->fetch();

        return $user === false ? null : $user;
    }

    public function findOneForApi(int $idUser): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                u.id_user AS id,
                u.id_role,
                u.nom,
                u.prenom,
                u.email,
                u.num_tel,
                r.code_role,
                r.libelle AS role
             FROM utilisateurs u
             INNER JOIN roles r ON r.id_role = u.id_role
             WHERE u.id_user = :id_user'
        );
        $stmt->execute(['id_user' => $idUser]);
        $user = $stmt->fetch();

        if ($user === false) {
            return null;
        }

        return $this->formatUserForApi($user);
    }

    public function formatUserForApi(array $user): array
    {
        return [
            'id' => (int) $user['id'],
            'nom' => (string) $user['nom'],
            'prenom' => (string) $user['prenom'],
            'email' => (string) $user['email'],
            'num_tel' => $user['num_tel'] === null ? null : (string) $user['num_tel'],
            'role' => [
                'id' => (int) $user['id_role'],
                'code' => (string) $user['code_role'],
                'libelle' => (string) $user['role'],
            ],
        ];
    }
}
