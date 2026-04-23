<?php

declare(strict_types=1);

/**
 * Initialisation DDL de la base WACDO via PDO.
 *
 * Exécution attendue dans le conteneur wacdo_php :
 *   php bin/init_db.php
 */

$projectRoot = dirname(__DIR__);
$mpdPath = $projectRoot . '/wacdo/architecture/MPD.sql';

if (!is_file($mpdPath)) {
    fwrite(STDERR, "MPD introuvable: {$mpdPath}\n");
    exit(1);
}

$dbHost = getenv('DB_HOST') ?: 'wacdo_mariadb';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_NAME') ?: 'wacdo';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: '';
$dbCharset = getenv('DB_CHARSET') ?: 'utf8mb4';

$serverPdo = new PDO(
    sprintf(
        'mysql:host=%s;port=%s;charset=%s',
        $dbHost,
        $dbPort,
        $dbCharset,
    ),
    $dbUser,
    $dbPass,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);

$serverPdo->exec(
    sprintf(
        'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
        str_replace('`', '``', $dbName)
    )
);

$databaseFactory = require $projectRoot . '/config/database.php';
$pdo = $databaseFactory();

$mpdSql = file_get_contents($mpdPath);
if ($mpdSql === false) {
    fwrite(STDERR, "Impossible de lire le MPD: {$mpdPath}\n");
    exit(1);
}

$mpdSql = preg_replace('/^--.*$/m', '', $mpdSql) ?? $mpdSql;
$statements = preg_split('/;\s*(?:\r?\n|$)/', $mpdSql) ?: [];

$executed = 0;

foreach ($statements as $statement) {
    $sql = trim($statement);

    if ($sql === '') {
        continue;
    }

    if (stripos($sql, 'CREATE DATABASE') === 0 || stripos($sql, 'USE ') === 0) {
        continue;
    }

    $sql = preg_replace('/^CREATE TABLE\s+/i', 'CREATE TABLE IF NOT EXISTS ', $sql) ?? $sql;

    $pdo->exec($sql);
    $executed++;
}

fwrite(STDOUT, sprintf(
    "Initialisation terminee: base `%s` preparee, %d requetes DDL executees.\n",
    $dbName,
    $executed
));
