<?php
declare(strict_types=1);
namespace Tsara\Resource;
use Generator;
use Tsara\Client;
use Tsara\Pagination\Page;
final class Customers {
    public function __construct(private readonly Client $client) {}
    public function create(array $payload): array { return $this->client->post('/customers', $payload); }
    public function all(array $filters = []): array { return $this->client->get('/customers', $filters); }
    public function page(array $filters = []): Page { return $this->client->page('/customers', $filters); }
    public function iterate(array $filters = []): Generator { return $this->client->iterate('/customers', $filters); }
    public function retrieve(string $customerId): array { return $this->client->get('/customers', ['id' => $customerId]); }
    public function update(string $customerId, array $payload): array { return $this->client->post('/customers/update', ['customer_id' => $customerId] + $payload); }
}
