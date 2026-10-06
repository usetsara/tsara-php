<?php
declare(strict_types=1);
namespace Tsara\Tests;
use PHPUnit\Framework\TestCase;
use Tsara\Client;
final class CustomerResourceTest extends TestCase {
    public function testCustomerAndIdentityRoutes(): void {
        $seen=[]; $client=new Client('sk_test_example', transport:function($method,$url,$headers,$body)use(&$seen){$seen[]=compact('method','url','body');return ['status'=>200,'body'=>'{"success":true,"data":[]}'];});
        $client->customers->retrieve('id_123');
        $client->customers->update('id_123',['email'=>'new@example.com','name'=>'User','type'=>'individual']);
        $client->customerIdentity->initiate('12345678901','bvn','id_123');
        $client->customerIdentity->validate('12345678901','123456','nin');
        self::assertStringContainsString('/customers?id=id_123',$seen[0]['url']);
        self::assertStringContainsString('"customer_id":"id_123"',$seen[1]['body']);
        self::assertStringContainsString('"type":"BVN"',$seen[2]['body']);
        self::assertStringEndsWith('/customers/identity/validate',$seen[3]['url']);
    }
    public function testCustomerIteratorReadsTotalPages(): void {
        $client=new Client('sk_test_example',transport:function($method,$url){parse_str((string)parse_url($url,PHP_URL_QUERY),$q);$page=(int)($q['page']??1);return ['status'=>200,'body'=>json_encode(['success'=>true,'data'=>[['id'=>'id_'.$page]],'meta'=>['page'=>$page,'total_pages'=>2]])];});
        self::assertCount(2,iterator_to_array($client->customers->iterate()));
    }
}
