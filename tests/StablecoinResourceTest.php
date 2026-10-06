<?php
declare(strict_types=1);
namespace Tsara\Tests;
use PHPUnit\Framework\TestCase;
use Tsara\Client;
final class StablecoinResourceTest extends TestCase
{
    public function testOnrampCreateAndReconcileRoutes(): void
    {
        $seen=[];
        $client=new Client('sk_test_example',transport:function($method,$url,$headers,$body)use(&$seen){$seen[]=compact('method','url','headers','body');return ['status'=>200,'body'=>'{"success":true}'];});
        $client->stablecoinOnramps->create(['amount'=>1000,'fiat_currency'=>'NGN','asset'=>'USDC','chain'=>'SOLANA'],'onramp_123');
        $client->stablecoinOnramps->reconcile('trx_123');
        self::assertStringEndsWith('/stablecoin/onramp',$seen[0]['url']);
        self::assertSame('onramp_123',$seen[0]['headers']['Idempotency-Key']);
        self::assertStringContainsString('"reference":"onramp_123"',$seen[0]['body']);
        self::assertStringContainsString('/stablecoin/onramp/status?uid=trx_123&refresh=1',$seen[1]['url']);
    }

    public function testOfframpQuoteAndCreateRoutes(): void
    {
        $seen=[];
        $client=new Client('sk_test_example',transport:function($method,$url,$headers,$body)use(&$seen){$seen[]=compact('url','headers','body');return ['status'=>200,'body'=>'{"success":true}'];});
        $client->stablecoinOfframps->quote(5000);
        $client->stablecoinOfframps->create(['amount'=>5000,'fiat_currency'=>'NGN','asset'=>'USDC','chain'=>'SOLANA','bank_code'=>'090286','account_number'=>'0110000000'],'offramp_123');
        self::assertStringEndsWith('/stablecoin/offramp/quote',$seen[0]['url']);
        self::assertStringEndsWith('/stablecoin/offramp',$seen[1]['url']);
        self::assertSame('offramp_123',$seen[1]['headers']['Idempotency-Key']);
    }

    public function testWalletAddressAndTransferRoutes(): void
    {
        $urls=[];
        $client=new Client('sk_test_example',transport:function($method,$url,$headers,$body)use(&$urls){$urls[]=$url;return ['status'=>200,'body'=>'{"success":true}'];});
        $client->stablecoinWallets->balance(['wallet_uid'=>'wallet_1']);
        $client->stablecoinAddresses->balance('address_1');
        $client->stablecoinTransfers->create(['from_address'=>'from','to_address'=>'to','amount'=>2,'network'=>'SOLANA','asset'=>'USDC'],'transfer_123');
        self::assertStringContainsString('/stablecoin/wallets/balance?wallet_uid=wallet_1',$urls[0]);
        self::assertStringContainsString('/stablecoin/wallets/addresses/balance?address=address_1',$urls[1]);
        self::assertStringEndsWith('/stablecoin/wallets/transfers',$urls[2]);
    }
}
