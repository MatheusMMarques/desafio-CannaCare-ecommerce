<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

class OrderRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(int $productId, int $quantity, string $status): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO pedidos (produto_id, quantidade, status)
             VALUES (:produto_id, :quantidade, :status)'
        );
        $statement->execute([
            'produto_id' => $productId,
            'quantidade' => $quantity,
            'status' => $status,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function getTotalRevenue(): float
    {
        $statement = $this->pdo->prepare(
            'SELECT COALESCE(SUM(p.quantidade * pr.preco), 0) AS faturamento_total
             FROM pedidos p
             INNER JOIN produtos pr ON pr.id = p.produto_id
             WHERE p.status = :status'
        );
        $statement->execute(['status' => 'pago']);

        return (float) $statement->fetchColumn();
    }
}
