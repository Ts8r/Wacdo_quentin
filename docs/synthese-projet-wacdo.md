# Synthèse du projet WACDO

## 1. Contexte

WACDO est un projet de commande en ligne inspiré du modèle de restauration rapide. L’objectif est de proposer une expérience de navigation et de commande simple, avec un catalogue de produits, des catégories, des menus, des images et un moteur de commande simple.

## 2. Objectif du projet

Le projet vise à :

- afficher un catalogue de produits organisé par catégories ;
- proposer des menus, options et visuels associatifs ;
- exécuter des commandes côté backend via une API PHP ;
- fournir un front statique qui exploite les données JSON et les ressources locales ;
- préparer le projet pour une mise en production avec HTTPS et reverse proxy.

## 3. Architecture

### Frontend

- pages HTML statiques ;
- CSS et JS dédiés à la borne ou à la commande client ;
- fichiers JSON de catégories et produits ;
- images stockées dans le dossier `wacdo/`.

### Backend

- application MVC/PHP orientée objet ;
- point d’entrée principal via `index.php` ;
- routes API sous `/api/*` ;
- accès aux données via PDO/MySQL.

### Base de données

- MariaDB 10.11
- schéma initialisé par `bin/init_db.php`
- données de test alimentées par `bin/seed_db.php`

### Déploiement

- `docker-compose.yml` orchestre :
  - `wacdo_mariadb`
  - `wacdo_php`
  - `wacdo_front`
- raccordement réseau `admin_proxy` pour Traefik
- labels Traefik avec `letsencrypt` pour le certificat ACME

## 4. Fonctionnement actuel

Le projet est fonctionnel en local :

- le conteneur PHP démarre bien ;
- MariaDB est sain ;
- la base est créée et alimentée ;
- le endpoint `/api/health` répond correctement ;
- la route `/api/categories` renvoie des données exploitables.

## 5. Points forts

- architecture simple et claire ;
- configuration Docker réutilisable ;
- base de données structurée et alimentée ;
- projet prêt pour démonstration technique ;
- système de routage HTTPS préparé pour un environnement Traefik.

## 6. Points de vigilance

- la vraie publication HTTPS dépend d’un hôte Traefik correctement configuré ;
- les domaines doivent être enregistrés DNS et la résolution ACME doit être active ;
- il faut sécuriser les credentials de la base dans un `.env` final propre.

## 7. Conclusion

WACDO a atteint un niveau de maturité suffisant pour une démonstration technique de qualité : architecture stable, environnement conteneurisé, données chargées, et base de déploiement prête pour production. Le prochain niveau d’évolution est la sécurisation du déploiement réel avec certifiats DNS, domaine public et validation utilisateur finale.
