<?php
declare(strict_types=1);
namespace Tsara\Resource;
use Tsara\Client;
final class ApiKeys
{
    public function __construct(private readonly Client $client) {}
    public function config(): array { return $this->client->get('/api-keys/config'); }
    public function rotateSecret(): array { return $this->client->post('/api-keys/rotate-secret'); }
}
