<?php
declare(strict_types=1);
namespace Tsara\Resource;
use Generator;
use Tsara\Client;
use Tsara\Pagination\Page;
final class RampWidgets
{
    public function __construct(private readonly Client $client) {}
    public function create(array $payload): array { return $this->client->post('/ramp/widgets', $payload); }
    public function all(array $filters = []): array { return $this->client->get('/ramp/widgets', $filters); }
    public function page(array $filters = []): Page { return $this->client->page('/ramp/widgets', $filters); }
    public function iterate(array $filters = []): Generator { return $this->client->iterate('/ramp/widgets', $filters); }
    public function retrieve(string $identifier): array { return $this->all(['id' => $identifier]); }
    public function retrieveByPublicKey(string $publicKey): array { return $this->all(['public_key' => $publicKey]); }
    public function update(string $identifier, array $payload): array { return $this->client->post('/ramp/widgets/update', ['id' => $identifier] + $payload); }
    public function updateDomains(string $identifier, array $domains): array { return $this->client->post('/ramp/widgets/domains', ['widget_id' => $identifier, 'domains' => $domains]); }
    public function updateStatus(string $identifier, string $status): array { return $this->client->post('/ramp/widgets/status', ['id' => $identifier, 'status' => strtolower($status)]); }
    public function analytics(array $filters = []): array { return $this->client->get('/ramp/widgets/analytics', $filters); }
}
