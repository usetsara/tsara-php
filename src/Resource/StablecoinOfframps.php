<?php
declare(strict_types=1);
namespace Tsara\Resource;
use Generator;
use Tsara\Client;
use Tsara\Pagination\Page;
final class StablecoinOfframps
{
    public function __construct(private readonly Client $client) {}
    public function quote(float $amount, string $asset = 'solana:usdc', string $currency = 'NGN'): array { return $this->client->post('/stablecoin/offramp/quote', ['amount' => $amount, 'asset' => strtolower($asset), 'fiat_currency' => strtoupper($currency)]); }
    public function create(array $payload, string $idempotencyKey): array { $payload['reference'] = $payload['reference'] ?? $idempotencyKey; return $this->client->post('/stablecoin/offramp', $payload, $idempotencyKey); }
    public function all(array $filters = []): array { return $this->client->get('/stablecoin/offramp', $filters); }
    public function page(array $filters = []): Page { return $this->client->page('/stablecoin/offramp', $filters); }
    public function iterate(array $filters = []): Generator { return $this->client->iterate('/stablecoin/offramp', $filters); }
    public function retrieve(string $identifier, bool $refresh = false): array { return $this->all(['uid' => $identifier, 'refresh' => $refresh]); }
    public function retrieveByReference(string $reference, bool $refresh = false): array { return $this->all(['reference' => $reference, 'refresh' => $refresh]); }
    public function rate(string $asset = 'solana:usdc', string $currency = 'NGN'): array { return $this->client->get('/stablecoin/offramp/rate', ['asset' => strtolower($asset), 'fiat_currency' => strtoupper($currency)]); }
    public function status(string $identifier, bool $refresh = false): array { return $this->client->get('/stablecoin/offramp/status', ['uid' => $identifier, 'refresh' => $refresh]); }
    public function reconcile(string $identifier): array { return $this->status($identifier, true); }
}
