C2.1.3 — Programmation Orientée Objet en PHP (Partie 2)

Introduction
Ce document est un cours détaillé et complet pour les séances 3 et 4 du cours C2.1.3 — POO en PHP. Il est destiné à des étudiants de niveau B2 inscrits à la formation Développeur Web RNCP 24345. Les étudiants connaissent déjà les notions fondamentales de classes, d'objets et d'héritage (traitées lors des séances 1 et 2). Ici nous approfondirons des concepts essentiels et pratiques : interfaces, traits, composition, namespaces, autoloading, Composer, exceptions personnalisées, architecture (Modèles, Services, Repositories) et un TP de refactoring.

Le cours est organisé en sections thématiques (séance 3 puis séance 4) avec des explications détaillées en prose, des exemples de code PHP complets et commentés ligne par ligne, ainsi que des exercices corrigés. Les exemples privilégient le PHP natif et suivent les bonnes pratiques modernes (type hints, visibilité, immutabilité quand adaptée, etc.).

Séance 3 — Interfaces, traits et composition
Cette séance aborde trois concepts complémentaires : interfaces, traits et composition, ainsi que des patrons simples (Repository, Strategy) pour illustrer l'usage des interfaces et de la composition dans des architectures claires et testables.

Interfaces : définition, implements, contrats, multiple interfaces
Une interface en PHP définit un contrat. Le contrat précise un ensemble de signatures de méthodes qu'une classe doit implémenter. L'interface ne contient pas d'implémentation (sauf méthodes statiques avec bodies depuis PHP 8.0? Non : les interfaces restent sans implémentation; les traits apportent l'implémentation). Utiliser des interfaces permet : d'exprimer l'intention, de découpler le code, de faciliter les tests (mocking), et de permettre la substitution (polymorphisme) entre différentes implémentations.

Syntax error in text
mermaid version 11.12.3
Voici un exemple concret : définissons une interface LoggerInterface et deux implémentations distinctes.

<?php
// LoggerInterface.php

// Déclare l'interface LoggerInterface
interface LoggerInterface
{
    // Méthode pour écrire un message d'information
    public function info(string $message): void;

    // Méthode pour écrire un message d'erreur
    public function error(string $message): void;
}

// FileLogger.php

// Implémentation de LoggerInterface qui écrit dans un fichier
class FileLogger implements LoggerInterface
{
    // Chemin du fichier de log
    private string $filePath;

    // Constructeur qui reçoit le chemin du fichier
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath; // stocke la valeur dans la propriété
    }

    // Implémentation de info : ajoute une ligne au fichier
    public function info(string $message): void
    {
        // Prépare la ligne avec un niveau et l'heure
        $line = sprintf("[INFO] [%s] %s\n", date('Y-m-d H:i:s'), $message);
        // Écrit la ligne en ajout dans le fichier
        file_put_contents($this->filePath, $line, FILE_APPEND | LOCK_EX);
    }

    // Implémentation de error : ajoute une ligne d'erreur
    public function error(string $message): void
    {
        $line = sprintf("[ERROR] [%s] %s\n", date('Y-m-d H:i:s'), $message);
        file_put_contents($this->filePath, $line, FILE_APPEND | LOCK_EX);
    }
}

// StdoutLogger.php

// Implémentation de LoggerInterface qui écrit sur la sortie standard
class StdoutLogger implements LoggerInterface
{
    // Affiche un message d'information
    public function info(string $message): void
    {
        echo sprintf("[INFO] [%s] %s\n", date('Y-m-d H:i:s'), $message);
    }

    // Affiche un message d'erreur
    public function error(string $message): void
    {
        echo sprintf("[ERROR] [%s] %s\n", date('Y-m-d H:i:s'), $message);
    }
}

// Usage : on peut typer les dépendances avec l'interface
function process(LoggerInterface $logger): void
{
    $logger->info('Démarrage du processus');
    // ... traitement ...
    $logger->info('Fin du processus');
}

// Exemple d'appel avec FileLogger
$logger = new FileLogger(__DIR__ . '/app.log');
process($logger);

// Exemple d'appel avec StdoutLogger
$logger2 = new StdoutLogger();
process($logger2);
Chaque méthode de l'interface est décrite par sa signature : nom, paramètres typés, et type de retour. Une classe qui implémente l'interface s'engage à fournir toutes ces méthodes. Une classe peut implémenter plusieurs interfaces, ce qui autorise la composition de contrats.

