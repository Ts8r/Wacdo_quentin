# Checklist d’acceptation – WACDO

## A. Pré-requis

- [ ] Docker installé et fonctionnel
- [ ] Docker Compose disponible
- [ ] réseau Docker `admin_proxy` créé
- [ ] fichier `.env` correctement renseigné
- [ ] domaine public ou point de test Traefik prêt

## B. Démarrage

- [ ] `docker compose up -d --build` exécuté sans erreur
- [ ] services `wacdo_mariadb`, `wacdo_php`, `wacdo_front` démarrés
- [ ] état des conteneurs sain

## C. Base de données

- [ ] `php bin/init_db.php` exécuté avec succès
- [ ] `php bin/seed_db.php` exécuté avec succès
- [ ] tables principales présentes : catégories, produits, menus, ingrédients
- [ ] données cohérentes et images corrigées

## D. API

- [ ] `/api/health` renvoie `status: ok`
- [ ] `/api/categories` renvoie un JSON exploitable
- [ ] routes backend répliquées selon le besoin métier

## E. Front

- [ ] page d’accueil servie correctement
- [ ] assets CSS/JS chargés
- [ ] images produit visibles
- [ ] navigation catalogue fonctionnelle

## F. HTTPS / Traefik

- [ ] Traefik accessible depuis le réseau Docker
- [ ] labels TLS activés pour le backend et le front
- [ ] `letsencrypt` configuré
- [ ] domaine public pointé vers le bon hôte
- [ ] certificat obtenu sans erreur

## G. Qualité

- [ ] code source bien organisé
- [ ] configuration centralisée dans `.env`
- [ ] documentation déploiement complète
- [ ] scripts d’init et de test fournis

## H. Acceptation finale

- [ ] projet démontrable en local
- [ ] projet démontrable via domaine public
- [ ] fonctionnement métier validé par l’utilisateur
- [ ] documentation de soutenance prête
