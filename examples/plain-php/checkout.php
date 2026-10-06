<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use Tsara\Client;

$tsara = new Client(secretKey: (string) getenv('TSARA_SECRET_KEY'));
$checkout = $tsara->checkout->create((string) getenv('TSARA_PUBLIC_KEY'), [
    'trx_id' => 'order_' . bin2hex(random_bytes(6)),
    'email' => 'customer@example.com',
    'name' => 'Example Customer',
    'amount' => 1000,
    'success_url' => 'https://merchant.example/payments/success',
    'cancel_url' => 'https://merchant.example/payments/cancel',
]);

header('Content-Type: application/json');
echo json_encode($checkout, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

