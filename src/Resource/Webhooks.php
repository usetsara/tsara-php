<?php

declare(strict_types=1);

namespace Tsara\Resource;

use Generator;
use Tsara\Client;
use Tsara\Pagination\Page;

final class Webhooks
{
    public function __construct(private readonly Client $client) {}
    public function resendTransaction(array $identifier): array { return $this->client->post('/webhook/resend', $identifier); }
    public function logs(array $filters = []): array { return $this->client->get('/webhook/logs', $filters); }
    public function logsPage(array $filters = []): Page { return $this->client->page('/webhook/logs', $filters); }
    public function iterateLogs(array $filters = []): Generator { return $this->client->iterate('/webhook/logs', $filters); }
    public function attempts(array $filters = []): array { return $this->client->get('/webhook/attempts', $filters); }
    public function test(array $payload = []): array { return $this->client->post('/webhook/test', $payload); }
    public function rotateSecret(): array { return $this->client->post('/webhook/rotate-secret'); }
    public function config(): array { return $this->client->get('/webhook/config'); }
    public function resendRefund(array $identifier): array { return $this->client->post('/webhook/refund-resend', $identifier); }
    public function refundLogs(array $filters = []): array { return $this->client->get('/webhook/refund-logs', $filters); }
    public function refundLogsPage(array $filters = []): Page { return $this->client->page('/webhook/refund-logs', $filters); }
    public function iterateRefundLogs(array $filters = []): Generator { return $this->client->iterate('/webhook/refund-logs', $filters); }
    public function refundAttempts(array $filters = []): array { return $this->client->get('/webhook/refund-attempts', $filters); }
}
