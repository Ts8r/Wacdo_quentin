# Benchmarks de choix techniques - WACDO

## Methode

Ces benchmarks sont des comparatifs de decision et de compatibilite avec le
projet. Ils ne pretendent pas mesurer une performance generale. Aucun temps,
debit ou score de charge n'est invente. Les notes sont des notes de compatibilite
avec WACDO, sur une echelle de 1 a 5, et les faits du projet sont identifies
separement.

## 1. Langage serveur

### Options comparees

PHP 8.3, Node.js et Python.

### Criteres et resultats

| Critere | PHP 8.3 | Node.js | Python |
|---|---:|---:|---:|
| API HTTP | 5 | 5 | 5 |
| Sessions serveur simples | 5 | 3 | 4 |
| Acces SQL adapte au projet | 5 | 4 | 4 |
| Visibilite MVC/POO/PDO | 5 | 3 | 4 |
| Coherence avec Apache/PHP-FPM deja deploye | 5 | 2 | 2 |
| Score de compatibilite WACDO | **25/25** | 17/25 | 19/25 |

### Decision

PHP l'emporte pour ce projet parce qu'il permet de montrer directement le
routage, les controleurs, les repositories, PDO, les sessions et les reponses
JSON, sans changer l'infrastructure Apache/PHP-FPM. Node.js et Python restent
techniquement possibles, mais demanderaient une autre chaine de dependances et
un autre deploiement.

**Preuves du projet :** `index.php`, `app/Http/Router.php`, `app/Controllers/`,
`app/Repositories/`, `config/database.php`, `docker/php-fpm/Dockerfile`.

**Sources officielles :**

- PHP : https://www.php.net/docs.php
- Node.js : https://nodejs.org/en/learn/getting-started/introduction-to-nodejs
- Python : https://docs.python.org/3/faq/general.html

## 2. Systeme de base de donnees

### Options comparees

MariaDB, MySQL, PostgreSQL et MongoDB.

### Criteres et resultats

| Critere | MariaDB | MySQL | PostgreSQL | MongoDB |
|---|---:|---:|---:|---:|
| Modele relationnel pour commandes et stock | 5 | 5 | 5 | 2 |
| Cles etrangeres et contraintes | 5 | 5 | 5 | 2 |
| Transactions et integrite | 5 | 5 | 5 | 3 |
| Coherence avec le MPD SQL existant | 5 | 5 | 4 | 1 |
| Integration Docker/PDO actuelle | 5 | 5 | 4 | 2 |
| Score de compatibilite WACDO | **25/25** | 25/25 | 23/25 | 10/25 |

### Decision

MariaDB et MySQL sont proches pour le besoin actuel. MariaDB est retenue car
le service Docker, le MPD, les migrations et la configuration PDO du projet
sont deja construits pour elle. PostgreSQL est une alternative relationnelle
solide, mais demanderait une migration. MongoDB est moins coherent avec le
modele fortement relationnel et les tables d'association du projet.

**Faits verifies dans le projet :** MariaDB 10.11, 53 produits, 13 menus,
42 ingredients et 14 commandes presentes dans la base de demonstration apres
nettoyage des tests.

**Preuves du projet :** `wacdo/architecture/MCD.mmd`, `MLD.md`, `MPD.sql`,
`db/migrations/0001_init_schema.php`, `config/database.php`, `docker-compose.yml`.

**Sources officielles :**

- MariaDB : https://mariadb.com/kb/en/mariadb-server/
- MySQL : https://dev.mysql.com/doc/
- PostgreSQL : https://www.postgresql.org/docs/current/intro-whatis.html
- MongoDB : https://www.mongodb.com/docs/manual/introduction/

## 3. Front avance

### Options comparees

JavaScript vanilla, React, Vue et Angular.

### Criteres et resultats

| Critere | Vanilla | React | Vue | Angular |
|---|---:|---:|---:|---:|
| Demarrage rapide de la borne | 5 | 3 | 4 | 2 |
| Dependances necessaires | 5 | 3 | 3 | 2 |
| Composants reutilisables | 2 | 5 | 5 | 5 |
| Lisibilite des fondamentaux navigateur | 5 | 4 | 4 | 3 |
| Coherence avec la version presentee | **5** | 2 | 2 | 1 |
| Score version presentee | **22/25** | 17/25 | 18/25 | 13/25 |

### Decision

La version presentee commence en vanilla pour valider le catalogue, les
categories, le panier et le ticket avec peu de dependances. React serait un
choix pertinent pour une version suivante avec davantage d'ecrans, d'etat et
de composants reutilisables. Il n'existe actuellement aucun projet React,
aucun `package.json` et aucun outil Vite ou Create React App dans ce depot.

**Sources officielles :**

- React : https://react.dev/learn
- Vue : https://vuejs.org/guide/introduction.html
- Angular : https://angular.dev/overview

## 4. Conteneurisation

### Options comparees

Docker Compose et installation classique sur une machine.

| Critere | Docker Compose | Installation classique |
|---|---:|---:|
| Reproduction des versions PHP/MariaDB/Apache | 5 | 2 |
| Separation des services | 5 | 2 |
| Demarrage reproductible | 5 | 3 |
| Coherence local/serveur | 5 | 2 |
| Simplicite initiale pour un petit script | 3 | 5 |
| Score pour WACDO | **23/25** | 14/25 |

### Decision

Docker est retenu car WACDO comporte plusieurs services : PHP-FPM, Apache,
MariaDB, migration, seed et borne statique. Le projet est demarrable par
`docker compose up -d --build`, avec des reseaux et volumes declares.

**Faits verifies :** 6 services declares dans Compose, dont 2 services
ponctuels de preparation de base, et 2 reseaux Docker utilises.

**Source officielle :**

- Docker : https://docs.docker.com/get-started/docker-overview/

## 5. Reverse proxy et HTTPS

### Options comparees

Traefik et Nginx.

| Critere | Traefik | Nginx |
|---|---:|---:|
| Routage par labels Docker | 5 | 2 |
| Adaptation a plusieurs services Docker | 5 | 4 |
| Configuration declarative dans Compose | 5 | 3 |
| HTTPS et certificats ACME | 5 | 4 |
| Coherence avec l'infrastructure existante | **5** | 3 |
| Score pour WACDO | **25/25** | 16/25 |

### Decision

Traefik est retenu car le serveur fournit deja un reseau `admin_proxy` et le
projet utilise des labels Docker pour exposer le back-office et la borne sur
des domaines distincts. Nginx est une alternative solide, mais impliquerait
une configuration externe plus manuelle pour ce deploiement.

**Faits verifies :** deux routeurs HTTPS sont declares dans Compose, un pour le
back-office/API et un pour la borne, avec certificats Let's Encrypt via ACME.

**Sources officielles :**

- Traefik : https://doc.traefik.io/traefik/
- Nginx reverse proxy : https://docs.nginx.com/nginx/admin-guide/web-server/reverse-proxy/

## Conclusion

Les scores sont des scores de compatibilite avec le perimetre WACDO, pas des
mesures universelles de performance. Le choix final est : PHP 8.3, MariaDB,
HTML/CSS/JavaScript vanilla pour la version presentee, Docker Compose et
Traefik. React reste une evolution possible et non une implementation existante.
