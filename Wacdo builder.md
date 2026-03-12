# Wacdo builder

Sauvegarde de session (resume).

## Contexte projet
- Projet: WACDO (borne de commande + back-office), MVP
- Stack: PHP 8 MVC + POO, MariaDB, HTML/CSS/JS
- Methode: micro-etapes courtes, livrables concrets

## Decisions structurelles
- `COMMANDES.id_user` nullable (commande anonyme possible)
- `numero_ticket` unique
- `ROLES`, `STATUTS_COMMANDES`, `CANAUX` separes avec FK
- M:N `PRODUITS_CATEGORIES`

## Fichiers produits / mis a jour
- `wacdo/architecture/MCD.mmd`
- `wacdo/architecture/MPD.sql`
- `wacdo/architecture/class-diagram.mmd`
- `wacdo/architecture/class-diagram-app.mmd`

## Cloture interview BYAN
- `_byan-output/project-context-wacdo.md`
- `_byan-output/agent-spec-kim-jung-un.yaml`
- `_byan-output/interview-summary-20260305-154208.md`
- `_byan/bmb/agents/kim-jung-un.md`

## Notes
- Le fichier `MCD.mmd` doit contenir uniquement du Mermaid brut (sans Markdown).
- Sur certaines versions Mermaid, `linkStyle` en `classDiagram` n'est pas supporte.
