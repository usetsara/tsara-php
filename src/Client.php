<?php

declare(strict_types=1);

namespace Tsara;

use Closure;
use Generator;
use InvalidArgumentException;
use JsonException;
use Throwable;
use Tsara\Exception\ApiException;
use Tsara\Exception\AuthenticationException;
use Tsara\Exception\AuthorizationException;
use Tsara\Exception\ConflictException;
use Tsara\Exception\NetworkException;
use Tsara\Exception\NotFoundException;
use Tsara\Exception\RateLimitException;
use Tsara\Exception\ServerException;
use Tsara\Exception\TimeoutException;
use Tsara\Exception\ValidationException;
use Tsara\Pagination\Page;
use Tsara\Resource\ApiKeys;
use Tsara\Resource\Checkout;
use Tsara\Resource\Bills;
use Tsara\Resource\CryptoBills;
use Tsara\Resource\Customers;
use Tsara\Resource\CustomerIdentity;
use Tsara\Resource\PaymentLinks;
use Tsara\Resource\Payouts;
use Tsara\Resource\Refunds;
use Tsara\Resource\RampTransactions;
use Tsara\Resource\RampWidgets;
use Tsara\Resource\StablecoinAddresses;
use Tsara\Resource\StablecoinOfframps;
use Tsara\Resource\StablecoinOnramps;
use Tsara\Resource\StablecoinTransfers;
use Tsara\Resource\StablecoinWallets;
use Tsara\Resource\Transactions;
use Tsara\Resource\Transfers;
use Tsara\Resource\Webhooks;

final class Client
{
    private const DEFAULT_BASE_URL = 'https://api.tsara.ng/v1';
    private const RETRYABLE_STATUSES = [429, 502, 503, 504];

    public readonly ApiKeys $apiKeys;
    public readonly Transactions $transactions;
    public readonly Checkout $checkout;
    public readonly Bills $bills;
    public readonly CryptoBills $cryptoBills;
    public readonly Customers $customers;
    public readonly CustomerIdentity $customerIdentity;
    public readonly PaymentLinks $paymentLinks;
    public readonly Transfers $transfers;
    public readonly Payouts $payouts;
    public readonly Refunds $refunds;
    public readonly RampWidgets $rampWidgets;
    public readonly RampTransactions $rampTransactions;
    public readonly StablecoinOnramps $stablecoinOnramps;
    public readonly StablecoinOfframps $stablecoinOfframps;
    public readonly StablecoinWallets $stablecoinWallets;
    public readonly StablecoinAddresses $stablecoinAddresses;
    public readonly StablecoinTransfers $stablecoinTransfers;
    public readonly Webhooks $webhooks;
    public readonly string $environment;

    /** @param null|Closure(string,string,array<string,string>,?string,int):array{status:int,headers?:array<string,string>,body:string} $transport */
    public function __construct(
        private readonly string $secretKey,
        ?string $environment = null,
        private readonly int $timeout = 30,
        private readonly string $baseUrl = self::DEFAULT_BASE_URL,
        private readonly ?Closure $transport = null,
        private readonly int $maxRetries = 2,
    ) {
        $keyEnvironment = str_starts_with($secretKey, 'sk_test_') ? 'test' : (str_starts_with($secretKey, 'sk_live_') ? 'live' : null);
        if ($keyEnvironment === null) throw new InvalidArgumentException('A secret key beginning with sk_test_ or sk_live_ is required.');
        if ($environment !== null && $environment !== $keyEnvironment) throw new InvalidArgumentException('The environment does not match the secret key prefix.');
        if ($timeout < 1) throw new InvalidArgumentException('Timeout must be positive.');
        if ($maxRetries < 0 || $maxRetries > 5) throw new InvalidArgumentException('Max retries must be between 0 and 5.');
        $this->validateBaseUrl($baseUrl);
        $this->environment = $environment ?? $keyEnvironment;

        $this->apiKeys = new ApiKeys($this);
        $this->transactions = new Transactions($this);
        $this->checkout = new Checkout($this);
        $this->bills = new Bills($this);
        $this->cryptoBills = new CryptoBills($this);
        $this->customers = new Customers($this);
        $this->customerIdentity = new CustomerIdentity($this);
        $this->paymentLinks = new PaymentLinks($this);
        $this->transfers = new Transfers($this);
        $this->payouts = new Payouts($this);
        $this->refunds = new Refunds($this);
        $this->rampWidgets = new RampWidgets($this);
        $this->rampTransactions = new RampTransactions($this);
        $this->stablecoinOnramps = new StablecoinOnramps($this);
        $this->stablecoinOfframps = new StablecoinOfframps($this);
        $this->stablecoinWallets = new StablecoinWallets($this);
        $this->stablecoinAddresses = new StablecoinAddresses($this);
        $this->stablecoinTransfers = new StablecoinTransfers($this);
        $this->webhooks = new Webhooks($this);
    }

