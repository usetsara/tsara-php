<?php
declare(strict_types=1);
namespace Tsara\Resource;
use Generator;
use Tsara\Client;
use Tsara\Pagination\Page;
final class StablecoinWallets
{
    public function __construct(private readonly Client $client) {}
    public function create(array $payload): array { return $this->client->post('/stablecoin/wallets', $payload); }
    public function all(array $filters = []): array { return $this->client->get('/stablecoin/wallets', $filters); }
    public function page(array $filters = []): Page { return $this->client->page('/stablecoin/wallets', $filters); }
    public function iterate(array $filters = []): Generator { return $this->client->iterate('/stablecoin/wallets', $filters); }
    public function retrieve(string $identifier): array { return $this->all(['uid' => $identifier]); }
    public function retrieveByReference(string $reference): array { return $this->all(['reference' => $reference]); }
    public function balance(array $identifier): array { return $this->client->get('/stablecoin/wallets/balance', $identifier); }
    public function rate(): array { return $this->client->get('/stablecoin/wallets/rate'); }
}
