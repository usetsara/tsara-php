<?php
declare(strict_types=1);
namespace Tsara\Resource;
use Generator;
use Tsara\Client;
use Tsara\Pagination\Page;
final class Payouts
{
    public function __construct(private readonly Client $client) {}
    public function create(array $payload, ?string $idempotencyKey = null): array { return $this->client->post('/payouts', $payload, $idempotencyKey); }
    public function all(array $filters = []): array { return $this->client->get('/payouts', $filters); }
    public function page(array $filters = []): Page { return $this->client->page('/payouts', $filters); }
    public function iterate(array $filters = []): Generator { return $this->client->iterate('/payouts', $filters); }
    public function retrieve(string $identifier): array { return $this->all(['reference' => $identifier]); }
    public function nameEnquiry(string $bankCode, string $accountNumber): array { return $this->client->post('/payouts/name-enquiry', ['bank_code' => $bankCode, 'account_number' => $accountNumber]); }
    public function createBulk(array $payload, ?string $idempotencyKey = null): array { return $this->client->post('/payouts/bulk', $payload, $idempotencyKey); }
    public function bulk(array $filters = []): array { return $this->client->get('/payouts/bulk', $filters); }
    public function reconcile(array $filters): array { return $this->client->get('/payouts/reconcile', $filters); }
    public function process(array $identifier): array { return $this->client->post('/payouts/process', $identifier); }
    public function resendWebhook(array $identifier): array { return $this->client->post('/payouts/webhook-resend', $identifier); }
    public function webhookAttempts(array $filters = []): array { return $this->client->get('/payouts/webhook-attempts', $filters); }
}
