<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection SpellCheckingInspection */
namespace Tests\StagingUnitTests\D\DataLab;
use App\Override\GeneratedTestRequest;
use App\Override\QMFileBag;
use App\Override\QMHeaderBag;
use App\Override\QMParameterBag;
use App\Override\QMServerBag;
use App\Properties\User\UserIdProperty;
use Illuminate\Testing\TestResponse;
use Tests\LaravelStagingTestCase;
class ApiNotificationsTest extends LaravelStagingTestCase
{
    protected $REQUEST_URI = "/api/v6/notifications";
    public function testApiVSixNotificationsAsRegularUser(): void{
        $this->actAsTestUser();
        $response = $this->stagingRequest(200, "");
		$json = $response->json();
        foreach($this->lastResponseData('data') as $datum){
            $this->assertEquals(UserIdProperty::USER_ID_TEST_USER, $datum->notifiable_id);
        }
        $this->checkTestDuration(10);
        $this->checkQueryCount(5);
    }
    public function testApiVSixNotificationsAsAdmin(): void{
        $this->actAsAdmin();
        $response = $this->stagingRequest(200, "");

        $this->checkTestDuration(5);
        $this->checkQueryCount(5);
    }
    public function testApiVSixNotificationsWithoutAuth(): void{
        $this->assertGuest();
        $this->assertUnauthenticatedResponse();
        $this->checkTestDuration(5);
        $this->checkQueryCount(2);
    }
    /**
     * @param int $expectedCode
     * @param string|null $expectedString
     * @return string|object
     */
    protected function stagingRequest(int $expectedCode = 200, string $expectedString = null): TestResponse {
		$this->serializedRequest = GeneratedTestRequest::__set_state(array(
   'json' => NULL,
   'convertedFiles' => NULL,
   'userResolver' => NULL,
   'routeResolver' => NULL,
   'attributes' =>
  QMParameterBag::__set_state(array(
     'parameters' =>
    array (
    ),
  )),
   'request' =>
  QMParameterBag::__set_state(array(
     'parameters' =>
    array (
    ),
  )),
   'query' =>
  QMParameterBag::__set_state(array(
     'parameters' =>
    array (
    ),
  )),
   'server' =>
  QMServerBag::__set_state(array(
     'parameters' =>
    array (
      'USER' => 'vagrant',
      'HOME' => '/home/vagrant',
      'HTTP_COOKIE' => '__cfduid=d0b262ec60d9dad143da74009904d74921601569701; _ga=GA1.2.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; DFTT_END_USER_PREV_BOOTSTRAPPED=true; u=084521ae39828198127bd5b3d1d7fe9ccf4eca35; _ga=GA1.1.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; __gads=ID=baf1b7342d7fe58c:T=1601862028:S=ALNI_MZ1pa8rvTAuTSgPwbsR16DZSDT1bw; XDEBUG_SESSION=PHPSTORM; _gid=GA1.2.1636261980.1602777261; _gid=GA1.1.1636261980.1602777261; driftt_eid=230; final_callback_url=https%3A%2F%2Fweb.quantimo.do%2Findex.html%23%2Fapp%2Flogin%3Fclient_id%3Dquantimodo; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1604195899%7C7bbd02cce0932081841094cc76a908c8%7Cquantimodo; driftt_sid=6b627692-d7aa-4fad-8ba7-3d440e59d453; XSRF-TOKEN=eyJpdiI6IkZIc2FhNkt5VEQ2RGMwUWk1SFhnYUE9PSIsInZhbHVlIjoiSmxtam50RUVhSXBcL1dyTDRHUExDQ0Z5UmRjY0VZeFdoVXBXRE50VmNhWmFVYVczVVRaeEh1XC9LcmJqQmVsUlplIiwibWFjIjoiMjRmOGMzZWZjZjM2OGI3YTU3NmQ4YzM5ZThiZWQ3OWNkMzg4ZGUxMmQ5NGNkNGQzMDJhMzU3NjNmMzk1ZDgwNiJ9; laravel_session=eyJpdiI6IjQzMG54bGhFKzNuNWlUUDFqWkd0N2c9PSIsInZhbHVlIjoiUzZFUnJTQmtaUzhEeEQ5TGxLRlhlVTBMQ1FvMDdTVHhyQTdWc0dXQWtQQjNCOE9GTmlyRGc4dTZpMmJSVGF1cyIsIm1hYyI6IjVhNWM0YTQyNzQ2YjVlZmZlNjAxOTlkNzczMzgxNzQ1YTM0NmQyYmFmZTFjZmUzNDhkZGI4MDMyNTMxZGNiOWMifQ%3D%3D',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_REFERER' => getenv('APP_URL').'/datalab/trackingReminders?userId=230',
      'HTTP_SEC_FETCH_DEST' => 'empty',
      'HTTP_SEC_FETCH_MODE' => 'cors',
      'HTTP_SEC_FETCH_SITE' => 'same-origin',
      'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36',
      'HTTP_ACCEPT' => '*/*',
      'HTTP_CONNECTION' => 'keep-alive',
      'REDIRECT_STATUS' => '200',
      'HTTPS' => 'on',
      'SERVER_NAME' => 'testing.quantimo.do',
      'SERVER_PORT' => '443',
      'REMOTE_PORT' => '13076',
      'SERVER_SOFTWARE' => 'nginx/1.15.8',
      'GATEWAY_INTERFACE' => 'CGI/1.1',
      'SERVER_PROTOCOL' => 'HTTP/1.1',
      'DOCUMENT_URI' => '/index.php',
      'REQUEST_URI' => $this->REQUEST_URI.'',
      'SCRIPT_NAME' => '/index.php',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => '',
      'REQUEST_METHOD' => 'GET',
      'QUERY_STRING' => '',
      'FCGI_ROLE' => 'RESPONDER',
      'PHP_SELF' => '/index.php',
      'HTTP_CONTENT_LENGTH' => '',
      'HTTP_CONTENT_TYPE' => '',
    ),
  )),
   'files' =>
  QMFileBag::__set_state(array(
     'parameters' =>
    array (
    ),
  )),
   'cookies' =>
  QMParameterBag::__set_state(array(
     'parameters' =>
    array (
      '__cfduid' => 'd0b262ec60d9dad143da74009904d74921601569701',
      '_ga' => 'GA1.2.644404966.1601578880',
      'driftt_aid' => 'df29ce65-369a-440c-9d5e-a1888f0cd13d',
      'DFTT_END_USER_PREV_BOOTSTRAPPED' => 'true',
      'u' => '084521ae39828198127bd5b3d1d7fe9ccf4eca35',
      '__gads' => 'ID=baf1b7342d7fe58c:T=1601862028:S=ALNI_MZ1pa8rvTAuTSgPwbsR16DZSDT1bw',
      'XDEBUG_SESSION' => 'PHPSTORM',
      '_gid' => 'GA1.2.1636261980.1602777261',
      'driftt_eid' => '230',
      'final_callback_url' => 'https://web.quantimo.do/index.html#/app/login?client_id=quantimodo',
      'quantimodo_logged_in_af6160480df78a3a6d520187243f05c9' => 'mike|1604195899|7bbd02cce0932081841094cc76a908c8|quantimodo',
      'driftt_sid' => '6b627692-d7aa-4fad-8ba7-3d440e59d453',
      'XSRF-TOKEN' => 'eyJpdiI6IkZIc2FhNkt5VEQ2RGMwUWk1SFhnYUE9PSIsInZhbHVlIjoiSmxtam50RUVhSXBcL1dyTDRHUExDQ0Z5UmRjY0VZeFdoVXBXRE50VmNhWmFVYVczVVRaeEh1XC9LcmJqQmVsUlplIiwibWFjIjoiMjRmOGMzZWZjZjM2OGI3YTU3NmQ4YzM5ZThiZWQ3OWNkMzg4ZGUxMmQ5NGNkNGQzMDJhMzU3NjNmMzk1ZDgwNiJ9',
      'laravel_session' => 'eyJpdiI6IjQzMG54bGhFKzNuNWlUUDFqWkd0N2c9PSIsInZhbHVlIjoiUzZFUnJTQmtaUzhEeEQ5TGxLRlhlVTBMQ1FvMDdTVHhyQTdWc0dXQWtQQjNCOE9GTmlyRGc4dTZpMmJSVGF1cyIsIm1hYyI6IjVhNWM0YTQyNzQ2YjVlZmZlNjAxOTlkNzczMzgxNzQ1YTM0NmQyYmFmZTFjZmUzNDhkZGI4MDMyNTMxZGNiOWMifQ==',
    ),
  )),
   'headers' =>
  QMHeaderBag::__set_state(array(
     'headers' =>
    array (
      'cookie' =>
      array (
        0 => '__cfduid=d0b262ec60d9dad143da74009904d74921601569701; _ga=GA1.2.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; DFTT_END_USER_PREV_BOOTSTRAPPED=true; u=084521ae39828198127bd5b3d1d7fe9ccf4eca35; _ga=GA1.1.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; __gads=ID=baf1b7342d7fe58c:T=1601862028:S=ALNI_MZ1pa8rvTAuTSgPwbsR16DZSDT1bw; XDEBUG_SESSION=PHPSTORM; _gid=GA1.2.1636261980.1602777261; _gid=GA1.1.1636261980.1602777261; driftt_eid=230; final_callback_url=https%3A%2F%2Fweb.quantimo.do%2Findex.html%23%2Fapp%2Flogin%3Fclient_id%3Dquantimodo; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1604195899%7C7bbd02cce0932081841094cc76a908c8%7Cquantimodo; driftt_sid=6b627692-d7aa-4fad-8ba7-3d440e59d453; XSRF-TOKEN=eyJpdiI6IkZIc2FhNkt5VEQ2RGMwUWk1SFhnYUE9PSIsInZhbHVlIjoiSmxtam50RUVhSXBcL1dyTDRHUExDQ0Z5UmRjY0VZeFdoVXBXRE50VmNhWmFVYVczVVRaeEh1XC9LcmJqQmVsUlplIiwibWFjIjoiMjRmOGMzZWZjZjM2OGI3YTU3NmQ4YzM5ZThiZWQ3OWNkMzg4ZGUxMmQ5NGNkNGQzMDJhMzU3NjNmMzk1ZDgwNiJ9; laravel_session=eyJpdiI6IjQzMG54bGhFKzNuNWlUUDFqWkd0N2c9PSIsInZhbHVlIjoiUzZFUnJTQmtaUzhEeEQ5TGxLRlhlVTBMQ1FvMDdTVHhyQTdWc0dXQWtQQjNCOE9GTmlyRGc4dTZpMmJSVGF1cyIsIm1hYyI6IjVhNWM0YTQyNzQ2YjVlZmZlNjAxOTlkNzczMzgxNzQ1YTM0NmQyYmFmZTFjZmUzNDhkZGI4MDMyNTMxZGNiOWMifQ%3D%3D',
      ),
      'accept-language' =>
      array (
        0 => 'en-US,en;q=0.9',
      ),
      'accept-encoding' =>
      array (
        0 => 'gzip, deflate, br',
      ),
      'referer' =>
      array (
        0 => getenv('APP_URL').'/datalab/trackingReminders?userId=230',
      ),
      'sec-fetch-dest' =>
      array (
        0 => 'empty',
      ),
      'sec-fetch-mode' =>
      array (
        0 => 'cors',
      ),
      'sec-fetch-site' =>
      array (
        0 => 'same-origin',
      ),
      'x-requested-with' =>
      array (
        0 => 'XMLHttpRequest',
      ),
      'user-agent' =>
      array (
        0 => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36',
      ),
      'accept' =>
      array (
        0 => '*/*',
      ),
      'connection' =>
      array (
        0 => 'keep-alive',
      ),
      'host' =>
      array (
        0 => 'local.quantimo.do',
      ),
      'content-length' =>
      array (
        0 => '',
      ),
      'content-type' =>
      array (
        0 => '',
      ),
    ),
     'cacheControl' =>
    array (
    ),
  )),
   'content' => NULL,
   'languages' => NULL,
   'charsets' => NULL,
   'encodings' => NULL,
   'acceptableContentTypes' => NULL,
   'pathInfo' => NULL,
   'requestUri' => NULL,
   'baseUrl' => NULL,
   'basePath' => NULL,
   'method' => NULL,
   'format' => NULL,
   'session' => NULL,
   'locale' => NULL,
   'defaultLocale' => 'en',
   'preferredFormat' => NULL,
   'isHostValid' => true,
   'isForwardedValid' => true,
));
		return  $this->callAndCheckResponse($expectedCode, $expectedString);
	}
}
