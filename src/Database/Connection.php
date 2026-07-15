<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use RuntimeException;

class Connection
{
    private PDO $pdo;

    public function __construct(string $databasePath)
    {
        $databaseDirectory = dirname($databasePath);

        if (!is_dir($databaseDirectory) && !mkdir($databaseDirectory, 0777, true) && !is_dir($databaseDirectory)) {
            throw new RuntimeException('Nao foi possivel criar o diretorio do banco de dados.');
        }

        $this->pdo = new PDO('sqlite:' . $databasePath, options: [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $this->pdo->exec('PRAGMA foreign_keys = ON');
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}
