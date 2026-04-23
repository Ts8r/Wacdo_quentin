-- MPD WACDO (MariaDB) - MVP
-- Alignement cours PHP: PDO, requetes preparees, CRUD, transactions.

CREATE DATABASE IF NOT EXISTS wacdo
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE wacdo;

CREATE TABLE roles (
  id_role INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code_role VARCHAR(50) NOT NULL,
  libelle VARCHAR(100) NOT NULL,
  UNIQUE KEY uq_roles_code_role (code_role)
) ENGINE=InnoDB;

CREATE TABLE utilisateurs (
  id_user INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_role INT UNSIGNED NOT NULL,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL,
  mot_de_passe_hash VARCHAR(255) NOT NULL,
  num_tel VARCHAR(20) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_utilisateurs_email (email),
  KEY idx_utilisateurs_id_role (id_role),
  CONSTRAINT fk_utilisateurs_roles
    FOREIGN KEY (id_role) REFERENCES roles(id_role)
    ON UPDATE RESTRICT
    ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE statuts_commandes (
  id_statut INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  libelle VARCHAR(50) NOT NULL,
  UNIQUE KEY uq_statuts_commandes_libelle (libelle)
) ENGINE=InnoDB;

CREATE TABLE canaux (
  id_canal INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  libelle VARCHAR(50) NOT NULL,
  UNIQUE KEY uq_canaux_libelle (libelle)
) ENGINE=InnoDB;

CREATE TABLE commandes (
  id_cmd INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_user INT UNSIGNED NULL,
  id_statut INT UNSIGNED NOT NULL,
  id_canal INT UNSIGNED NOT NULL,
  numero_ticket VARCHAR(30) NOT NULL,
  date_cmd DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  total_ttc DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  UNIQUE KEY uq_commandes_numero_ticket (numero_ticket),
  KEY idx_commandes_id_user (id_user),
  KEY idx_commandes_id_statut (id_statut),
  KEY idx_commandes_id_canal (id_canal),
  CONSTRAINT fk_commandes_utilisateurs
    FOREIGN KEY (id_user) REFERENCES utilisateurs(id_user)
    ON UPDATE RESTRICT
    ON DELETE SET NULL,
  CONSTRAINT fk_commandes_statuts
    FOREIGN KEY (id_statut) REFERENCES statuts_commandes(id_statut)
    ON UPDATE RESTRICT
    ON DELETE RESTRICT,
  CONSTRAINT fk_commandes_canaux
    FOREIGN KEY (id_canal) REFERENCES canaux(id_canal)
    ON UPDATE RESTRICT
    ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE menus (
  id_menu INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(120) NOT NULL,
  prix DECIMAL(10,2) NOT NULL,
  image VARCHAR(255) NULL,
  disponibilite TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE produits (
  id_produit INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(120) NOT NULL,
  description TEXT NULL,
  prix_unitaire DECIMAL(10,2) NOT NULL,
  image VARCHAR(255) NULL,
  disponibilite TINYINT(1) NOT NULL DEFAULT 1,
  quantite INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE categories (
  id_cat INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(80) NOT NULL,
  image VARCHAR(255) NULL,
  description VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE ingredients (
  id_ingredient INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(120) NOT NULL,
  cout_unitaire DECIMAL(10,2) NOT NULL,
  quantite INT UNSIGNED NOT NULL DEFAULT 0,
  UNIQUE KEY uq_ingredients_nom (nom)
) ENGINE=InnoDB;

CREATE TABLE commande_produit (
  id_cmd INT UNSIGNED NOT NULL,
  id_produit INT UNSIGNED NOT NULL,
  quantite INT NOT NULL,
  prix_unitaire DECIMAL(10,2) NOT NULL,
  prix_ligne DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id_cmd, id_produit),
  KEY idx_commande_produit_id_produit (id_produit),
  CONSTRAINT fk_commande_produit_commandes
    FOREIGN KEY (id_cmd) REFERENCES commandes(id_cmd)
    ON UPDATE RESTRICT
    ON DELETE CASCADE,
  CONSTRAINT fk_commande_produit_produits
    FOREIGN KEY (id_produit) REFERENCES produits(id_produit)
    ON UPDATE RESTRICT
    ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE commande_menu (
  id_cmd INT UNSIGNED NOT NULL,
  id_menu INT UNSIGNED NOT NULL,
  quantite INT NOT NULL,
  taille VARCHAR(20) NOT NULL,
  prix_unitaire DECIMAL(10,2) NOT NULL,
  prix_ligne DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id_cmd, id_menu),
  KEY idx_commande_menu_id_menu (id_menu),
  CONSTRAINT fk_commande_menu_commandes
    FOREIGN KEY (id_cmd) REFERENCES commandes(id_cmd)
    ON UPDATE RESTRICT
    ON DELETE CASCADE,
  CONSTRAINT fk_commande_menu_menus
    FOREIGN KEY (id_menu) REFERENCES menus(id_menu)
    ON UPDATE RESTRICT
    ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE menu_produit (
  id_menu INT UNSIGNED NOT NULL,
  id_produit INT UNSIGNED NOT NULL,
  quantite INT NOT NULL,
  PRIMARY KEY (id_menu, id_produit),
  KEY idx_menu_produit_id_produit (id_produit),
  CONSTRAINT fk_menu_produit_menus
    FOREIGN KEY (id_menu) REFERENCES menus(id_menu)
    ON UPDATE RESTRICT
    ON DELETE CASCADE,
  CONSTRAINT fk_menu_produit_produits
    FOREIGN KEY (id_produit) REFERENCES produits(id_produit)
    ON UPDATE RESTRICT
    ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE ingredients_produits (
  id_ingredient INT UNSIGNED NOT NULL,
  id_produit INT UNSIGNED NOT NULL,
  quantite INT UNSIGNED NOT NULL,
  PRIMARY KEY (id_ingredient, id_produit),
  KEY idx_ingredients_produits_id_produit (id_produit),
  CONSTRAINT fk_ingredients_produits_ingredients
    FOREIGN KEY (id_ingredient) REFERENCES ingredients(id_ingredient)
    ON UPDATE RESTRICT
    ON DELETE RESTRICT,
  CONSTRAINT fk_ingredients_produits_produits
    FOREIGN KEY (id_produit) REFERENCES produits(id_produit)
    ON UPDATE RESTRICT
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE produits_categories (
  id_produit INT UNSIGNED NOT NULL,
  id_cat INT UNSIGNED NOT NULL,
  PRIMARY KEY (id_produit, id_cat),
  KEY idx_produits_categories_id_cat (id_cat),
  CONSTRAINT fk_produits_categories_produits
    FOREIGN KEY (id_produit) REFERENCES produits(id_produit)
    ON UPDATE RESTRICT
    ON DELETE CASCADE,
  CONSTRAINT fk_produits_categories_categories
    FOREIGN KEY (id_cat) REFERENCES categories(id_cat)
    ON UPDATE RESTRICT
    ON DELETE RESTRICT
) ENGINE=InnoDB;
