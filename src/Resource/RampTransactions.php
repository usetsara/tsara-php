<?php
declare(strict_types=1);
namespace Tsara\Resource;
use Generator;
use Tsara\Client;
use Tsara\Pagination\Page;
final class RampTransactions
{
    public function __construct(private readonly Client $client) {}
    public function all(array $filters = []): array { return $this->client->get('/ramp/transactions', $filters); }
    public function page(array $filters = []): Page { return $this->client->page('/ramp/transactions', $filters); }
    public function iterate(array $filters = []): Generator { return $this->client->iterate('/ramp/transactions', $filters); }
    public function retrieve(string $identifier, bool $refresh = false): array { return $this->all(['id' => $identifier, 'refresh' => $refresh]); }
    public function retrieveByReference(string $reference, bool $refresh = false): array { return $this->all(['reference' => $reference, 'refresh' => $refresh]); }
    public function reconcile(string $identifier): array { return $this->retrieve($identifier, true); }
}
