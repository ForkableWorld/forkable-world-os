<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\D\Variables\UserVariables;
use App\Properties\Base\BaseAccessTokenProperty;
use Tests\SlimStagingTestCase;

class UserVariableChartsTest extends SlimStagingTestCase {
    public function testUserVariableCharts(){
		$expectedString = '';
		$responseBody = $this->callAndCheckResponse($expectedString);
		$this->checkTestDuration(50);
		$this->checkQueryCount(7);
	}
	public $expectedResponseSizes = [
        //0 => 31,
    ];
	public $slimEnvironmentSettings = [
      'REQUEST_METHOD' => 'GET',
      'REMOTE_ADDR' => '10.0.2.2',
      'SCRIPT_NAME' => '',
      'PATH_INFO' => '/api/v3/variables',
      'QUERY_STRING' => 'includeCharts=true&name=Overall%20Mood&limit=50&sort=-latestTaggedMeasurementTime&clientId=quantimodo&platform=web',
      'SERVER_NAME' => '_',
      'SERVER_PORT' => '443',
      'HTTP_X_FIRELOGGER' => '1.3',
      'HTTP_COOKIE' => '__stripe_mid=6f430ca4-9d0b-4469-9f73-cdb54da696e6; PHPSESSID=cache-sync-status; bp-members-scope=all; XDEBUG_SESSION=PHPSTORM; laravel_session=eyJpdiI6Im1zaEExUHZFckVCSVpJT0tVbFFMXC9RPT0iLCJ2YWx1ZSI6IlQ1eGNsaTcwcjVBRDdvOVV2Uk0rUTYxNkZOSkFqcTdvYzdsTlRcL0hrbklVeFRicEs4bkFoZUUrdjlGZCtqbWtDQkNUWnVSZkxFbjRxaTllNWJ5a21iUT09IiwibWFjIjoiODkyYjNmMjliZTJmOWM2OTc0MDE3MDIwNGY2MGY1ZmZhMzYyMDFlNDYwNjViN2YwZmM3MDQ4NGZkYjU2ZGIzZiJ9; __cfduid=d400bc2cadb7a79f4a01b284bef2ac92a1538419547; _ga=GA1.2.374266088.1538419637; _gid=GA1.2.663612061.1538419637; _gat=1',
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
      'slim.request.query_hash' => [
        'includeCharts' => 'true',
        'name' => 'Overall Mood',
        'limit' => '50',
        'sort' => '-latestTaggedMeasurementTime',
        'clientId' => 'quantimodo',
        'platform' => 'web',
      ],
      'slim.request.form_hash' => [],
      'responseStatusCode' => 200,
      'unixtime' => 1538421657,
      'requestDuration' => 48.379809856414795,
    ];
}
