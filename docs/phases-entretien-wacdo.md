# Phases de l’entretien de soutenance – WACDO

## Phase 1 – Présentation du projet (2 à 3 min)

- présenter le contexte métier ;
- expliquer le besoin de commande en ligne de restauration rapide ;
- exposer les objectifs de la plateforme.

### Questions possibles

- Quel est le besoin métier couplé à ce projet ?
- Pourquoi ce type d’application est-il pertinent ?
- Que distingue le front statique du backend PHP ?

## Phase 2 – Architecture technique (3 à 5 min)

- expliquer le rôle de chaque couche : front, backend, base, proxy, conteneurs ;
- détailler l’architecture Docker ;
- montrer la logique de routage Traefik.

### Questions possibles

- Pourquoi avoir séparé le front et le backend ?
- Quelle est la différence entre un conteneur PHP et un front statique ?
- Comment Traefik simplifie-t-il la gestion HTTPS ?

## Phase 3 – Données et logique métier (3 min)

- expliquer le rôle des tables de catégories, produits, menus, ingrédients ;
- montrer comment les données sont chargées et exploitées par les routes API.

### Questions possibles

- Comment les données sont-elles structurées ?
- Pourquoi la seed est-elle importante dans un projet de démonstration ?
- Quelles évolutions métier seraient utiles ensuite ?

## Phase 4 – Déploiement et production (3 à 5 min)

- expliquer la mise en place Docker Compose ;
- monter la logique `admin_proxy` + `letsencrypt` ;
- noter le rôle d’un hôte Traefik avec ACME.

### Questions possibles

- Qu’est-ce qui manque pour une mise en production complète ?
- Que devrions-nous configurer côté DNS et TLS ?
- Quelle serait la prochaine évolution de la stack ?

## Phase 5 – Analyse critique et améliorations (2 min)

- signaler les points forts ;
- identifier les axes d’amélioration : sécurité, tests, CI/CD, monitoring.

### Questions possibles

- Quels sont les points faibles actuels ?
- Quelles améliorations prioriseriez-vous ?
- Comment valideriez-vous la robustesse du projet ?

## Conclusion

Le projet démontre une bonne maîtrise du développement Web PHP, de l’architecture logicielle, de la conteneurisation et du déploiement moderne avec Traefik et HTTPS. Le candidat doit montrer qu’il comprend les choix technologiques et leurs implications opérationnelles.
