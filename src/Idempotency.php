<?php

declare(strict_types=1);

namespace Tsara;

use InvalidArgumentException;

final class Idempotency
{
    public static function generate(string $prefix = 'tsara'): string
    {
        $prefix = trim($prefix);
        if ($prefix === '' || !preg_match('/^[A-Za-z0-9_-]+$/', $prefix)) throw new InvalidArgumentException('Idempotency prefix may contain only letters, numbers, underscores, and hyphens.');
        return strtolower($prefix) . '_' . bin2hex(random_bytes(16));
    }
}
