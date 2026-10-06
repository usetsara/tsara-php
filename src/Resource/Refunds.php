<?php
declare(strict_types=1);
namespace Tsara\Resource;
use Generator;
use Tsara\Client;
use Tsara\Pagination\Page;
final class Refunds
{
    public function __construct(private readonly Client $client) {}
    public function create(array $payload, ?string $idempotencyKey = null): array { return $this->client->post('/refunds', $payload, $idempotencyKey); }
    public function all(array $filters = []): array { return $this->client->get('/refunds', $filters); }
    public function page(array $filters = []): Page { return $this->client->page('/refunds', $filters); }
    public function iterate(array $filters = []): Generator { return $this->client->iterate('/refunds', $filters); }
    public function retrieve(string $identifier): array { return $this->all(['reference' => $identifier]); }
    public function process(array $identifier): array { return $this->client->post('/refunds/process', $identifier); }
    public function finalize(array $identifier): array { return $this->client->post('/refunds/finalize', $identifier); }
}
