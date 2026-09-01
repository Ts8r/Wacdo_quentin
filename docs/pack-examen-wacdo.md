# Pack examen WACDO

Ce document centralise les livrables de synthèse, de validation et de démonstration du projet WACDO.

## Fichiers associés

- [docs/synthese-projet-wacdo.md](synthese-projet-wacdo.md)
- [docs/checklist-acceptation-wacdo.md](checklist-acceptation-wacdo.md)
- [docs/demo-wacdo.md](demo-wacdo.md)
- [docs/phases-entretien-wacdo.md](phases-entretien-wacdo.md)

## Résumé court

Le projet WACDO est une application de commande en ligne de restauration rapide pensée comme une plateforme de commande, de gestion de produits et d’API. La version actuelle a été containerisée avec Docker Compose, un backend PHP/Apache, une base MariaDB et un front statique. Le projet est prêt à être démontré en local et compatible avec un routage HTTPS via Traefik + ACME.

## État technique

- Backend: PHP 8.3 + Apache + PDO MySQL
- Base: MariaDB 10.11
- Front: Apache HTTPD statique
- Reverse proxy: Traefik
- Déploiement: Docker Compose
- Validation: healthcheck + init DB + seed DB

## Commandes principales

```bash
cd /home/acadenice/quentin_wacdo/Wacdo_quentin
./scripts/init.sh
./scripts/test-curl.sh
```

## URLs utiles

- Backend local: `http://localhost/api/health`
- Backend exposé: `https://quentin-wacdo.stark.a3n.fr/api/health`
- Front exposé: `https://front-quentin-wacdo.stark.a3n.fr`

## Point de vigilance

Le bon fonctionnement de HTTPS réel dépend du hôte Traefik et de la configuration DNS avec ACME. En local, la validation passe via le réseau Docker et l’API est bien accessible.
