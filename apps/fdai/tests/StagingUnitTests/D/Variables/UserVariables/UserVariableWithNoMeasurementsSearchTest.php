<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\D\Variables\UserVariables;
use App\Exceptions\CommonVariableNotFoundException;
use App\Models\Variable;
use App\Properties\Base\BaseAccessTokenProperty;
use Tests\QMBaseTestCase;
use Tests\SlimStagingTestCase;
class UserVariableWithNoMeasurementsSearchTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testUserVariableWithNoMeasurementsSearch(){
		$expectedString = '';
		$this->skipTest("TODO");
		$v = Variable::findByName('Uncle Dee');
		$this->assertNotNull($v);
//		$uv = UserVariable::query()
//            ->where(UserVariable::FIELD_USER_ID, 18535)
//            ->where(UserVariable::FIELD_NUMBER_OF_MEASUREMENTS, 0)
//            ->where(UserVariable::FIELD_NUMBER_OF_COMMON_TAGS, 0)
//            ->where(UserVariable::FIELD_NUMBER_OF_USER_TAGS, 0)
//            ->where(UserVariable::FIELD_NUMBER_COMMON_TAGGED_BY, 0)
//            ->where(UserVariable::FIELD_NUMBER_USER_TAGGED_BY, 0)
//            ->first();
//		$this->slimEnvironmentSettings['slim.request.query_hash']['name'] = $uv->getName();
        QMBaseTestCase::setExpectedRequestException(CommonVariableNotFoundException::class);
		$responseBody = $this->callAndCheckResponse($expectedString);
		$this->checkTestDuration(11);
		$this->checkQueryCount(6);
	}
	public $expectedResponseSizes = [
        0 => 21,
    ];
	public $slimEnvironmentSettings = [
      'REQUEST_METHOD' => 'GET',
      'REMOTE_ADDR' => '10.0.2.2',
      'SCRIPT_NAME' => '',
      'PATH_INFO' => '/api/v3/variables',
      'SERVER_NAME' => '_',
      'SERVER_PORT' => '443',
      'HTTP_X_FIRELOGGER' => '1.3',
      'HTTP_COOKIE' => '__stripe_mid=6f430ca4-9d0b-4469-9f73-cdb54da696e6; PHPSESSID=cache-sync-status; bp-members-scope=all; XDEBUG_SESSION=PHPSTORM; __cfduid=d400bc2cadb7a79f4a01b284bef2ac92a1538419547; _ga=GA1.2.374266088.1538419637; fbm_225078261031461=base_domain=.quantimo.do; heateorSsSLOptin=1; tk_tc=1BXEiAZ5hV8O0WY4; final_callback_url=https%3A%2F%2Flocal.quantimo.do%2Fionic%2FModo%2Fsrc%2Fconfiguration-index.html; _gid=GA1.2.1497139921.1540738795; _gat=1',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_REFERER' => 'https://local.quantimo.do/ionic/Modo/src/index.html',
      'HTTP_X_FRAMEWORK' => 'ionic',
      'HTTP_X_PLATFORM' => 'web',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
      'HTTP_X_CLIENT_ID' => 'quantimodo',
      'HTTP_ACCEPT' => 'application/json',
      'HTTP_CONTENT_TYPE' => 'application/json',
      'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,
      'HTTP_X_APP_VERSION' => '2.8.1001',
      'HTTP_CACHE_CONTROL' => 'no-cache',
      'HTTP_PRAGMA' => 'no-cache',
      'HTTP_CONNECTION' => 'keep-alive',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => 'application/json',
      'slim.url_scheme' => 'https',
      'slim.input' => '',
      'slim.request.query_hash' =>
      [
        'includeCharts' => 'true',
        'name' => 'Uncle Dee',
        'limit' => '50',
        'sort' => '-latestTaggedMeasurementTime',
        'clientId' => 'quantimodo',
        'platform' => 'web',
      ],
      'slim.request.form_hash' =>
      [
      ],
      'responseStatusCode' => 200,
      'unixtime' => 1540741569,
      'requestDuration' => 5.759397983551025,
    ];
}