Exemple : une classe peut implémenter LoggerInterface et Countable simultanément (Countable est une interface fournie par PHP). Lorsque plusieurs interfaces sont implémentées, la classe doit satisfaire à tous les contrats.

Traits : réutilisation de code sans héritage, résolution de conflits
Les traits permettent de réutiliser du code entre classes qui ne partagent pas forcément une relation d'héritage. Un trait contient des méthodes (et propriétés) que plusieurs classes peuvent importer via le mot-clé use. Les traits résolvent la répétition et permettent le partage d'implémentations sans créer une hiérarchie de classes artificielle.

Exemple d'utilisation d'un trait pour fournir des méthodes utilitaires de logging.

<?php
// LoggableTrait.php

trait LoggableTrait
{
    // Propriété pour stocker un logger optionnel
    private ?LoggerInterface $logger = null; // peut être null si non fourni

    // Méthode pour attacher un logger
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger; // associe le logger à la classe
    }

    // Méthode protégée pour écrire une info si un logger est présent
    protected function logInfo(string $message): void
    {
        if ($this->logger !== null) {
            $this->logger->info($message);
        }
    }

    // Méthode protégée pour écrire une erreur si un logger est présent
    protected function logError(string $message): void
    {
        if ($this->logger !== null) {
            $this->logger->error($message);
        }
    }
}

// Classe qui utilise le trait
class ProductService
{
    use LoggableTrait; // injecte les méthodes du trait dans la classe

    public function createProduct(array $data): void
    {
        // exemple d'utilisation du trait pour journaliser
        $this->logInfo('Création d\'un produit');
        // ... logique de création ...
    }
}

// Résolution de conflits

trait A
{
    public function hello(): string
    {
        return 'hello from A';
    }
}

trait B
{
    public function hello(): string
    {
        return 'hello from B';
    }
}

class Greeter
{
    use A, B {
        // Résolution : on choisit la méthode hello de A
        A::hello insteadof B;
        // Ou on peut renommer la méthode hello de B
        B::hello as helloFromB;
    }
}

$g = new Greeter();
echo $g->hello(); // affiche 'hello from A'
echo $g->helloFromB(); // affiche 'hello from B'
Les traits sont pratiques mais doivent être utilisés avec parcimonie : abuser des traits peut rendre l'architecture moins claire que des relations explicites par composition ou héritage.

Composition vs héritage : quand utiliser quoi
Héritage (extends) exprime une relation de type "est un" (is-a). La sous-classe hérite des comportements et de l'interface publique de la superclasse. L'héritage est puissant mais peut conduire à des systèmes rigides si la hiérarchie devient profonde ou si des classes mutent pour supporter des cas variés.

La composition exprime une relation "a un" (has-a) : un objet contient d'autres objets et délègue certains comportements à ces objets. La composition favorise la flexibilité, le découplage et la testabilité. Elle est souvent recommandée comme préférence par rapport à l'héritage pour la plupart des besoins.

Composition

contient

contient

contient

Voiture

Moteur

Roues

GPS

Héritage

Animal

Chat

Chien

Illustration : imaginons un service de notification. Au lieu de créer une hiérarchie NotificationEmail, NotificationSms qui étendent NotificationBase, il est souvent préférable d'avoir un NotificationService qui contient (compose) un ChannelInterface (EmailChannel, SmsChannel) et délègue l'envoi au channel choisi. Cela permet d'ajouter de nouveaux channels sans modifier la hiérarchie.

<?php
// ChannelInterface.php
interface ChannelInterface
{
    public function send(string $recipient, string $message): bool;
}

// EmailChannel.php
class EmailChannel implements ChannelInterface
{
    public function send(string $recipient, string $message): bool
    {
        // code d'envoi d'email (simulation)
        // ici on renvoie true pour indiquer le succès
        return true;
    }
}

// SmsChannel.php
class SmsChannel implements ChannelInterface
{
    public function send(string $recipient, string $message): bool
    {
        // code d'envoi SMS (simulation)
        return true;
    }
}

// NotificationService.php
class NotificationService
{
    private ChannelInterface $channel; // composition : on stocke un channel

    public function __construct(ChannelInterface $channel)
    {
        $this->channel = $channel; // on injecte la dépendance via le constructeur
    }

