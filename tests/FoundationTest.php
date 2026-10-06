<?php

declare(strict_types=1);

namespace Tsara\Tests;

use PHPUnit\Framework\TestCase;
use Tsara\Client;
use Tsara\Exception\AuthenticationException;
use Tsara\Idempotency;
use Tsara\Webhook;

final class FoundationTest extends TestCase
{
    public function testMapsAuthenticationError(): void
    {
        $client = new Client('sk_test_example', transport: fn () => ['status' => 401, 'body' => '{"success":false,"message":"Unauthorized"}'], maxRetries: 0);
        $this->expectException(AuthenticationException::class);
        $client->transactions->all();
    }

    public function testRetriesSafeRead(): void
    {
        $attempts = 0;
        $client = new Client('sk_test_example', transport: function () use (&$attempts) { $attempts++; return $attempts === 1 ? ['status' => 503, 'body' => '{"success":false}'] : ['status' => 200, 'body' => '{"success":true}']; }, maxRetries: 1);
        $client->transactions->all();
        self::assertSame(2, $attempts);
    }

    public function testDoesNotRetryUnsafeWriteWithoutIdempotencyKey(): void
    {
        $attempts = 0;
        $client = new Client('sk_test_example', transport: function () use (&$attempts) { $attempts++; return ['status' => 503, 'body' => '{"success":false}']; }, maxRetries: 1);
        try { $client->paymentLinks->create(['title' => 'Test']); } catch (\Throwable) {}
        self::assertSame(1, $attempts);
    }

    public function testPaginationAndIteration(): void
    {
        $client = new Client('sk_test_example', transport: function ($method, $url) { parse_str((string) parse_url($url, PHP_URL_QUERY), $query); $page = (int) ($query['page'] ?? 1); return ['status' => 200, 'body' => json_encode(['success' => true, 'data' => [['id' => $page]], 'meta' => ['current_page' => $page, 'last_page' => 2]])]; });
        self::assertSame([['id' => 1], ['id' => 2]], iterator_to_array($client->transactions->iterate()));
    }

    public function testIdempotencyAndWebhookHelpers(): void
    {
        self::assertMatchesRegularExpression('/^refund_[a-f0-9]{32}$/', Idempotency::generate('refund'));
        $payload = '{"event":"transaction.success"}';
        $signature = hash_hmac('sha512', $payload, 'whsec_test');
        self::assertSame('transaction.success', Webhook::constructEvent($payload, $signature, 'whsec_test')['event']);
        self::assertFalse(Webhook::verify($payload, 'bad', 'whsec_test'));
    }
}
