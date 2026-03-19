<?php

declare(strict_types=1);

namespace App\Models;

final class Utilisateur
{
    private string $motDePasseHash;

    public function __construct(
        public int $idUser = 0,
        public int $idRole = 0,
        public string $nom = '',
        public string $prenom = '',
        public string $email = '',
        string $motDePasseHash = '',
        public string $numTel = '',
    ) {
        $this->motDePasseHash = $motDePasseHash;
    }

    public function assignerRole(Role $role): void
    {
        $this->idRole = $role->idRole;
    }

    public function getMotDePasseHash(): string
    {
        return $this->motDePasseHash;
    }

    public function setMotDePasseHash(string $motDePasseHash): void
    {
        $this->motDePasseHash = $motDePasseHash;
    }
}
