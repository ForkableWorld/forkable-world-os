<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\D\Users;
use App\Computers\ThisComputer;
use Tests\SlimStagingTestCase;
class GetKidsTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testGetKids(): void{
        $this->skipTest("Work in progress");
        return;
		$expectedString = 'Invite your kids';
		$responseBody = $this->callAndCheckResponse($expectedString);
		$this->checkTestDuration(6);
		$this->checkQueryCount(1);
	}
	public $expectedResponseSizes = array (
  'users' => 6.45,
  'authUrl' => 0.118,
  'description' => 0.596,
  'link' => 0.635,
  'card' => 6.262,
  'success' => 0.004,
  'status' => 0.009,
  'code' => 0.006,
  'summary' => 0.028,
  'errors' => 0.006,
  'sessionTokenObject' => 0.002,
  'avatar' => 0.002,
  'warnings' => 0.006,
);
	public $slimEnvironmentSettings = array (
  'REQUEST_METHOD' => 'GET',
  'REMOTE_ADDR' => '192.168.10.1',
  'SCRIPT_NAME' => '',
  'PATH_INFO' => '/api/v3/users',
  'SERVER_NAME' => ThisComputer::LOCAL_HOST_NAME,
  'SERVER_PORT' => '443',
  'HTTP_COOKIE' => 'driftt_aid=98a8a3d2-1d31-4327-9838-6eaec7f8a19f; DFTT_END_USER_PREV_BOOTSTRAPPED=true; __cfduid=dcdb09420c75424a77f4f65d19ff030c01548823023; _ga=GA1.2.1344114312.1548823044; fbm_225078261031461=base_domain=.quantimo.do; __utma=109117957.1344114312.1548823044.1550087604.1550091488.2; quantimodo-_zldp=DAZDRQtOaPcXH8HX%2BOqgkjjGotu1HZpYg1gRXUtQw%2BA4DMxo%2B%2FTZ2QBNmYCEtq2D; DFTT_END_USER_PREV_BOOTSTRAPPED=true; quantimodo-_siqid=DAZDRQtOaPczbhKinEnCHK3z9pRolhmyjvfeO%252B8YacTQTzsd46klmd54mMMk9cZwXApI11xAy10T%250AScYJrfjC4FmL7GtWyeHWCQVYZpk98wnfTzZGzm6Dbg%253D%253D; PHPSESSID=6mk9sbiqknui97pijdlqe11iau; __gads=Test; gclid=undefined; driftt_aid=98a8a3d2-1d31-4327-9838-6eaec7f8a19f; XSRF-TOKEN=eyJpdiI6InBSelFFd0VYRDNXbUdFXC9aQ0VzQlBBPT0iLCJ2YWx1ZSI6ImV4SU51Qzd3Y004VHA0WkdJUlhaV1lcL3lKb1N0dEgyczlLTkRvMGhBU3d0bzhWWXRmazFQclB5ckRpcWlRdmxwIiwibWFjIjoiNTdkNjJjZDY3MGJiNmU4ZGFhMWU5YzIzZjA1MWNjNGMyYTYzZmU2ZjI2NGM5YjJkNmEwYzBhMzc2NGNjZWZlNiJ9; laravel_session=eyJpdiI6IjJnOUl4ODNiT1RDV29SMUpFVnlWTWc9PSIsInZhbHVlIjoicnJ3a3lObzJTVDdhck1hMFNmbWhrQ3BHU3phK0pJdGdVU0ZWY3RQeWZ4VXFHamtWZTYwcFZIdEl2UEwzWVpQbCIsIm1hYyI6IjA4NjkzODJiOWYxMGQxNTRlMDM1YWViOWNiYzFlY2U3OWUxMjAyNTdkMGYzOTY1ZWMwNWYyMGJlZmNjNjMzNmIifQ%3D%3D; _gid=GA1.2.1380112793.1573243178; driftt_sid=df3626a8-d969-405f-bb1b-2f357b1dd670; final_callback_url=https%3A%2F%2Fdev-physician.quantimo.do%2F%23%2Fapp%2Flogin%3Fclient_id%3Dquantimodo%26message%3DConnected%2BGoogle%2521%26quantimodoAccessToken%3Dmike-test-token%26quantimodoUserId%3D230; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1574556523%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; driftt_eid=230; _gat=1',
  'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
  'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
  'HTTP_SEC_FETCH_MODE' => 'navigate',
  'HTTP_SEC_FETCH_SITE' => 'none',
  'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
  'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.97 Safari/537.36',
  'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
  'HTTP_CONNECTION' => 'keep-alive',
  'CONTENT_LENGTH' => '',
  'CONTENT_TYPE' => '',
  'slim.url_scheme' => 'https',
  'slim.input' => '',
  'slim.request.query_hash' =>
  array (
    'clientId' => 'kiddomodo',
    'appVersion' => '2.9.1022',
    'platform' => 'web',
  ),
  'responseStatusCode' => 200,
  'unixtime' => 1573349127,
  'requestDuration' => 4.793921947479248,
);
}
