# Chronologie et vocabulaire - WACDO

## 1. Chronologie corrigee

Il faut distinguer l'ordre logique de conception et l'ordre reel de construction.

### 1.1 Ordre logique a expliquer au jury

1. Dictionnaire de donnees : definir les entites, attributs et regles.
2. MCD : representer les entites et leurs relations.
3. MLD : transformer le MCD en relations, cles primaires et cles etrangeres.
4. MPD et script SQL : choisir les types, contraintes et tables MariaDB.
5. Migrations et seed : reconstruire la base et charger les donnees de demonstration.
6. Diagrammes de classes : traduire le metier et l'architecture objet.
7. API PHP : routes, controleurs, validation, repositories et PDO.
8. Securite : sessions, hash, roles et autorisations.
9. Front borne vanilla : catalogue local, categories, panier et ticket de demonstration.
10. Back-office : ecran interne et chemins API prepares.
11. Docker et deploiement : services, volumes, reseaux, Apache, PHP-FPM et Traefik.
12. Tests : syntaxe, routes, validations, securite et parcours metier.
13. Framework front React : evolution prevue, non realisee dans cette version.

Le Framework front doit etre place en evolution et non comme une etape deja
realisee. Les tests doivent accompagner chaque etape, puis etre regroupes dans
une validation finale.

### 1.2 Chronologie observable dans Git

| Date | Etape observee | Preuve |
|---|---|---|
| 12/03/2026 | Premier cadrage et premiers elements Docker | commits `8bce4a4`, `5f342ee` |
| 19/03/2026 | Dictionnaire, modeles MCD/MLD/MPD et diagrammes de classes | commits `5ac7f30`, `d52bcff`, `dce2c2d`, `2161412` |
| 23/04/2026 | Evolution de la base et debut de l'API | commits `44f2a74`, `6beaff5` |
| 30/04/2026 | Separation front/back, HTML, CSS et JavaScript | commits `3d878fc`, `a31c83b` |
| 21/07/2026 | Stabilisation de la borne et documentation | commit `bd3f6d1` |
| 01/09/2026 | Docker Compose, Traefik, HTTPS, migrations et seed | commit `9bb57a8` |
| 18/09/2026 | MLD visuel, architecture, parcours, securite et dossier visuel | commits `c26bc50`, `81b3f30`, `26c03c2` |

Cette chronologie montre que Docker a ete commence tot puis consolide apres la
stabilisation du code. Ce n'est pas une incoherence : l'infrastructure peut
etre preparee en parallele, tandis que les fonctionnalites sont construites par
increments.

## 2. Vocabulaire technique a definir

La liste initiale est bonne. Il faut ajouter :

- **endpoint** : adresse d'une ressource API ;
- **routeur** : composant qui associe une methode HTTP et un chemin a une action ;
- **payload** : donnees envoyees dans le corps d'une requete ;
- **code HTTP** : resultat d'une requete, par exemple 200, 201, 401, 404 ou 422 ;
- **validation** : controle des donnees avant traitement ;
- **CORS** : regle qui autorise le front a appeler l'API depuis une autre origine ;
- **transaction** : groupe d'operations SQL validees ensemble ou annulees ensemble ;
- **rollback** : annulation d'une transaction incomplete ;
- **CRUD** : creation, lecture, modification et suppression ;
- **repository** : classe qui centralise l'acces aux donnees ;
- **controleur** : classe qui traite une requete et construit la reponse ;
- **middleware** : traitement execute autour d'une requete ou d'une route ;
- **variable d'environnement** : valeur de configuration fournie hors du code ;
- **volume Docker** : stockage ou montage de fichiers partage avec un conteneur ;
- **reseau Docker** : reseau permettant aux services de communiquer ;
- **DNS** : correspondance entre un nom de domaine et une adresse ;
- **ACME** : protocole utilise pour obtenir automatiquement un certificat ;
- **certificat TLS** : preuve cryptographique utilisee par HTTPS ;
- **idempotence** : operation repetee sans effet non prevu supplementaire ;
- **contrainte d'integrite** : regle SQL qui protege la coherence des donnees.

## 3. Vocabulaire metier WACDO

- **borne** : interface utilisee par le client pour composer sa commande ;
- **catalogue** : ensemble des categories, produits et menus affichables ;
- **produit** : article vendu individuellement ;
- **menu** : composition de plusieurs produits avec une tarification ;
- **ingredient** : element consomme par un produit et suivi en stock ;
- **commande** : demande d'achat complete d'un client ;
- **ligne de commande** : produit ou menu et sa quantite dans une commande ;
- **ticket** : numero unique permettant d'identifier une commande ;
- **canal** : origine de la commande, par exemple borne, caisse, appli ou drive ;
- **statut** : etat de la commande, par exemple en_attente, en_preparation,
  prete, servie ou annulee ;
- **stock** : quantite disponible d'un ingredient ou d'un produit ;
- **back-office** : interface reservee au personnel du restaurant ;
- **role** : niveau de droits d'un utilisateur interne ;
- **autorisation** : verification qu'un role peut effectuer une action.

## 4. React et informations de build

La version presentee ne contient pas d'application React.

Constats verifies dans le depot :

- aucun `package.json` ;
- aucun `vite.config.*` ;
- aucun Create React App ;
- aucun dossier `src/` React ;
- aucun lien de deploiement React ;
- le front actuel est `index.html` avec `assets/css/borne.css` et
  `assets/js/borne.js`.

Formulation conseillee :

> Le front presente est en HTML, CSS et JavaScript vanilla. React est une
> evolution envisagee pour componentiser l'interface si le nombre d'ecrans et
> d'etats augmente. Aucun outil de build React ni deploiement React n'est donc
> a presenter comme existant dans cette version.

## 5. Depot et liens disponibles

- Depot Git : `https://github.com/Ts8r/Wacdo_quentin.git`
- URLs de projet configurees dans le README :
  - back-office/API : `https://quentin-wacdo.stark.a3n.fr`
  - borne : `https://front-quentin-wacdo.stark.a3n.fr`

Ces URLs correspondent a l'infrastructure WACDO actuelle. Elles ne constituent
pas un deploiement React.

## 6. Sources officielles pour les definitions

- PHP : https://www.php.net/docs.php
- Node.js : https://nodejs.org/en/learn/getting-started/introduction-to-nodejs
- Python : https://docs.python.org/3/faq/general.html
- MariaDB : https://mariadb.com/kb/en/mariadb-server/
- PostgreSQL : https://www.postgresql.org/docs/current/intro-whatis.html
- React : https://react.dev/learn
- Vue : https://vuejs.org/guide/introduction.html
- Angular : https://angular.dev/overview
- Docker : https://docs.docker.com/get-started/docker-overview/
- Traefik : https://doc.traefik.io/traefik/
- Nginx : https://docs.nginx.com/nginx/admin-guide/web-server/reverse-proxy/
