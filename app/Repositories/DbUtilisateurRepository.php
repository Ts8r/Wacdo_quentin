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

    public function findAllForApi(?string $search, ?string $role, int $limit, int $offset): array
    {
        [$whereSql, $params] = $this->userFilters($search, $role);

        $stmt = $this->pdo->prepare(
            'SELECT
                u.id_user AS id,
                u.id_role,
                u.nom,
                u.prenom,
                u.email,
                u.num_tel,
                u.created_at,
                r.code_role,
                r.libelle AS role
             FROM utilisateurs u
             INNER JOIN roles r ON r.id_role = u.id_role
             ' . $whereSql . '
             ORDER BY u.id_user DESC
             LIMIT :limit OFFSET :offset'
        );

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return array_map([$this, 'formatUserForApi'], $stmt->fetchAll());
    }

    public function countForApi(?string $search, ?string $role): int
    {
        [$whereSql, $params] = $this->userFilters($search, $role);

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM utilisateurs u
             INNER JOIN roles r ON r.id_role = u.id_role
             ' . $whereSql
        );
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function formatUserForApi(array $user): array
    {
        $formatted = [
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

        if (array_key_exists('created_at', $user)) {
            $formatted['created_at'] = (string) $user['created_at'];
        }

        return $formatted;
    }

    private function userFilters(?string $search, ?string $role): array
    {
        $where = [];
        $params = [];

        if ($search !== null && $search !== '') {
            $where[] = '(u.nom LIKE :search_nom OR u.prenom LIKE :search_prenom OR u.email LIKE :search_email OR u.num_tel LIKE :search_num_tel)';
            $params['search_nom'] = '%' . $search . '%';
            $params['search_prenom'] = '%' . $search . '%';
            $params['search_email'] = '%' . $search . '%';
            $params['search_num_tel'] = '%' . $search . '%';
        }

        if ($role !== null && $role !== '') {
            $where[] = 'r.code_role = :role';
            $params['role'] = strtoupper($role);
        }

        return [
            $where === [] ? '' : 'WHERE ' . implode(' AND ', $where),
            $params,
        ];
    }
}