    public function notify(string $recipient, string $message): bool
    {
        return $this->channel->send($recipient, $message);
    }
}

// Usage : on change de comportement sans changer NotificationService
$emailService = new NotificationService(new EmailChannel());
$smsService   = new NotificationService(new SmsChannel());

$emailService->notify('user@example.com', 'Bonjour');
$smsService->notify('+33123456789', 'Bonjour');
En résumé : privilégiez la composition pour la flexibilité et l'extensibilité ; utilisez l'héritage lorsque vous modélisez une relation clairement "est un" et que vous souhaitez réutiliser l'implémentation de la superclasse.

Type hinting avec interfaces
Le type hinting (typage des paramètres, retours, propriétés) avec des interfaces est une pratique essentielle pour garantir les contrats au niveau du code et pour permettre l'injection de dépendances polymorphes. Exemple : une méthode qui accepte LoggerInterface peut recevoir n'importe quelle implémentation de logger, ce qui facilite les tests unitaires en passant des mocks ou des doubles.

<?php
function save(object $entity, RepositoryInterface $repo): void
{
    // Ici nous nous attendons à un repo qui respecte le contrat RepositoryInterface
    $repo->save($entity);
}
Les annotations de types et le typage strict (declare(strict_types=1);) aident à détecter les erreurs tôt. L'usage de types union ou de types nullables est possible selon les besoins (PHP 8+), par exemple ?LoggerInterface pour un logger optionnel.

Design patterns simples : Repository, Strategy (exemples PHP concrets)
Patterns a) Repository

Le pattern Repository isole la logique d'accès aux données (persist, find, etc.) derrière une interface. Il permet de remplacer l'implémentation (base de données, fichier, API distante) sans impacter le reste du code.

<?php
// RepositoryInterface.php
interface ProductRepositoryInterface
{
    public function save(Product $product): void;
    public function findById(int $id): ?Product;
}

// InMemoryProductRepository.php
class InMemoryProductRepository implements ProductRepositoryInterface
{
    private array $storage = [];

    public function save(Product $product): void
    {
        $this->storage[$product->getId()] = $product;
    }

    public function findById(int $id): ?Product
    {
        return $this->storage[$id] ?? null;
    }
}

// DbProductRepository.php (exemple simplifié)
class DbProductRepository implements ProductRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(Product $product): void
    {
        // Exemple abrégé : requête d'insertion / update
        $stmt = $this->pdo->prepare('REPLACE INTO products (id, name, price) VALUES (:id, :name, :price)');
        $stmt->execute([
            ':id' => $product->getId(),
            ':name' => $product->getName(),
            ':price' => $product->getPrice(),
        ]);
    }

    public function findById(int $id): ?Product
    {
        $stmt = $this->pdo->prepare('SELECT id, name, price FROM products WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return new Product((int)$row['id'], $row['name'], (float)$row['price']);
    }
}

// ProductService utilise le repository via l'interface
class ProductService
{
    private ProductRepositoryInterface $repo;

    public function __construct(ProductRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function create(array $data): Product
    {
        $product = new Product($data['id'], $data['name'], $data['price']);
        $this->repo->save($product);
        return $product;
    }
}
Patterns b) Strategy

Le pattern Strategy définit une famille d'algorithmes interchangeables ; il encapsule chacun d'eux et les rend interchangeables à l'exécution. Cela est très utile pour des comportements modulaires (ex : différentes stratégies de tarification).

<?php
// PricingStrategyInterface.php
interface PricingStrategyInterface
{
    public function calculatePrice(Product $product): float;
}

// RegularPriceStrategy.php
class RegularPriceStrategy implements PricingStrategyInterface
{
    public function calculatePrice(Product $product): float
    {
        return $product->getPrice();
    }
}

// DiscountStrategy.php
class DiscountStrategy implements PricingStrategyInterface
{
    private float $discountPercent;

    public function __construct(float $discountPercent)
    {
        $this->discountPercent = $discountPercent;
    }

    public function calculatePrice(Product $product): float
    {
        return $product->getPrice() * (1 - $this->discountPercent / 100);
    }
}

// PriceCalculator.php
class PriceCalculator
{
    private PricingStrategyInterface $strategy;

    public function __construct(PricingStrategyInterface $strategy)
    {
        $this->strategy = $strategy;
    }

    public function setStrategy(PricingStrategyInterface $strategy): void
    {
        $this->strategy = $strategy;
    }

