<?php

declare(strict_types=1);

namespace Tsara\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tsara\Client;

final class ClientTest extends TestCase
{
    public function testRejectsPublicKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Client('pk_test_not_allowed');
    }

    public function testRejectsEnvironmentMismatch(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Client('sk_live_example', 'test');
    }
    public function testRejectsInsecureRemoteBaseUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Client('sk_test_example', baseUrl: 'http://api.example.com/v1');
    }

    public function testAllowsLocalHttpBaseUrl(): void
    {
        $client = new Client('sk_test_example', baseUrl: 'http://127.0.0.1:8080/v1');
        self::assertSame('test', $client->environment);
    }

    public function testRejectsCredentialsInBaseUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Client('sk_test_example', baseUrl: 'https://user@api.example.com/v1');
    }

}
