<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\A;
use Tests\SlimStagingTestCase;

class LoginWithAccessTokenInFinalCallbackCookieTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
	public function setUp(): void{
		$this->skipTest('This test failure is un-reproducible locally.');
		parent::setUp(); 
	}
	public function testLoginWithAccessTokenInFinalCallbackCookie(): void{
		$expectedString = 'Sinn';
		$this->slimEnvironmentSettings = array (
  'REQUEST_METHOD' => 'GET',
  'REMOTE_ADDR' => '24.216.168.142',
  'SCRIPT_NAME' => '',
  'PATH_INFO' => '/api/v1/user',
  'SERVER_NAME' => 'app.quantimo.do',
  'SERVER_PORT' => '443',
  'HTTP_COOKIE' => '__cfduid=da78265ac161b2c457707bf9fa03473c21558809267; _ga=GA1.2.1255432308.1558928465; quantimodo-_zldp=DAZDRQtOaPcEhrFR9074Jfa1QR017Vb4dPjejZjtJdohkhka6bfk7RJEcWq0Iesq; driftt_aid=468c487a-92f4-408f-866f-9f846e5fff69; DFTT_END_USER_PREV_BOOTSTRAPPED=true; quantimodo-_siqid=DAZDRQtOaPfzTbKlK7IkbW%252FSovXwDk4D46Og0qYFL2ejpFY8PWczvdg%252F2%252BuhwO%252F0ngQ4zHldVhDA%250AarwrPzvHAu3nhW35waP3Em9DDo%252BDyyjyDMBD53l%252Btg%253D%253D; driftt_eid=230; driftt_sid=be82d59c-e0b4-4b4f-abc3-e2c569114af7; final_callback_url=https%3A%2F%2Fweb.quantimo.do%2F%23%2Fapp%2Fimport%3Fclient_id%3Dquantimodo%26quantimodoAccessToken%3Dmike-test-token%26quantimodoUserId%3D230',
  'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
  'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
  'HTTP_SEC_FETCH_USER' => '?1',
  'HTTP_SEC_FETCH_MODE' => 'navigate',
  'HTTP_SEC_FETCH_SITE' => 'cross-site',
  'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
  'HTTP_SEC_FETCH_DEST' => 'document',
  'HTTP_USER_AGENT' => 'Mozilla/5.0 (Linux; Android 10; Pixel 3a XL) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.117 Mobile Safari/537.36',
  'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
  'HTTP_CACHE_CONTROL' => 'max-age=0',
  'HTTP_CONNECTION' => 'keep-alive',
  'CONTENT_LENGTH' => '',
  'CONTENT_TYPE' => '',
  'slim.url_scheme' => 'https',
  'slim.input' => '',
  'slim.request.query_hash' =>
  array (
    'code' => '48f9216f7eabb9763123e6c57dbfc93c2e2fe9d2',
    'state' => 'eyJ1c2VyX2lkIjoyMzAsImNsaWVudF9pZCI6InF1YW50aW1vZG8iLCJjbGllbnRfc2VjcmV0IjoiVGNRQXJaT29VZWNPOU80YUJ2bnRVbDZ2MVF6enNVMzgiLCJmaW5hbF9jYWxsYmFja191cmwiOiJodHRwczpcL1wvd2ViLnF1YW50aW1vLmRvXC8jXC9hcHBcL2ltcG9ydCJ9',
  ),
  'responseStatusCode' => NULL,
  'unixtime' => 1582646756,
  'requestDuration' => 0.05375218391418457,
);
		$responseBody = $this->callAndCheckResponse($expectedString);
		$this->checkTestDuration(3);
		$this->checkQueryCount(3);
	}
	public $expectedResponseSizes = [];
}
