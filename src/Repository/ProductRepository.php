<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

class ProductRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, nome, preco, estoque FROM produtos WHERE id = :id'
        );
        $statement->execute(['id' => $id]);

        $product = $statement->fetch();

        return $product === false ? null : $product;
    }

    public function decrementStock(int $productId, int $quantity): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE produtos SET estoque = estoque - :quantidade WHERE id = :produto_id'
        );
        $statement->execute([
            'produto_id' => $productId,
            'quantidade' => $quantity,
        ]);
    }
}
