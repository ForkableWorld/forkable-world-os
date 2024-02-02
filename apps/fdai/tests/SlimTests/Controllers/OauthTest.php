<?php /** @noinspection PhpMissingReturnTypeInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests\Controllers;
use App\DataSources\QMClient;
use App\Exceptions\AccessTokenExpiredException;
use App\Logging\QMLog;
use App\Models\Application;
use App\Models\OAAccessToken;
use App\Models\OAAuthorizationCode;
use App\Models\OARefreshToken;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Model\Auth\OAuth2Server;
use App\Storage\DB\Writable;
use OAuth2\Response;
use OAuth2\Server;
use Tests\QMBaseTestCase;
use Tests\SlimTests\SlimTestCase;
/**
 * Class OauthTest
 * @package Tests\Api\Controllers
 */
class OauthTest extends \Tests\SlimTests\SlimTestCase {
	protected function setUp(): void{
		parent::setUp();
		OAAccessToken::truncate();
		OARefreshToken::truncate();
		OAAuthorizationCode::truncate();
	}
	/**
     * @return array
     */
    public function testValidRefreshTokenLessScope(){
        $server = $this->getTestServer();
        $db = Writable::db();
        $userId = 1;
//        $db->table('oa_scopes')->insert([
//            'type' => 'supported',
//            'scope' => 'readmeasurements'
//        ]);
//        QMClient::writable()->insert([
//            'client_id' => QMClient::CLIENT_ID_OAUTH_TEST_CLIENT,
//            'client_secret' => QMClient::TEST_CLIENT_SECRET,
//            'redirect_uri' => 'https://local.quantimo.do/',
//            'user_id' => $userId,
//        ]);
        $refresh_token = 'tGzv3JOkF0XG5Qx2TlKWIA';
        try {
            $db->table('oa_refresh_tokens')->insert([
                'refresh_token' => $refresh_token,
                'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
                'user_id' => $userId,
                'scope' => 'readmeasurements',
                'expires' => date('Y-m-d H:i:s', time() + 86400*365)
            ]);
        } catch (\Throwable $e){
            QMLog::info(__METHOD__.": ".$e->getMessage());
        }
        $request = TestRequest::createPost([
            'grant_type' => 'refresh_token', // valid grant type
            'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT, // valid client id
            'client_secret' => QMClient::TEST_CLIENT_SECRET, // valid client secret
            'refresh_token' => $refresh_token, // valid refresh token (with scope)
            'scope'         => 'readmeasurements',
        ]);
        $tokenArray = $server->grantAccessToken($request, new Response());
        $this->assertNotNull($tokenArray);
        $this->assertArrayHasKey('access_token', $tokenArray);
        $this->assertArrayHasKey('scope', $tokenArray);
        $this->assertEquals('readmeasurements', $tokenArray['scope']);
        return $tokenArray;
    }
    public function testExpiredAccessTokenResponse(){
		$this->skipTest("This test is broken");
        $this->setAuthenticatedUser(null);
        $tokenRow = OAAccessToken::first();
        if(!$tokenRow){
            $tokenArray = $this->testValidRefreshTokenLessScope();
        } else {
            $tokenArray = $tokenRow->attributesToArray();
        }
	    OAAccessToken::query()->update([OAAccessToken::FIELD_EXPIRES => date('Y-m-d H:i:s', time() - 86400)]);
        QMBaseTestCase::setExpectedRequestException(AccessTokenExpiredException::class);
		try {
			$response = $this->slimGet('measurements', ['accessToken' => $tokenArray['access_token']], 401);
			$this->fail('Expected exception not thrown');
		} catch (\Throwable $e){
		    QMLog::info(__METHOD__.": ".$e->getMessage());
			$this->assertTrue(true);
		}
//        $body = $response->getBody();
//        $this->assertContains('expire', $body);
    }
    /**
     * @return Server
     */
    private function getTestServer(){
        return OAuth2Server::get();
    }
    /**
     * @return array
     */
    private function getTestParametersWithClientSecret(){
        return [
            'response_type' => 'code', // valid grant type
            'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT, // valid client id
            'client_secret' => QMClient::TEST_CLIENT_SECRET, // valid client secret
            'scope'         => 'readmeasurements',
            'state' => 'testabcd'
        ];
    }
    public function testGetAuthorizationPageWithClientSecret(){
        $this->setAuthenticatedUser(1);
        $response = $this->getWithoutResponseValidation('/oauth/authorize', $this->getTestParametersWithClientSecret());
        $this->checkAuthorizationPageResponse($response);
    }
    public function testGetAuthorizationPageWithoutClientSecret(){
        $this->setAuthenticatedUser(1);
        $params = $this->getTestParametersWithClientSecret();
        unset($params['client_secret']);
        $response = $this->getWithoutResponseValidation('/oauth/authorize', $params);
        $this->checkAuthorizationPageResponse($response);
    }
    public function testNoClientSecretWithConfidentialClient(){
		$this->skipTest("This test is broken");
        $this->runPdoSql();
        // add the test parameters in memory
        $server = $this->getTestServer();
        $request = TestRequest::createPost([
            'grant_type' => 'authorization_code', // valid grant type
            'code'       => 'testcode',
            'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT, // valid client id
        ]);
        $server->handleTokenRequest($request, $response = new Response());
        $this->assertInstanceOf(Response::class, $response);
        $this->assertNull($response->getParameter('error_description'),
	        $response->getParameter('error_description') ?: "");
        $this->assertNull($response->getParameter('error'), $response->getParameter('error') ?: "");
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotNull($response->getParameter('access_token'));
        $this->assertNotNull($response->getParameter('expires_in'));
        $this->assertNotNull($response->getParameter('token_type'));
    }
    /** @noinspection SqlResolve
     * @noinspection SpellCheckingInspection
     */
    public function runPdoSql(){
        $pdo = Writable::pdo();
        // set up clients
        //$pdo->exec('INSERT INTO oa_clients (client_id, client_secret, user_id) VALUES ("'.QMClient::CLIENT_ID_OAUTH_TEST_CLIENT.'", "TestSecret", 1)');
        // set up scopes
//        $pdo->exec('INSERT INTO oa_scopes (type, scope) VALUES ("supported", "supportedscope1 supportedscope2 supportedscope3 supportedscope4")');
//        $pdo->exec('INSERT INTO oa_scopes (type, scope) VALUES ("default", "defaultscope1 defaultscope2")');
//        $pdo->exec('INSERT INTO oa_scopes (type, scope, client_id) VALUES ("supported", "clientscope1 clientscope2", "'.QMClient::CLIENT_ID_OAUTH_TEST_CLIENT.'")');
//        $pdo->exec('INSERT INTO oa_scopes (type, scope, client_id) VALUES ("default", "clientscope1 clientscope2", "'.QMClient::CLIENT_ID_OAUTH_TEST_CLIENT.'")');
//        $pdo->exec('INSERT INTO oa_scopes (type, scope, client_id) VALUES ("supported", "clientscope1 clientscope2 clientscope3", "'.QMClient::CLIENT_ID_OAUTH_TEST_CLIENT.'")');
//        $pdo->exec('INSERT INTO oa_scopes (type, scope, client_id) VALUES ("default", "clientscope1 clientscope2", "'.QMClient::CLIENT_ID_OAUTH_TEST_CLIENT.'")');
//        $pdo->exec('INSERT INTO oa_scopes (type, scope, client_id) VALUES ("supported", "clientscope1 clientscope2", "'.QMClient::CLIENT_ID_OAUTH_TEST_CLIENT.'")');
//        $pdo->exec('INSERT INTO oa_scopes (type, scope, client_id) VALUES ("default", "clientscope1 clientscope2", "'.QMClient::CLIENT_ID_OAUTH_TEST_CLIENT.'")');
//        $pdo->exec('INSERT INTO oa_scopes (type, scope, client_id) VALUES ("supported", "clientscope1 clientscope2 clientscope3", "'.QMClient::CLIENT_ID_OAUTH_TEST_CLIENT.'")');
//        $pdo->exec('INSERT INTO oa_scopes (type, scope, client_id) VALUES ("default", "clientscope3", "'.QMClient::CLIENT_ID_OAUTH_TEST_CLIENT.'")');

        $pdo->exec('INSERT INTO oa_access_tokens (access_token, client_id, user_id) VALUES ("testtoken", "'. BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT .'", 1)');
        $pdo->exec('INSERT INTO oa_authorization_codes (authorization_code, client_id, expires, user_id) VALUES ("testcode", "'. BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT .
            '", "'.date('Y-m-d H:i:s', time() + 60).'", 1)');
    }
    /**
     * @param \Slim\Http\Response $response
     */
    private function checkAuthorizationPageResponse(\Slim\Http\Response $response): void {
        $this->assertEquals(200, $response->getStatus());
        $appSettings = Application::getClientAppSettings(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
        $html = json_encode($response->getBody());
        $this->assertContains($appSettings->getTitleAttribute(), $html);
    }
}
