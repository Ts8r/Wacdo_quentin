# WACDO - Contrat front/back MVP examen

Ce document fixe le contrat conserve pour le MVP examen entre la borne, le backend et le back-office.

Decision actuelle : l'API existante est conservee. On ne branche pas de nouveau contrat API pour l'examen.

## 1. Regle de travail

- Le front ne doit pas inventer le total final.
- Le backend reste la source de verite pour les prix, les stocks et la validation.
- Le front garde les chemins API actuels.
- Les options non stockees ne doivent pas etre presentees comme fonctionnelles.
- Le choix visuel "sur place / a emporter" peut exister dans le front, mais il n'est pas persiste dans le payload actuel.

## 2. Chemins API existants a conserver

### Public borne

```txt
GET  /api/health
GET  /api/catalogue
GET  /api/categories
GET  /api/produits
GET  /api/produits/{id}
GET  /api/menus
POST /api/commandes
```

### Authentification

```txt
POST /api/auth/login
POST /api/auth/logout
GET  /api/auth/me
```

### Back-office protege

```txt
GET   /api/commandes
GET   /api/commandes/{id}
PATCH /api/commandes/{id}/statut
PATCH /api/produits/{id}
PATCH /api/menus/{id}
GET   /api/ingredients
PATCH /api/ingredients/{id}
GET   /api/utilisateurs
POST  /api/utilisateurs
```

## 3. Contrat commande actuel

Payload actuellement accepte par le backend :

```json
{
  "canal": "borne",
  "produits": [
    {
      "id": 17,
      "quantite": 1
    }
  ],
  "menus": [
    {
      "id": 1,
      "quantite": 1,
      "taille": "M"
    }
  ]
}
```

Limite actuelle :

- pas de `mode_service` persiste ;
- pas de boisson incluse dans un menu ;
- pas d'accompagnement choisi ;
- pas de sauces choisies ;
- pas de taille boisson `30CL / 50CL` stockee ;
- pas de supplement boisson cote backend.

## 4. Contrat post-examen uniquement

Le payload suivant decrit une piste future. Il ne fait pas partie du MVP examen et ne doit pas etre branche maintenant.

```json
{
  "canal": "borne",
  "mode_service": "sur_place",
  "produits": [
    {
      "id": 17,
      "quantite": 1,
      "options": {
        "taille_boisson": "50CL"
      }
    }
  ],
  "menus": [
    {
      "id": 1,
      "quantite": 1,
      "taille": "M",
      "options": {
        "accompagnement_id": 21,
        "boisson_id": 31,
        "taille_boisson": "50CL",
        "sauce_ids": [41, 42]
      }
    }
  ]
}
```

Important :

- `mode_service`, `taille_boisson`, les sauces et les choix de composition menu sont des perspectives post-examen.
- Le supplement boisson devrait etre calcule cote backend si cette evolution est ajoutee.
- Les IDs d'options devraient correspondre a des produits existants et disponibles.
- `numero_chevalet` n'est pas dans le MVP. Il reste une option future uniquement si une consigne demande un service a table par chevalet.

## 5. Reponse commande post-examen

Reponse cible possible pour une version plus complete, non branchee dans le MVP examen :

```json
{
  "data": {
    "id": 123,
    "numero_ticket": "288",
    "mode_service": "sur_place",
    "date_cmd": "2026-07-05 12:00:00",
    "total_ttc": 18.50,
    "statut": {
      "id": 1,
      "libelle": "en_attente"
    },
    "canal": {
      "id": 1,
      "libelle": "borne"
    },
    "produits": [],
    "menus": [
      {
        "id": 1,
        "nom": "Menu Big Tasty",
        "quantite": 1,
        "taille": "M",
        "prix_unitaire": 10.60,
        "prix_ligne": 10.60,
        "options": {
          "accompagnement": {
            "id": 21,
            "nom": "Frites"
          },
          "boisson": {
            "id": 31,
            "nom": "Coca-Cola",
            "taille": "50CL",
            "supplement": 0.50
          },
          "sauces": [
            {
              "id": 41,
              "nom": "Barbecue"
            }
          ]
        }
      }
    ]
  }
}
```

## 6. Evolution base de donnees post-examen

### Table `commandes`

Champ possible a ajouter plus tard si le mode de service doit etre persiste :

```txt
mode_service VARCHAR(20) NOT NULL DEFAULT 'a_emporter'
```

Valeurs attendues :

```txt
a_emporter
sur_place
```

### Options de lignes

Deux options possibles.

Option simple :

```txt
commande_menu_options
```

Cette table stocke les choix lies a une ligne menu.

Option plus evolutive :

```txt
commande_ligne_options
```

Cette table stocke les options pour produits et menus.

Choix recommande : `commande_ligne_options`, car elle supporte les options produit et menu sans multiplier les tables.

Champs cibles :

```txt
id_option_commande PK
id_cmd FK
type_ligne ENUM('produit','menu')
id_reference
type_option ENUM('accompagnement','boisson','taille_boisson','sauce')
id_produit_option NULL
valeur NULL
prix_supplement DECIMAL(10,2) NOT NULL DEFAULT 0.00
```

## 7. Chemins front a preparer

Fichier borne :

```txt
assets/js/borne.js
```

Constantes de chemins :

```txt
CHEMINS_API.catalogue
CHEMINS_API.commandes
```

Fichier back-office :

```txt
assets/js/back-office.js
```

Constantes de chemins :

```txt
API_PATHS.authMe
API_PATHS.authLogin
API_PATHS.authLogout
API_PATHS.catalogue
API_PATHS.commandes
API_PATHS.commandeStatut(id)
API_PATHS.produit(id)
API_PATHS.menu(id)
API_PATHS.ingredients
API_PATHS.ingredient(id)
API_PATHS.utilisateurs
```

## 8. Chemins backend a modifier plus tard

Point d'entree :

```txt
index.php
```

Validation requete :

```txt
app/Controllers/CommandeController.php
```

Creation commande, prix, stock, lecture detail :

```txt
app/Repositories/DbCommandeRepository.php
```

Modeles a enrichir si besoin :

```txt
app/Models/Commande.php
app/Models/CommandeMenu.php
app/Models/CommandeProduit.php
```

Back-office affichage :

```txt
assets/js/back-office.js
app/Views/back_office.php
assets/css/back-office.css
```

## 9. Ordre de travail recommande pour le MVP

1. Conserver le contrat API actuel.
2. Finaliser le visuel front de la borne.
3. Stabiliser le parcours de demonstration : catalogue, panier, creation commande, back-office.
4. Verifier les chemins front/back existants.
5. Ajouter des tests smoke et controles manuels suffisants pour l'examen.
6. Documenter les evolutions post-examen sans les presenter comme deja branchees.

## 10. Decision actuelle

Pour l'examen, on garde l'API actuelle.

On ne branche pas encore :

- `mode_service` ;
- la persistance du chevalet ;
- la persistance des sauces ;
- la persistance de la boisson incluse ;
- le supplement 50CL ;
- les calculs avances de composition menu.
