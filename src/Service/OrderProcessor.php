<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use PDO;
use Throwable;

class OrderProcessor
{
    public function __construct(
        private PDO $pdo,
        private ProductRepository $productRepository,
        private OrderRepository $orderRepository,
    ) {
    }

    public function process(array $orderData): array
    {
        $productId = $orderData['produto_id'] ?? null;
        $quantity = $orderData['quantidade'] ?? null;

        if (!is_int($productId) || $productId <= 0) {
            return [
                'produto_id' => $productId,
                'quantidade' => $quantity,
                'produto_nome' => null,
                'estoque_antes' => null,
                'estoque_depois' => null,
                'status' => 'erro',
                'mensagem' => 'Produto ID invalido',
            ];
        }

        if (!is_int($quantity) || $quantity <= 0) {
            return [
                'produto_id' => $productId,
                'quantidade' => $quantity,
                'produto_nome' => null,
                'estoque_antes' => null,
                'estoque_depois' => null,
                'status' => 'erro',
                'mensagem' => 'Quantidade invalida',
            ];
        }

        $productName = null;
        $stockBefore = null;
        $stockAfter = null;

        try {
            $this->pdo->beginTransaction();

            $product = $this->productRepository->findById($productId);

            if ($product === null) {
                $this->pdo->rollBack();

                return [
                    'produto_id' => $productId,
                    'quantidade' => $quantity,
                    'produto_nome' => null,
                    'estoque_antes' => null,
                    'estoque_depois' => null,
                    'status' => 'erro',
                    'mensagem' => 'Produto nao encontrado',
                ];
            }

            $productName = $product['nome'];
            $stockBefore = (int) $product['estoque'];
            $stockAfter = $stockBefore;

            if ($stockBefore >= $quantity) {
                $this->productRepository->decrementStock($productId, $quantity);
                $stockAfter = $stockBefore - $quantity;
                $status = 'pago';
                $message = 'Pedido criado com sucesso';
            } else {
                $status = 'cancelado_sem_estoque';
                $message = 'Estoque insuficiente';
            }

            $orderId = $this->orderRepository->create($productId, $quantity, $status);
            $this->pdo->commit();

            return [
                'produto_id' => $productId,
                'quantidade' => $quantity,
                'produto_nome' => $productName,
                'estoque_antes' => $stockBefore,
                'estoque_depois' => $stockAfter,
                'status' => $status,
                'mensagem' => $message,
                'pedido_id' => $orderId,
            ];
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            return [
                'produto_id' => $productId,
                'quantidade' => $quantity,
                'produto_nome' => $productName,
                'estoque_antes' => $stockBefore,
                'estoque_depois' => $stockAfter,
                'status' => 'erro',
                'mensagem' => 'Erro ao processar pedido: ' . $exception->getMessage(),
            ];
        }
    }
}
