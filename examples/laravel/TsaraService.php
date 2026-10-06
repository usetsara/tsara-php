<?php

declare(strict_types=1);

namespace App\Services;

use Tsara\Client;

final class TsaraService
{
    public readonly Client $client;

    public function __construct()
    {
        $this->client = new Client(secretKey: (string) config('services.tsara.secret_key'));
    }
}

