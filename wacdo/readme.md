# WACDO

WACDO est un projet de borne de commande accompagnee d'un back-office, pense comme un MVP. L'objectif est simple: fluidifier la prise de commande, reduire la charge humaine en point de vente et accelerer le service.

Le projet s'inscrit dans une logique de conception progressive. L'idee n'est pas de produire une application "complete" d'un seul coup, mais de construire une base propre, compréhensible et evolutive, avec des livrables concrets a chaque etape: modelisation des donnees, architecture backend, organisation MVC, puis implementation des fonctionnalites CRUD.

## Contexte

Dans un contexte de restauration rapide ou de vente au comptoir, la prise de commande est un point critique:

- elle doit etre rapide
- elle doit limiter les erreurs
- elle doit fonctionner avec ou sans compte client
- elle doit rester simple a maintenir

WACDO repond a ce besoin avec deux volets:

- une borne de commande cote client
- un back-office pour gerer les produits, menus, categories et le suivi des commandes

Le projet est volontairement cadre en `MVP` afin de rester realiste par rapport aux contraintes de temps et de charge de travail.

## Objectifs du projet

- Permettre a un client de passer une commande simplement
- Autoriser les commandes anonymes
- Structurer les donnees metier de maniere coherente
- Poser une base backend propre en `PHP 8`
- Mettre en place une architecture maintenable sans framework

## Stack technique

- Back-end: `PHP 8`
- Architecture: `MVC + POO`
- Base de donnees: `MariaDB`
- Front-end: `HTML / CSS / JavaScript`

Le choix a ete fait de travailler sans framework pour bien maitriser les fondamentaux: routing simple, controllers, services, repositories, `PDO`, CRUD, relations SQL et organisation du code.

## Avancement actuel

La phase de cadrage et de modelisation a deja ete engagee.

Travail deja produit:

- modelisation des donnees
- MCD en Mermaid
- MPD SQL MariaDB
- diagrammes de classes metier et applicatif
- cadrage du backend autour de `PDO` et des operations CRUD

## Decisions de conception importantes

- `COMMANDES.id_user` est nullable pour permettre une commande anonyme
- `numero_ticket` est unique
- les tables de reference `ROLES`, `STATUTS_COMMANDES` et `CANAUX` sont separees
- la relation entre `PRODUITS` et `CATEGORIES` est en `M:N`
- la structure vise une implementation progressive par couches: modele, repository, service, controller

## Structure du projet

Les fichiers utiles a ce stade se trouvent principalement dans:

- `wacdo/architecture/` pour la modelisation et les schemas
- `wacdo/suppor_cour/` pour les supports de cours utilises comme reference technique
- `wacdo/produits.json` et `wacdo/categories.json` pour les donnees de base
- les dossiers d'assets pour les visuels produits et categories

## Prototype

Lien public vers le prototype Figma:

`https://www.figma.com/design/0qnd0pH4qryZqjzXcB4qjN/borne?node-id=97-775&t=SJ4QkHUyIRA5QSb0-1`

## Demarche de travail

Le projet avance par micro-etapes courtes afin de rester compatible avec des contraintes reelles de temps. Chaque session doit produire un resultat concret et reutilisable.

La logique suivie est:

1. cadrer le besoin
2. modeliser les donnees
3. definir l'architecture
4. implementer couche par couche
5. tester et faire evoluer

## Suite prevue

Les prochaines etapes naturelles du projet sont:

- mise en place de la connexion `PDO`
- creation des repositories CRUD
- implementation des services metier
- mise en place des controllers MVC
- connexion progressive avec l'interface de borne et le back-office

## Intention pedagogique

Ce projet sert aussi de support d'apprentissage. Il permet de travailler concretement:

- la modelisation Merise
- les diagrammes UML
- la programmation orientee objet en PHP
- `PDO` et les requetes preparees
- la separation des responsabilites dans une architecture MVC

WACDO n'est donc pas seulement une maquette fonctionnelle: c'est aussi une base technique pour apprendre a construire une application web propre, coherente et evolutive.
