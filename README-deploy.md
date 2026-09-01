Deployment notes — Docker + Traefik (adapted from Lynna)

Prerequisites
- A Traefik instance handling TLS/ACME and an external Docker network named `admin_proxy`.

Quick start (example)

1. Create the Traefik network if you don't have it:

```bash
docker network create admin_proxy
```

2. Copy `.env.example` to `.env` and edit `BACK_DOMAIN` to your domain.

```bash
cp .env.example .env
# edit .env -> BACK_DOMAIN=your-domain.example
```

3. Build and start:

```bash
docker compose up -d --build
```

4. Initialize DB and seed (inside PHP container):

```bash
docker compose exec wacdo_php bash
php bin/init_db.php
php bin/seed_db.php
exit
```

Notes specific to this project
- The backend entrypoint is `index.php` and the router exposes:
  - API endpoints under `/api/*` (see `index.php` routes)
  - Back-office UI at `/` served by `HomeController::backOffice`
- Front-end markup uses classes/IDs such as `.application`, `#ecran-accueil`, `.carte-choix`, `.catalogue`, `.barre-haut`. Traefik routes point to the PHP container which serves these assets and API.

Traefik labels used
- The `wacdo_php` service in `docker-compose.yml` uses the label rule `Host(${BACK_DOMAIN})` and `tls.certresolver=letsencrypt` so Traefik will request certificates for the domain provided in `.env`.

If you don't run Traefik on the same Docker host, you can still use the compose file but the `admin_proxy` network must be reachable by Traefik or you should replace labels with a direct port mapping for local testing.
