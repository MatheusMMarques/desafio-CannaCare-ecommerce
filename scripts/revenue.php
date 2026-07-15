<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Repository\OrderRepository;

require __DIR__ . '/../vendor/autoload.php';

$databasePath = __DIR__ . '/../storage/database.sqlite';
$pdo = (new Connection($databasePath))->getPdo();
$orderRepository = new OrderRepository($pdo);
$totalRevenue = $orderRepository->getTotalRevenue();

echo 'Faturamento total dos pedidos pagos: R$ '
    . number_format($totalRevenue, 2, '.', '')
    . "\n";
