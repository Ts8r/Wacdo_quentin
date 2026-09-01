# Pack Examen — WACDO (Quentin)

## 1 — Synthèse (1 page)
- Projet: WACDO — borne de commande + back-office (MVP)
- Objectif: fluidifier prise de commande, réduire charge humaine, accélérer service
- Stack: PHP 8 (sans framework), MVC+POO, MariaDB, Front: HTML/CSS/JS
- Contraintes: micro‑étapes, livrables concrets, temps limité

## 2 — Fichiers clés fournis
- `wacdo/architecture/MCD.mmd` (MCD)
- `wacdo/architecture/MPD.sql` (MPD / DDL)
- `wacdo/architecture/MLD.md` (MLD)
- `wacdo/architecture/Dictionnaire_de_données.csv`
- `bin/init_db.php` — initialisation DDL
- `bin/seed_db.php` — seed & données (categories/produits JSON)
- `config/database.php` — factory PDO
- `wacdo/categories.json`, `wacdo/produits.json` — données catalogue

## 3 — Commandes pour l'examinateur (local / Docker)
Préparer l'env (ou exécuter dans conteneur PHP):

```bash
# depuis la racine du projet
php bin/init_db.php
php bin/seed_db.php
# ou via Docker (si compose présent)
docker compose up -d --build
docker compose exec wacdo_php php bin/init_db.php
docker compose exec wacdo_php php bin/seed_db.php
```

## 4 — Checklist de validation (acceptance)
- [ ] MPD importé sans erreur (`php bin/init_db.php`)
- [ ] Données seedées correctement (`php bin/seed_db.php`) — vérifier catégories/produits
- [ ] `COMMANDES.numero_ticket` unique et `COMMANDES.id_user` nullable
- [ ] FK et contraintes conformes au MCD/MPD
- [ ] Endpoints/back-office basiques fonctionnels (CRUD produits, commandes)
- [ ] Instructions d'exécution et demo listées

## 5 — Cas d'usage pour démonstration (3 scénarios)
1. Commander en mode anonyme → créer `commande` sans `id_user` et vérifier status
2. Créer produit + lier catégorie → vérifier `produits_categories`
3. Générer ticket de commande et vérifier unicité `numero_ticket`

## 6 — Risques & recommandations rapides
- Toute affirmation de performance/security nécessite source L2 — tapez `[FC]` pour vérification
- Tester FK cascade/DELETE comportement avant sprint d'intégration
- Ajouter tests unitaires pour fonctions critiques (création commande, calcul totaux)

## 7 — Livrables à fournir
- Document synthèse (cette page)
- Export MPD.sql (fourni)
- Dictionnaire de données (CSV fourni)
- Script d'initialisation + seed (fourni)
- Instructions de démo (section 3)

---
Pack généré automatiquement par BYAN — prêt à être téléchargé ou modifié sur demande.