    public function getPrice(Product $product): float
    {
        return $this->strategy->calculatePrice($product);
    }
}

// Usage
$calculator = new PriceCalculator(new RegularPriceStrategy());
$finalPrice = $calculator->getPrice($product);

$calculator->setStrategy(new DiscountStrategy(10));
$discountedPrice = $calculator->getPrice($product);
Ces patterns montrent l'intérêt des interfaces et de la composition : on définit des contrats clairs et on peut substituer des implémentations sans changer le code appelant.

Exercices séance 3 avec corrections
Exercice 1 — Interfaces et types

Énoncé : Créez une interface CacheInterface qui définit les méthodes get(string $key): mixed et set(string $key, $value, ?int $ttl = null): void. Ensuite, implémentez deux classes : ArrayCache (stockage en mémoire) et FileCache (stockage simple sur disque). Enfin, écrivez une fonction fetchOrCompute(string $key, callable $compute, CacheInterface $cache): mixed qui renvoie la valeur depuis le cache si elle existe, sinon calcule la valeur via $compute, la stocke et la renvoie.

Correction :

<?php
interface CacheInterface
{
    public function get(string $key);
    public function set(string $key, $value, ?int $ttl = null): void;
}

class ArrayCache implements CacheInterface
{
    private array $store = [];
    private array $expiries = [];

    public function get(string $key)
    {
        if (!isset($this->store[$key])) {
            return null;
        }
        if (isset($this->expiries[$key]) && time() > $this->expiries[$key]) {
            unset($this->store[$key], $this->expiries[$key]);
            return null;
        }
        return $this->store[$key];
    }

    public function set(string $key, $value, ?int $ttl = null): void
    {
        $this->store[$key] = $value;
        if ($ttl !== null) {
            $this->expiries[$key] = time() + $ttl;
        }
    }
}

class FileCache implements CacheInterface
{
    private string $dir;

    public function __construct(string $dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->dir = rtrim($dir, DIRECTORY_SEPARATOR);
    }

    private function path(string $key): string
    {
        return $this->dir . DIRECTORY_SEPARATOR . sha1($key) . '.cache';
    }

    public function get(string $key)
    {
        $path = $this->path($key);
        if (!file_exists($path)) {
            return null;
        }
        $data = unserialize(file_get_contents($path));
        if ($data['expiry'] !== null && time() > $data['expiry']) {
            unlink($path);
            return null;
        }
        return $data['value'];
    }

    public function set(string $key, $value, ?int $ttl = null): void
    {
        $path = $this->path($key);
        $data = [
            'value' => $value,
            'expiry' => $ttl !== null ? time() + $ttl : null,
        ];
        file_put_contents($path, serialize($data), LOCK_EX);
    }
}

function fetchOrCompute(string $key, callable $compute, CacheInterface $cache)
{
    $value = $cache->get($key);
    if ($value !== null) {
        return $value;
    }
    $value = $compute();
    $cache->set($key, $value);
    return $value;
}

// Exemple d'utilisation
$cache = new ArrayCache();
$result = fetchOrCompute('answer', function() { return 42; }, $cache);
Exercice 2 — Traits et résolution de conflits

Énoncé : Créez deux traits A et B possédant chacun une méthode describe(): string qui renvoie une chaîne différente. Créez une classe C qui utilise les deux traits et résout le conflit en choisissant la méthode de A tout en donnant un alias à la méthode de B.

Correction :

<?php
trait A { public function describe(): string { return 'from A'; } }
trait B { public function describe(): string { return 'from B'; } }

class C {
    use A, B {
        A::describe insteadof B;
        B::describe as describeFromB;
    }
}

$c = new C();
echo $c->describe(); // 'from A'
echo $c->describeFromB(); // 'from B'
Exercice 3 — Composition vs héritage

Énoncé : Refactorez ce code hérité pour utiliser la composition. Code initial :

class BaseNotifier { public function send($to, $msg) {} }
class EmailNotifier extends BaseNotifier { /* ... */ }
class SmsNotifier extends BaseNotifier { /* ... */ }
Demandez de proposer une version composée où Notifier reçoit un ChannelInterface.

Correction :

interface ChannelInterface { public function send(string $to, string $msg): bool; }
class EmailChannel implements ChannelInterface { public function send(string $to, string $msg): bool { /* ... */ return true; } }
class SmsChannel implements ChannelInterface { public function send(string $to, string $msg): bool { /* ... */ return true; } }

