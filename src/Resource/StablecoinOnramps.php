<?php
declare(strict_types=1);
namespace Tsara\Resource;
use Generator;
use Tsara\Client;
use Tsara\Pagination\Page;
final class StablecoinOnramps
{
    public function __construct(private readonly Client $client) {}
    public function create(array $payload, string $idempotencyKey): array { $payload['reference'] = $payload['reference'] ?? $idempotencyKey; return $this->client->post('/stablecoin/onramp', $payload, $idempotencyKey); }
    public function all(array $filters = []): array { return $this->client->get('/stablecoin/onramp', $filters); }
    public function page(array $filters = []): Page { return $this->client->page('/stablecoin/onramp', $filters); }
    public function iterate(array $filters = []): Generator { return $this->client->iterate('/stablecoin/onramp', $filters); }
    public function retrieve(string $identifier, bool $refresh = false): array { return $this->all(['uid' => $identifier, 'refresh' => $refresh]); }
    public function retrieveByReference(string $reference, bool $refresh = false): array { return $this->all(['reference' => $reference, 'refresh' => $refresh]); }
    public function rate(string $asset = 'solana:usdc', string $currency = 'NGN'): array { return $this->client->get('/stablecoin/onramp/rate', ['asset' => strtolower($asset), 'currency' => strtoupper($currency)]); }
    public function status(string $identifier, bool $refresh = false): array { return $this->client->get('/stablecoin/onramp/status', ['uid' => $identifier, 'refresh' => $refresh]); }
    public function reconcile(string $identifier): array { return $this->status($identifier, true); }
}
