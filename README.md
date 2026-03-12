# Wacdo Back - quentin

Projet PHP/MariaDB sous Docker avec Traefik (wildcard DNS).

## URL

- **Production** : https://quentin-wacdo.stark.a3n.fr

## Demarrage

```bash
docker compose up -d --build
```

## Arret

```bash
docker compose down
```

## Structure

```
quentin_wacdo/
  .env                    # Variables d'environnement (DB, domaine)
  docker-compose.yml      # Orchestration des services
  docker/php/Dockerfile   # Image PHP 8.3 + Apache + PDO MySQL
  index.php               # Point d'entree (test connexion DB)
```

## Reseau

- `wacdo_bak_quentin` : reseau interne (PHP <-> MariaDB)
- `admin_proxy` : reseau externe (Traefik)

## Certificat SSL

Automatique via Let's Encrypt (DNS-01 challenge, wildcard `*.stark.a3n.fr`).
Aucune entree DNS manuelle requise dans Infomaniak.