    public function get(string $path, array $query = []): array
    {
        return $this->request('GET', $path, query: $query);
    }

    public function post(string $path, array $payload = [], ?string $idempotencyKey = null): array
    {
        return $this->request('POST', $path, $payload, idempotencyKey: $idempotencyKey);
    }

    public function publicPost(string $path, array $payload = []): array
    {
        return $this->request('POST', $path, $payload, authenticate: false);
    }

    public function page(string $path, array $query = []): Page
    {
        return Page::fromResponse($this->get($path, $query));
    }

    public function iterate(string $path, array $query = []): Generator
    {
        $pageNumber = max(1, (int) ($query['page'] ?? 1));
        do {
            $page = $this->page($path, array_merge($query, ['page' => $pageNumber]));
            foreach ($page->items as $item) yield $item;
            $pageNumber++;
        } while ($page->hasNextPage());
    }

    public function request(string $method, string $path, array $payload = [], array $query = [], ?string $idempotencyKey = null, bool $authenticate = true): array
    {
        $method = strtoupper($method);
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');
        $query = array_filter($query, static fn ($value) => $value !== null);
        if ($query !== []) $url .= '?' . http_build_query($query);

        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json', 'User-Agent' => 'tsara-php/0.1.0'];
        if ($authenticate) $headers['Authorization'] = 'Bearer ' . $this->secretKey;
        if ($idempotencyKey !== null && $idempotencyKey !== '') $headers['Idempotency-Key'] = $idempotencyKey;
        $body = $payload === [] ? null : json_encode($payload, JSON_THROW_ON_ERROR);
        $safeToRetry = in_array($method, ['GET', 'HEAD', 'OPTIONS'], true) || ($idempotencyKey !== null && $idempotencyKey !== '');

        for ($attempt = 0; ; $attempt++) {
            try {
                $response = $this->transport ? ($this->transport)($method, $url, $headers, $body, $this->timeout) : $this->curl($method, $url, $headers, $body);
            } catch (ApiException $exception) {
                if (!$safeToRetry || !$exception->retryable || $attempt >= $this->maxRetries) throw $exception;
                $this->waitBeforeRetry($attempt);
                continue;
            } catch (Throwable $exception) {
                $networkException = str_contains(strtolower($exception->getMessage()), 'timed out')
                    ? new TimeoutException($exception->getMessage() ?: 'Tsara request timed out.', retryable: true)
                    : new NetworkException($exception->getMessage() ?: 'Tsara network request failed.', retryable: true);
                if (!$safeToRetry || $attempt >= $this->maxRetries) throw $networkException;
                $this->waitBeforeRetry($attempt);
                continue;
            }

            $status = (int) $response['status'];
            $responseHeaders = array_change_key_case($response['headers'] ?? [], CASE_LOWER);
            if ($safeToRetry && in_array($status, self::RETRYABLE_STATUSES, true) && $attempt < $this->maxRetries) {
                $this->waitBeforeRetry($attempt, $responseHeaders['retry-after'] ?? null);
                continue;
            }

            try { $data = json_decode($response['body'], true, 512, JSON_THROW_ON_ERROR); }
            catch (JsonException) { throw new ApiException('Tsara returned invalid JSON.', $status, requestId: $responseHeaders['x-request-id'] ?? null); }
            $data = is_array($data) ? $data : ['data' => $data];
            if ($status >= 200 && $status < 300 && ($data['success'] ?? true) !== false) return $data;
            throw $this->apiException($status, $data, $responseHeaders);
        }
    }

