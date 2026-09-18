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
`disponibilite,`  
`quantite`  
`)`

### MENUS

`MENUS(`  
`id_menu PK,`  
`nom,`  
`prix,`  
`disponibilite`  
`)`

### CATEGORIES

`CATEGORIES(`  
`id_cat PK,`  
`type,`  
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
- `INGREDIENTS.nom` est idealement unique.
- `COMMANDES.id_user` est nullable pour permettre la commande anonyme.
- Les tables d'association portent des cles primaires composees.

## Observations de coherence

- `total_ttc` apparait dans le diagramme de classes et le MPD, mais pas dans le MCD Mermaid actuel. Je le conserve dans le MLD car il fait partie du modele metier deja stabilise.
- `prix_unitaire` existe bien dans `COMMANDE_MENU` dans le dictionnaire et le MPD. Il doit rester dans le MLD pour historiser le prix au moment de la commande.
- `created_at` existe dans `UTILISATEURS` dans le MPD comme attribut technique de tracabilite. Il est documente dans le MLD mais n'a pas besoin d'etre remonte dans le modele objet metier.
- `mot_de_passe_hash` est nomme explicitement ainsi dans le modele de donnees pour refleter le stockage d'un hash et s'aligner sur le diagramme de classes.
- Le MLD est coherent avec une traduction future vers MariaDB, mais reste logique: il decrit les relations et contraintes sans entrer dans les details techniques complets du SQL.
