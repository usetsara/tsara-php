<?php
declare(strict_types=1);
namespace Tsara\Resource;
use Generator;
use InvalidArgumentException;
use Tsara\Client;
use Tsara\Pagination\Page;
final class Bills {
    public function __construct(private readonly Client $client) {}
    public function services(array $filters=[]): array { return $this->client->get('/bill/services',$filters); }
    public function categories(string $service,array $filters=[]): array { return $this->client->get('/bill/categories',['service'=>strtoupper($service)]+$filters); }
    public function products(string $service,array $filters=[]): array { return $this->client->get('/bill/products',['service'=>strtoupper($service)]+$filters); }
    public function providers(string $service,array $filters=[]): array { return $this->client->get('/bill/'.$this->path($service).'/providers',$filters); }
    public function plans(string $service,array $filters=[]): array { $service=strtoupper($service); $suffix=$service==='UTILITY'?'products':'plans'; return $this->client->get('/bill/'.$this->path($service).'/'.$suffix,$filters); }
    public function verify(string $service,string $provider,string $number,?string $vendType=null): array { return $this->client->post('/bill/verify',array_filter(['service'=>strtoupper($service),'provider'=>$provider,'number'=>$number,'vend_type'=>$vendType?strtoupper($vendType):null],static fn($v)=>$v!==null)); }
    public function providerTransactions(?string $id=null): array { return $this->client->get('/bill/transactions',['id'=>$id]); }
    public function history(array $filters=[]): array { return $this->client->get('/bill/history',$filters); }
    public function historyPage(array $filters=[]): Page { return $this->client->page('/bill/history',$filters); }
    public function iterateHistory(array $filters=[]): Generator { return $this->client->iterate('/bill/history',$filters); }
    public function retrieve(string $identifier): array { return $this->history(['trx_id'=>$identifier]); }
    public function purchaseAirtime(array $payload,string $idempotencyKey): array { return $this->purchase('airtime',$payload,$idempotencyKey); }
    public function purchaseData(array $payload,string $idempotencyKey): array { return $this->purchase('data',$payload,$idempotencyKey); }
    public function purchaseCable(array $payload,string $idempotencyKey): array { return $this->purchase('cable',$payload,$idempotencyKey); }
    public function purchaseElectricity(array $payload,string $idempotencyKey): array { if(isset($payload['vend_type']))$payload['vend_type']=strtoupper((string)$payload['vend_type']); return $this->purchase('electricity',$payload,$idempotencyKey); }
    private function purchase(string $type,array $payload,string $idempotencyKey): array { $payload['idempotency_key']=$idempotencyKey; return $this->client->post('/bill/'.$type,$payload,$idempotencyKey); }
    private function path(string $service): string { return match(strtoupper($service)){'AIRTIME'=>'airtime','DATA'=>'data','CABLETV'=>'cable','UTILITY'=>'electricity',default=>throw new InvalidArgumentException('Unsupported bill service.')}; }
}
