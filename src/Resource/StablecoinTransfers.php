<?php
declare(strict_types=1);
namespace Tsara\Resource;
use Generator;
use Tsara\Client;
use Tsara\Pagination\Page;
final class StablecoinTransfers
{
    public function __construct(private readonly Client $client) {}
    public function create(array $payload, string $idempotencyKey): array { $payload['reference'] = $payload['reference'] ?? $idempotencyKey; return $this->client->post('/stablecoin/wallets/transfers', $payload, $idempotencyKey); }
    public function all(array $filters = []): array { return $this->client->get('/stablecoin/wallets/transfers', $filters); }
    public function page(array $filters = []): Page { return $this->client->page('/stablecoin/wallets/transfers', $filters); }
    public function iterate(array $filters = []): Generator { return $this->client->iterate('/stablecoin/wallets/transfers', $filters); }
    public function retrieve(string $identifier): array { return $this->all(['uid' => $identifier]); }
    public function retrieveByReference(string $reference): array { return $this->all(['reference' => $reference]); }
}
