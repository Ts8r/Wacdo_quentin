<?php

declare(strict_types=1);

return static function (PDO $pdo, string $projectRoot): void {
    $mpdPath = $projectRoot . '/wacdo/architecture/MPD.sql';

    if (!is_file($mpdPath)) {
        throw new RuntimeException("MPD introuvable: {$mpdPath}");
    }

    $sql = file_get_contents($mpdPath);

    if ($sql === false) {
        throw new RuntimeException("Impossible de lire le MPD: {$mpdPath}");
    }

    $sql = preg_replace('/^--.*$/m', '', $sql) ?? $sql;
    $statements = preg_split('/;\s*(?:\r?\n|$)/', $sql) ?: [];

    foreach ($statements as $statement) {
        $statement = trim($statement);

        if ($statement === '') {
            continue;
        }

        if (stripos($statement, 'CREATE DATABASE') === 0 || stripos($statement, 'USE ') === 0) {
            continue;
        }

        $statement = preg_replace('/^CREATE TABLE\s+/i', 'CREATE TABLE IF NOT EXISTS ', $statement) ?? $statement;
        $pdo->exec($statement);
    }
};
