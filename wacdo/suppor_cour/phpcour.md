C2.1.1 — HTTP et architecture client-serveur
Ce cours s'adresse aux étudiants de la formation Développeur Web RNCP 24345, niveau B2, et couvre de manière détaillée les notions fondamentales du protocole HTTP et de l'architecture client-serveur. Il est conçu pour être lu comme un manuel pédagogique, avec des explications approfondies, des analogies, des exemples concrets, des blocs de code commentés ligne par ligne, des schémas ASCII et des exercices complets accompagnés de leurs corrigés. Le contenu rassemble et développe les connaissances utiles pour comprendre le fonctionnement d'Internet côté application et pour démarrer la programmation serveur en PHP.

Introduction générale
Le monde du web repose sur un échange de messages structurés entre machines, et la compréhension fine de ces échanges est essentielle pour tout développeur backend. Dans ce chapitre initial, nous allons poser les bases conceptuelles : qu'est-ce qu'un client, qu'est-ce qu'un serveur, comment communiquent-ils, et quelles architectures intermédiaires (proxies) interviennent entre eux. Nous donnerons des analogies pour rendre ces notions tangibles, puis nous aborderons progressivement le protocole HTTP, ses méthodes, ses codes de statut, ses en-têtes, la sécurisation via TLS/HTTPS, et enfin des bonnes pratiques REST et des outils pour tester et analyser les échanges.

Le modèle client-serveur : rôles et communication
Le modèle client-serveur est un paradigme d'architecture dans lequel des acteurs distincts remplissent des rôles complémentaires. Le "client" est l'entité qui initie une communication en formulant une demande ; le "serveur" est l'entité qui écoute, traite les demandes, et renvoie des réponses. Cette séparation des rôles permet de spécialiser les machines selon leurs missions : les clients se concentrent sur l'interface utilisateur ou l'automatisation, tandis que les serveurs se concentrent sur la gestion des ressources, la logique métier et le stockage.

Requête HTTP

Requête HTTP

Réponse HTTP

Réponse HTTP

Requête SQL

Résultats

Client
Navigateur

Internet

Serveur Web
Apache/Nginx

Base de données
MySQL

Une bonne analogie pour comprendre le modèle client-serveur est celle d'un restaurant. Le client est la personne qui entre, consulte le menu et commande un plat. Le serveur (dans le sens humain) emmène la commande à la cuisine, la cuisine prépare le plat, et le serveur ramène le plat au client. Dans cette analogie, la commande du client est la requête, la cuisine est le serveur au sens informatique, et le plat servi est la réponse. Les rôles sont distincts et spécialisés : le client ne cuisine pas, la cuisine ne prend pas la décision finale sur l'addition.

Sur le réseau, la communication se déroule généralement selon un schéma de requête-réponse synchronisé : un client envoie une requête au serveur, le serveur traite la requête et renvoie une réponse. Cette interaction s'appuie sur des couches basses (comme TCP/IP) pour acheminer les paquets, mais au niveau applicatif nous nous concentrons sur HTTP qui définit la syntaxe et la sémantique des messages échangés.

Il est important de dissocier le modèle logique du modèle physique. Plusieurs clients peuvent partager un même serveur ; un serveur peut être composé de plusieurs services répartis sur plusieurs machines physiques ou virtuelles ; un client peut être un navigateur, une application mobile, un script automatisé, ou même un autre serveur.

Proxies

Un proxy agit comme intermédiaire entre un client et un serveur. D'un point de vue technique, il intercepte les requêtes et peut les modifier, les filtrer, les mettre en cache ou les rediriger. Les proxies peuvent être utilisés pour améliorer la performance, appliquer des règles de sécurité, ou masquer des architectures internes.

Une distinction importante est celle entre proxy simple (forward proxy) et reverse proxy. Un forward proxy est utilisé par le client : le client configure son proxy pour accéder à Internet, et toutes ses requêtes passent par ce proxy. Cela permet par exemple de filtrer l'accès, d'appliquer des politiques de concordance, ou de faire de l'anonymisation. Un reverse proxy, quant à lui, est positionné devant un ou plusieurs serveurs et sert de point d'entrée unique pour les clients. Le reverse proxy distribue les requêtes vers les serveurs applicatifs (load balancing), peut faire de la terminaison TLS, mettre en cache des réponses ou appliquer des règles de sécurité (WAF).

Analogie : imaginez un grand hôtel (le site web) avec une porte d'entrée unique où un concierge (reverse proxy) oriente les visiteurs vers les bons services (réception, restaurant, spa). Les visiteurs ne connaissent pas directement la configuration intérieure ; ils passent par le concierge.

Exemple concret : Nginx ou HAProxy est souvent utilisé comme reverse proxy pour répartir la charge entre plusieurs instances d'une application, gérer les certificats TLS et servir des fichiers statiques. Squid peut être utilisé comme forward proxy pour filtrer et mettre en cache des contenus côté client.

Communication client-serveur détaillée
Les échanges sont asynchrones au sens applicatif (un client peut émettre plusieurs requêtes successives), mais chaque requête individuelle suit un cycle synchronisé : émettre, attendre, recevoir. Le cycle requête-réponse s'appuie sur une convention : le client doit identifier la ressource cible via une URI, déclarer une méthode (par ex. GET) et éventuellement inclure un corps ou des en-têtes. Le serveur reçoit la requête, l'interprète, applique sa logique métier et renvoie un message de réponse structuré.

La robustesse de la communication repose sur plusieurs éléments : la gestion des erreurs, la définition claire de la sémantique des méthodes, la capacité à authentifier et autoriser les demandes, et la gestion des états quand l'application est distribuée. Nous reviendrons sur ces aspects en détail.

Le protocole HTTP en détail : cycle requête-réponse et structure des messages
HTTP (Hypertext Transfer Protocol) est un protocole de la couche application qui définit comment des messages sont formatés et transmis entre clients et serveurs. Son évolution a abouti à plusieurs versions (HTTP/1.0, HTTP/1.1, HTTP/2, HTTP/3) qui optimisent la performance et ajoutent des fonctionnalités, mais les concepts de base de requête et de réponse restent.

Structure d'une requête HTTP

Une requête HTTP contient plusieurs parties : la ligne de requête (request-line), les en-têtes (headers), une ligne vide, et éventuellement un corps (body). La request-line contient la méthode, la cible (URI) et la version du protocole. Par exemple : "GET /index.html HTTP/1.1".

Les en-têtes sont des paires clé: valeur qui fournissent un contexte (User-Agent, Accept, Host, Content-Type, Authorization, etc.). La ligne vide sépare les en-têtes du corps. Le corps contient les données envoyées par le client (par exemple pour une requête POST) et peut être encodé et typé par l'en-tête Content-Type.

Exemple de requête HTTP (texte brut):

GET /articles/42 HTTP/1.1
Host: example.com
User-Agent: Mozilla/5.0 (compatible; ExempleBot/1.0)
Accept: text/html,application/xhtml+xml

Explication ligne par ligne : - "GET /articles/42 HTTP/1.1" : méthode GET sur la ressource "/articles/42" avec la version HTTP/1.1. - "Host: example.com" : obligatoire en HTTP/1.1, indique le nom d'hôte virtuel ciblé. - "User-Agent: ..." : identifie le client. - "Accept: ..." : indique les types MIME acceptés.

Structure d'une réponse HTTP

La réponse HTTP comprend une ligne d'état (status-line), des en-têtes, une ligne vide, et un corps optionnel. La status-line contient la version, un code numérique et une raison textuelle, par exemple "HTTP/1.1 200 OK".

Un exemple de réponse :

HTTP/1.1 200 OK
Content-Type: text/html; charset=utf-8
Content-Length: 137

<html>
  <body>
    <h1>Article 42</h1>
    <p>Contenu de l'article...</p>
  </body>
</html>
Chaque en-tête a une utilité : Content-Type précise le type de représentation, Content-Length la taille en octets, Set-Cookie permet de stocker un cookie côté client, etc.

Cycle détaillé requête-réponse

