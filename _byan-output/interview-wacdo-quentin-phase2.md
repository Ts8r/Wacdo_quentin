# Interview BYAN — WACDO (Phase 2: Business / Domain)

Répondez aux questions ci-dessous pour la Phase 2 (Business/Domain). Réponds succinctement ou détaille selon ce que tu sais.

1. Domaine métier et description courte (contexte commercial) :
	- Domaine: Restauration rapide / borne de commande (QSR). Objectif: permettre commandes self‑service en borne + back‑office pour gestion catalogue et suivi des commandes.

2. Principaux acteurs / rôles (au moins 5 si possible) :
	- Client (utilisateur borne, peut être anonyme)
	- Opérateur / Caissier (back‑office léger)
	- Cuisinier / Personnel de préparation
	- Administrateur / Manager (gestion produits, prix, rapports)
	- Système/API (service backend exposant endpoints pour front)

3. Glossaire — liste de 5 concepts métiers avec définition courte :
	- Commande: agrégat d'items (produits/menus) créé par un client, avec `numero_ticket`, `statut`, total.
	- Menu: offre composée (ex: menu burger + boisson) avec prix et produits associés.
	- Produit: article unitaire vendable (prix_unitaire, disponibilité, stock logique).
	- Catégorie: regroupe produits (ex: boissons, desserts) pour filtrage et affichage.
	- Statut_commande: état de la commande (en_attente, en_preparation, prête, livrée/terminée, annulée).

4. Processus principaux (flow) — décrire 3 processus critiques :
	- Prise de commande (kiosk): sélection produits/menus → validation panier → génération `numero_ticket` unique → persistence en `commandes` (id_user nullable).
	- Préparation: back‑office/kitchen poll ou webhook → changement `statut_commande` → gestion files/notifications.
	- Gestion catalogue/stock: CRUD produits/menus → propagation disponibilité → vérification stock avant validation commande (prévention sur‑vente).

5. Règles métier importantes (tarification, remises, disponibilité, stock) :
	- Unicité: `COMMANDES.numero_ticket` unique.
	- `COMMANDES.id_user` nullable pour commandes anonymes.
	- Vérifier disponibilité/quantité avant validation; bloquer commande si stock insuffisant.
	- Remises: appliquer règles simples (pour examen, définir coupon fixe ou % si demandé).
	- Disponibilité: champ `disponibilite` sur produits/menus contrôle affichage en borne.

6. Cas limites et erreurs attendues (ex: commande sans produit, stock insuffisant, paiement échoué) :
	- Panier vide → bloquer validation.
	- Stock insuffisant → message et blocage de la ligne concernée.
	- Base de données indisponible → message d'erreur et mode dégradé.
	- Doublons de `numero_ticket` → détecter et régénérer.
	- Images manquantes → afficher placeholder sans planter.

7. Contraintes réglementaires (RGPD, conservation des données, logs, paiements) :
	- RGPD: anonymisation possible (commande anonyme), minimiser stockage de données personnelles.
	- Paiement: si non implémenté, éviter stockage de données PCI; si ajouté, externaliser via PSP.
	- Logs: conserver traces d'audit basiques (création/modif commandes) pour debugging.

8. KPIs métier précis à suivre (3 priorités) :
	- Temps moyen prise de commande (borne) — objectif ex: < 60s
	- Taux de conversion / commandes par heure
	- Taux de ruptures de stock (stockouts) / commandes échouées

9. Exigences d'interface/back-office (CRUD, rapports, gestion produits, gestion commandes) :
	- CRUD produits, catégories, menus (images, prix, disponibilité).
	- Gestion statuts commandes (filtre, recherche par `numero_ticket`).
	- Rapports simples: ventes par jour, produits top ventes, commandes en cours.
	- Gestion utilisateurs/roles (roles minimal: admin, opérateur).

10. Données de test ou scénarios pour valider intégration front/back (3 scénarios précis) :
	- Scenario A: Commander anonymement 2 produits → vérifier insertion `commandes`, `commande_produit`, `numero_ticket` unique, total calculé.
	- Scenario B: Créer nouveau produit via back‑office, lier à une catégorie → vérifier affichage en borne et possibilité d'ajout au panier.
	- Scenario C: Simuler rupture de stock pour un produit puis tenter commande → vérifier blocage et message d'erreur.

Après réception de ces réponses (ou validation ci‑dessus), j'exécuterai :
 - Mapping MCD → MCT vérifié et relevé des incohérences ;
 - Checklist d'acceptance détaillée ;
 - Génération des artefacts d'examen finaux (doc + scripts d'initialisation + instructions de démo).
