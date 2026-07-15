<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Service\OrderProcessor;
require __DIR__ . '/../vendor/autoload.php';

$databasePath = __DIR__ . '/../storage/database.sqlite';
$ordersPath = __DIR__ . '/../data/pedidos.json';
$json = file_get_contents($ordersPath);

if ($json === false) {
    echo "Nao foi possivel ler o arquivo de pedidos.\n";
    exit(1);
}

try {
    $orders = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
} catch (\JsonException) {
    echo "O arquivo de pedidos contem JSON invalido.\n";
    exit(1);
}

if (!is_array($orders) || !array_is_list($orders)) {
    echo "O arquivo de pedidos deve conter uma lista.\n";
    exit(1);
}

$pdo = (new Connection($databasePath))->getPdo();
$productRepository = new ProductRepository($pdo);
$orderRepository = new OrderRepository($pdo);
$processor = new OrderProcessor($pdo, $productRepository, $orderRepository);

echo "Processando pedidos...\n\n";

foreach ($orders as $index => $orderData) {
    if (!is_array($orderData)) {
        echo 'Pedido #' . ($index + 1) . "\n";
        echo "Produto: Nao informado\n";
        echo "Quantidade solicitada: Nao informada\n";
        echo "Estoque antes: Nao disponivel\n";
        echo "Estoque depois: Nao disponivel\n";
        echo "Status: erro\n";
        echo "Resultado: Pedido invalido\n\n";
        continue;
    }

    $result = $processor->process($orderData);

    echo 'Pedido #' . ($index + 1) . "\n";
    echo 'Produto: ' . ($result['produto_nome'] ?? 'Nao encontrado') . "\n";
    echo 'Quantidade solicitada: ' . ($result['quantidade'] ?? 'Nao informada') . "\n";
    echo 'Estoque antes: ' . ($result['estoque_antes'] ?? 'Nao disponivel') . "\n";
    echo 'Estoque depois: ' . ($result['estoque_depois'] ?? 'Nao disponivel') . "\n";
    echo 'Status: ' . $result['status'] . "\n";
    echo 'Resultado: ' . $result['mensagem'] . "\n\n";
}