Le client établit une connexion TCP (ou utilise une connexion HTTP/2 multiplexée) vers le serveur.
Le client envoie la request-line et les en-têtes, puis un éventuel corps.
Le serveur reçoit la requête, la parse et exécute la logique associée à la ressource demandée.
Le serveur construit une response-line, ajoute les en-têtes nécessaires, et envoie le corps de la réponse.
Selon la version d'HTTP et les en-têtes (Connection: keep-alive), la connexion peut être réutilisée pour d'autres requêtes ou fermée.
Avec HTTP/2 et HTTP/3, le transport sous-jacent change (multiplexage sur une seule connexion, ou usage de QUIC pour HTTP/3), mais la logique applicative reste structurée en requêtes et réponses indépendantes.

Serveur Web
Client (Navigateur)
Serveur Web
Client (Navigateur)
Traitement de la requête
Requête HTTP (GET /index.html)
Réponse HTTP (200 OK + HTML)
Requête HTTP (GET /style.css)
Réponse HTTP (200 OK + CSS)
Requête HTTP (GET /logo.png)
Réponse HTTP (200 OK + Image)
Les méthodes HTTP : GET, POST, PUT, DELETE, PATCH — sémantique et idempotence
Les méthodes HTTP définissent l'intention du client vis-à-vis de la ressource ciblée. Chaque méthode a une sémantique et des garanties concernant l'effet sur l'état du serveur.

GET

La méthode GET sert à récupérer une représentation d'une ressource. Elle ne doit pas modifier l'état côté serveur (elle est dite "safe") et est généralement mise en cache. Les requêtes GET doivent être idempotentes (les mêmes requêtes répétées n'ont pas d'effet supplémentaire), même si l'idempotence est une propriété distincte de la "safety".

Exemple : récupérer un article via GET /articles/42 renvoie la représentation HTML ou JSON de l'article.

POST

La méthode POST est utilisée pour soumettre des données au serveur, qui peuvent provoquer un changement d'état ou créer une nouvelle ressource. POST n'est pas idempotente par nature : répéter une requête POST peut créer plusieurs ressources.

Exemple : poster un formulaire de contact qui crée une entrée dans la base de données.

PUT

PUT remplace entièrement la ressource ciblée par la représentation fournie. PUT est idempotente : envoyer la même requête PUT plusieurs fois produit le même résultat que l'envoyer une seule fois.

Exemple : PUT /profiles/5 avec un JSON décrivant un profil remplace le profil numéro 5.

DELETE

DELETE supprime la ressource identifiée par la cible. DELETE est idempotente : supprimer plusieurs fois la même ressource aboutira à la même situation (la ressource n'existe plus). Toutefois, la réponse peut varier (204 No Content, 404 Not Found si déjà supprimée).

PATCH

PATCH permet d'appliquer des modifications partielles à une ressource. Contrairement à PUT, PATCH peut être non idempotente ou idempotente selon la sémantique du patch appliqué, mais en pratique on tend à concevoir des PATCH idempotents lorsque c'est logique.

Read

Create

Update

Delete

Update

Méthodes HTTP

GET

POST

PUT

DELETE

PATCH

Récupérer une ressource

Créer une ressource

Remplacer complètement

Supprimer une ressource

Modifier partiellement

Idempotence et sécurité

Idempotence signifie que plusieurs exécutions successives de la même opération produisent le même effet que la première exécution. Cela a des implications pratiques : les clients peuvent réessayer une requête idempotente en cas d'échec sans risque de duplication d'effet. Les méthodes safe (comme GET) ne modifient pas l'état et sont en principe utilisables sans effet de bord.

Exemples concrets

GET /cart/items pour lire le contenu du panier.
POST /orders pour créer une commande (chaque POST peut créer une nouvelle commande).
PUT /users/123 pour remplacer complètement le profil de l'utilisateur 123.
PATCH /users/123 pour changer uniquement l'adresse e-mail.
DELETE /sessions/abc pour supprimer une session authentifiée.
Les codes de statut HTTP : classes et cas d'usage
Les codes de statut HTTP sont des nombres à trois chiffres qui informent le client du résultat de sa requête. Ils sont organisés en classes : 1xx, 2xx, 3xx, 4xx, 5xx. Chaque code a une signification précise et des usages concrets.

1xx — Informations

Les codes 1xx sont des réponses informatives (par exemple 100 Continue) et sont rarement manipulées directement par les développeurs d'applications. Elles servent à signaler l'avancement d'une transaction en cours.

2xx — Succès

Les codes 2xx indiquent que la requête a été traitée avec succès. Le plus courant est 200 OK. D'autres incluent 201 Created (lorsqu'une ressource a été créée), 202 Accepted (requête acceptée pour traitement asynchrone), et 204 No Content (succès sans corps de réponse).

Exemple concret : après un POST /orders qui a créé une commande, le serveur renvoie souvent 201 Created et fournit un en-tête Location pointant vers l'URL de la nouvelle ressource.

3xx — Redirections

Les codes 3xx indiquent que le client doit effectuer une autre action pour compléter la requête, comme suivre une redirection. 301 Moved Permanently et 302 Found (historique) sont courants. 307 Temporary Redirect et 308 Permanent Redirect préservent la méthode HTTP d'origine lorsqu'ils redirigent.

Pratique : on utilise 301 pour indiquer que l'URL a changé définitivement (utile pour le SEO) et 302 ou 307 pour les redirections temporaires.

4xx — Erreurs client

Les codes 4xx signalent un problème côté client : par exemple 400 Bad Request signifie que la requête était mal formée, 401 Unauthorized indique que l'authentification est requise ou invalide, 403 Forbidden signifie que le serveur comprend la requête mais refuse l'accès, et 404 Not Found indique que la ressource n'existe pas.

Exemple : si un client tente d'accéder à /admin sans être authentifié, le serveur renverra 401 ou 403 selon le contexte.

5xx — Erreurs serveur

Les codes 5xx indiquent que le serveur a rencontré une condition d'erreur lors du traitement. 500 Internal Server Error est générique. 502 Bad Gateway et 503 Service Unavailable sont souvent vus dans des architectures distribuées et indiquent que le serveur frontal (reverse proxy) n'a pas pu obtenir une réponse correcte du serveur en amont ou que le service est indisponible.

Cas pratiques et recommandations

Renvoyer 201 Created quand une création a réussi, avec Location: /resource/{id}.
Utiliser 400 Bad Request pour des erreurs de validation de données du client.
Utiliser 422 Unprocessable Entity pour indiquer que la syntaxe est correcte mais que la sémantique échoue (validation métier), surtout en API REST.
Utiliser 429 Too Many Requests pour la limitation de débit (rate limiting).
Les en-têtes HTTP importants
Les en-têtes HTTP jouent un rôle central car ils transportent des métadonnées et des directives sur la requête ou la réponse. Nous allons détailler certains des en-têtes les plus importants : Content-Type, Authorization, Cache-Control et CORS, avec des exemples et bonnes pratiques.

Content-Type

L'en-tête Content-Type indique le type MIME de la représentation présente dans le corps. Pour une API REST qui renvoie du JSON, on utilisera "Content-Type: application/json; charset=utf-8". Pour des formulaires encodés en URL on verra "application/x-www-form-urlencoded" et pour l'envoi de fichiers multipart/form-data.

Il est essentiel que le serveur fournisse un Content-Type correct pour que le client interprète correctement la réponse et applique le bon décodage. Inversement, le client doit envoyer le bon Content-Type pour que le serveur sache comment parser le corps.

Authorization

L'en-tête Authorization transporte les informations d'authentification. Le schéma le plus courant pour les API REST modernes est Bearer token : "Authorization: Bearer ". D'autres schémas existent, comme Basic (base64 du couple login:motdepasse), Digest, ou OAuth.

Important : ne jamais envoyer de jetons sensibles via des paramètres d'URL lorsqu'on peut éviter, car les URL peuvent apparaître dans les logs et être plus exposées.

Cache-Control

Cache-Control est utilisé pour contrôler le comportement de cache des réponses HTTP. Il permet d'indiquer aux caches intermédiaires et aux navigateurs comment stocker ou invalider une ressource. Par exemple, "Cache-Control: max-age=3600, public" indique que la ressource peut être mise en cache pendant 1 heure.

