# WACDO - Reference des classes

Ce document reprend les classes actuellement definies dans le diagramme, avec leurs attributs et leurs methodes, afin de faciliter la validation avant implementation en PHP.

## Relations d'heritage et d'implementation

### Hierarchie de classes

- `NotFoundException` est une classe enfant de `DomainException`
- `ValidationException` est une classe enfant de `DomainException`

### Implementations d'interfaces

- `DbProduitRepository` implemente `ProductRepositoryInterface`
- `DbCommandeRepository` implemente `CommandeRepositoryInterface`

### Classes sans heritage

- `Commande`, `Produit`, `Menu`, `Utilisateur`, `Role`, `StatutCommande`, `Canal`, `Categorie`, `Ingredient`
- `CommandeProduit`, `CommandeMenu`, `MenuProduit`, `IngredientsProduits`, `ProduitsCategories`
- `CommandeService`

Ces classes n'heritent d'aucune autre classe dans le diagramme actuel.

## Coeur metier

### Commande

**Attributs**

- `idCmd: int`
- `idUser: int?`
- `idStatut: int`
- `idCanal: int`
- `numeroTicket: string`
- `dateCmd: string`
- `totalTtc: float`

**Methodes**

- `ajouterLigneProduit(ligne: CommandeProduit): void`
- `ajouterLigneMenu(ligne: CommandeMenu): void`
- `calculerTotal(): float`
- `changerStatut(statut: StatutCommande): void`

### Produit

**Attributs**

- `idProduit: int`
- `nom: string`
- `description: string`
- `prixUnitaire: float`
- `disponibilite: bool`
- `quantite: int`

**Methodes**

- `estDisponible(qte: int): bool`
- `reserverStock(qte: int): void`
- `restituerStock(qte: int): void`

### Menu

**Attributs**

- `idMenu: int`
- `nom: string`
- `prix: float`
- `disponibilite: bool`

**Methodes**

- `estDisponible(): bool`

### Utilisateur

**Attributs**

- `idUser: int`
- `idRole: int`
- `nom: string`
- `prenom: string`
- `email: string`
- `motDePasseHash: string` `private`
- `numTel: string`

**Methodes**

- `assignerRole(role: Role): void`

## References / Lookup

### Role

**Attributs**

- `idRole: int`
- `codeRole: string`
- `libelle: string`

**Methodes**

- Aucune pour le moment

### StatutCommande

**Attributs**

- `idStatut: int`
- `libelle: string`

**Methodes**

- Aucune pour le moment

### Canal

**Attributs**

- `idCanal: int`
- `libelle: string`

**Methodes**

- Aucune pour le moment

### Categorie

**Attributs**

- `idCat: int`
- `type: string`
- `description: string`

**Methodes**

- Aucune pour le moment

### Ingredient

**Attributs**

- `idIngredient: int`
- `nom: string`
- `coutUnitaire: float`
- `quantite: float`

**Methodes**

- `debiter(qte: float): void`
- `crediter(qte: float): void`
- `estDisponible(qte: float): bool`

## Classes de liaison

### CommandeProduit

**Attributs**

- `idCmd: int`
- `idProduit: int`
- `quantite: int`
- `prixUnitaire: float`
- `prixLigne: float`

**Methodes**

- `recalculerPrixLigne(): float`
- `changerQuantite(qte: int): void`

### CommandeMenu

**Attributs**

- `idCmd: int`
- `idMenu: int`
- `quantite: int`
- `taille: string`
- `prixLigne: float`

**Methodes**

- `recalculerPrixLigne(): float`
- `changerQuantite(qte: int): void`
- `changerTaille(taille: string): void`

### MenuProduit

**Attributs**

- `idMenu: int`
- `idProduit: int`
- `quantite: int`

**Methodes**

- Aucune pour le moment

### IngredientsProduits

**Attributs**

- `idIngredient: int`
- `idProduit: int`
- `quantite: float`

**Methodes**

- Aucune pour le moment

### ProduitsCategories

**Attributs**

- `idProduit: int`
- `idCat: int`

**Methodes**

- Aucune pour le moment

## Architecture POO

### ProductRepositoryInterface

**Type**

- `interface`

**Heritage / implementation**

- Interface racine
- Implementee par `DbProduitRepository`

**Methodes**

- `findAll(): array`
- `findById(id: int): Produit`
- `save(produit: Produit): void`
- `delete(id: int): bool`
- `findByCategorie(idCat: int): array`

### CommandeRepositoryInterface

**Type**

- `interface`

**Heritage / implementation**

- Interface racine
- Implementee par `DbCommandeRepository`

**Methodes**

- `findById(id: int): Commande`
- `findByTicket(ticket: string): Commande`
- `save(commande: Commande): void`
- `addLigneProduit(idCmd: int, ligne: CommandeProduit): void`
- `addLigneMenu(idCmd: int, ligne: CommandeMenu): void`
- `updateStatut(idCmd: int, idStatut: int): bool`

### DbProduitRepository

**Attributs**

- `pdo: PDO` `private`

**Heritage / implementation**

- Enfant de aucune classe
- Implemente `ProductRepositoryInterface`

**Methodes**

- `findAll(): array`
- `findById(id: int): Produit`
- `save(produit: Produit): void`
- `delete(id: int): bool`
- `findByCategorie(idCat: int): array`

### DbCommandeRepository

**Attributs**

- `pdo: PDO` `private`

**Heritage / implementation**

- Enfant de aucune classe
- Implemente `CommandeRepositoryInterface`

**Methodes**

- `findById(id: int): Commande`
- `findByTicket(ticket: string): Commande`
- `save(commande: Commande): void`
- `addLigneProduit(idCmd: int, ligne: CommandeProduit): void`
- `addLigneMenu(idCmd: int, ligne: CommandeMenu): void`
- `updateStatut(idCmd: int, idStatut: int): bool`

### CommandeService

**Attributs**

- `commandeRepo: CommandeRepositoryInterface` `private`
- `produitRepo: ProductRepositoryInterface` `private`

**Heritage / implementation**

- Enfant de aucune classe
- N'implemente aucune interface
- Depend de `CommandeRepositoryInterface` et `ProductRepositoryInterface`

**Methodes**

- `creerCommandeAnonyme(idCanal: int): Commande`
- `ajouterProduit(idCmd: int, idProduit: int, qte: int): void`
- `validerCommande(idCmd: int): void`

## Exceptions

### DomainException

**Type**

- `exception`

**Heritage / implementation**

- Classe parente de `NotFoundException`
- Classe parente de `ValidationException`

**Methodes**

- `fromMessage(message: string): DomainException`

### NotFoundException

**Type**

- `exception`

**Heritage / implementation**

- Classe enfant de `DomainException`

**Methodes**

- `forId(entity: string, id: int): NotFoundException`
- `forKey(entity: string, key: string): NotFoundException`

### ValidationException

**Type**

- `exception`

**Heritage / implementation**

- Classe enfant de `DomainException`

**Methodes**

- `forField(field: string, reason: string): ValidationException`
- `forRule(rule: string): ValidationException`
