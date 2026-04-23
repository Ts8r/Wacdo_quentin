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
}