La bonne utilisation des en-têtes de cache peut améliorer significativement les performances et diminuer la charge sur le serveur.

CORS (Cross-Origin Resource Sharing)

CORS est un mécanisme qui permet à un serveur d'autoriser ou de refuser des requêtes cross-origin initiées depuis des scripts exécutés dans le navigateur. Par défaut, une page web chargée depuis origin A (scheme+host+port) ne peut pas appeler des ressources sur origin B. Le serveur B doit renvoyer des en-têtes spécifiques comme "Access-Control-Allow-Origin" pour autoriser ces appels.

Exemple : pour autoriser toutes les origines, le serveur peut renvoyer "Access-Control-Allow-Origin: *". Pour autoriser uniquement une origine spécifique, on renverra "Access-Control-Allow-Origin: https://client.example.com".

Les requêtes dites "préflight" (OPTIONS) permettent au navigateur de vérifier si la requête cross-origin est acceptée avant d'envoyer les informations sensibles.

Autres en-têtes utiles

Accept: types MIME acceptés par le client.
Accept-Encoding: algorithmes de compression acceptés (gzip, br, deflate).
Set-Cookie: stocke des cookies côté client avec directives (Secure, HttpOnly, SameSite).
Location: utilisée pour indiquer l'URL d'une ressource nouvellement créée (201) ou pour redirections.
HTTPS et TLS : handshake, certificats, différences avec HTTP
HTTP est un protocole texte non chiffré. HTTPS est HTTP sur TLS (anciennement SSL) : le canal entre client et serveur est chiffré et authentifié. Les objectifs principaux de TLS sont la confidentialité (personne ne peut lire le contenu), l'intégrité (les données ne peuvent pas être modifiées à l'insu) et l'authentification du serveur (s'assurer que le client parle au bon serveur via les certificats).

Handshake TLS

Le handshake TLS est le processus initial d'établissement d'une session sécurisée. En simplifiant, voici les étapes essentielles :

ClientHello : le client propose des versions, suites cryptographiques et envoie un nonce (random).
ServerHello : le serveur choisit la suite cryptographique et envoie son certificat (cert)
Le client vérifie le certificat (chaîne de confiance, signature, validité). Si valide, il génère un secret pré-master, le chiffre avec la clé publique du serveur et l'envoie (ou utilise des échanges Diffie-Hellman pour un secret partagé sans chiffrage direct).
Les deux parties dérivent les clés de session à partir du secret et des nonces.
Les messages suivants sont chiffrés avec les clés négociées.
Les détails varient selon les versions (TLS 1.2 vs TLS 1.3). TLS 1.3, par exemple, simplifie et accélère le handshake, réduit les allers-retours et améliore la sécurité.

Certificats

Un certificat X.509 lie une clé publique à une identité (par exemple un nom de domaine). Les certificats sont signés par une autorité de certification (CA) reconnue, qui confirme que le propriétaire du domaine a le droit d'utiliser la clé publique. Les clients (navigateurs) contiennent une liste d'autorités de confiance.

Let's Encrypt a démocratisé l'obtention de certificats SSL/TLS gratuits et automatisés, facilitant le déploiement généralisé d'HTTPS.

Différences entre HTTP et HTTPS

Confidentialité : HTTPS chiffre le trafic, HTTP ne le fait pas.
Intégrité : HTTPS empêche la modification silencieuse du contenu pendant le transport.
Authentification : HTTPS permet de vérifier l'identité du serveur via des certificats.
Pratique : toujours utiliser HTTPS en production, même pour des API qui ne semblent "pas sensibles", car les cookies, tokens ou données échangées peuvent être interceptés via du MITM si non chiffrés.

Principes REST : ressources, représentations, HATEOAS et bonnes pratiques d'URI
REST (Representational State Transfer) est un style d'architecture pour les services web qui repose sur des principes simples : ressources identifiables via des URI, utilisation des méthodes HTTP pour manipuler ces ressources, recours aux représentations (JSON, XML) pour transporter l'état, et communication stateless.

GET /api/products

POST /api/products

PUT /api/products/42

DELETE /api/products/42

200 + JSON

201 Created

204 No Content

Client

API REST

Ressources et représentations

Une ressource est un concept ou un objet manipulable par l'API (par exemple un utilisateur, un article, un commentaire). Une représentation est la manière dont la ressource est rendue (par ex. JSON). Une ressource peut avoir plusieurs représentations (HTML pour navigateur, JSON pour client API), déterminées par l'en-tête Accept.

URIs et bonnes pratiques

Les URI doivent être claires, hiérarchiques et refléter les ressources. Exemples : "/articles", "/articles/42", "/users/123/orders". Évitez les verbes dans les URI ; utilisez les méthodes HTTP pour exprimer l'action (par ex. POST /orders pour créer).

HATEOAS

HATEOAS (Hypermedia as the Engine of Application State) est un principe selon lequel les réponses contiennent des liens fournissant les actions possibles pour une ressource, guidant ainsi le client dans sa navigation de l'API. Par exemple, la représentation JSON d'une commande pourrait inclure un lien "cancel" si la commande est annulable.

Statelessness et sessions

REST préconise que chaque requête contient toutes les informations nécessaires au serveur pour la traiter (stateless). Cela facilite la scalabilité, car n'importe quelle instance peut traiter la requête. Les sessions d'état côté serveur contredisent ce principe, mais des compromis existent : on peut utiliser des tokens JWT (stateless) ou stocker l'état dans une base partagée.

Exemples et anti-patrons

Evitez d'exposer les détails d'implémentation (par ex. id internes) lorsque ce n'est pas nécessaire.
Préférez des ressources nudges : /products/23 plutôt que /getProduct?id=23.
Utilisez des codes de statut appropriés et retournez des messages d'erreur standardisés en JSON pour les API.
Outils pratiques : DevTools, curl, Postman
Analyser et tester les échanges HTTP est essentiel. Les outils principaux sont :

DevTools Network panel (navigateur) : permet d'inspecter les requêtes et réponses, voir les en-têtes, le corps, les timings et les erreurs. C'est indispensable pour debugger côté front-end et comprendre la charge de ressources.
curl : outil en ligne de commande pour envoyer des requêtes HTTP. Il est scriptable et très utile pour tester des endpoints rapidement.
Postman ou Insomnia : interfaces graphiques pour construire et exécuter des requêtes, gérer des collections, et automatiser des tests.
Exemples d'utilisation de curl (avec commentaires ligne par ligne) :

# curl -i -X GET 'https://api.example.com/articles/42'
# -i : affiche les en-têtes de la réponse
# -X GET : méthode GET (souvent implicite)
curl -i -X GET 'https://api.example.com/articles/42'

# Exemple POST avec JSON
curl -i -X POST 'https://api.example.com/articles' \
  -H 'Content-Type: application/json' \
  -d '{"title":"Mon article","content":"Contenu"}'
# -H : ajoute un en-tête
# -d : définit le corps de la requête
Postman permet d'enregistrer des variables d'environnement, de tester des scénarios et d'automatiser des suites de tests. DevTools, quant à lui, permet de reproduire des requêtes réalisées depuis une page web et de copier la commande curl correspondante.

Introduction à PHP côté serveur : premier script, $_GET, $_POST
PHP est un langage de script fréquemment utilisé côté serveur. Il s'intègre facilement à un serveur web comme Apache ou Nginx via PHP-FPM. Nous allons écrire un premier script PHP simple, expliquer comment récupérer des paramètres en GET et POST, et détailler des considérations de sécurité élémentaires.

Premier script PHP

Créez un fichier index.php avec le contenu suivant :

<?php
// index.php

// Affiche une en-tête HTML minimale
header('Content-Type: text/html; charset=utf-8');

// Affiche un message simple au navigateur
echo "<!doctype html>\n"; // début du document HTML
echo "<html><head><meta charset='utf-8'><title>Exemple PHP</title></head><body>\n"; // balises head et body
echo "<h1>Bienvenue</h1>\n"; // titre

