<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\D\Users;
use App\Models\OAAccessToken;
use App\Models\OAClient;
use App\Models\OARefreshToken;
use App\Models\User;
use App\Models\Variable;
use App\Models\WpUsermetum;
use App\Storage\DB\TestDB;
use Tests\SlimStagingTestCase;

class NewRelicGetUserTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testNewRelicGetUser(){
		$clock = clock();
		$expectedString = '';
		TestDB::setWhiteListedTables([
            OAAccessToken::TABLE,
            OARefreshToken::TABLE,
            User::TABLE,
            OAClient::TABLE,
            WpUsermetum::TABLE, // Needed to determine shareAllData TODO: Add this to user table
            Variable::TABLE, // Needed for primary outcome variable name TODO: Add this to user table
        ]);
		$responseBody = $this->callAndCheckResponse($expectedString);
		$this->checkTestDuration(10);
		$this->checkQueryCount(7);
		
	}
	public $expectedResponseSizes = [];
	public $slimEnvironmentSettings = [
        'REQUEST_METHOD' => 'GET',
        'REMOTE_ADDR' => '10.190.186.216',
        'SCRIPT_NAME' => '',
        'PATH_INFO' => '/api/user',
        'SERVER_NAME' => '_',
        'SERVER_PORT' => '443',
        'HTTP_CF_CONNECTING_IP' => '54.76.137.83',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.67 Safari/537.36',
        'HTTP_DNT' => '1',
        'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/json;q=0.9,application/javascript;q=0.9,text/javascript;q=0.9,application/xml;q=0.9,text/plain;q=0.8,*/*;q=0.7',
        'HTTP_X_NEWRELIC_SYNTHETICS' => 'PwcbVl9WDwoHSEMBAgABUFwFAB5bUgUBHVZbBFsVVgoCBhQEUFFQAAEHVFFRBwsRHEYDB1NXUwJRUxsAWldRTwQHAAEVWgNTBUgMVAQFVQNWBQQCVlQaHxJWBQNSB1RQVxxVClNVTlYAUVxPAFsCAB4AXVQJBwdWAlcOAFBBZQ==',
        'HTTP_X_ABUSE_INFO' => 'Request sent by a New Relic Synthetics Monitor (https://docs.newrelic.com/docs/synthetics/new-relic-synthetics/administration/identify-synthetics-requests-your-app) - monitor id: 2dae6c63-c913-403d-8c62-ed58f44fa9a6 | account id: 795797',
        'HTTP_CF_VISITOR' => '{"scheme":"https"}',
        'HTTP_X_FORWARDED_PROTO' => 'https',
        'HTTP_CF_RAY' => '488c35ec4c186a43-LHR',
        'HTTP_X_FORWARDED_FOR' => '141.101.98.224',
        'HTTP_CF_IPCOUNTRY' => 'IE',
        'HTTP_ACCEPT_ENCODING' => 'gzip',
        'CONTENT_LENGTH' => '',
        'CONTENT_TYPE' => '',
        'slim.url_scheme' => 'https',
        'slim.input' => '',
        'slim.request.query_hash' => [
            'log' => 'testuser',
            'pwd' => 'testing123',
        ],
        'responseStatusCode' => NULL,
        'unixtime' => 1544743752,
        'requestDuration' => 2.024125099182129,
    ];
}
