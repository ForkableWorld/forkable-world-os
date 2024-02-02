<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests\Controllers;
use App\Models\OAClient;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseClientSecretProperty;
use App\Storage\DB\TestDB;
class ConnectJsTest extends \Tests\SlimTests\SlimTestCase
{
    public function testJavascript(){
	    /** @noinspection SpellCheckingInspection */
	    $params = ['t' => '$P$BBAT7.X/pipFIuLRnyKOQS.fSpRg/v0'];
        $response = $this->slimGet('/api/v1/connect.js', $params, 200);
        $this->assertEquals('application/x-javascript', $response->headers->get('Content-Type'));
        $this->assertQueryCountLessThan(3);
    }
    public function testQuantiModoIntegrationJs() {
        $response = $this->slimGet('/api/v1/integration.js', [], 200);
        $this->assertEquals('application/x-javascript', $response->headers->get('Content-Type'));
        $this->assertQueryCountLessThan(3);
    }
    public function testConnectorsListV3ForNewUser() {
        TestDB::resetUserTables();
        $response =
            $this->slimGet('/api/v3/connectors/list', [
	            'clientUserId' => 100,
	            'clientId' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
	            'email' => "test-user@client.com",
            ]);
        $body = json_decode($response->getBody(), false);
        if(!$body->sessionTokenObject->clientId){
            $response =
                $this->slimGet('/api/v3/connectors/list',
	                ['clientUserId' => 100, 'clientId' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT]);
            $body = json_decode($response->getBody(), false);
        }
        $this->assertEquals(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT, $body->sessionTokenObject->clientId);
        //$this->assertNotNull($body->sessionTokenObject->quantimodoUserId);
        $this->assertNotNull($body->sessionTokenObject->sessionToken);
        $this->assertEquals("100", $body->sessionTokenObject->clientUserId);
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->setAuthenticatedUser(1);
        $body->sessionTokenObject->clientSecret = BaseClientSecretProperty::TEST_CLIENT_SECRET;
		$c = OAClient::whereClientSecret(BaseClientSecretProperty::TEST_CLIENT_SECRET)
			->first()->setHidden([]);
		$this->assertEquals(BaseClientSecretProperty::TEST_CLIENT_SECRET, $c->client_secret);
		$this->assertEquals(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT, $c->client_id);
	    $body = $this->postAndGetDecodedBody('api/v1/connect/tokens', $body->sessionTokenObject);
        $this->assertNotNull($body->accessToken);
        $this->assertNotNull($body->publicToken);
        //$this->assertNotNull($body->quantimodoUserId);
        $this->assertEquals("100", $body->clientUserId);
        $this->assertQueryCountLessThan(27);
    }
    public function testJavascriptNoToken() {
        $response = $this->slimGet('/api/v1/connect.js', [], 200);
        $this->assertEquals('application/x-javascript', $response->headers->get('Content-Type'));
    }
    public function testJavascriptWrongToken(){
        $params = ['t' => 'wrong'];
        $response = $this->slimGet('/api/v1/connect.js', $params, 200);
        $this->assertEquals('application/x-javascript', $response->headers->get('Content-Type'));
    }
    public function testMobilePage(){
        $params = ['t' => '$P$BBAT7.X/pipFIuLRnyKOQS.fSpRg/v0'];
        $response = $this->slimGet('/api/v1/connect/mobile', $params, 200);
        $this->assertEquals('text/html', $response->headers->get('Content-Type'));
    }
    public function testMobilePageNoToken(){
        $params = [];
        $response = $this->slimGet('/api/v1/connect/mobile', $params, 200);
        $this->assertEquals('text/html', $response->headers->get('Content-Type'));
    }
    public function testMobilePageWrongToken() {
        $params = ['t' => 'wrong'];
        $response = $this->slimGet('/api/v1/connect/mobile', $params, 200);
        $this->assertEquals('text/html', $response->headers->get('Content-Type'));
    }
}