// Récupère un paramètre 'name' en GET
if (isset($_GET['name'])) {
    // Affiche un message personnalisé en échappant la valeur pour éviter XSS
    $name = htmlspecialchars($_GET['name'], ENT_QUOTES, 'UTF-8');
    echo "<p>Bonjour, " . $name . "</p>\n"; // message personnalisé
} else {
    echo "<p>Bonjour, visiteur anonyme</p>\n"; // message par défaut
}

echo "</body></html>\n"; // fin du document HTML
Explication ligne par ligne : - <?php : ouverture du bloc PHP. - header('Content-Type: text/html; charset=utf-8'); : envoie un en-tête HTTP pour indiquer le type de contenu. - echo : imprime du texte dans la réponse HTTP. - isset($_GET['name']) : vérifie si le paramètre 'name' est présent dans la requête GET. - htmlspecialchars(... ) : protège contre les attaques XSS en échappant les caractères spéciaux.

Récupérer des données POST

Pour manipuler des données publiques envoyées via un formulaire POST, utilisez la superglobale $_POST. Exemple :

<?php
// traitement_post.php
header('Content-Type: application/json; charset=utf-8');

// Attendre des champs 'title' et 'content' en POST
if (!isset($_POST['title']) || !isset($_POST['content'])) {
    http_response_code(400); // 400 Bad Request
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$title = trim($_POST['title']);
$content = trim($_POST['content']);

// Simuler une insertion et retourner un identifiant
$newId = rand(1000, 9999);
http_response_code(201); // Created
header('Location: /articles/' . $newId);

echo json_encode(['id' => $newId, 'title' => $title]);
Commentaires ligne par ligne : - header('Content-Type: application/json; charset=utf-8'); : on renvoie du JSON. - if (!isset($_POST['title']) ...) : validation basique. - http_response_code(400); : envoie un code d'erreur. - rand(1000, 9999); : simule la génération d'un identifiant. - header('Location: /articles/' . $newId); : indique l'URL de la ressource créée.

Sécurité basique

Toujours valider et assainir les entrées utilisateurs.
Utiliser des requêtes préparées pour éviter les injections SQL.
Échapper les sorties dans un contexte HTML pour prévenir XSS.
Ne jamais exposer des informations sensibles dans les messages d'erreur retournés au client.
TP complet : analyser des requêtes HTTP, tester une API REST, écrire un premier script PHP
Le but de ce TP est de mettre en pratique les connaissances acquises : inspecter des requêtes, utiliser curl/Postman pour appeler une API REST, et écrire un script PHP simple qui répond à des requêtes GET et POST.

Énoncé

Inspecter une requête envoyée par votre navigateur vers une page HTML qui charge des ressources (images, CSS, JS). Utilisez DevTools pour lister les requêtes et expliquer pour chacune son type, sa méthode, son code de statut et les en-têtes importants.
Avec curl, envoyer une requête POST à une API fictive locale (on supposera que le serveur PHP tourne sur localhost) pour créer une ressource. Récupérer l'en-tête Location et vérifier que la ressource existe via une requête GET.
Écrire un script PHP 'api.php' capable de gérer :
GET /api/items : retourner la liste des items en JSON.
GET /api/items/{id} : retourner un item unique ou 404.
POST /api/items : créer un item et renvoyer 201 avec Location.
Correction et guide pas à pas

Analyse avec DevTools : ouvrez l'onglet Network, rechargez la page, triez par Type. Identifiez les requêtes document, script, stylesheet, image, xhr/fetch. Pour chaque requête XHR (ou fetch), regardez les en-têtes Accept, Content-Type, Authorization s'il y en a, et le code de statut. Notez le temps de chargement et la taille de la réponse.

Exemple d'utilisation de curl :

# Création d'un item via POST
curl -i -X POST 'http://localhost/api.php/items' \
  -H 'Content-Type: application/json' \
  -d '{"name":"Widget","price":9.99}'

# Supposons que la réponse contient 'Location: /api.php/items/123'
# Vérification via GET
curl -i -X GET 'http://localhost/api.php/items/123'
Exemple complet d'implémentation PHP (fichier api.php) :
<?php
// api.php

// Fichier: api.php
// But: fournir un micro-endpoint RESTful pour démonstration.

header('Content-Type: application/json; charset=utf-8');

// Simulation d'une base de données en mémoire
$items = [
    1 => ['id' => 1, 'name' => 'Widget', 'price' => 9.99],
    2 => ['id' => 2, 'name' => 'Gadget', 'price' => 12.5],
];

// Parsing de l'URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Retirer les paramètres de requête
$path = parse_url($uri, PHP_URL_PATH);
$segments = array_values(array_filter(explode('/', $path)));

// Logique de routage simple
if ($method === 'GET' && preg_match('#^/api.php/items$#', $path)) {
    // Retourne la liste des items
    echo json_encode(array_values($items));
    exit;
}

if ($method === 'GET' && preg_match('#^/api.php/items/(\d+)$#', $path, $m)) {
    $id = (int)$m[1];
    if (isset($items[$id])) {
        echo json_encode($items[$id]);
        exit;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
        exit;
    }
}

if ($method === 'POST' && preg_match('#^/api.php/items$#', $path)) {
    // Récupérer le corps JSON
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);
    if (!is_array($data) || !isset($data['name']) || !isset($data['price'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid payload']);
        exit;
    }
    // Simuler création
    $newId = rand(100, 999);
    $item = ['id' => $newId, 'name' => $data['name'], 'price' => (float)$data['price']];
    // Normalement, on insèrerait en base; ici on retourne juste la ressource
    http_response_code(201);
    header('Location: /api.php/items/' . $newId);
    echo json_encode($item);
    exit;
}

// Si aucune route ne correspond
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
Commentaires ligne par ligne importants : - header('Content-Type: application/json; charset=utf-8'); : indique que la réponse est du JSON. - $method = $_SERVER['REQUEST_METHOD']; : méthode HTTP utilisée. - parse_url(...); et explode('/', ...) : découpage simple de l'URI. - file_get_contents('php://input'); : lecture du corps brut pour récupérer le JSON envoyé. - http_response_code(201); header('Location: ...'); : conformité REST pour la création.

Tests et vérifications

Utiliser curl ou Postman pour envoyer POST, récupérer Location, puis GET pour vérifier la création. Vérifier également les codes 404 et 400 en envoyant des chemins inexistants ou des payloads invalides.

Schémas ASCII illustrant le cycle requête/réponse
Voici un schéma ASCII simple représentant le cheminement d'une requête HTTP et de sa réponse :

Client                                     Server
  |                                           |
  |----------- TCP handshake ---------------->|
  |                                           |
  |-- HTTP Request: GET /resource HTTP/1.1 -->|
  |  Host: example.com                        |
  |  Accept: application/json                 |
  |                                           |
  |<-- HTTP Response: 200 OK -----------------|
  |   Content-Type: application/json          |
  |   Content-Length: 123                     |
  |   { "id": 1, "name": "Ex" }          |
  |                                           |
  |----------- Close or keep-alive ---------->|
Schéma avec reverse proxy entre client et serveur :

Client --> Reverse Proxy (Nginx) --> Backend App Server
  |              |                      |
  |-- request -->|-- request ---------->|
  |<-- response <-|<-- response --------|
Ce schéma montre que le reverse proxy peut agir comme terminus pour TLS, cache et répartiteur de charge.

Exercices intégrés avec corrigés
Exercice 1 — Identifier les parties d'une requête

Énoncé : On vous fournit la requête suivante. Identifiez la request-line, chaque en-tête et le corps, et expliquez le rôle de chaque élément.

POST /api/login HTTP/1.1
Host: shop.example.com
Content-Type: application/json
Content-Length: 47
User-Agent: TestClient/1.0

{"username":"alice","password":"secret"}
Corrigé détaillé : - Request-line : POST /api/login HTTP/1.1. Indique que la méthode est POST, la ressource est /api/login et la version est HTTP/1.1. - Host: shop.example.com. Permet de cibler le serveur virtuel adapté. - Content-Type: application/json. Indique le type de données dans le corps (JSON). - Content-Length: 47. Indique la taille du corps en octets. - User-Agent: identifie le client. - Corps : {"username":"alice","password":"secret"}. Contient les données d'authentification. Note de sécurité : ne jamais logguer de mots de passe en clair.

Exercice 2 — Méthodes et idempotence

Énoncé : Pour chaque méthode suivante, dites si elle est safe, idempotente, et donnez un exemple d'usage : GET, POST, PUT, DELETE, PATCH.

Corrigé : - GET : safe, idempotente. Usage : récupérer une page ou une ressource. - POST : non safe, non idempotente (par défaut). Usage : créer une ressource ou soumettre un formulaire. - PUT : non safe, idempotente. Usage : remplacer complètement une ressource. - DELETE : non safe (modifie l'état) mais idempotente. Usage : supprimer une ressource. - PATCH : non safe, idempotence dépend de l'opération ; usage : modification partielle.

Exercice 3 — En-têtes et CORS

Énoncé : Vous développez une API sur api.example.com. Une application frontend sur app.client.com exécute des fetch vers votre API et obtient des erreurs CORS. Quelles en-têtes devrez-vous renvoyer côté serveur pour autoriser l'origine app.client.com et permettre l'envoi de cookies ? Décrivez également les risques et précautions.

Corrigé : - En-têtes à renvoyer : - Access-Control-Allow-Origin: https://app.client.com - Access-Control-Allow-Credentials: true (pour autoriser l'envoi de cookies) - Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS (selon besoin) - Access-Control-Allow-Headers: Content-Type, Authorization (selon les en-têtes envoyés) - Précautions : n'autorisez pas * avec Allow-Credentials: true. Vérifiez l'origine côté serveur et n'exposez pas des endpoints sensibles à des origines non fiables. Utilisez des tokens CSRF si vous autorisez les cookies pour protéger contre les attaques de type CSRF.

Exercice 4 — TLS handshake expliqué

Énoncé : Décrivez, en étapes simples, ce qui se passe lors d'un handshake TLS entre un navigateur et un serveur web.

Corrigé : 1. Le navigateur envoie un ClientHello indiquant les suites cryptographiques possibles. 2. Le serveur répond avec un ServerHello, choisit une suite et renvoie son certificat. 3. Le navigateur vérifie le certificat (chaîne de confiance, date, nom de domaine). 4. Si OK, le navigateur négocie un secret partagé (via clé publique du serveur ou Diffie-Hellman), et les deux parties dérivent les clés de session. 5. Les échanges suivants sont chiffrés.

Exercice 5 — Écrire un endpoint PHP

Énoncé : Écrivez un script PHP qui accepte POST /contact avec des champs 'email' et 'message', valide que 'email' ressemble à une adresse et renvoie 201 avec JSON contenant ok=true et id demandé.

Corrigé (exemple) :

<?php
// contact.php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Récupération du body JSON
$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (!is_array($data) || !isset($data['email']) || !isset($data['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing fields']);
    exit;
}

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid email']);
    exit;
}

// Simuler enregistrement
$id = rand(1000,9999);
http_response_code(201);
header('Location: /contact.php/' . $id);
echo json_encode(['ok' => true, 'id' => $id]);
Commentaires : on utilise filter_var pour valider l'email; on renvoie des codes de statut appropriés; on lit le corps brut pour permettre JSON.

Annexes : bonnes pratiques, mise en production et pièges courants
Authentification et sécurité

Préférez des schémas d'authentification robustes (OAuth2, JWT, sessions sécurisées).
Protégez les endpoints sensibles par l'usage systématique de HTTPS.
Ne stockez jamais de mots de passe en clair ; utilisez des fonctions de hachage adaptatif (bcrypt, Argon2).
Performance et cache

Configurez correctement les en-têtes Cache-Control et ETag pour tirer parti des caches.
Utilisez un reverse proxy pour délester les serveurs applicatifs des tâches de SSL/TLS et de cache.
Surveillance et résilience

Surveillez les métriques (latence, taux d'erreur, saturation CPU/mémoire).
Implémentez des stratégies de retry et circuit breaker pour les appels entre services.
PIÈGES courants

Confondre idempotence et sécurité: une méthode idempotente peut quand même être protégée par authentification.
Exposer des informations sensibles via des messages d'erreur.
Mal configurer CORS et ainsi ouvrir des vulnérabilités.
Ressources complémentaires
RFC 7230–7235 (HTTP/1.1)
RFC 7540 (HTTP/2)
RFC 9110 (HTTP Semantics)
Documentation PHP : https://www.php.net/manual/fr/
Let's Encrypt : https://letsencrypt.org/
MDN Web Docs — HTTP : https://developer.mozilla.org/fr/docs/Web/HTTP
Fin du cours.



ntroduction
Ce document fait partie du module M2.1 Langages Serveur (56h) pour la formation Développeur Web RNCP 24345 (niveau B2). Il couvre les séances 3 et 4, en approfondissant l'utilisation des formulaires, la validation, la gestion de fichiers, les sessions et la connexion à une base de données MySQL via PDO. Le contenu est rédigé comme un cours détaillé (type livre/textbook), en français, avec des explications approfondies, des exemples de code PHP complets et commentés, ainsi que des exercices avec énoncés et corrigés.

Ce fichier remplace toute version antérieure et contient l'intégralité du cours demandé.

Formulaires HTML et PHP : GET vs POST et superglobales
Les formulaires HTML sont le principal moyen par lequel un client (navigateur) envoie des données à un serveur web. En PHP, les données envoyées par un formulaire sont accessibles via des variables superglobales : $_GET, $_POST et $_REQUEST (et parfois $_FILES pour les fichiers). Comprendre la différence entre les méthodes GET et POST est essentiel :

GET : les données sont ajoutées à l'URL (query string). Avantages : simple, partageable via URL, utile pour des filtres ou recherches. Inconvénients : longueur limitée selon le navigateur/serveur, données visibles dans l'URL, pas adapté aux données sensibles.

POST : les données sont envoyées dans le corps de la requête HTTP. Avantages : pas visible dans l'URL, plus de données possibles, adapté aux formulaires d'authentification ou d'upload. Inconvénients : pas bookmarkable directement, nécessite parfois plus de précautions côté serveur.

En PHP :

$_GET : tableau associatif contenant les paramètres envoyés via la méthode GET.
$_POST : tableau associatif contenant les paramètres envoyés via la méthode POST.
$_REQUEST : contient une combinaison de GET, POST et COOKIE (selon la configuration). Son utilisation est déconseillée pour des raisons de clarté et sécurité : il vaut mieux accéder explicitement à $_GET ou $_POST.
Exemple concret : un formulaire simple avec GET.

<?php
// form-get.php : Formulaire utilisant la méthode GET
// Ligne 1 : ouverture du script PHP

// Si l'utilisateur a soumis le formulaire, les paramètres seront présents dans $_GET
// Ligne 4 : on récupère le paramètre 'q' dans $_GET
$query = isset($_GET['q']) ? $_GET['q'] : '';

// Ligne 7 : affichage du formulaire et du résultat
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Recherche - GET</title>
</head>
<body>
    <h1>Recherche (GET)</h1>
    <!-- Formulaire avec méthode GET -->
    <form action="form-get.php" method="get">
        <!-- Le champ 'q' sera transmis via la query string -->
        <label for="q">Recherche :</label>
        <input type="text" id="q" name="q" value="<?php echo htmlspecialchars($query); ?>">
        <button type="submit">Rechercher</button>
    </form>

    <h2>Résultat</h2>
    <p>Vous avez cherché : <?php echo htmlspecialchars($query); ?></p>
</body>
</html>
Exemple équivalent en POST :

<?php
// form-post.php : Formulaire utilisant la méthode POST

// Récupération du paramètre 'username' envoyé par POST
$username = isset($_POST['username']) ? $_POST['username'] : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Formulaire POST</title>
</head>
<body>
    <h1>Connexion (POST)</h1>
    <form action="form-post.php" method="post">
        <label for="username">Nom d'utilisateur :</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>">
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password">
        <button type="submit">Se connecter</button>
    </form>

    <?php if (!empty($username)): ?>
        <p>Bonjour <?php echo htmlspecialchars($username); ?> !</p>
    <?php endif; ?>
</body>
</html>
Notes importantes : - Utilisez htmlspecialchars() lorsque vous affichez des données provenant de l'utilisateur pour éviter les attaques XSS (voir section dédiée). - Préférez $_POST pour les opérations d'écriture (création, modification) et $_GET pour les opérations de lecture (filtres, recherche).

Validation et assainissement : filter_var et filter_input
La validation consiste à vérifier que les données reçues correspondent au format attendu (par exemple, qu'une adresse e-mail est valide), tandis que l'assainissement (sanitization) modifie les données en supprimant ou échappant les éléments indésirables.

PHP offre une API de filtrage utile : filter_var(), filter_input(), ainsi qu'une collection de constantes FILTER_VALIDATE_ et FILTER_SANITIZE_.

Exemples :

filter_var( $value, FILTER_VALIDATE_EMAIL ) : retourne l'adresse email si valide, sinon false.
filter_var( $value, FILTER_SANITIZE_STRING ) : retire ou encode certains caractères (note : deprecated behaviour in recent versions, préférer FILTER_SANITIZE_FULL_SPECIAL_CHARS ou htmlspecialchars selon le cas).
Utilisation de filter_input() :

filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) permet de valider directement une entrée POST.
Exemple complet : formulaire d'inscription minimal avec validation et assainissement.

<?php
// register.php : inscription simple avec validation

$errors = [];
$clean = [];

// Vérification si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et validation du nom
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if (empty($name)) {
        $errors['name'] = 'Le nom est requis.';
    } else {
        $clean['name'] = $name; // déjà assaini
    }

    // Validation de l'email
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if ($email === false || $email === null) {
        $errors['email'] = 'Adresse e-mail invalide.';
    } else {
        $clean['email'] = $email;
    }

    // Validation de l'âge (entier)
    $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 120]
    ]);
    if ($age === false) {
        $errors['age'] = 'L\'âge doit être un entier entre 1 et 120.';
    } else {
        $clean['age'] = $age;
    }

    if (empty($errors)) {
        // Traitement : par exemple insertion en base
        // Ici on affiche les données nettoyées
        echo "Inscription réussie pour : " . htmlspecialchars($clean['name']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Inscription</title>
</head>
<body>
    <h1>Inscription</h1>
    <?php if (!empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $err): ?>
                <li><?php echo htmlspecialchars($err); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="register.php">
        <label for="name">Nom :</label>
        <input type="text" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">

        <label for="email">Email :</label>
        <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

        <label for="age">Âge :</label>
        <input type="number" id="age" name="age" value="<?php echo isset($_POST['age']) ? htmlspecialchars($_POST['age']) : ''; ?>">

        <button type="submit">S'inscrire</button>
    </form>
</body>
</html>
Bonnes pratiques pour validation/assainissement : - Validez d'abord (format attendu), puis assainissez. - Ne faites jamais confiance aux données client ; assumez qu'elles peuvent être malveillantes. - Pour les chaînes destinées à l'affichage HTML, utilisez htmlspecialchars() avec ENT_QUOTES et encodage UTF-8. - Pour les entrées destinées à la base de données, préférez l'utilisation de requêtes préparées (PDO) plutôt que de compter uniquement sur sanitizers.

Sécurité : XSS, CSRF et bonnes pratiques
Sécurité web est vaste ; nous couvrons ici les vecteurs les plus courants pour des formulaires PHP : Cross-Site Scripting (XSS) et Cross-Site Request Forgery (CSRF).

XSS (Cross-Site Scripting) :

Description : une application web affiche des données fournies par un utilisateur sans les échapper, permettant l'injection de scripts côté client.
Prévention : échapper toutes les données affichées dans le contexte HTML, JS, CSS, ou attributs.
Exemples d'échappement :

htmlspecialchars( $string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' ) : échappe &, <, >,\" et '\'' en entités HTML.
htmlentities() : encode davantage de caractères en entités HTML, mais n'est pas toujours nécessaire.
Exemple simple :

<?php
// Exemple XSS : affichage sûr
$user_input = '<script>alert("xss")</script>';
// Affichage non sécurisé (ne faites pas ça)
// echo $user_input; // ceci exécuterait le script si injecté

// Affichage sécurisé
echo htmlspecialchars($user_input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
CSRF (Cross-Site Request Forgery) :

Description : un attaquant pousse un utilisateur authentifié à exécuter une action non désirée (par ex. ajouter un produit, modifier un compte) en exploitant les cookies ou sessions déjà valides.
Prévention : utiliser des tokens CSRF uniques par session/formulaire et vérifier ces tokens côté serveur.
Implémentation typique d'un token CSRF :

Générer un token aléatoire stocké dans la session (ou base).
Insérer le token dans le formulaire en champ caché.
À la soumission, vérifier que le token reçu correspond à celui en session.
Exemple :

<?php
// csrf-example.php
session_start();

// Génération du token si nécessaire
if (empty($_SESSION['csrf_token'])) {
    // random_bytes pour cryptographie sécurisée, bin2hex pour stockage lisible
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['csrf_token'];

// Vérification à la soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!hash_equals($_SESSION['csrf_token'], $userToken)) {
        // Token invalide : bloquer l'opération
        die('Token CSRF invalide.');
    }
    // Token valide : procéder au traitement
    echo 'Formulaire reçu et token vérifié.';
}
?>
<form method="post" action="csrf-example.php">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token); ?>">
    <button type="submit">Soumettre</button>
</form>
Autres bonnes pratiques générales : - Utiliser HTTPS pour toutes les opérations sensibles. - Définir des en-têtes de sécurité (Content-Security-Policy, X-Frame-Options, X-Content-Type-Options, Referrer-Policy). - Limiter les données renvoyées au strict nécessaire. - Logger les événements sensibles (tentatives de connexion échouées, modifications critiques).

Upload de fichiers : $_FILES, vérification MIME, taille et stockage sécurisé
La gestion d'upload de fichiers est courante mais dangereuse si mal faite : risques d'upload de fichiers malveillants (scripts), d'exposition de données, etc. Les étapes typiques :

Créer le formulaire HTML avec enctype="multipart/form-data" et method="post".
Vérifier l'existence du fichier dans $_FILES et l'absence d'erreurs.
Vérifier la taille ($_FILES['file']['size']).
Vérifier le type MIME de l'image (finfo_file) et l'extension si nécessaire.
Stocker le fichier dans un répertoire sécurisé, en utilisant un nom de fichier contrôlé (ex: nom aléatoire) et empêcher l'exécution.
Mettre des permissions bonnes (par ex. 0644 pour fichiers, 0755 pour répertoires) et désactiver l'exécution dans le répertoire upload via configuration du serveur (ex: .htaccess ou nginx config).
Exemple complet : upload d'images.

<?php
// upload-image.php
session_start();

$errors = [];
$uploadDir = __DIR__ . '/uploads';
// Créer le répertoire si nécessaire (attention aux permissions)
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['image'])) {
        $errors[] = 'Aucun fichier envoyé.';
    } else {
        $file = $_FILES['image'];

        // Vérifier qu'il n'y a pas d'erreur à l'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Erreur lors de l\'upload (code: ' . $file['error'] . ').';
        } else {
            // Taille maximale en octets (ex: 2MB)
            $maxSize = 2 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                $errors[] = 'Le fichier est trop volumineux.';
            }

            // Vérification du type MIME via finfo (plus fiable que $_FILES['type'])
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
            if (!array_key_exists($mimeType, $allowed)) {
                $errors[] = 'Type de fichier non autorisé.';
            }

            if (empty($errors)) {
                // Générer un nom de fichier sécurisé
                $ext = $allowed[$mimeType];
                $basename = bin2hex(random_bytes(8)); // nom aléatoire
                $target = $uploadDir . DIRECTORY_SEPARATOR . $basename . '.' . $ext;

                // Déplacer le fichier depuis le tmp vers le répertoire cible
                if (!move_uploaded_file($file['tmp_name'], $target)) {
                    $errors[] = 'Impossible de sauvegarder le fichier.';
                } else {
                    // Optionnel : définir des permissions sécurisées
                    chmod($target, 0644);
                    echo 'Upload réussi : ' . htmlspecialchars(basename($target));
                    exit;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Upload image</title>
</head>
<body>
    <h1>Uploader une image</h1>
    <?php if (!empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="upload-image.php" enctype="multipart/form-data">
        <label for="image">Image (JPEG/PNG/GIF, max 2MB):</label>
        <input type="file" name="image" id="image" accept="image/*">
        <button type="submit">Uploader</button>
    </form>
</body>
</html>
Remarques de sécurité supplémentaires : - Ne jamais faire confiance à l'extension du fichier envoyée par l'utilisateur. - Vérifier le contenu réel via finfo. - Ne stocker pas les fichiers uploadés dans un répertoire accessible en exécution par le serveur, ou configurer le serveur pour empêcher l'exécution de scripts dans ce répertoire. - Scanner les fichiers avec un antivirus côté serveur si nécessaire dans des environnements sensibles.

Sessions : session_start(), $_SESSION, régénération d'ID et sécurité
Les sessions en PHP sont utilisées pour conserver l'état entre les requêtes HTTP (par ex. utilisateur connecté). PHP propose une API simple : session_start(), $_SESSION, session_regenerate_id(), session_destroy().

Points clés : - Appelez toujours session_start() avant tout envoi d'en-têtes (headers) et généralement tout en haut du script. - Utilisez session_regenerate_id(true) lors de la connexion ou à intervalles réguliers pour éviter le session fixation. - Stockez le moins possible d'informations sensibles dans la session ; utilisez des identifiants et stockez les données sensibles côté serveur (base).

Exemple : système de connexion minimal.

<?php
// login.php
session_start();

// Exemple d'authentification très basique
$users = [
    'alice' => 'password1',
    'bob' => 'password2'
];

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (isset($users[$username]) && $users[$username] === $password) {
        // Authentification réussie
        // Régénérer l'ID de session pour éviter fixation
        session_regenerate_id(true);
        $_SESSION['user'] = $username;
        header('Location: dashboard.php');
        exit;
    } else {
        $errors[] = 'Identifiants invalides.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Connexion</title>
</head>
<body>
    <h1>Connexion</h1>
    <?php if ($errors): ?>
        <ul>
            <?php foreach ($errors as $err): ?>
                <li><?php echo htmlspecialchars($err); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="login.php">
        <label for="username">Utilisateur :</label>
        <input type="text" id="username" name="username">
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password">
        <button type="submit">Se connecter</button>
    </form>
</body>
</html>
Fichier dashboard.php simple pour vérifier la session :

<?php
// dashboard.php
session_start();
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Tableau de bord</title>
</head>
<body>
    <h1>Bienvenue <?php echo htmlspecialchars($user); ?></h1>
    <p><a href="logout.php">Se déconnecter</a></p>
</body>
</html>
Et logout.php :

<?php
// logout.php
session_start();
// Vider toutes les variables de session
$_SESSION = [];
// Détruire le cookie de session si nécessaire
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']
    );
}
session_destroy();
header('Location: login.php');
exit;
Conseils de sécurité pour les sessions : - Utiliser des cookies avec l'attribut HttpOnly (empêche l'accès via JS) et Secure si HTTPS. - Fixer SameSite (Lax ou Strict) pour réduire les risques de CSRF. - Régénérer l'ID après login et lors d'actions sensibles. - Configurer la durée de vie de session appropriée (session.cookie_lifetime, session.gc_maxlifetime).

Cookies : $_COOKIE et options httponly/secure/samesite
Les cookies sont des paires clé/valeur stockées côté client. En PHP, setcookie() permet de définir des cookies et $_COOKIE d'y accéder.

Depuis PHP 7.3, il est recommandé d'utiliser le tableau d'options pour setcookie( $name, $value, [ 'expires' => time()+3600, 'path' => '/', 'domain' => '', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax' ] );

Exemple :

<?php
// cookie-example.php
// Définir un cookie sécurisé pour une heure
setcookie('lang', 'fr', [
    'expires' => time() + 3600,
    'path' => '/',
    'secure' => true,       // seulement via HTTPS
    'httponly' => true,     // inaccessible via JS
    'samesite' => 'Lax'
]);

// Lecture
$lang = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : null;
Bonnes pratiques : - Ne stockez pas de données sensibles en clair dans les cookies. - Préférez stocker un identifiant renvoyant à des données côté serveur. - Utilisez HttpOnly pour empêcher l'accès JavaScript et Secure pour forcer HTTPS. - SameSite réduit les risques CSRF ; Lax est souvent un bon compromis.

Introduction à PDO : avantages, DSN, connexion sécurisée
PDO (PHP Data Objects) est une interface orientée objet pour accéder aux bases de données. Avantages : - Support de multiples moteurs (MySQL, PostgreSQL, SQLite...) avec la même API. - Requêtes préparées pour prévenir les injections SQL. - Gestion des transactions. - Possibilités de configuration (modes d'erreur, fetch modes).

Connexion basique à MySQL via PDO :

<?php
// pdo-connexion.php
$host = '127.0.0.1';
$db   = 'wacdo';
$user = 'dbuser';
$pass = 'dbpass';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // exceptions pour erreurs
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // fetch par défaut en tableau associatif
    PDO::ATTR_EMULATE_PREPARES   => false,                  // utiliser les vraies requêtes préparées si possible
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // En production, ne pas afficher le message d'erreur complet
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}
Remarques : - Utiliser ATTR_ERRMODE = ERRMODE_EXCEPTION facilite la gestion des erreurs via try/catch. - ATTR_EMULATE_PREPARES à false permet d'utiliser les capacités natives du pilote pour les requêtes préparées.

Requêtes préparées : prepare(), bindParam(), bindValue() et prévention des injections SQL
Les requêtes préparées séparent la structure de la requête des données envoyées, empêchant l'injection SQL. Exemple :

prepare($sql) : prépare la requête.
execute([$param1, $param2]) : exécute la requête avec les paramètres.
bindParam() lie une variable par référence (utile si la variable change avant execute).
bindValue() lie une valeur immédiatement.
Exemples :

<?php
// pdo-select.php
$sql = 'SELECT * FROM products WHERE category = :category AND price <= :price';
$stmt = $pdo->prepare($sql);
$category = 'outdoor';
$maxPrice = 100;
// bindValue lie la valeur immédiatement
$stmt->bindValue(':category', $category, PDO::PARAM_STR);
$stmt->bindValue(':price', $maxPrice, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();
Ou version plus concise :

$stmt = $pdo->prepare('SELECT * FROM products WHERE category = ? AND price <= ?');
$stmt->execute([$category, $maxPrice]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
Points à noter : - N'insérez jamais directement des variables dans les requêtes SQL via concaténation. - Les placeholders nommés (:name) ou positionnels (?) fonctionnent ; choisissez le style qui convient.

CRUD complet avec PDO : INSERT, SELECT, UPDATE, DELETE
Exemples complets pour une entité "produit".

1) CREATE (INSERT)

<?php
// create-product.php
$sql = 'INSERT INTO products (name, price, description) VALUES (:name, :price, :desc)';
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':name' => 'Chaise',
    ':price' => 49.99,
    ':desc' => 'Chaise confortable en bois.'
]);
// Récupérer l'ID inséré
$id = $pdo->lastInsertId();
2) READ (SELECT)

// read-products.php
$stmt = $pdo->query('SELECT id, name, price FROM products ORDER BY name');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo htmlspecialchars($row['name']) . ' - ' . number_format($row['price'], 2) . "\n";
}
3) UPDATE

