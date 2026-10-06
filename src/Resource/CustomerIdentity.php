<?php
declare(strict_types=1);
namespace Tsara\Resource;
use Tsara\Client;
final class CustomerIdentity {
    public function __construct(private readonly Client $client) {}
    public function retrieve(string $number, ?string $type = null): array { return $this->client->get('/customers/identity', ['number' => $number, 'type' => $type]); }
    public function initiate(string $number, string $type = 'BVN', ?string $customerId = null, ?string $scenario = null): array {
        return $this->client->post('/customers/identity', array_filter(['number' => $number, 'type' => strtoupper($type), 'customer_id' => $customerId, 'scenario' => $scenario], static fn($v) => $v !== null));
    }
    public function validate(string $number, string $otp, string $type = 'BVN', ?string $scenario = null): array {
        return $this->client->post('/customers/identity/validate', array_filter(['number' => $number, 'otp' => $otp, 'type' => strtoupper($type), 'scenario' => $scenario], static fn($v) => $v !== null));
    }
}
