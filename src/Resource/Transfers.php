<?php
declare(strict_types=1);
namespace Tsara\Resource;
use Generator;
use LogicException;
use Tsara\Client;
use Tsara\Pagination\Page;
final class Transfers
{
    public function __construct(private readonly Client $client) {}
    public function all(array $filters = []): array { $this->liveOnly(); return $this->client->get('/transfers', $filters); }
    public function page(array $filters = []): Page { $this->liveOnly(); return $this->client->page('/transfers', $filters); }
    public function iterate(array $filters = []): Generator { $this->liveOnly(); return $this->client->iterate('/transfers', $filters); }
    public function retrieve(string $uid): array { return $this->all(['uid' => $uid]); }
    public function create(array $payload, string $idempotencyKey): array { $this->liveOnly(); $payload['idempotency_key'] = $idempotencyKey; return $this->client->post('/transfers', $payload, $idempotencyKey); }
    public function banks(): array { $this->liveOnly(); return $this->client->get('/transfers/banks'); }
    public function nameEnquiry(string $bankCode, string $accountNumber): array { $this->liveOnly(); return $this->client->post('/transfers/name-enquiry', ['bank_code' => $bankCode, 'account_number' => $accountNumber]); }
    public function status(string $uid): array { $this->liveOnly(); return $this->client->post('/transfers/status', ['uid' => $uid]); }
    private function liveOnly(): void { if ($this->client->environment !== 'live') throw new LogicException('Business-wallet transfers require an sk_live_ key.'); }
}
