<?php

declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'WACDO', ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body>
    <main>
        <h1><?= htmlspecialchars($title ?? 'WACDO', ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($message ?? '', ENT_QUOTES, 'UTF-8') ?></p>
    </main>
</body>
</html>
