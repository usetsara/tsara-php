<?php
declare(strict_types=1);
namespace Tsara\Resource;
use Generator;
use Tsara\Client;
use Tsara\Pagination\Page;
final class PaymentLinks
{
    public function __construct(private readonly Client $client) {}
    public function create(array $payload): array { return $this->client->post('/payment-links', $payload); }
    public function all(array $filters = []): array { return $this->client->get('/payment-links', $filters); }
    public function page(array $filters = []): Page { return $this->client->page('/payment-links', $filters); }
    public function iterate(array $filters = []): Generator { return $this->client->iterate('/payment-links', $filters); }
    public function retrieve(string $id): array { return $this->client->get('/payment-links', ['id' => $id]); }
    public function transactions(array $filters = []): array { return $this->client->get('/payment-links/transactions', $filters); }
    public function updateStatus(string $uid, string $status): array { return $this->client->post('/payment-links/status', compact('uid', 'status')); }
}
