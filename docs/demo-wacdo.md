# Script de démonstration WACDO

## Objectif

Montrer rapidement que le projet est fonctionnel, est conteneurisé, que les données sont chargées, et que le backend est exposé comme une application prête à la production.

## Plan de démonstration (5 à 8 minutes)

### 1. Démarrage du projet

```bash
cd /home/acadenice/quentin_wacdo/Wacdo_quentin
./scripts/init.sh
```

Explication :
- création du réseau Docker `admin_proxy` si nécessaire ;
- montée des services ;
- init de la base ;
- seed des données.

### 2. Vérification technique

```bash
./scripts/test-curl.sh
```

Montrer :
- `status: ok` sur `/api/health`
- retour JSON de `/api/categories`
- validation du réseau et des endpoints

### 3. Vérification du backend

Ouvrir l’URL backend :

```text
https://quentin-wacdo.stark.a3n.fr/api/health
```

Le résultat attendu :

```json
{"status":"ok","database":"wacdo_quentin"}
```

### 4. Vérification du front

Ouvrir :

```text
https://front-quentin-wacdo.stark.a3n.fr
```

Montrer :
- page d’accueil ;
- catalogue ;
- images ;
- navigation et rendu visuel.

### 5. Explication de l’architecture

Présenter rapidement :

- PHP/Apache pour le backend
- MariaDB pour le stockage
- Apache HTTPD statique pour le front
- Traefik pour TLS/ACME
- Docker Compose pour orchestration

### 6. Point de maturité

Expliquer que le projet est prêt en local et quasi prêt pour production dès que le hôte Traefik et les domaines DNS sont branchés.

## Message de conclusion

Le projet WACDO est fonctionnel, bien structuré, et démontre une capacité de déploiement réaliste avec conteneurs, base de données et reverse proxy HTTPS.
