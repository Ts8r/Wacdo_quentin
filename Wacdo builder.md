# Wacdo builder

Sauvegarde de session (resume).

## Contexte projet
- Projet: WACDO (borne de commande + back-office), MVP
- Stack: PHP 8 MVC + POO, MariaDB, HTML/CSS/JS
- Methode: micro-etapes courtes, livrables concrets
- Objectif: fluidifier la prise de commande, reduire la charge humaine, accelerer le service

## Decisions structurelles
- `COMMANDES.id_user` nullable (commande anonyme possible)
- `numero_ticket` unique
- `ROLES`, `STATUTS_COMMANDES`, `CANAUX` separes avec FK
- M:N `PRODUITS_CATEGORIES`
- backend sans framework, avec `PDO`, `CRUD`, `Repository`, `Service`, `MVC`

## Fichiers produits / mis a jour
- `wacdo/readme.md`
- `wacdo/architecture/MCD.mmd`
- `wacdo/architecture/MPD.sql`
- `wacdo/architecture/class-diagram.mmd`
- `wacdo/architecture/class-diagram-app.mmd`
- `wacdo/architecture/wacdo-class-diagram.excalidraw`

## Cloture interview BYAN
- `_byan-output/project-context-wacdo.md`
- `_byan-output/agent-spec-kim-jung-un.yaml`
- `_byan-output/interview-summary-20260305-154208.md`
- `_byan/bmb/agents/kim-jung-un.md`
- `_byan/bmb/config.yaml` mis a jour avec l'agent `kim-jung-un`

## Etat des diagrammes
- `MCD.mmd` contient uniquement du Mermaid brut
- `class-diagram.mmd` a ete reorganise par blocs:
  - coeur metier
  - references / lookup
  - classes de liaison
  - architecture POO
- les relations ont ete clarifiees:
  - `--` association metier/data
  - `<|..` implementation d'interface
  - `-->` dependance technique
  - `--|>` heritage
- les exceptions ont ete explicitees avec methodes minimales:
  - `DomainException`
  - `NotFoundException`
  - `ValidationException`
- un fichier Excalidraw a ete genere puis retravaille pour reduire les croisements:
  - `wacdo/architecture/wacdo-class-diagram.excalidraw`

## README
- le README GitHub a ete refait pour presenter:
  - le contexte du projet
  - les objectifs
  - la stack technique
  - les decisions de conception
  - la structure du projet
  - la suite prevue
  - le lien Figma

## Notes
- Le fichier `MCD.mmd` doit contenir uniquement du Mermaid brut (sans Markdown).
- Sur certaines versions Mermaid, `linkStyle` en `classDiagram` n'est pas supporte.
- Si besoin d'un diagramme avec fleches colorees, privilegier `draw.io` ou `Excalidraw`.
