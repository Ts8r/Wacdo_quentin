<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);

require $projectRoot . '/autoload.php';

$databaseFactory = require $projectRoot . '/config/database.php';
$pdo = $databaseFactory();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS schema_migrations (
        version VARCHAR(120) NOT NULL PRIMARY KEY,
        applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB'
);

$migrations = glob($projectRoot . '/db/migrations/*.php') ?: [];
sort($migrations, SORT_STRING);

$applied = $pdo->query('SELECT version FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);
$appliedVersions = array_fill_keys(array_map('strval', $applied), true);

foreach ($migrations as $migrationPath) {
    $version = pathinfo($migrationPath, PATHINFO_FILENAME);

    if (isset($appliedVersions[$version])) {
        continue;
    }

    $migration = require $migrationPath;

    if (!is_callable($migration)) {
        throw new RuntimeException("Migration invalide: {$migrationPath}");
    }

    $pdo->beginTransaction();

    try {
        $migration($pdo, $projectRoot);
        $statement = $pdo->prepare('INSERT INTO schema_migrations (version) VALUES (:version)');
        $statement->execute(['version' => $version]);
        if ($pdo->inTransaction()) {
            $pdo->commit();
        }

        printf("Migration appliquee: %s\n", $version);
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

fwrite(STDOUT, "Migrations terminees.\n");