// update-product.php
$sql = 'UPDATE products SET price = :price WHERE id = :id';
$stmt = $pdo->prepare($sql);
$stmt->execute([':price' => 59.99, ':id' => 10]);
$updated = $stmt->rowCount(); // nombre de lignes affectées
4) DELETE

// delete-product.php
$sql = 'DELETE FROM products WHERE id = :id';
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => 10]);
$deleted = $stmt->rowCount();
Transactions, lastInsertId et modes de fetch
Transactions :

beginTransaction(), commit(), rollback() permettent d'assurer l'atomicité d'une série d'opérations (ex: créer une commande + réduire stock).
Exemple :

<?php
try {
    $pdo->beginTransaction();

    // Plusieurs opérations liées
    $stmt1 = $pdo->prepare('INSERT INTO orders (user_id, total) VALUES (:user_id, :total)');
    $stmt1->execute([':user_id' => $userId, ':total' => $total]);
    $orderId = $pdo->lastInsertId();

    $stmt2 = $pdo->prepare('INSERT INTO order_items (order_id, product_id, qty) VALUES (:order_id, :product_id, :qty)');
    foreach ($items as $item) {
        $stmt2->execute([':order_id' => $orderId, ':product_id' => $item['id'], ':qty' => $item['qty']]);
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    throw $e; // ou gérer l'erreur
}
Modes de fetch : - PDO::FETCH_ASSOC : retourne un tableau associatif. - PDO::FETCH_OBJ : retourne un objet anonyme (->propriété). - PDO::FETCH_NUM : retourne un tableau indexé numériquement. - PDO::FETCH_CLASS : mappe les résultats dans une instance d'une classe donnée.

Exemple :

$stmt = $pdo->query('SELECT * FROM products');
while ($p = $stmt->fetch(PDO::FETCH_OBJ)) {
    echo $p->name . ' - ' . $p->price . "\n";
}
Gestion des exceptions PDO
Utiliser PDO::ERRMODE_EXCEPTION permet de gérer les erreurs via try/catch et d'assurer un comportement prévisible.

Exemple :

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->prepare('SELECT * FROM nonexistent');
    $stmt->execute();
} catch (PDOException $e) {
    // Journaliser le message d'erreur et afficher un message générique
    error_log('PDO error: ' . $e->getMessage());
    echo 'Une erreur serveur est survenue.';
}
Ne jamais afficher en production des messages d'erreur détaillés (ils peuvent contenir des informations sensibles sur la base de données).

