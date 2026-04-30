# Preparation Jury - Wacdo Backend (Q/R)

## Q1. Pourquoi avoir choisi PHP pour ce projet ?
**Reponse :**
J'ai choisi PHP parce qu'en mode full vanilla c'etait le meilleur compromis entre simplicite, rapidite de developpement et apprentissage des fondamentaux backend.

## Q2. Pourquoi PHP plutot qu'un autre langage ?
**Reponse :**
D'autres langages etaient possibles (Node.js, Python, Go), mais dans un contexte d'examen avec temps limite, PHP permettait de livrer plus vite un backend propre (routing, validation, auth, SQL) sans dependre d'un framework.

## Q3. L'ecole a impose PHP : pourquoi selon toi ?
**Reponse :**
Pour standardiser l'evaluation, reduire la complexite d'outillage, et se concentrer sur les bases web (HTTP, session, SQL, architecture backend) avec un langage accessible aux debutants.

## Q4. Node.js est-il moins bien ?
**Reponse :**
Non. Node.js n'est pas moins bien. Il est tres bon, notamment pour l'I/O et le temps reel. Mais pour cet examen en full vanilla, PHP etait plus adapte a mes contraintes de livraison rapide et propre.

## Q5. Node.js etait-il possible pour ce projet ?
**Reponse :**
Oui, totalement possible. Mon choix de PHP etait un choix contextuel (temps, fiabilite, clarte), pas un jugement absolu sur la qualite de Node.js.

## Q6. Pourquoi full vanilla est formateur ?
**Reponse :**
Parce qu'on construit soi-meme la structure du backend (routes, controllers, acces base, erreurs, auth). Cela force a comprendre le role de chaque couche et pas seulement a utiliser des outils "magiques".

## Q7. Pourquoi Laravel plutot que Symfony pour la version framework ?
**Reponse :**
J'ai choisi Laravel car il m'a permis d'aller plus vite avec une bonne qualite. Il propose beaucoup d'outils integres (routing, validation, ORM, auth), ce qui me permet de me concentrer sur la logique metier. Symfony est excellent aussi, mais plus lourd a mettre en place dans un temps court d'examen.

## Q8. Symfony est-il moins bon que Laravel ?
**Reponse :**
Non. Symfony est tres solide, surtout pour des architectures enterprise. J'ai choisi Laravel pour un meilleur compromis vitesse/clarte/livraison dans le contexte de l'examen.

## Q9. Definitions simples a connaitre
- **Routing** : associe une URL + methode HTTP a une action de code.
- **Controller** : recoit la requete, appelle la logique metier, renvoie la reponse.
- **ORM** : mappe tables SQL et objets PHP (moins de SQL repetitif).
- **Model** : represente une entite metier (Utilisateur, Commande, Produit).
- **Migration** : versionne les changements de structure de base de donnees.
- **Seeder** : insere des donnees de test/depart.
- **Middleware** : filtre entre requete et controller (auth, logs, CORS).
- **Validation** : verifie la conformite des donnees entrantes.
- **Repository** : isole l'acces aux donnees de la logique metier.
- **API** : endpoints exposes pour communiquer avec le frontend.

## Q10. Argumentaire final (30 secondes)
J'ai choisi PHP en full vanilla pour apprendre et demonstrer les fondamentaux backend avec une livraison fiable dans le temps imparti. Puis j'ai choisi Laravel pour accelerer la phase framework sans sacrifier la structure et la qualite. Ce sont des choix adaptes au contexte du projet et de l'examen.
