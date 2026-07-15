<?php

declare(strict_types=1);

use App\Database\Connection;

require __DIR__ . '/../vendor/autoload.php';

$databasePath = __DIR__ . '/../storage/database.sqlite';
$schemaPath = __DIR__ . '/../database/schema.sql';
$seedPath = __DIR__ . '/../database/seed.sql';

$pdo = (new Connection($databasePath))->getPdo();

$pdo->exec(file_get_contents($schemaPath));
$pdo->exec(file_get_contents($seedPath));

echo "Banco de dados configurado com sucesso.\n";