TP fil rouge : mini-application de gestion de produits (prépare le projet Wacdo)
Objectif : créer une mini-application CRUD pour gérer des produits. Ce TP servira de base pour la suite du module et du projet Wacdo.

Fonctionnalités minimales : - Lister les produits - Ajouter un produit (nom, description, prix, image) - Modifier un produit - Supprimer un produit - Authentification simple (session)

Tables MySQL simplifiées :

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
Étapes recommandées : 1. Créer la connexion PDO (fichier config/connection.php). 2. Créer une page d'administration restreinte par session. 3. Créer les pages : index.php (liste), add.php (formulaire et traitement), edit.php, delete.php. 4. Gérer l'upload des images de produits en toute sécurité. 5. Utiliser des tokens CSRF sur les formulaires de modification/suppression.

Exemple simplifié : add.php (traitement)

<?php
// add.php - traitement d'ajout de produit (extrait)
require 'config/connection.php'; // $pdo
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token invalide');
    }

    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $desc = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Validation minimale
    $errors = [];
    if (!$name) $errors[] = 'Nom requis.';
    if ($price === false) $errors[] = 'Prix invalide.';

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO products (name, price, description) VALUES (:name, :price, :desc)');
        $stmt->execute([':name' => $name, ':price' => $price, ':desc' => $desc]);
        header('Location: index.php');
        exit;
    }
}
Conseils d'organisation du projet Wacdo : - Séparer configuration, templates et logique (ex: folder config/, templates/, public/). - Utiliser des fonctions ou classes pour DRY (Don't Repeat Yourself) : connexion PDO, affichage d'erreurs, génération de tokens CSRF. - Versionner via git et documenter les étapes d'installation (README).

