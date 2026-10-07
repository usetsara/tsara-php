<?php
declare(strict_types=1);
namespace Tsara\Resource;
use Generator;
use Tsara\Client;
use Tsara\Pagination\Page;
final class Transfers
{
    public function __construct(private readonly Client $client) {}
    public function all(array $filters = []): array { return $this->client->get('/transfers', $filters); }
    public function page(array $filters = []): Page { return $this->client->page('/transfers', $filters); }
    public function iterate(array $filters = []): Generator { return $this->client->iterate('/transfers', $filters); }
    public function retrieve(string $uid): array { return $this->all(['uid' => $uid]); }
    public function create(array $payload, string $idempotencyKey): array { $payload['idempotency_key'] = $idempotencyKey; return $this->client->post('/transfers', $payload, $idempotencyKey); }
    public function banks(): array { return $this->client->get('/transfers/banks'); }
    public function nameEnquiry(string $bankCode, string $accountNumber): array { return $this->client->post('/transfers/name-enquiry', ['bank_code' => $bankCode, 'account_number' => $accountNumber]); }
    public function status(string $uid): array { return $this->client->post('/transfers/status', ['uid' => $uid]); }
}
