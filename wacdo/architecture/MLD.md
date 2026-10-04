# WACDO - MLD

Ce document formalise le Modele Logique de Donnees a partir du MCD, du dictionnaire de donnees et du MPD existants.

## Regles de passage MCD -> MLD

- Chaque entite devient une relation.
- Les associations `1,N` sont materialisees par une cle etrangere dans la table du cote `N`.
- Les associations `N,N` sont materialisees par une table d'association a cle primaire composee.
- Les identifiants deviennent des cles primaires.
- Les attributs metier calcules ou historises utiles a l'application sont conserves lorsqu'ils sont deja presents dans les artefacts existants.

## Schema relationnel

### ROLES

`ROLES(`  
`id_role PK,`  
`code_role UK,`  
`libelle`  
`)`

### UTILISATEURS

`UTILISATEURS(`  
`id_user PK,`  
`nom,`  
`prenom,`  
`email UK,`  
`mot_de_passe_hash,`  
`num_tel,`  
`id_role FK -> ROLES.id_role,`  
`created_at [attribut technique de tracabilite]`  
`)`

### STATUTS_COMMANDES

`STATUTS_COMMANDES(`  
`id_statut PK,`  
`libelle UK`  
`)`

### CANAUX

`CANAUX(`  
`id_canal PK,`  
`libelle UK`  
`)`

### COMMANDES

`COMMANDES(`  
`id_cmd PK,`  
`numero_ticket UK,`  
`date_cmd,`  
`total_ttc,`  
`id_user FK -> UTILISATEURS.id_user NULL,`  
`id_statut FK -> STATUTS_COMMANDES.id_statut,`  
`id_canal FK -> CANAUX.id_canal`  
`)`

### PRODUITS

`PRODUITS(`  
`id_produit PK,`  
`nom,`  
`description,`  
`prix_unitaire,`  
`image [attribut technique : contenu binaire de l'image, optionnel],`  
`image_mime [attribut technique : type MIME de l'image, optionnel],`  
`disponibilite,`  
`quantite`  
`)`

### MENUS

`MENUS(`  
`id_menu PK,`  
`nom,`  
`prix [prix de la taille M],`  
`image [attribut technique : contenu binaire de l'image, optionnel],`  
`image_mime [attribut technique : type MIME de l'image, optionnel],`  
`disponibilite`  
`)`

### CATEGORIES

`CATEGORIES(`  
`id_cat PK,`  
`type,`  
`image [attribut technique : contenu binaire de l'image, optionnel],`  
`image_mime [attribut technique : type MIME de l'image, optionnel],`  
`description`  
`)`

### INGREDIENTS

`INGREDIENTS(`  
`id_ingredient PK,`  
`nom UK,`  
`cout_unitaire,`  
`quantite`  
`)`

### COMMANDE_PRODUIT

`COMMANDE_PRODUIT(`  
`id_cmd PK, FK -> COMMANDES.id_cmd,`  
`id_produit PK, FK -> PRODUITS.id_produit,`  
`quantite,`  
`prix_unitaire,`  
`prix_ligne`  
`)`

### COMMANDE_MENU

`COMMANDE_MENU(`  
`id_cmd PK, FK -> COMMANDES.id_cmd,`  
`id_menu PK, FK -> MENUS.id_menu,`  
`quantite,`  
`taille,`  
`prix_unitaire,`  
`prix_ligne`  
`)`

### MENU_PRODUIT

`MENU_PRODUIT(`  
`id_menu PK, FK -> MENUS.id_menu,`  
`id_produit PK, FK -> PRODUITS.id_produit,`  
`quantite`  
`)`

### INGREDIENTS_PRODUITS

`INGREDIENTS_PRODUITS(`  
`id_ingredient PK, FK -> INGREDIENTS.id_ingredient,`  
`id_produit PK, FK -> PRODUITS.id_produit,`  
`quantite`  
`)`

### PRODUITS_CATEGORIES

`PRODUITS_CATEGORIES(`  
`id_produit PK, FK -> PRODUITS.id_produit,`  
`id_cat PK, FK -> CATEGORIES.id_cat`  
`)`

## Contraintes logiques a retenir

