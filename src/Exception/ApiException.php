<?php

declare(strict_types=1);

namespace Tsara\Exception;

use RuntimeException;

class ApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $httpStatus = 0,
        public readonly ?int $statusCode = null,
        public readonly array $errors = [],
        public readonly ?string $requestId = null,
        public readonly bool $retryable = false,
    ) {
        parent::__construct($message, $httpStatus);
    }
}