class Notifier {
    private ChannelInterface $channel;
    public function __construct(ChannelInterface $channel) { $this->channel = $channel; }
    public function notify(string $to, string $msg): bool { return $this->channel->send($to, $msg); }
}

// Usage
$notifier = new Notifier(new EmailChannel());
$notifier->notify('user@example.com', 'Bonjour');
Fin de la séance 3.

Séance 4 — Namespaces, autoloading et architecture
La séance 4 couvre l'organisation d'un projet PHP moderne avec namespaces, autoloading (spl_autoload_register et PSR-4), Composer basics, exceptions personnalisées et la séparation de l'architecture en Modèles, Services et Repositories. Nous conclurons par un TP de refactoring d'un système de gestion de produits.

Namespaces : pourquoi, déclaration, use, alias
Les namespaces évitent les conflits de noms et organisent le code en espaces logiques. Ils permettent d'avoir deux classes portant le même nom dans des contextes différents (par exemple App\Model\Product et Vendor\Lib\Product). Les déclarations se font avec la directive namespace en tête de fichier. On importe des symboles avec use et on peut aliaser avec as.

Exemple détaillé :

<?php
// src/Model/Product.php
namespace App\Model;

class Product
{
    private int $id;
    private string $name;

    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
}

// src/Repository/ProductRepository.php

use App\Model\Product; // import de la classe Product

class ProductRepository
{
    public function find(int $id): ?Product
    {
        // ... récupère depuis la source de données ...
        return null;
    }
}

// src/Script.php

use App\Model\Product as ProductModel;
use App\Repository\ProductRepository;

$repo = new ProductRepository();
$product = $repo->find(1);
Les namespaces doivent correspondre à la structure logique du projet, et idéalement se refléter dans l'arborescence des fichiers lorsque l'on suit PSR-4.

Autoloading : spl_autoload_register, PSR-4, structure de répertoires
Autoloading : mécanisme pour charger automatiquement les classes sans require/ include manuels. spl_autoload_register permet d'enregistrer une ou plusieurs fonctions d'autochargement. PSR-4 est la recommandation qui mappe un préfixe de namespace à un dossier racine : la classe \App\Model\Product se trouve généralement dans src/Model/Product.php.

src/

Controllers/

Models/

Services/

Repositories/

HomeController.php
App\Controllers

ProductController.php
App\Controllers

Product.php
App\Models

ProductService.php
App\Services

ProductRepository.php
App\Repositories

Exemple d'autoloader simple (non Composer) :

