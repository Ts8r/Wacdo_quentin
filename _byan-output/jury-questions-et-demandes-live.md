# Pack jury WACDO — questions et demandes de modification en live

Ce document est conçu pour être donné à une autre IA ou utilisée comme base de préparation de soutenance. Il contient :
- 45 questions de jury variées
- 60 demandes de modification de code en live, couvrant de nombreux domaines

---

## 1. Questions que le jury pourrait poser (45)

### 1. Architecture et choix techniques
1. Pourquoi avez-vous choisi PHP pour le backend au lieu d’un framework plus moderne comme Laravel ou Symfony ?
2. Quels critères ont guidé votre choix de MariaDB plutôt qu’une base NoSQL ?
3. Quel est l’intérêt d’avoir choisi une architecture MVC avec repository plutôt qu’un monolithe unique ?
4. Pourquoi avez-vous choisi un front en JavaScript vanilla au lieu d’un framework front comme React ou Vue ?
5. Comment justifiez-vous le fait que le framework n’était pas imposé, mais une option de réflexion ?
6. Qu’est-ce qui a motivé le choix d’un projet orienté serveur avec API simple plutôt qu’une SPA complète ?
7. Quels sont les avantages et limites de votre architecture actuelle ?
8. Qu’aurait changé votre projet si vous aviez choisi un framework front complet ?
9. Que pensez-vous de l’équilibre entre simplicité de développement et évolutivité de votre solution ?
10. Quelle partie de votre architecture vous semble la plus robuste et laquelle la plus fragile ?

### 2. Base de données et modélisation
11. Expliquez le choix de la normalisation de votre base de données.
12. Comment gérez-vous les relations entre produits, catégories, commandes et utilisateurs ?
13. Quelles tables sont essentielles et pourquoi ?
14. Comment avez-vous traité les données de menu, ingrédients et tarifs ?
15. Quelles contraintes métier ont été représentées au niveau base de données ?
16. Comment envisageriez-vous l’ajout de promotions, offres ou remises ?
17. Comment gérez-vous les éventuelles mises à jour de stock ?
18. Que se passerait-il si un produit était supprimé alors qu’une commande passée l’utilise ?
19. Comment validez-vous l’intégrité des données avant enregistrement ?
20. Quelles sont les limites de votre modèle actuel pour une vraie exploitation commerciale ?

### 3. Sécurité et authentification
21. Que se passe-t-il si un client essaie d’accéder à des routes sans être connecté ?
22. Comment protégez-vous votre API contre les injections SQL ?
23. Pourquoi avez-vous choisi une authentification session plutôt qu’un JWT côté front ?
24. Quelles sont les règles de sécurité appliquées sur les champs du formulaire de commande ?
25. Comment gérez-vous les droits d’accès entre client, gestionnaire et administrateur ?
26. Que faire si l’utilisateur modifie le panier dans le navigateur ?
27. Comment sécurisez-vous les données sensibles telles que les identifiants et les commandes ?
28. Quelles sont les failles potentielles d’un système de commande en session uniquement ?
29. Comment pourriez-vous améliorer votre système d’authentification pour un usage réel en production ?
30. Que se passerait-il si un utilisateur tente d’envoyer un payload mal formé à l’API ?

### 4. UX, front et expérience utilisateur
31. Pourquoi avez-vous choisi de développer une interface simple et directe plutôt qu’un parcours très riche visuellement ?
32. Comment votre interface répond-elle aux besoins d’un utilisateur qui veut commander rapidement ?
33. Comment avez-vous géré les cas où le client est anonyme ou connecté ?
34. En quoi votre interface facilite-t-elle la validation de commande ?
35. Comment réagiriez-vous à un feedback disant que l’interface manque de fluidité ?
36. Quelles améliorations UX seraient prioritaires pour la prochaine version ?
37. Comment pensez-vous intégrer une prise de commande avec paiement en ligne ?
38. Est-ce que la borne est bien adaptée à un usage réel en restaurant ?
39. Quel impact a le fait d’avoir choisi un front sans framework sur la maintenabilité ?