Exercices pratiques avec corrigés
Exercice 1 : Formulaire GET vs POST

Énoncé : créez deux pages, search_get.php et search_post.php. Les deux pages contiennent un formulaire avec un champ "q" et affichent la valeur saisie. search_get.php doit utiliser la méthode GET, search_post.php la méthode POST. Expliquez la différence en terme d'URL après soumission.

Corrigé (exemple minimal pour search_post.php) :

<?php
// search_post.php
$q = isset($_POST['q']) ? $_POST['q'] : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"><title>Search POST</title></head>
<body>
    <form method="post" action="search_post.php">
        <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>">
        <button type="submit">OK</button>
    </form>
    <p>Valeur : <?php echo htmlspecialchars($q); ?></p>
</body>
</html>
Explication : Avec GET l'URL devient search_get.php?q=valeur après soumission, tandis qu'avec POST les données ne sont pas visibles dans l'URL.

Exercice 2 : Validation d'un email

Énoncé : écrivez une fonction validate_email($email) qui retourne true si l'email est valide selon FILTER_VALIDATE_EMAIL et false sinon. Testez-la avec plusieurs cas.

Corrigé :

<?php
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Tests
$tests = ['toto@example.com', 'bad@', 'alice@domain.co'];
foreach ($tests as $t) {
    var_dump($t, validate_email($t));
}
Exercice 3 : Protection XSS

