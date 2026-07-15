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

        try {
            $this->pdo->beginTransaction();

            if (!is_int($productId) || $productId <= 0) {
                return $this->cancelWithError($productId, $quantity, 'Produto ID invalido');
            }

            if (!is_int($quantity) || $quantity <= 0) {
                return $this->cancelWithError($productId, $quantity, 'Quantidade invalida');
            }

            $product = $this->productRepository->findById($productId);

            if ($product === null) {
                return $this->cancelWithError($productId, $quantity, 'Produto nao encontrado');
            }

            if ((int) $product['estoque'] >= $quantity) {
                $this->productRepository->decrementStock($productId, $quantity);
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
                'status' => 'erro',
                'mensagem' => 'Erro ao processar pedido: ' . $exception->getMessage(),
            ];
        }
    }

    private function cancelWithError(mixed $productId, mixed $quantity, string $message): array
    {
        $this->pdo->rollBack();

        return [
            'produto_id' => $productId,
            'quantidade' => $quantity,
            'status' => 'erro',
            'mensagem' => $message,
        ];
    }
}
