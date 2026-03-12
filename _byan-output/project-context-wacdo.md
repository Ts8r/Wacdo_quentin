# Project Context - WACDO (MVP)

## Identite projet
- Nom: `WACDO`
- Scope: borne de commande + back-office
- Niveau: `MVP`
- Objectif: fluidifier la prise de commande, reduire la charge humaine, accelerer le service

## Stack validee
- Back-end: `PHP 8` sans framework
- Architecture: `MVC + POO`
- Base de donnees: `MariaDB`
- Front-end: `HTML/CSS/JS` sans framework

## Contraintes reelles
- Temps limite (travail/famille/etudes)
- Besoin de progression par micro-etapes courtes
- Exigence de livrables concrets a chaque session

## Decisions MCD/MLD validees
- `COMMANDES.id_user` nullable (commande anonyme)
- `COMMANDES.numero_ticket` unique
- `ROLES` separe + FK dans `UTILISATEURS`
- `STATUTS_COMMANDES` separe + FK dans `COMMANDES`
- `CANAUX` separe + FK dans `COMMANDES`
- `PRODUITS_CATEGORIES` en M:N

## Entites principales
- `ROLES`, `UTILISATEURS`, `COMMANDES`, `STATUTS_COMMANDES`, `CANAUX`
- `MENUS`, `PRODUITS`, `CATEGORIES`, `INGREDIENTS`
- `COMMANDE_PRODUIT`, `COMMANDE_MENU`, `MENU_PRODUIT`
- `INGREDIENTS_PRODUITS`, `PRODUITS_CATEGORIES`

## Livrables architecture existants
- `wacdo/architecture/MCD.mmd`
- `wacdo/architecture/MPD.sql`
- `wacdo/architecture/class-diagram.mmd`
- `wacdo/architecture/class-diagram-app.mmd`
