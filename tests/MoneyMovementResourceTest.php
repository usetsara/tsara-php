<?php

declare(strict_types=1);

namespace Tsara\Tests;

use PHPUnit\Framework\TestCase;
use Tsara\Client;

final class MoneyMovementResourceTest extends TestCase
{
    public function testTransferAddsIdempotencyToBodyAndHeader(): void
    {
        $seen = [];
        $client = new Client('sk_live_example', transport: function ($method, $url, $headers, $body) use (&$seen) { $seen = compact('method', 'url', 'headers', 'body'); return ['status' => 200, 'body' => '{"success":true}']; });
        $client->transfers->create(['amount' => 1000, 'bank_code' => '001', 'account_number' => '1234567890'], 'idem_12345678');
        self::assertSame('idem_12345678', $seen['headers']['Idempotency-Key']);
        self::assertStringContainsString('"idempotency_key":"idem_12345678"', $seen['body']);
    }

    public function testTransferSupportsSandboxWallet(): void
    {
        $seen = [];
        $client = new Client('sk_test_example', transport: function ($method, $url, $headers, $body) use (&$seen) { $seen = compact('method', 'url', 'headers', 'body'); return ['status' => 200, 'body' => '{"success":true}']; });
        $client->transfers->create(['amount' => 1000], 'sandbox_transfer_example');
        self::assertSame('Bearer sk_test_example', $seen['headers']['Authorization']);
        self::assertSame('sandbox_transfer_example', $seen['headers']['Idempotency-Key']);
        self::assertStringEndsWith('/transfers', $seen['url']);
    }

    public function testPayoutAndRefundUseCanonicalPaths(): void
    {
        $urls = [];
        $client = new Client('sk_test_example', transport: function ($method, $url) use (&$urls) { $urls[] = $url; return ['status' => 200, 'body' => '{"success":true}']; });
        $client->payouts->create(['reference' => 'po_1']);
        $client->refunds->process(['reference' => 'rf_1']);
        self::assertStringEndsWith('/payouts', $urls[0]);
        self::assertStringEndsWith('/refunds/process', $urls[1]);
    }
}
