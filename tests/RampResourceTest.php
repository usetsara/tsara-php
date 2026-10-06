<?php
declare(strict_types=1);
namespace Tsara\Tests;
use PHPUnit\Framework\TestCase;
use Tsara\Client;
final class RampResourceTest extends TestCase
{
    public function testWidgetManagementRoutes(): void
    {
        $seen=[];
        $client=new Client('sk_test_example',transport:function($method,$url,$headers,$body)use(&$seen){$seen[]=compact('method','url','body');return ['status'=>200,'body'=>'{"success":true}'];});
        $client->rampWidgets->create(['name'=>'Store widget']);
        $client->rampWidgets->updateDomains('rwdg_1',['https://merchant.example']);
        $client->rampWidgets->updateStatus('rwdg_1','DISABLED');
        self::assertStringEndsWith('/ramp/widgets',$seen[0]['url']);
        self::assertStringEndsWith('/ramp/widgets/domains',$seen[1]['url']);
        self::assertStringContainsString('"widget_id":"rwdg_1"',$seen[1]['body']);
        self::assertStringContainsString('"status":"disabled"',$seen[2]['body']);
    }

    public function testRampTransactionRetrievalAndReconciliation(): void
    {
        $urls=[];
        $client=new Client('sk_test_example',transport:function($method,$url)use(&$urls){$urls[]=$url;return ['status'=>200,'body'=>'{"success":true}'];});
        $client->rampTransactions->retrieveByReference('rmp_1');
        $client->rampTransactions->reconcile('rmp_uid_1');
        self::assertStringContainsString('/ramp/transactions?reference=rmp_1',$urls[0]);
        self::assertStringContainsString('/ramp/transactions?id=rmp_uid_1&refresh=1',$urls[1]);
    }
}
