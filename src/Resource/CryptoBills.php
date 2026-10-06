<?php
declare(strict_types=1);
namespace Tsara\Resource;
use Generator;
use Tsara\Client;
use Tsara\Pagination\Page;
final class CryptoBills {
    public function __construct(private readonly Client $client) {}
    public function create(array $payload,string $idempotencyKey): array { $payload['idempotency_key']=$idempotencyKey; return $this->client->post('/bill/crypto',$payload,$idempotencyKey); }
    public function retrieve(array $identifier): array { return $this->client->get('/bill/crypto',$identifier); }
    public function history(array $filters=[]): array { return $this->client->get('/bill/crypto/history',$filters); }
    public function historyPage(array $filters=[]): Page { return $this->client->page('/bill/crypto/history',$filters); }
    public function iterateHistory(array $filters=[]): Generator { return $this->client->iterate('/bill/crypto/history',$filters); }
    public function manual(array $filters=[]): array { return $this->client->get('/bill/crypto/manual',$filters); }
    public function reconcile(array $filters=[]): array { return $this->client->get('/bill/crypto/reconcile',$filters); }
    public function retry(array $identifier): array { return $this->client->post('/bill/crypto/retry',$identifier); }
}
