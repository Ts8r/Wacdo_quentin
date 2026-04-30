# BACK_PHP

## Contexte

Projet : `Wacdo_quentin`

Chemin serveur :

```txt
/home/acadenice/quentin_wacdo/Wacdo_quentin
```

Objectif du chantier : construire le backend PHP de WACDO avant le front vanilla, puis préparer un vrai back office, pas seulement un MVP jetable.

Utilisateur du projet : Quentin.

## Base et environnement

Le projet tourne avec :

- PHP 8.3
- MariaDB
- Docker
- architecture MVC / POO simple

Le `.env` contient notamment :

```txt
DB_NAME=wacdo_quentin
BACK_URL=quentin-wacdo.stark.a3n.fr
```

La base existe et a ete seedee. Les quantites ingredients ont ete remises en `INT UNSIGNED` dans le MPD et dans la base reelle.

## Fichiers importants deja touches

- `index.php`
- `app/Http/Router.php`
- `app/Http/JsonRequest.php`
- `app/Http/JsonResponse.php`
- `app/Controllers/ApiController.php`
- `app/Controllers/UtilisateurController.php`
- `app/Controllers/AuthController.php`
- `app/Controllers/CommandeController.php`
- `app/Controllers/IngredientController.php`
- `app/Repositories/DbProduitRepository.php`
- `app/Repositories/DbMenuRepository.php`
- `app/Repositories/DbUtilisateurRepository.php`
- `app/Repositories/DbCommandeRepository.php`
- `app/Repositories/DbIngredientRepository.php`
- `app/Security/SessionAuthGuard.php`
- `bin/seed_db.php`
- `wacdo/architecture/MPD.sql`

## Decisions metier prises

### Menus

La taille du menu modifie le prix, pas encore la composition.

Regle actuelle :

```txt
S = prix menu - 1.00
M = prix menu
L = prix menu + 1.00
```

La taille ne change pas encore les produits internes du menu. Pour aller plus loin plus tard, il faudra une vraie modelisation de type `menu_tailles` ou equivalent.

### Ingredients

Les ingredients sont exposes sur `GET /api/produits/{id}`.

Les ingredients sont debites a la creation d une commande.

Les ingredients sont recrédités si une commande est annulee.

Les colonnes suivantes sont en entier, pas en decimal :

```txt
ingredients.quantite
ingredients_produits.quantite
```

### Commandes

La creation de commande :

- verifie produits et menus
- verifie leur disponibilite
- calcule le prix cote serveur
- cree les lignes commande
- debite le stock ingredients

Les transitions de statut autorisees sont :

```txt
en_attente -> en_preparation
en_attente -> annulee

en_preparation -> prete
en_preparation -> annulee

prete -> servie
prete -> annulee

servie -> aucune transition
annulee -> aucune transition
```

## Auth et roles

Un systeme d auth par session PHP a ete ajoute.

Routes :

- `POST /api/auth/login`
- `POST /api/auth/logout`
- `GET /api/auth/me`

Un guard de session / role existe :

- `app/Security/SessionAuthGuard.php`

Roles back office actuellement autorises :

```txt
EMPLOYE
MANAGER
ADMIN
```

Routes back office protegees :

- `GET /api/commandes`
- `GET /api/commandes/{id}`
- `PATCH /api/commandes/{id}/statut`
- `GET /api/ingredients`
- `PATCH /api/ingredients/{id}`

Route publique conservee volontairement :

- `POST /api/commandes`

## Compte admin cree

Compte admin de travail cree et verifie :

```txt
Email: quentin.admin@wacdo.local
Mot de passe: Admin1234!
Role: ADMIN
```

Compte client de test cree :

```txt
Email: auth.test.20260423@example.com
Mot de passe: Password123
Role: CLIENT
```

## APIs actuellement disponibles

### Sante / catalogue

```txt
GET /api/health
GET /api/categories
GET /api/produits
GET /api/produits/{id}
GET /api/menus
GET /api/catalogue
```

### Utilisateurs

```txt
POST /api/utilisateurs
```

### Auth

```txt
POST /api/auth/login
POST /api/auth/logout
GET  /api/auth/me
```

### Commandes

```txt
POST  /api/commandes
GET   /api/commandes
GET   /api/commandes/{id}
PATCH /api/commandes/{id}/statut
```

### Ingredients

```txt
GET   /api/ingredients
PATCH /api/ingredients/{id}
```

## Exemples utiles

### Login admin

```bash
curl -X POST "https://quentin-wacdo.stark.a3n.fr/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "quentin.admin@wacdo.local",
    "mot_de_passe": "Admin1234!"
  }'
```

### Creation commande

```bash
curl -X POST "https://quentin-wacdo.stark.a3n.fr/api/commandes" \
  -H "Content-Type: application/json" \
  -d '{
    "canal": "borne",
    "produits": [
      { "id": 17, "quantite": 1 }
    ],
    "menus": [
      { "id": 1, "quantite": 1, "taille": "M" }
    ]
  }'
```

### Liste back office commandes

```bash
curl -X GET "https://quentin-wacdo.stark.a3n.fr/api/commandes?statut=en_attente&canal=borne&limit=10&offset=0"
```

### Changement statut commande

```bash
curl -X PATCH "https://quentin-wacdo.stark.a3n.fr/api/commandes/18/statut" \
  -H "Content-Type: application/json" \
  -d '{
    "statut": "en_preparation"
  }'
```

### Lecture stock ingredients

```bash
curl -X GET "https://quentin-wacdo.stark.a3n.fr/api/ingredients?limit=20&search=burger"
```

### Correction stock ingredient

```bash
curl -X PATCH "https://quentin-wacdo.stark.a3n.fr/api/ingredients/1" \
  -H "Content-Type: application/json" \
  -d '{
    "quantite": 510
  }'
```

## Tests deja verifies

### Auth

- login admin OK
- login client OK
- mauvais mot de passe -> `401`
- `GET /api/auth/me` -> `200` si connecte, `401` sinon
- logout -> OK

### Back office commandes

- non connecte -> `401`
- client connecte -> `403`
- admin connecte -> `200`

### Statuts commande

- transition normale -> OK
- transition absurde -> `422`
- annulation -> remet les ingredients en stock

### Ingredients

- lecture ingredients -> OK
- mise a jour stock -> OK
- quantite negative -> `422`

### Base ingredients

Verification faite :

```txt
ingredients.quantite = int(10) unsigned
ingredients_produits.quantite = int(10) unsigned
```

## Ce qu il reste a faire ensuite

Prochaine suite logique pour le back office :

1. `PATCH /api/produits/{id}`
   - prix
   - disponibilite
   - quantite
   - image
   - description

2. `PATCH /api/menus/{id}`
   - prix
   - disponibilite
   - image

3. `GET /api/utilisateurs`
   - pour voir les comptes en back office

4. Eventuellement plus tard :
   - modification mot de passe
   - gestion roles admin / employe / manager
   - journalisation ou historique des changements

5. Ensuite seulement :
   - front back office vanilla
   - front client vanilla

## Note de reprise

Le chantier backend PHP est deja bien avance. Le socle a conserver absolument :

- auth par session
- guard de roles
- routes back office protegees
- commandes avec logique ingredients
- annulation qui remet le stock

La prochaine vraie brique admin cohérente a construire est la gestion des produits et menus, afin qu un admin puisse piloter le catalogue sans toucher directement la base.
