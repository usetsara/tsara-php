<?php
declare(strict_types=1);
namespace Tsara\Tests;
use PHPUnit\Framework\TestCase;
use Tsara\Client;
final class BillResourceTest extends TestCase {
 public function testCatalogAndVerificationRoutes():void{$seen=[];$c=new Client('sk_test_example',transport:function($m,$u,$h,$b)use(&$seen){$seen[]=compact('m','u','b');return ['status'=>200,'body'=>'{"success":true,"data":[]}'];});$c->bills->services();$c->bills->plans('DATA',['provider'=>'MTN']);$c->bills->verify('UTILITY','IKEDC','123','prepaid');self::assertStringEndsWith('/bill/services',$seen[0]['u']);self::assertStringContainsString('/bill/data/plans?provider=MTN',$seen[1]['u']);self::assertStringContainsString('"vend_type":"PREPAID"',$seen[2]['b']);}
 public function testPurchaseUsesIdempotencyBodyAndHeader():void{$seen=[];$c=new Client('sk_test_example',transport:function($m,$u,$h,$b)use(&$seen){$seen=compact('u','h','b');return ['status'=>200,'body'=>'{"success":true}'];});$c->bills->purchaseData(['provider'=>'MTN','bundle_code'=>'20','phone_number'=>'08000000000','amount'=>1000],'bill_12345678');self::assertSame('bill_12345678',$seen['h']['Idempotency-Key']);self::assertStringContainsString('"idempotency_key":"bill_12345678"',$seen['b']);}
 public function testCryptoBillRoutes():void{$urls=[];$c=new Client('sk_test_example',transport:function($m,$u)use(&$urls){$urls[]=$u;return ['status'=>200,'body'=>'{"success":true}'];});$c->cryptoBills->create(['service'=>'AIRTIME','provider'=>'MTN','amount'=>1000,'asset'=>'solana:usdc','phone_number'=>'08000000000'],'cb_12345678');$c->cryptoBills->retrieve(['trx_id'=>'ts_1']);self::assertStringEndsWith('/bill/crypto',$urls[0]);self::assertStringContainsString('/bill/crypto?trx_id=ts_1',$urls[1]);}
}
