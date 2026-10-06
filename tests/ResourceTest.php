<?php

declare(strict_types=1);

namespace Tsara\Tests;

use PHPUnit\Framework\TestCase;
use Tsara\Client;

final class ResourceTest extends TestCase
{
    public function testTransactionRetrieveUsesSecretAuthentication(): void
    {
        $seen = [];
        $client = new Client('sk_test_example', transport: function ($method, $url, $headers, $body) use (&$seen) { $seen = compact('method', 'url', 'headers', 'body'); return ['status' => 200, 'body' => '{"success":true}']; });
        $client->transactions->retrieve('order_1');
        self::assertStringContainsString('/transactions?trx_id=order_1', $seen['url']);
        self::assertSame('Bearer sk_test_example', $seen['headers']['Authorization']);
    }

    public function testCheckoutCreationDoesNotSendSecretKey(): void
    {
        $seen = [];
        $client = new Client('sk_test_example', transport: function ($method, $url, $headers, $body) use (&$seen) { $seen = compact('method', 'url', 'headers', 'body'); return ['status' => 200, 'body' => '{"success":true}']; });
        $client->checkout->create('pk_test_example', ['trx_id' => 'order_1', 'amount' => 100]);
        self::assertArrayNotHasKey('Authorization', $seen['headers']);
        self::assertStringContainsString('pk_test_example', $seen['body']);
    }
}
