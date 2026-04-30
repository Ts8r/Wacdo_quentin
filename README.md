# Wacdo Back - quentin

Projet PHP/MariaDB sous Docker avec Traefik (wildcard DNS).

## URL

- **Back-office + API** : https://quentin-wacdo.stark.a3n.fr
- **Borne client statique** : https://front-quentin-wacdo.stark.a3n.fr

Le back-office est servi a la racine de `BACK_URL`. L'ancienne route
`/back-office` n'est plus exposee.

## Topologie Docker

- `wacdo_php` : PHP 8.3 + Apache, API REST `/api/*` et back-office `/`.
- `wacdo_front` : `httpd:2.4-alpine`, borne client statique uniquement.
- `wacdo_mariadb` : base MariaDB interne.

Le container front ne monte que `index.html`, les assets CSS/JS de la borne et
les JSON/images sous `wacdo/`. Il ne monte ni le code PHP, ni `app/`, ni
`config/`, ni `.env`.

La borne appelle l'API en HTTPS sur `BACK_URL` avec cookies inclus. Les reponses
`/api/*` autorisent l'origine exacte de `FRONT_URL` avec credentials.

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
  index.php               # Point d'entree API + back-office
  index.html              # Borne client statique
```

## Reseau

- `wacdo_bak_quentin` : reseau interne (PHP <-> MariaDB)
- `admin_proxy` : reseau externe (Traefik vers PHP et front statique)

## Certificat SSL

Automatique via Let's Encrypt (DNS-01 challenge, wildcard `*.stark.a3n.fr`).
Aucune entree DNS manuelle requise dans Infomaniak.