- `ROLES.code_role` est unique.
- `UTILISATEURS.email` est unique.
- `COMMANDES.numero_ticket` est unique.
- `STATUTS_COMMANDES.libelle` est unique.
- `CANAUX.libelle` est unique.
- `INGREDIENTS.nom` est unique.
- `COMMANDES.id_user` est nullable pour permettre la commande anonyme.
- Les tables d'association portent des cles primaires composees.

## Prix des menus par taille

- `MENUS.prix` est le prix de la taille M : c'est la seule colonne de prix d'un menu.
- L'API calcule les deux autres tailles : S = M - 1 euro (0,01 euro au minimum), L = M + 1 euro.
- Le back-office envoie les trois prix ; l'API en deduit M et refuse tout autre ecart que 1 euro (reponse 422).
- `COMMANDE_MENU.taille` vaut S, M ou L (M par defaut). `COMMANDE_MENU.prix_unitaire` garde le prix de la taille choisie au moment de la commande.

## Suppressions (ON DELETE)

Le MPD fixe le comportement de chaque cle etrangere lors d'une suppression :

- `SET NULL` : supprimer un utilisateur conserve ses commandes, dont `id_user` devient vide (commande anonyme).
- `CASCADE` :
  - supprimer une commande supprime ses lignes (`COMMANDE_PRODUIT`, `COMMANDE_MENU`) ;
  - supprimer un menu supprime sa composition (`MENU_PRODUIT`) ;
  - supprimer un produit supprime ses liens avec les ingredients (`INGREDIENTS_PRODUITS`) et les categories (`PRODUITS_CATEGORIES`).
- `RESTRICT` (suppression refusee tant que la ligne est referencee) :
  - un role, un statut ou un canal encore utilise ;
  - une categorie ou un ingredient encore lie a un produit ;
  - un produit deja commande ou inclus dans un menu ;
  - un menu deja commande.

Toutes les cles etrangeres sont en `ON UPDATE RESTRICT`.

## Cardinalites minimales : MCD et MLD

- Le MCD de reference (figure 5 du dossier) exprime trois regles metier minimales 1,n : un menu est compose d'au moins un produit (`composer`), un ingredient est consomme par au moins un produit (`consommer`), un produit appartient a au moins une categorie (`appartenir`).
- Une cle etrangere garantit qu'une ligne d'association designe un menu, un produit, un ingredient ou une categorie qui existe. Elle ne peut pas obliger un menu, un produit ou un ingredient a avoir au moins une ligne d'association : le schema relationnel ne garantit donc pas ce minimum du cote N.
- Le MLD dessine donc `o{` (zero ou plusieurs) sur ces liens : il represente ce que la structure relationnelle garantit reellement. La regle 1,n reste une regle de gestion.
- Dans WACDO, ces tables d'association sont remplies par le seed (`bin/seed_db.php`), qui signale les menus sans produit ; aucune route de l'API ne les modifie.

## Observations de coherence

- `total_ttc` est un montant calcule par l'API (somme des `prix_ligne`) et historise dans la commande. Il figure dans le dictionnaire, le diagramme de classes et le MPD ; le MCD de reference (figure 5 du dossier) n'affiche pas les proprietes.
- `prix_unitaire` existe bien dans `COMMANDE_MENU` dans le dictionnaire et le MPD. Il doit rester dans le MLD pour historiser le prix au moment de la commande.
- `created_at` existe dans `UTILISATEURS` dans le MPD comme attribut technique de tracabilite. Il est documente dans le dictionnaire et le MLD, mais n'a pas besoin d'etre remonte dans le modele objet metier.
- `mot_de_passe_hash` est nomme explicitement ainsi dans le modele de donnees pour refleter le stockage d'un hash et s'aligner sur le diagramme de classes.
- `image` et `image_mime` (CATEGORIES, PRODUITS, MENUS) stockent l'image en base (MEDIUMBLOB) et son type MIME. Elles ont ete ajoutees en avril 2026 et sont optionnelles.
- Le MLD est coherent avec une traduction future vers MariaDB, mais reste logique: il decrit les relations et contraintes sans entrer dans les details techniques complets du SQL.
