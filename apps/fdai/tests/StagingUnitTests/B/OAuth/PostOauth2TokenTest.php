<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\B\OAuth;
use App\Models\OAAuthorizationCode;
use App\Models\OAClient;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseClientSecretProperty;
use App\Properties\User\UserIdProperty;
use Tests\SlimStagingTestCase;

class PostOauth2TokenTest extends SlimStagingTestCase
{
	private const TEST_CODE = 'test_code';
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testPostOauth2Token(){
	    $attributes = [
		    OAAuthorizationCode::FIELD_AUTHORIZATION_CODE => self::TEST_CODE,
		    OAAuthorizationCode::FIELD_CLIENT_ID => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
		    OAAuthorizationCode::FIELD_USER_ID => UserIdProperty::USER_ID_TEST_USER,
		    OAAuthorizationCode::FIELD_REDIRECT_URI => "http://localhost:5000/auth/quantimodo/callback",
		    OAAuthorizationCode::FIELD_EXPIRES => time() + 3600,
	    ];
	    $code = OAAuthorizationCode::updateOrCreate([
			OAAuthorizationCode::FIELD_AUTHORIZATION_CODE => self::TEST_CODE
	                                                ], $attributes);
		$attributes[OAClient::FIELD_CLIENT_SECRET] = BaseClientSecretProperty::TEST_CLIENT_SECRET;
		$attributes['code'] = self::TEST_CODE;
		$attributes['grant_type'] = 'authorization_code';
		$attributes['scope'] = 'readmeasurements writemeasurements';
		//$input = 'code=test_code&client_id=oauth_test_client&client_secret=oauth_test_secret&redirect_uri=http
	    //://staging.quantimo.do/api/v1/connectors/quantimodo/connect&grant_type=authorization_code';
		$expectedString = '';
		$testResponse = $this->post('/oauth/token', $attributes);
		$testResponse->assertStatus(200);
		$body = $testResponse->getContent();
		$testResponse->assertJsonStructure([
			'access_token',
			'expires_in',
			'token_type',
			'scope',
			'refresh_token',
		]);
		$this->checkTestDuration(10);
		$this->checkQueryCount(5);
	}
	public $expectedResponseSizes = [];
	public $slimEnvironmentSettings = array (
  'REQUEST_METHOD' => 'POST',
  'SCRIPT_NAME' => '',
  'PATH_INFO' => '/oauth/token',
  'SERVER_NAME' => '_',
  'SERVER_PORT' => '80',
  'HTTP_USER_AGENT' => 'Guzzle/3.8.0 curl/7.35.0 PHP/7.1.20-1 ubuntu14.04.1 deb.sury.org 1',
  'HTTP_CONTENT_TYPE' => 'application/x-www-form-urlencoded; charset=utf-8',
  'HTTP_CURL' => '0, 0',
  'CONTENT_LENGTH' => '237',
  'CONTENT_TYPE' => 'application/x-www-form-urlencoded; charset=utf-8',
  'slim.url_scheme' => 'http',
  'slim.input' => 'code=f87ced9c2564a6a3e70e3ac555d40452f99374dd&client_id=quantimodo&client_secret=TcQArZOoUecO9O4aBvntUl6v1QzzsU38&redirect_uri=https://staging.quantimo.do/api/v1/connectors/quantimodo/connect&grant_type=authorization_code',
);
}