Énoncé : vous recevez un paramètre 'msg' via GET et devez l'afficher dans la page de façon sécurisée. Montrez une version vulnérable et une version corrigée.

Corrigé :

Vulnérable :

<?php
// vuln.php
echo $_GET['msg'];
Corrigé :

<?php
// safe.php
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
echo htmlspecialchars($msg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
Exercice 4 : Upload sécurisé

Énoncé : implémentez un script qui autorise l'upload d'images PNG/JPEG uniquement, taille max 1MB, et stocke les fichiers dans un dossier "uploads" en nommant les fichiers avec un identifiant aléatoire.

Corrigé résumé : voir la section "Upload de fichiers" ci-dessus ; ajuster $maxSize à 1 * 1024 * 1024 et la liste $allowed en conséquence.

Exercice 5 : Sessions et sécurité

Énoncé : expliquez pourquoi il est important d'appeler session_regenerate_id(true) après authentification et montrez un exemple minimal.

Corrigé :

Explication : la régénération d'ID empêche le session fixation où un attaquant fixe l'ID de session avant que l'utilisateur ne se connecte. Après la connexion, régénérer l'ID assure que l'ID précédemment connu ne permet plus d'accéder à la session.

Exemple :

<?php
session_start();
// Après vérification des identifiants :
$_SESSION['user'] = $username;
session_regenerate_id(true);
Exercice 6 : PDO - insertion sécurisée

Énoncé : écrivez un script qui insère un produit en utilisant une requête préparée PDO sans risque d'injection SQL.

Corrigé :

<?php
// insert-secure.php
require 'config/connection.php'; // $pdo
$stmt = $pdo->prepare('INSERT INTO products (name, price, description) VALUES (:name, :price, :desc)');
$stmt->execute([':name' => $name, ':price' => $price, ':desc' => $desc]);
Exercice 7 : Transactions

Énoncé : pourquoi utiliser une transaction pour créer une commande et ses lignes ? Donnez un exemple avec rollback.

Corrigé :

Explication : pour garantir que toutes les opérations liées (création de la commande et insertion de toutes ses lignes) réussissent ou qu'aucune n'est appliquée en cas d'erreur. Cela évite d'avoir une commande sans lignes ou des lignes sans commande.

Exemple : voir la section "Transactions" ci-dessus.

Annexes : bonnes pratiques, checklist et références
Checklist pour mise en production : - Forcer HTTPS et utiliser Secure cookies. - Utiliser CSP et autres en-têtes de sécurité. - Configurer les permissions des répertoires d'upload et empêcher l'exécution. - Limiter les tailles d'upload et valider le type via finfo. - Utiliser PDO avec requêtes préparées. - Avoir un système de logs et de surveillance.

Ressources utiles : - Documentation PHP officielle : https://www.php.net/ - PDO : https://www.php.net/manual/fr/book.pdo.php - OWASP (XSS, CSRF) : https://owasp.org/

FIN DU DOCUMENT