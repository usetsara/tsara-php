<?php
declare(strict_types=1);
namespace Tsara\Resource;
use Generator;
use Tsara\Client;
use Tsara\Pagination\Page;
final class Transactions
{
    public function __construct(private readonly Client $client) {}
    public function all(array $filters = []): array { return $this->client->get('/transactions', $filters); }
    public function page(array $filters = []): Page { return $this->client->page('/transactions', $filters); }
    public function iterate(array $filters = []): Generator { return $this->client->iterate('/transactions', $filters); }
    public function retrieve(string $transactionId): array { return $this->client->get('/transactions', ['trx_id' => $transactionId]); }
    public function retrieveByReference(string $reference): array { return $this->client->get('/transactions', ['reference' => $reference]); }
    public function create(array $payload, ?string $idempotencyKey = null): array { return $this->client->post('/transactions', $payload, $idempotencyKey); }
    public function bills(array $filters = []): array { return $this->client->get('/transactions/bill', $filters); }
}
