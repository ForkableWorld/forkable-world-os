<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\A\Connectors;
use App\DataSources\Connectors\MyFitnessPalConnector;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Slim\Model\User\QMUser;
use Tests\SlimStagingTestCase;
class ConnectorsMyfitnesspalConnectErrorTest extends SlimStagingTestCase {
    public $expectedCode = 503;
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public const DISABLED_UNTIL = "2023-04-01";
    public function testConnectorsMyfitnesspalConnect(): void{
        if(time() < strtotime(self::DISABLED_UNTIL)){ // Might be temporarily broken
            $this->skipTest('This test seems to mess up other tests');
            return;
        }
		$expectedString = 'MyFitnessPal is temporarily unavailable. Please Try again tomorrow.  Thanks!';
		$responseBody = $this->callAndCheckResponse($expectedString);
		$this->assertEquals($expectedString, $responseBody->errors[0]->message);
		$user = QMUser::find(80006);
		$connectors = $user->getOrSetConnectors();
		foreach($connectors as $connector){
		    if($connector->getNameAttribute() === MyFitnessPalConnector::NAME){
		        $this->assertEquals($expectedString, $connector->errorMessage);
                $this->assertEquals($expectedString, $connector->message);
            }
        }
		$this->checkTestDuration(17);
		$this->checkQueryCount(3);
	}
	public $expectedResponseSizes = [];
	public $slimEnvironmentSettings = array (
  'REQUEST_METHOD' => 'GET',
  'REMOTE_ADDR' => '10.190.186.216',
  'SCRIPT_NAME' => '',
  'PATH_INFO' => '/api/v3/connectors/myfitnesspal/connect',
  'SERVER_NAME' => '_',
  'SERVER_PORT' => '443',
  'HTTP_CF_CONNECTING_IP' => '2600:6c44:800:9:f480:dc4a:595c:adb3',
  'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
  'HTTP_REFERER' => 'https://web.quantimo.do/',
  'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.17 Safari/537.36',
  'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,
  'HTTP_ORIGIN' => 'https://web.quantimo.do',
  'HTTP_ACCEPT' => 'application/json, text/plain, */*',
  'HTTP_CF_VISITOR' => '{"scheme":"https"}',
  'HTTP_X_FORWARDED_PROTO' => 'https',
  'HTTP_CF_RAY' => '48945b38887641f5-MSP',
  'HTTP_X_FORWARDED_FOR' => '162.158.214.56',
  'HTTP_CF_IPCOUNTRY' => 'US',
  'HTTP_ACCEPT_ENCODING' => 'gzip',
  'CONTENT_LENGTH' => '',
  'CONTENT_TYPE' => '',
  'slim.url_scheme' => 'https',
  'slim.input' => '',
  'slim.request.query_hash' =>
  array (
    'username' => 'danzyger',
    'password' => 'namaste8001451',
    'appName' => 'QuantiModo',
    'appVersion' => '2.8.1214',
    'clientId' => 'quantimodo',
  ),
  'slim.request.form_hash' =>
  array (
  ),
  'responseStatusCode' => NULL,
  'unixtime' => 1544829159,
  'requestDuration' => 2.443871021270752,
);
}
