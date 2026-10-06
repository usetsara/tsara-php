<?php
declare(strict_types=1);
namespace Tsara\Tests;
use PHPUnit\Framework\TestCase;
use Tsara\Client;
final class ApiKeysWebhookResourceTest extends TestCase
{
    public function testApiKeyConfigAndRotationRoutes(): void
    {
        $seen=[];
        $client=new Client('sk_test_example',transport:function($method,$url)use(&$seen){$seen[]=compact('method','url');return ['status'=>200,'body'=>'{"success":true}'];});
        $client->apiKeys->config();
        $client->apiKeys->rotateSecret();
        self::assertSame('GET',$seen[0]['method']);
        self::assertStringEndsWith('/api-keys/config',$seen[0]['url']);
        self::assertSame('POST',$seen[1]['method']);
        self::assertStringEndsWith('/api-keys/rotate-secret',$seen[1]['url']);
    }

    public function testWebhookOperationsAndPaginationRoutes(): void
    {
        $seen=[];
        $client=new Client('sk_test_example',transport:function($method,$url,$headers,$body)use(&$seen){$seen[]=compact('method','url','body');return ['status'=>200,'body'=>'{"success":true,"data":{"data":[],"meta":{"page":1,"total_pages":1}}}'];});
        $client->webhooks->resendTransaction(['reference'=>'order_1']);
        $client->webhooks->logsPage(['delivery_status'=>'failed']);
        $client->webhooks->resendRefund(['uid'=>'ref_1']);
        self::assertStringEndsWith('/webhook/resend',$seen[0]['url']);
        self::assertStringContainsString('/webhook/logs?delivery_status=failed',$seen[1]['url']);
        self::assertStringEndsWith('/webhook/refund-resend',$seen[2]['url']);
    }
}
