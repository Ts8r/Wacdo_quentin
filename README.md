# Wacdo Back - quentin

Projet PHP/MariaDB sous Docker avec Traefik (wildcard DNS).

## URL

- **Back-office + API** : https://quentin-wacdo.stark.a3n.fr
- **Borne client statique** : https://front-quentin-wacdo.stark.a3n.fr

Le back-office est servi a la racine de `BACK_URL`. L'ancienne route
`/back-office` n'est plus exposee.

## Topologie Docker

- `wacdo_app` : PHP 8.3-FPM, API REST `/api/*` et back-office `/`.
- `wacdo_web` : Apache, fichiers statiques et relais FastCGI vers `wacdo_app`.
- `wacdo_front` : `httpd:2.4-alpine`, borne client statique uniquement.
- `wacdo_mariadb` : base MariaDB interne.
- `wacdo_migrate` et `wacdo_seed` : services ponctuels de preparation de la base.

Le container front ne monte que `index.html`, les assets CSS/JS de la borne et
les JSON/images sous `wacdo/`. Il ne monte ni le code PHP, ni `app/`, ni
`config/`, ni `.env`.

La borne appelle l'API en HTTPS sur `BACK_URL` avec cookies inclus. Les reponses
`/api/*` autorisent l'origine exacte de `FRONT_URL` avec credentials.

## Demarrage

```bash
docker compose up -d --build
```

## Deploiement production

Le serveur fournit deja Traefik et le reseau `admin_proxy`. La production
utilise un override local a l'hote, non versionne :

```bash
cp .env.prod.example .env.prod
# remplacer les mots de passe dans .env.prod
docker compose --env-file .env.prod \
  -f docker-compose.yml -f docker-compose.prod.yml \
  up -d --build
```

L'override conserve les services et domaines Quentin, rend le code monte en
lecture seule et laisse les migrations puis le seed s'executer avant PHP.
Traefik reste fourni par l'infrastructure existante.

## Arret

```bash
docker compose down
```

## Structure

```
quentin_wacdo/
  .env                    # Variables d'environnement (DB, domaine)
  docker-compose.yml      # Orchestration des services
  docker/php-fpm/Dockerfile # Image PHP 8.3-FPM + PDO MySQL
  docker/apache/           # Image Apache + relais FastCGI
  index.php               # Point d'entree API + back-office
  index.html              # Borne client statique
```

## Reseau

- `wacdo_bak_quentin` : reseau interne (PHP <-> MariaDB)
- `admin_proxy` : reseau externe (Traefik vers PHP et front statique)

## Certificat SSL

Automatique via Let's Encrypt (DNS-01 challenge, wildcard `*.stark.a3n.fr`).
Aucune entree DNS manuelle requise dans Infomaniak.