    private function apiException(int $status, array $data, array $headers): ApiException
    {
        $arguments = [
            (string) ($data['message'] ?? 'Tsara API request failed.'),
            $status,
            isset($data['status_code']) ? (int) $data['status_code'] : null,
            is_array($data['errors'] ?? null) ? $data['errors'] : [],
            $headers['x-request-id'] ?? ($data['request_id'] ?? null),
            in_array($status, self::RETRYABLE_STATUSES, true),
        ];
        $class = match (true) {
            $status === 401 => AuthenticationException::class,
            $status === 403 => AuthorizationException::class,
            in_array($status, [400, 402, 422], true) => ValidationException::class,
            $status === 404 => NotFoundException::class,
            $status === 409 => ConflictException::class,
            $status === 429 => RateLimitException::class,
            $status >= 500 => ServerException::class,
            default => ApiException::class,
        };
        return new $class(...$arguments);
    }

    private function waitBeforeRetry(int $attempt, string|int|null $retryAfter = null): void
    {
        $milliseconds = is_numeric($retryAfter)
            ? min(2000, max(0, (int) $retryAfter * 1000))
            : min(1000, 100 * (2 ** $attempt));
        if ($milliseconds > 0) usleep($milliseconds * 1000);
    }

    private function validateBaseUrl(string $baseUrl): void
    {
        $parts = parse_url($baseUrl);
        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            throw new InvalidArgumentException('The base URL must be an absolute HTTP or HTTPS URL.');
        }

        $scheme = strtolower((string) $parts['scheme']);
        $host = strtolower(trim((string) $parts['host'], '[]'));
        $isLocal = in_array($host, ['localhost', '127.0.0.1', '::1'], true);
        if ($scheme !== 'https' && !($scheme === 'http' && $isLocal)) {
            throw new InvalidArgumentException('The base URL must use HTTPS unless it targets localhost.');
        }
        if (isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
            throw new InvalidArgumentException('The base URL cannot contain credentials, a query string, or a fragment.');
        }
    }

    /** @return array{status:int,headers:array<string,string>,body:string} */
    private function curl(string $method, string $url, array $headers, ?string $body): array
    {
        $curl = curl_init($url);
        $responseHeaders = [];
        $headerLines = array_map(static fn ($name, $value) => $name . ': ' . $value, array_keys($headers), $headers);
        curl_setopt_array($curl, [CURLOPT_CUSTOMREQUEST => $method, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => $this->timeout, CURLOPT_HTTPHEADER => $headerLines, CURLOPT_HEADERFUNCTION => static function ($curl, string $line) use (&$responseHeaders): int { $parts = explode(':', $line, 2); if (count($parts) === 2) $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]); return strlen($line); }]);
        if ($body !== null) curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        $responseBody = curl_exec($curl);
        if ($responseBody === false) {
            $message = curl_error($curl);
            $errorNumber = curl_errno($curl);
            curl_close($curl);
            if ($errorNumber === CURLE_OPERATION_TIMEDOUT) throw new TimeoutException($message ?: 'Tsara request timed out.', retryable: true);
            throw new NetworkException($message ?: 'Tsara network request failed.', retryable: true);
        }
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
        return ['status' => $status, 'headers' => $responseHeaders, 'body' => (string) $responseBody];
    }
}
