<?php

declare(strict_types=1);

namespace Tsara\Resource;

use InvalidArgumentException;
use Tsara\Client;

final class Checkout
{
    public function __construct(private readonly Client $client) {}

    public function create(string $publicKey, array $payload): array
    {
        if (!str_starts_with($publicKey, 'pk_test_') && !str_starts_with($publicKey, 'pk_live_')) throw new InvalidArgumentException('A Checkout public key is required.');
        return $this->client->publicPost('/checkout', ['public_key' => $publicKey] + $payload);
    }

    public function all(array $filters = []): array
    {
        return $this->client->get('/checkout', $filters);
    }

    public function retrieve(string $transactionId): array
    {
        return $this->client->get('/checkout', ['trx_id' => $transactionId]);
    }

    public function status(string $transactionId): array
    {
        return $this->client->get('/checkout/status', ['trx_id' => $transactionId]);
    }

    public function paymentInstructions(string $transactionId, ?string $method = null): array
    {
        return $this->client->get('/checkout/account', ['trx_id' => $transactionId, 'method' => $method]);
    }

    public function selectCrypto(string $transactionId, string $asset): array
    {
        return $this->client->post('/checkout/crypto', ['trx_id' => $transactionId, 'asset' => $asset]);
    }
}
