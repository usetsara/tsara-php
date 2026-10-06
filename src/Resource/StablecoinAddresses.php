<?php
declare(strict_types=1);
namespace Tsara\Resource;
use Generator;
use Tsara\Client;
use Tsara\Pagination\Page;
final class StablecoinAddresses
{
    public function __construct(private readonly Client $client) {}
    public function create(array $payload): array { return $this->client->post('/stablecoin/wallets/addresses', $payload); }
    public function all(array $filters = []): array { return $this->client->get('/stablecoin/wallets/addresses', $filters); }
    public function page(array $filters = []): Page { return $this->client->page('/stablecoin/wallets/addresses', $filters); }
    public function iterate(array $filters = []): Generator { return $this->client->iterate('/stablecoin/wallets/addresses', $filters); }
    public function retrieve(string $identifier): array { return $this->all(['uid' => $identifier]); }
    public function retrieveByAddress(string $address): array { return $this->all(['address' => $address]); }
    public function balance(string $address): array { return $this->client->get('/stablecoin/wallets/addresses/balance', ['address' => $address]); }
    public function send(array $payload, string $idempotencyKey): array { $payload['reference'] = $payload['reference'] ?? $idempotencyKey; return $this->client->post('/stablecoin/wallets/addresses/send', $payload, $idempotencyKey); }
}
