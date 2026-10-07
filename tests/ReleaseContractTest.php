<?php
declare(strict_types=1);
namespace Tsara\Tests;
use PHPUnit\Framework\TestCase;
use Tsara\Client;
use Tsara\Exception\AuthorizationException;
use Tsara\Exception\ValidationException;
use Tsara\Webhook;
final class ReleaseContractTest extends TestCase
{
    public function testEnvelopeFailureMapsToAuthorizationWithoutRetry(): void
    {
        foreach ([['success' => false], ['status' => 'failed'], []] as $flags) {
            $attempts = 0;
            $client = new Client('sk_test_example', transport: function () use (&$attempts, $flags) {
                $attempts++;
                return ['status' => 200, 'body' => json_encode($flags + ['status_code' => 403, 'message' => 'Disabled'])];
            });
            try {
                $client->transfers->banks();
                self::fail('Expected authorization failure.');
            } catch (AuthorizationException $error) {
                self::assertSame(200, $error->httpStatus);
                self::assertSame(403, $error->statusCode);
                self::assertFalse($error->retryable);
            }
            self::assertSame(1, $attempts);
        }
    }
    public function testInsufficientFundsEnvelopeIsValidationFailure(): void
    {
        $client = new Client('sk_test_example', transport: fn () => ['status' => 200, 'body' => '{"status":"failed","status_code":402,"message":"Not enough balance!"}']);
        $this->expectException(ValidationException::class);
        $client->payouts->create(['amount' => 1000], 'payout_example');
    }
    public function testIncomingTransferRetainsSenderAndWebhookSignature(): void
    {
        $payload = '{"event":"transfer.received","data":{"direction":"IN","sender":{"account_number":"1234567890"}}}';
        $event = Webhook::constructEvent($payload, hash_hmac('sha512', $payload, 'whsec_test_example'), 'whsec_test_example');
        self::assertSame('IN', $event['data']['direction']);
        self::assertSame('1234567890', $event['data']['sender']['account_number']);
        self::assertFalse(Webhook::verify($payload . ' ', hash_hmac('sha512', $payload, 'whsec_test_example'), 'whsec_test_example'));
    }
}
