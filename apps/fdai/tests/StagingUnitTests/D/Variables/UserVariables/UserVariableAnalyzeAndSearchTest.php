<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\D\Variables\UserVariables;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Variables\QMUserVariable;
use Tests\SlimStagingTestCase;

class UserVariableAnalyzeAndSearchTest extends SlimStagingTestCase
{
    protected $REQUEST_URI = "/api/v3/variables";
    public function testUserVariableAnalyzeAndSearch(){
		$expectedString = 'Blood Pressure (Diastolic - Bottom Number)';
        //QMProfile::startProfile();
		$uv = QMUserVariable::findUserVariableByNameIdOrSynonym(230, $expectedString);
		//$uv->outputConstructor();
		$uv->analyzeFully(__FUNCTION__);
		//QMProfile::endProfileAndSaveResult();
        $this->assertGreaterThan(0, $uv->numberOfMeasurements);
		$variables = $this->callAndCheckResponse($expectedString);
        /** @var QMUserVariable $bloodPressure */
        $bloodPressure = $variables[0];
        $this->assertGreaterThan(0, $bloodPressure->numberOfMeasurements);
        $this->assertEquals($expectedString, $bloodPressure->name);
		$this->checkTestDuration(12);
		$this->checkQueryCount(49);
	}
	public $slimEnvironmentSettings = [
      'REQUEST_METHOD' => 'GET',
      'REMOTE_ADDR' => '10.0.2.2',
      'SCRIPT_NAME' => '',
      'PATH_INFO' => '/api/v3/variables',
      'QUERY_STRING' => 'limit=50&sort=-latestTaggedMeasurementTime&includePublic=true&clientId=quantimodo&searchPhrase=blood%20pressure%20di&platform=web',
      'SERVER_NAME' => '_',
      'SERVER_PORT' => '443',
      'HTTP_X_FIRELOGGER' => '1.3',
      'HTTP_COOKIE' => '__stripe_mid=6f430ca4-9d0b-4469-9f73-cdb54da696e6; PHPSESSID=cache-sync-status; bp-members-scope=all; XDEBUG_SESSION=PHPSTORM; __cfduid=d400bc2cadb7a79f4a01b284bef2ac92a1538419547; _ga=GA1.2.374266088.1538419637; fbm_225078261031461=base_domain=.quantimo.do; heateorSsSLOptin=1; _gid=GA1.2.943895932.1538757523; final_callback_url=https%3A%2F%2Flocal.quantimo.do%2Fionic%2FModo%2Fsrc%2Findex.html%23%2Fapp%2Flogin%3Ffinal_callback_url%3Dhttps%253A%252F%252Flocal.quantimo.do%252Fionic%252FModo%252Fsrc%252Findex.html%2523%252Fapp%252Flogin%26clientId%3Dquantimodo%26clientSecret%3DTcQArZOoUecO9O4aBvntUl6v1QzzsU38%26register%3Dfalse%26message%3DConnected%2BQuantiModo%2521; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=testuser%7C1539967151%7C86db9a3d39d98100ae332be88d45d355%7Cquantimodo; _gat=1',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_REFERER' => 'https://local.quantimo.do/ionic/Modo/src/index.html',
      'HTTP_X_FRAMEWORK' => 'ionic',
      'HTTP_X_PLATFORM' => 'web',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
      'HTTP_X_CLIENT_ID' => 'quantimodo',
      'HTTP_ACCEPT' => 'application/json',
      'HTTP_CONTENT_TYPE' => 'application/json',
      'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::ADMIN_TEST_TOKEN,
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
        'limit' => '50',
        'sort' => '-latestTaggedMeasurementTime',
        'includePublic' => 'true',
        'clientId' => 'quantimodo',
        'searchPhrase' => 'blood pressure bottom',
        'platform' => 'web',
      ],
      'responseStatusCode' => 200,
      'unixtime' => 1538757601,
      'requestDuration' => 2.417862892150879,
    ];
}
