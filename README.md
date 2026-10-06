# Tsara PHP

Official server-side PHP SDK for the Tsara API.

## Requirements

- PHP 8.2 or newer
- cURL and JSON extensions

## Install

```bash
composer require tsara/tsara-php
```

## Configure

```php
use Tsara\Client;

$tsara = new Client(secretKey: getenv('TSARA_SECRET_KEY'));
```

Use `sk_test_...` while developing and `sk_live_...` in production. The SDK rejects public keys and keys that do not match an explicitly selected environment. Never expose a secret key in browser code.

The default API URL is `https://api.tsara.ng/v1`. A custom `baseUrl` must use HTTPS; plain HTTP is accepted only for `localhost`, `127.0.0.1`, or `::1` development.

## Create Checkout

Checkout creation is the only SDK resource method that accepts a public key. The request intentionally omits secret-key authorization.

```php
$checkout = $tsara->checkout->create(
    publicKey: getenv('TSARA_PUBLIC_KEY'),
    payload: [
        'trx_id' => 'order_001',
        'email' => 'customer@example.com',
        'name' => 'Customer Name',
        'amount' => 1000,
        'success_url' => 'https://merchant.example/payments/success',
        'cancel_url' => 'https://merchant.example/payments/cancel',
    ],
);
```

Open the returned Checkout URL with `@tsara/checkout-js` or redirect the customer to it. Confirm payment using a signed webhook or a server-side status request before delivering value.

## Resources

The client exposes:

- `$tsara->transactions` and `$tsara->checkout`
- `$tsara->paymentLinks`
- `$tsara->customers` and `$tsara->customerIdentity`
- `$tsara->transfers`, `$tsara->payouts`, and `$tsara->refunds`
- `$tsara->bills` and `$tsara->cryptoBills`
- `$tsara->stablecoinOnramps`, `$tsara->stablecoinOfframps`, `$tsara->stablecoinWallets`, `$tsara->stablecoinAddresses`, and `$tsara->stablecoinTransfers`
- `$tsara->rampWidgets` and `$tsara->rampTransactions`
- `$tsara->apiKeys` and `$tsara->webhooks`

```php
$transaction = $tsara->transactions->retrieve('order_001');
$successful = $tsara->checkout->all(['status' => 'success']);
```

## Idempotency and retries

Use a unique idempotency key for every logical money-moving operation. Reuse the same key when retrying the same operation; generate a new key for a new operation.

```php
use Tsara\Idempotency;

$refund = $tsara->refunds->create(
    ['transaction_id' => 'ts_123', 'amount' => 1000],
    Idempotency::generate('refund'),
);
```

The SDK retries transient `429`, `502`, `503`, and `504` responses. Read requests are safe to retry automatically. Write requests are retried only when an idempotency key is present.

## Pagination

```php
foreach ($tsara->transactions->iterate(['status' => 'success']) as $transaction) {
    // Process every result across all pages.
}
```

## Errors

HTTP and network failures throw typed exceptions under `Tsara\Exception`, including authentication, authorization, validation, conflict, rate-limit, server, network, and timeout errors. `ApiException` exposes the HTTP status, API status code, validation errors, request ID, and whether the error is retryable.

## Verify webhooks

Verify the exact raw request body before decoding JSON. Tsara sends the HMAC-SHA512 digest in `X_TSARA_SIGNATURE`.

```php
use Tsara\Webhook;

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_TSARA_SIGNATURE'] ?? '';
$event = Webhook::constructEvent($payload, $signature, getenv('TSARA_WEBHOOK_SECRET'));
```

Return a `2xx` response only after the event is safely accepted. Make event processing idempotent because webhook delivery can be retried.

## Framework examples

- `examples/plain-php/checkout.php`
- `examples/laravel/TsaraService.php`

## Credential rotation

API-key and webhook-secret rotation are separate. A rotated API key is returned once; store it securely and recreate the SDK client. A rotated webhook secret starts with `whsec_test_` or `whsec_live_` and does not change API authentication.

## Release status

`0.1.x` is a release-candidate line. Validate your test integration before enabling live money movement.

