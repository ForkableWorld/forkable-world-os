<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\D\Users;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Properties\Base\BaseClientIdProperty;
use Tests\SlimStagingTestCase;
class GetUserWithWebClientIdTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testGetUserWithWebClientId(){
		$expectedString = '';
        $responseBody = $this->callAndCheckResponse($expectedString);
        BaseClientIdProperty::setInMemory(BaseClientIdProperty::CLIENT_ID_WEB);
        $this->assertEquals(BaseClientIdProperty::CLIENT_ID_WEB,
            BaseClientIdProperty::fromMemory()); // We should only replace with quantimodo when getting app settings
        $this->checkTestDuration(9);
		$this->checkQueryCount(4);
	}
	public $expectedResponseSizes = [];
	public $slimEnvironmentSettings = [
      'REQUEST_METHOD' => 'GET',
      'REMOTE_ADDR' => '10.0.2.2',
      'SCRIPT_NAME' => '',
      'PATH_INFO' => '/api/v1/user',
      'SERVER_NAME' => '_',
      'SERVER_PORT' => '443',
      'HTTP_X_FIRELOGGER' => '1.3',
      'HTTP_COOKIE' => '_ga=GA1.2.506304397.1541100803; __cfduid=d6fc81d9344afdf0201f6ec5be411fa501541101912; final_callback_url=https%3A%2F%2Flocal.quantimo.do%2Fionic%2FModo%2Fsrc%2Findex.html%23%2Fapp%2Flogin%3Ffinal_callback_url%3Dhttps%253A%252F%252Flocal.quantimo.do%252Fionic%252FModo%252Fsrc%252Findex.html%2523%252Fapp%252Flogin%26clientId%3Dquantimodo%26message%3DConnected%2BGoogle%2BPlus%2521; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1542383060%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; fbm_225078261031461=base_domain=.quantimo.do; _gid=GA1.2.1022812616.1541433617; _gat=1',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_REFERER' => 'https://local.quantimo.do/ionic/Modo/www/index.html',
      'HTTP_ACCEPT' => '*/*',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36',
      'HTTP_CACHE_CONTROL' => 'no-cache',
      'HTTP_PRAGMA' => 'no-cache',
      'HTTP_CONNECTION' => 'keep-alive',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => '',
      'slim.url_scheme' => 'https',
      'slim.input' => '',
      'slim.request.query_hash' => ['clientId' => BaseClientIdProperty::CLIENT_ID_WEB,],
      'slim.request.form_hash' => [],
      'responseStatusCode' => 200,
      'unixtime' => 1541442381,
      'requestDuration' => 2.113081932067871,
      'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::ADMIN_TEST_TOKEN,
    ];

}
