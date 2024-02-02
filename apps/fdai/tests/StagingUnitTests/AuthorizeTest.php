<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests;
use App\Buttons\Auth\LoginButton;
use Tests\SlimStagingTestCase;
/**
 * @covers GetAuthorizationPageController
 */
class AuthorizeTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
	/**
	 * @covers GetAuthorizationPageController::renderAuthorizationDialogPage
	 */
    public function testGetAuthorizationPageController(): void{
		$expectedString = '';
		$this->slimEnvironmentSettings = [
  'REQUEST_METHOD' => 'GET',
  'SCRIPT_NAME' => '',
  'PATH_INFO' => '/oauth/authorize',
  'SERVER_NAME' => 'local.quantimo.do',
  'SERVER_PORT' => '443',
  'HTTP_COOKIE' => 'XDEBUG_SESSION=PHPSTORM; intended_url=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Foauth2%2Fauthorize%3Fresponse_type%3Dcode%26scope%3Dreadmeasurements%2Bwritemeasurements%26state%3Dtestabcd%26client_id%3Doauth_test_client%26client_secret%3Doauth_test_client_secret%26register%3Dfalse%26XDEBUG_SESSION_START%3DPHPSTORM; x-clockwork=%7B%22requestId%22%3A%221632684730-2790-1778974312%22%2C%22version%22%3A%225.0.7%22%2C%22path%22%3A%22%5C%2F__clockwork%5C%2F%22%2C%22token%22%3A%22ecd012fa%22%2C%22metrics%22%3Atrue%2C%22toolbar%22%3Atrue%7D; _ga=GA1.1.863220127.1632684735; _gid=GA1.1.2082920425.1632684735; _gat=1; drift_campaign_refresh=2d9f2ef8-a755-489f-91fc-d76da586017f; __cypress.initial=true; drift_aid=1e2ff31d-ce8f-4a7f-b9e4-6b32155f6109; driftt_aid=1e2ff31d-ce8f-4a7f-b9e4-6b32155f6109; remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6ImQ2S2x5UitWcVZKcmRqbFhPeHp3UkE9PSIsInZhbHVlIjoieGZnMHNHTURucVorSklnTm5lYlFEUXJPQ1lEZXhFdFRmVG9YMkZId2ppckJuUkJJUkJyek1pTGdpaHNFeTUzS1R2eWZURk40M1U5RXErSWd5OXV4TEJXaFpiN1BQejRhOU16T05lTlFPc0JhYlV5R1puU3Nxb2FmcUF1S2RTUkJBU1ZBNDk2R3pwbTJjWmF2Y1oyVWlBPT0iLCJtYWMiOiJiNDJkODk4MzIwNWQ5MmVmZGIwMTYyNTE5NGYyMjA0YTY4MTBmYmZmOTdiMjU3Y2E3YjhjNDZjZjdlYzRiNzMwIn0%3D; ID=eyJpdiI6ImN1VFY0YXBBdkVnWFh2bUFQbHVaOFE9PSIsInZhbHVlIjoiRnNqUUdIV3JrZG9Vd2lMVFBORW9KZz09IiwibWFjIjoiNmUzMTMzZjc3ZDJkMTljYzljZWZjMDYxMTcyNjMxMjliZTliMmY2NjNiNjFiYTdjMGQxNmM1NGQzNTAyNGIxMyJ9; XSRF-TOKEN=eyJpdiI6InQ4VU52bmM3K05aVDFUYzJYa2xaRFE9PSIsInZhbHVlIjoiSGh6OVB4ZXB1K0dHUjNwaCt2eVgwWUZCdTJkXC9PY0xndTVFS2hvWk10V0lRTVdadDExWUR6VFFEeTBFUnZydGciLCJtYWMiOiI1NDkxNTliZmZlNGZiYjgzMTJmYThlMWRlYzAwNzcyNTk5NzBmMjVjYTUxYjcxYzc0ODhkYWZhODI0MGM0ZGM4In0%3D; laravel_session_local=eyJpdiI6ImNXRDB1ZStEdUp0cWVWOVwvZ0UwTTNnPT0iLCJ2YWx1ZSI6IlUxRVR2RWh0S09cL1o3M0ZDcExpdmNQK2szb1RCYXpxa0hyalVmbkRFcE5RYldsek9jc0R1Sis5NFV0V3lma0xFIiwibWFjIjoiNzBlNGJhMDBhYWUxZDdiM2Q1ZjI4NTAzNzAyNzMwNTc2OWIwNmFlOWQ2ZTEyZDlmYWMyOTI3MzllMzE2ZDY4OSJ9',
  'HTTP_ACCEPT_LANGUAGE' => 'en-US',
  'HTTP_ACCEPT_ENCODING' => 'gzip',
  'HTTP_REFERER' => getenv('APP_URL').'/auth/login?response_type=code&scope=readmeasurements+writemeasurements&state=testabcd&client_id=oauth_test_client&client_secret=oauth_test_client_secret&register=false&XDEBUG_SESSION_START=PHPSTORM&intended_url=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Foauth2%2Fauthorize%3Fresponse_type%3Dcode%26scope%3Dreadmeasurements%2Bwritemeasurements%26state%3Dtestabcd%26client_id%3Doauth_test_client%26client_secret%3Doauth_test_client_secret%26register%3Dfalse%26XDEBUG_SESSION_START%3DPHPSTORM',
  'HTTP_SEC_FETCH_DEST' => 'iframe',
  'HTTP_SEC_FETCH_MODE' => 'navigate',
  'HTTP_SEC_FETCH_SITE' => 'same-origin',
  'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
  'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cypress/8.4.1 Chrome/91.0.4472.164 Electron/13.2.0 Safari/537.36',
  'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
  'HTTP_CACHE_CONTROL' => 'max-age=0',
  'HTTP_CONNECTION' => 'keep-alive',
  'CONTENT_LENGTH' => '',
  'CONTENT_TYPE' => '',
  'slim.url_scheme' => 'https',
  'slim.input' => '',
  'slim.request.query_hash' => 
  [
    'response_type' => 'code',
    'scope' => 'readmeasurements writemeasurements',
    'state' => 'testabcd',
    'client_id' => 'oauth_test_client',
    'client_secret' => 'oauth_test_client_secret',
    'register' => 'false',
    'XDEBUG_SESSION_START' => 'PHPSTORM',
  ],
  'responseStatusCode' => NULL,
  'unixtime' => 1632684797,
  'requestDuration' => 0,
		];
		$responseBody = $this->callAndCheckResponse($expectedString, 302, LoginButton::url());
		$this->checkTestDuration(0);
		$this->checkQueryCount(1);
	}
}
