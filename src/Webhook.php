<?php

declare(strict_types=1);

namespace Tsara;

use JsonException;
use Tsara\Exception\WebhookSignatureException;

final class Webhook
{
    public static function verify(string $payload, string $signature, string $secret): bool
    {
        $signature = trim($signature);
        if (str_starts_with(strtolower($signature), 'sha512=')) $signature = substr($signature, 7);
        if ($signature === '' || $secret === '') return false;
        return hash_equals(hash_hmac('sha512', $payload, $secret), $signature);
    }

    public static function constructEvent(string $payload, string $signature, string $secret): array
    {
        if (!self::verify($payload, $signature, $secret)) throw new WebhookSignatureException('Invalid Tsara webhook signature.');
        try { $event = json_decode($payload, true, 512, JSON_THROW_ON_ERROR); }
        catch (JsonException $exception) { throw new WebhookSignatureException('Tsara webhook payload is not valid JSON.'); }
        return is_array($event) ? $event : ['data' => $event];
    }
}