<?php
// autoload.php
spl_autoload_register(function (string $class) {
    // Remplace les backslashes par DIRECTORY_SEPARATOR
    $file = __DIR__ . '/src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Usage
require __DIR__ . '/autoload.php';
$repo = new App\Repository\ProductRepository();
PSR-4 : chaque vendor/namespace a une racine. Exemple de mapping dans composer.json :

{
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
Avec Composer, après avoir déclaré l'autoload PSR-4, il suffit d'exécuter composer dump-autoload ou composer install pour générer le fichier vendor/autoload.php qui gérera l'autoloading.

Composer basics : composer.json, autoload, require
Composer est le gestionnaire de dépendances standard en PHP. composer.json décrit les dépendances, l'autoloading, les métadonnées du package, les scripts, etc. Exemple minimal :

{
    "name": "monprojet/app",
    "description": "Exemple pour le cours",
    "require": {
        "php": ">=8.0",
        "doctrine/annotations": "^1.0"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
Après composer install, on inclut vendor/autoload.php dans notre bootstrap pour profiter de l'autoloading et des dépendances.

Exceptions personnalisées et hiérarchie d'exceptions
Créer des exceptions personnalisées améliore la lisibilité et la gestion des erreurs. On peut définir une hiérarchie : par exemple App\Exception\DomainException extends \Exception pour regrouper les erreurs métier, et App\Exception\NotFoundException extends DomainException pour un type d'erreur précis.

<?php
namespace App\Exception;

class DomainException extends \Exception {}
class NotFoundException extends DomainException {}
class ValidationException extends DomainException {}

// Utilisation
use App\Exception\NotFoundException;

function findProduct(int $id) {
    throw new NotFoundException("Produit $id introuvable");
}

try {
    findProduct(42);
} catch (NotFoundException $e) {
    // Gestion spécifique
} catch (DomainException $e) {
    // Gestion générique des erreurs métier
} catch (\Exception $e) {
    // Autres erreurs
}
Les classes d'exception personnalisées facilitent la capture fine des erreurs et la séparation des responsabilités entre couche métier et couche technique.

Architecture : séparer Modèles, Services, Repositories en PHP natif
Séparer les responsabilités rend le code maintenable. Une organisation typique : - Model (entités simples, DTO) : classes qui représentent les données (Product, User) - Repository : accès aux données (SQL, API, fichiers) - Service : logique métier, orchestre les repositories et autres services - Controller / Script : couche de haut niveau qui compose les services

Exemple d'organisation des classes et responsabilités :

<?php
namespace App\Model;
class Product { /* getters/setters */ }

namespace App\Repository;
interface ProductRepositoryInterface { public function findById(int $id): ?\App\Model\Product; }

namespace App\Service;
class ProductService {
    private \App\Repository\ProductRepositoryInterface $repo;
    public function __construct(\App\Repository\ProductRepositoryInterface $repo) { $this->repo = $repo; }
    public function getProductDetails(int $id): array { $product = $this->repo->findById($id); /* transforme en tableau */ }
}
Cette séparation facilite le test unitaire : les services peuvent être testés en fournissant des implémentations factices (mocks) des repositories.

TP: refactoring du système produits en architecture propre avec namespaces + autoload
Objectif du TP : partir d'un code monolithique (sans namespaces ni autoload) et le refactorer pour appliquer : - organisation par namespaces (App\Model, App\Repository, App\Service) - autoload PSR-4 via Composer - utilisation d'exceptions personnalisées - séparation claire des responsabilités

Voici un exemple complet et commenté du refactoring. Le code ci-dessous illustre les fichiers principaux et leur contenu.

Fichier: src/Model/Product.php

<?php
namespace App\Model;

// Entité Product simple
class Product
{
    private int $id; // identifiant
    private string $name; // nom du produit
    private float $price; // prix

    public function __construct(int $id, string $name, float $price)
    {
        $this->id = $id; // initialise l'id
        $this->name = $name; // initialise le nom
        $this->price = $price; // initialise le prix
    }

    public function getId(): int
    {
        return $this->id; // renvoie l'id
    }

    public function getName(): string
    {
        return $this->name; // renvoie le nom
    }

    public function getPrice(): float
    {
        return $this->price; // renvoie le prix
    }
}
Fichier: src/Repository/ProductRepositoryInterface.php

<?php
namespace App\Repository;

use App\Model\Product;

// Contrat du repository de produits
interface ProductRepositoryInterface
{
    public function save(Product $product): void; // persiste le produit
    public function findById(int $id): ?Product; // retrouve un produit ou null
}
Fichier: src/Repository/InMemoryProductRepository.php

<?php
namespace App\Repository;

use App\Model\Product;

class InMemoryProductRepository implements ProductRepositoryInterface
{
    private array $storage = []; // tableau associatif id => Product

    public function save(Product $product): void
    {
        $this->storage[$product->getId()] = $product; // stocke le produit
    }

    public function findById(int $id): ?Product
    {
        return $this->storage[$id] ?? null; // renvoie null si absent
    }
}
Fichier: src/Service/ProductService.php

<?php
namespace App\Service;

use App\Repository\ProductRepositoryInterface;
use App\Model\Product;
use App\Exception\NotFoundException;

class ProductService
{
    private ProductRepositoryInterface $repo; // le repository injecté

    public function __construct(ProductRepositoryInterface $repo)
    {
        $this->repo = $repo; // on conserve la dépendance
    }

    public function createProduct(int $id, string $name, float $price): Product
    {
        $product = new Product($id, $name, $price); // crée l'entité
        $this->repo->save($product); // persiste via le repo
        return $product; // renvoie l'entité créée
    }

    public function getProduct(int $id): Product
    {
        $product = $this->repo->findById($id); // recherche
        if ($product === null) {
            throw new NotFoundException("Produit $id introuvable"); // lance une exception métier
        }
        return $product; // renvoie le produit
    }
}
Fichier: src/Exception/NotFoundException.php

<?php
namespace App\Exception;

class NotFoundException extends \Exception {}
Fichier: composer.json (exemple)

{
    "name": "app/products",
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    },
    "require": {
        "php": ">=8.0"
    }
}
Procédure : 1. Créer l'arborescence src/Model, src/Repository, src/Service, src/Exception. 2. Placer les fichiers ci-dessus. 3. Exécuter composer dump-autoload ou composer install. 4. Dans un script bootstrap, require 'vendor/autoload.php' puis instancier ProductService avec un InMemoryProductRepository.

Fichier: scripts/run.php

<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Repository\InMemoryProductRepository;
use App\Service\ProductService;

$repo = new InMemoryProductRepository();
$service = new ProductService($repo);

$product = $service->createProduct(1, 'Stylo', 2.5);
print_r($service->getProduct(1));
Ce TP illustre comment transformer un code peu structuré en un projet organisé, testable et maintenable.

Exercices séance 4 avec corrections
Exercice 4 — Namespaces et autoloading

Énoncé : Créez un petit projet avec namespace App\Utils et une classe StringHelper dans src/Utils/StringHelper.php qui contient une méthode static slugify(string $s): string. Configurez composer.json pour autoloader PSR-4 et montrez comment appeler App\Utils\StringHelper::slugify('Mon titre');

Correction :

<?php
namespace App\Utils;

class StringHelper
{
    public static function slugify(string $s): string
    {
        $s = mb_strtolower($s, 'UTF-8');
        $s = preg_replace('~[^\pL\d]+~u', '-', $s); // remplace séparateurs par -
        $s = trim($s, '-');
        $s = iconv('utf-8', 'us-ascii//TRANSLIT', $s);
        $s = preg_replace('~[^-\w]+~', '', $s);
        $s = preg_replace('~-+~', '-', $s);
        return $s ?: 'n-a';
    }
}

// Usage après composer dump-autoload
require 'vendor/autoload.php';
echo \App\Utils\StringHelper::slugify('Mon titre');
Exercice 5 — Exceptions personnalisées

Énoncé : Créez une hiérarchie d'exceptions App\Exception\DomainException et App\Exception\EntityNotFoundException; modifiez ProductService::getProduct pour lancer EntityNotFoundException et montrez comment la capturer.

Correction :

<?php
namespace App\Exception;

class DomainException extends \Exception {}
class EntityNotFoundException extends DomainException {}

// ProductService : lancer EntityNotFoundException
// try / catch :

try {
    $service->getProduct(999);
} catch (\App\Exception\EntityNotFoundException $e) {
    echo 'Produit introuvable';
} catch (\App\Exception\DomainException $e) {
    echo 'Erreur métier';
}
Exercice 6 — Refactorisation complète (TP)

Énoncé : Vous avez un script monolithique qui gère un tableau $products et des fonctions globales saveProduct, findProduct. Refactorez en classes Product, ProductRepository, ProductService, namespace App et autoload PSR-4. Fournissez les fichiers et un script d'exécution.

Correction :

Le corrigé complet est similaire aux fichiers fournis dans la section TP ci-dessus (src/Model/Product.php, src/Repository/InMemoryProductRepository.php, src/Service/ProductService.php, scripts/run.php). Veillez à ajouter composer.json et à exécuter composer dump-autoload.

Bonnes pratiques et pièges à éviter
Favorisez l'injection de dépendances (constructor injection) plutôt que l'instanciation directe dans les classes, cela facilite les tests.
Préférez la composition à l'héritage sauf quand la relation "est un" est évidemment modélisable.
Utilisez des interfaces pour décrire des contrats, pas pour regrouper méthodes non liées.
Limitez l'usage des traits aux cas où la duplication serait lourde; privilégiez la composition si le trait masque des dépendances implicites.
Respectez PSR-4 pour l'autoloading et organisez les namespaces selon l'arborescence.
Créez des exceptions métier spécifiques pour capturer et traiter les erreurs selon le contexte.
Conclusion
Ces deux séances (3 et 4) approfondissent des aspects cruciaux de la POO en PHP : interfaces et traits pour modulariser et réutiliser le code, la composition pour construire des objets flexibles, ainsi que les mécanismes modernes d'organisation du code (namespaces, autoloading, Composer) et d'architecture (séparation Modèles/Services/Repositories). Les exercices fournis permettent de mettre en pratique immédiatement ces concepts. En vous entraînant sur les TP et exercices, vous consoliderez votre capacité à concevoir des applications PHP maintenables et tests-friendly.