### 5. Gestion de projet, qualité et évolution
40. Comment avez-vous validé votre travail au fur et à mesure ?
41. Quelles sont les étapes de validation que vous avez installées avant d’affirmer qu’une fonctionnalité était terminée ?
42. Comment différenciez-vous ce qui est réellement implémenté de ce qui est simplement envisagé ou préparé ?
43. Si le jury vous demandait d’ajouter une fonctionnalité complexe en une journée, comment feriez-vous ?
44. Qu’est-ce qui dans votre projet est facile à étendre et ce qui serait plus difficile à faire évoluer ?
45. Quelle serait votre prochaine amélioration majeure si vous deviez continuer ce projet pendant 3 mois ?

---

## 2. Demandes de modification de code en live du jury (60)

### 2.1 Demandes liées à l’authentification et aux clients
1. Ajoute une page de connexion client avec formulaire email/mot de passe et redirection vers la borne.
2. Ajoute une inscription client avec validation des champs et message d’erreur clair.
3. Modifie le flux pour qu’un client connecté ait son identifiant enregistré automatiquement à la commande.
4. Fais en sorte qu’un client anonyme puisse toujours commander sans être bloqué.
5. Ajoute un bouton de déconnexion proactif dans l’interface.
6. Ajoute une gestion des sessions expirées avec message explicite à l’utilisateur.
7. Empêche la création de compte avec email déjà utilisé.
8. Ajoute un garde-fou si l’utilisateur essaie d’accéder à une commande sans session valide.
9. Affiche le nom du client connecté dans le panier et dans la validation de commande.
10. Ajoute une vue “Mon compte” avec historique des commandes récentes.

### 2.2 Demandes liées aux commandes et panier
11. Ajoute un bouton “Supprimer le panier” avec confirmation.
12. Modifie le panier pour qu’il affiche le prix total en temps réel.
13. Ajoute la possibilité de modifier la quantité d’un article directement depuis le panier.
14. Empêche l’ajout d’un produit en quantité négative ou nulle.
15. Ajoute un message si le panier est vide avant validation.
16. Permet de sauvegarder une commande en brouillon avant validation.
17. Ajoute la prise en charge de plusieurs adresses de livraison ou de retrait.
18. Modifie le statut des commandes pour inclure “en préparation”, “prête”, “livrée”.
19. Transforme la commande en format plus structuré avec lignes de commande détaillées.
20. Ajoute la possibilité de fusionner plusieurs paniers en un seul pour un même client.

### 2.3 Demandes liées aux produits, menus et catalogues
21. Ajoute un filtre par catégorie dans l’affichage des produits.
22. Ajoute un tri par prix croissant et décroissant.
23. Ajoute un système de recherche sur les produits par nom.
24. Permet d’afficher les produits en stock ou en rupture.
25. Ajoute des options complémentaires sur les produits comme sauce, supplément ou taille.
26. Modifie la structure du catalogue pour intégrer des images de produit.
27. Ajoute la gestion des produits en “promotion” avec prix réduit.
28. Crée une section “recommandations du jour” dynamique.
29. Ajoute un affichage des allergènes ou des ingrédients détaillés.
30. Gère les produits indisponibles avec un badge visuel et blocage d’ajout.

### 2.4 Demandes liées à l’interface et UX
31. Modifie l’interface pour rendre la commande plus accessible sur mobile.
32. Ajoute un mode “commande rapide” sans étapes intermédiaires.
33. Ajoute une animation lors de l’ajout d’un produit au panier.
34. Améliore les messages d’erreur pour qu’ils soient compréhensibles pour un utilisateur non technique.
35. Ajoute un bouton “Retour” clair entre les étapes de commande.
36. Remplace les messages texte par une interface plus visuelle et plus lisible.
37. Ajoute une confirmation visuelle après validation de commande.
38. Modifie le style pour un rendu plus premium ou plus pro.
39. Ajoute un indicateur de progression dans le parcours de commande.
40. Ajoute un mode sombre ou un thème alternatif.

