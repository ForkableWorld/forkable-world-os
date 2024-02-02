<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\A\Connectors;
use App\Computers\ThisComputer;
use App\DataSources\Connectors\WhatPulseConnector;
use App\DataSources\QMConnector;
use App\Properties\Connection\ConnectionConnectStatusProperty;
use App\Slim\Controller\Connector\ConnectorConnectedResponse;
use App\Storage\Memory;
use Illuminate\Support\Arr;
use Tests\SlimStagingTestCase;
class ConnectWhatpulseTest extends SlimStagingTestCase {
	public const DISABLED_UNTIL = WhatPulseConnector::DISABLED_UNTIL;
	public $maximumResponseArrayLength = false;
	public $minimumResponseArrayLength = false;
	public function testConnectWhatpulse(): void{
		if($this->weShouldSkip()){return;}
		$connector = WhatPulseConnector::getByUserId(230);
		$this->assertTrue(!isset($connector->connector));
		$connection = $connector->getConnectionIfExists();
		$this->assertTrue(!isset($connector->connector));
		$connection->disconnect(__FUNCTION__, __FUNCTION__);
		$this->assertTrue(!isset($connector->connector));
		/** @var ConnectorConnectedResponse $responseBody */
		$responseBody = $this->callAndCheckResponse('Connected');
		$connectors = $responseBody->connectors;
		$this->assertIsArray($connectors);
		/** @var QMConnector $whatpulse */
		$whatpulse = Arr::first($connectors, function($c){
			return $c->name === WhatPulseConnector::NAME;
		});
		$this->assertNotNull($whatpulse);
		$this->assertEquals(ConnectionConnectStatusProperty::CONNECT_STATUS_CONNECTED, $whatpulse->connectStatus);
		Memory::flush();
		$c = WhatPulseConnector::getByUserId(230);
		$this->assertEquals(ConnectionConnectStatusProperty::CONNECT_STATUS_CONNECTED, $c->connectStatus);
		$this->checkTestDuration(10);
		$this->checkQueryCount(15);
	}
	public $expectedResponseSizes = [
		'user' => 2.737,
		'connectors' => 130,
	];
	public $slimEnvironmentSettings = [
		'REQUEST_METHOD' => 'GET',
		'REMOTE_ADDR' => '192.168.10.1',
		'SCRIPT_NAME' => '',
		'PATH_INFO' => '/api/v3/connectors/whatpulse/connect',
		'SERVER_NAME' => ThisComputer::LOCAL_HOST_NAME,
		'SERVER_PORT' => '443',
		'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
		'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
		'HTTP_REFERER' => 'https://dev-web.quantimo.do/',
		'HTTP_SEC_FETCH_SITE' => 'same-site',
		'HTTP_SEC_FETCH_MODE' => 'cors',
		'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.120 Safari/537.36',
		'HTTP_AUTHORIZATION' => 'Bearer mike-test-token',
		'HTTP_ORIGIN' => 'https://dev-web.quantimo.do',
		'HTTP_ACCEPT' => 'application/json, text/plain, */*',
		'HTTP_CONNECTION' => 'keep-alive',
		'CONTENT_LENGTH' => '',
		'CONTENT_TYPE' => '',
		'slim.url_scheme' => 'https',
		'slim.input' => '',
		'slim.request.query_hash' => [
			'appName' => 'QuantiModo',
			'appVersion' => '2.9.1022',
			'accessToken' => 'mike-test-token',
			'clientId' => 'quantimodo',
			'platform' => 'web',
			'XDEBUG_SESSION_START' => 'PHPSTORM',
			'username' => 'mikepsinn',
		],
		'slim.request.form_hash' => [],
		'responseStatusCode' => 200,
		'unixtime' => 1572984686,
		'requestDuration' => 6.7034831047058105,
	];
}
