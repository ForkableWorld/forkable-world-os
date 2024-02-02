<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\C\Studies\UserStudies;
use App\Models\User;
use App\Models\Variable;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Slim\Model\User\QMUser;
use Tests\SlimStagingTestCase;

class ShareAllDataTest extends SlimStagingTestCase
{
    public function testShareAllData(): void{
        $this->assertTrue(QMUser::demo()->getShareAllData());
        $mike = User::mike();
        $mike->share_all_data = false;
        $mike->save();
		$expectedString = '';
		$this->slimEnvironmentSettings = [
  'REQUEST_METHOD' => 'POST',
  'REMOTE_ADDR' => '162.158.74.212',
  'SCRIPT_NAME' => '',
  'PATH_INFO' => '/api/v3/userSettings',
  'SERVER_NAME' => 'app.quantimo.do',
  'SERVER_PORT' => '443',
  'HTTP_CDN_LOOP' => 'cloudflare',
  'HTTP_CF_CONNECTING_IP' => '24.216.168.142',
  'HTTP_CF_REQUEST_ID' => '05ca3b2cd50000a32734878000000001',
  'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
  'HTTP_REFERER' => 'https://web.quantimo.do/',
  'HTTP_SEC_FETCH_DEST' => 'empty',
  'HTTP_SEC_FETCH_MODE' => 'cors',
  'HTTP_SEC_FETCH_SITE' => 'same-site',
  'HTTP_ORIGIN' => 'https://web.quantimo.do',
  'HTTP_ACCEPT' => '*/*',
  'HTTP_X_FRAMEWORK' => 'ionic',
  'HTTP_X_PLATFORM' => 'web',
  'HTTP_X_CLIENT_ID' => 'quantimodo',
  'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.121 Safari/537.36',
  'HTTP_CONTENT_TYPE' => 'application/json;charset=UTF-8',
  'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::ADMIN_TEST_TOKEN,
  'HTTP_X_APP_VERSION' => '2.10.1014',
  'HTTP_X_TIMEZONE' => 'America/Chicago',
  'HTTP_CF_VISITOR' => '{"scheme":"https"}',
  'HTTP_X_FORWARDED_PROTO' => 'https',
  'HTTP_CF_RAY' => '5e23c7c158d0a327-ORD',
  'HTTP_X_FORWARDED_FOR' => '24.216.168.142',
  'HTTP_CF_IPCOUNTRY' => 'US',
  'HTTP_ACCEPT_ENCODING' => 'gzip',
  'HTTP_CONNECTION' => 'Keep-Alive',
  'CONTENT_LENGTH' => '34',
  'CONTENT_TYPE' => 'application/json;charset=UTF-8',
  'slim.url_scheme' => 'https',
  'slim.input' => '{"shareAllData":true,"userId":230}',
  'slim.request.form_hash' =>
  [],
  'slim.request.query_hash' =>
  [
    'appVersion' => '2.10.1014',
    'clientId' => 'quantimodo',
    'platform' => 'web',
  ],
  'responseStatusCode' => NULL,
  'unixtime' => 1602704512,
  'requestDuration' => 0.1247549057006836,
		];
		$responseBody = $this->callAndCheckResponse($expectedString, 201);
		$this->checkTestDuration(10);
		$this->checkQueryCount(8);
		$this->assertTrue(User::find(230)->share_all_data);
        $this->assertTrue(QMUser::mike()->shareAllData);
	}
    public function testCanViewMikeStudyWithoutAuth(): void{
        $mike = User::mike();
        $this->assertTrue($mike->share_all_data);
        $v = Variable::findByName("Eggs (serving)");
        $this->assertNotNull($v);
        $expectedString = '';
        $this->slimEnvironmentSettings = [
            'REQUEST_METHOD' => 'GET',
            'REMOTE_ADDR' => '162.158.75.111',
            'SCRIPT_NAME' => '',
            'PATH_INFO' => '/api/v4/study',
            'SERVER_NAME' => 'app.quantimo.do',
            'SERVER_PORT' => '443',
            'HTTP_CDN_LOOP' => 'cloudflare',
            'HTTP_CF_CONNECTING_IP' => '24.216.168.142',
            'HTTP_CF_REQUEST_ID' => '05ca2e69a50000a327e32a6000000001',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_CONTENT_TYPE' => 'application/json',
            'HTTP_X_FRAMEWORK' => 'ionic',
            'HTTP_X_APP_VERSION' => '2.10.929',
            'HTTP_X_PLATFORM' => 'gulp',
            'HTTP_X_CLIENT_ID' => 'quantimodo',
            'HTTP_CF_VISITOR' => '{"scheme":"https"}',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_CF_RAY' => '5e23b355dca9a327-ORD',
            'HTTP_X_FORWARDED_FOR' => '24.216.168.142',
            'HTTP_CF_IPCOUNTRY' => 'US',
            'HTTP_ACCEPT_ENCODING' => 'gzip',
            'HTTP_CONNECTION' => 'Keep-Alive',
            'CONTENT_LENGTH' => '',
            'CONTENT_TYPE' => 'application/json',
            'slim.url_scheme' => 'https',
            'slim.input' => '',
            'slim.request.query_hash' =>
                [
                    'causeVariableName' => 'Eggs (serving)',
                    'effectVariableName' => 'Overall Mood',
                    'userId' => '230',
                    'clientId' => 'quantimodo',
                    'platform' => 'gulp',
                ],
            'responseStatusCode' => NULL,
            'unixtime' => 1602703676,
            'requestDuration' => 0.30086708068847656,
        ];
        $responseBody = $this->callAndCheckResponse($expectedString, 200);
        $this->checkTestDuration(20);
        $this->checkQueryCount(30);
    }
}