### 2.5 Demandes liées à la base de données
41. Ajoute une table pour gérer les promotions et codes promo.
42. Modifie la structure pour supporter plusieurs menus selon la période de la journée.
43. Ajoute une table pour historiser les changements de statut des commandes.
44. Ajoute une table pour stocker les logs d’activité de l’application.
45. Modifie la base pour supporter des pièces jointes ou photos de commande.
46. Ajoute un champ “note client” dans la commande.
47. Ajoute une table pour gérer les avis clients ou évaluations de commande.
48. Modifie le schéma pour gérer l’identité d’un client avec plus d’informations de profil.
49. Ajoute une gestion des produits avec variantes et tailles.
50. Ajoute une relation entre catégories et sous-catégories.

### 2.6 Demandes liées aux performances et robustesse
51. Optimise un chargement de page qui devient lent avec beaucoup de produits.
52. Ajoute une pagination ou un chargement progressif du catalogue.
53. Limite les requêtes répétées côté serveur pour améliorer la vitesse.
54. Ajoute un cache simple pour les catégories ou les produits peu volatils.
55. Rend la validation de commande plus tolérante aux erreurs réseau mineures.
56. Ajoute une gestion des erreurs backend plus explicite à l’interface.
57. Ajoute un journal des erreurs côté serveur pour faciliter le debug.
58. Automatise la réinitialisation du panier après validation de commande.
59. Ajoute une protection contre les double validations de commande.
60. Ajoute un mécanisme de reprise ou de sécurisation des commandes en cas de timeout réseau.

### 2.7 Demandes liées à l’évolution métier
61. Ajoute une gestion des commandes en ligne pour livraison à domicile.
62. Ajoute une gestion des commandes sur place et à emporter.
63. Ajoute un rôle gestionnaire avec accès au tableau de bord des commandes.
64. Ajoute un rôle administrateur avec gestion des produits et des utilisateurs.
65. Permet de gérer les jours de fermeture du restaurant.
66. Ajoute une gestion des heures d’ouverture et des créneaux de commande.
67. Ajoute une commande pour les groupes ou les tables.
68. Ajoute une logique de fidélité client avec points ou réductions.
69. Ajoute des statistiques de vente par produit et par période.
70. Permet d’exporter les commandes au format CSV ou PDF.

---

## 3. Conseils d’utilisation

- Utiliser ces questions comme base de simulation de jury oral.
- Utiliser les demandes de modification comme “spikes de refactor” ou tâches de debugging live.
- Pour une préparation plus forte, alterner :
  - question de compréhension
  - question critique
  - question de justification de choix
  - question de sécurité
  - question d’évolution
- Pour la partie “demande de modification en live”, la bonne méthode est de donner à l’IA trois éléments :
  - contexte métier
  - point exact à modifier
  - résultat attendu

---

## 4. Exemple de prompt pour l’autre IA

“Tu es un assistant de préparation de soutenance. Tu dois simuler un jury technique sur un projet de commande restaurant en PHP/MariaDB avec front JavaScript vanilla. Pose-moi 10 questions variées sur l’architecture, la base de données, la sécurité et la UX. Ensuite, donne-moi 10 demandes de modification live en français, réalistes et de niveau jury, sans code de départ, en me donnant uniquement l’objectif fonctionnel attendu.”

---

## 5. Version courte pour copier-coller

Questions : 45 variations sur choix techniques, sécurité, metier, architecture, UX, évolutivité.
Modifications live : 60 demandes réalistes sur authentification, commandes, panier, produits, interface, base de données, performances et évolution métier.

Ce paquet est prêt à être utilisé pour :
- entraînement oral
- entraînement à l’IA jurée
- préparation de réponse à des critiques techniques
- simulation d’outils de correction live
