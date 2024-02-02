<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection SpellCheckingInspection */
namespace Tests\StagingUnitTests\D;
use App\DataSources\QMConnector;
use App\Override\GeneratedTestRequest;
use App\Override\QMHeaderBag;
use App\Override\QMParameterBag;
use App\Override\QMServerBag;
use Illuminate\Testing\TestResponse;
use Tests\LaravelStagingTestCase;
class ConnectTest extends LaravelStagingTestCase
{
    protected $REQUEST_URI = "/connect";
    public function testConnectAsRegularUser(): void{
        $this->actAsTestUser();
        $this->stagingRequest(200, "");
        $response = $this->getTestResponse();
		foreach(QMConnector::getEnabled() as $c){
			$response->assertSee($c->getTitleAttribute());
		}
        $this->checkTestDuration(11);
        $this->checkQueryCount(5);
    }
    public function testConnectAsAdmin(): void{
        $this->actAsAdmin();
        $this->stagingRequest(200, "");
        $response = $this->getTestResponse();
	    foreach(QMConnector::getEnabled() as $c){
		    $response->assertSee($c->getTitleAttribute());
	    }
        $this->checkTestDuration(11);
        $this->checkQueryCount(5);
    }
    public function testConnectWithoutAuth(): void{
        $this->assertGuest();
        $this->stagingRequest(200, "");
        $response = $this->getTestResponse();
	    foreach(QMConnector::getLoginConnectors() as $c){
		    $response->assertSee($c->getTitleAttribute());
	    }
		//$response->assertDontSee(FitbitConnector::NAME);
        $this->checkTestDuration(5);
        $this->checkQueryCount(3);
    }
    /**
     * @param int $expectedCode
     * @param string|null $expectedString
     * @return string|object
     */
    protected function stagingRequest(int $expectedCode = 200, string $expectedString = null): TestResponse {
		$this->serializedRequest = GeneratedTestRequest::__set_state(array(
   'server' =>
  QMServerBag::__set_state(array(
     'parameters' =>
    array (
      'USER' => 'vagrant',
      'HOME' => '/home/vagrant',
      'HTTP_COOKIE' => 'u=0107f812c049ec0b13a33b06d87e3d15937c69ac; _ga=GA1.1.1696477107.1633314261; drift_aid=597ab9ce-8bc9-4024-be90-ace829486562; driftt_aid=597ab9ce-8bc9-4024-be90-ace829486562; remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6Ill0Rnc2SWw0MFFWenJEWmhKWnhWZnc9PSIsInZhbHVlIjoiZmJaSEVCUG9qalJ2ZVdKSUNnTVQ2TEJOWGdmcmVvOEx4OWs0OEFVbHpFWHUwMFMxV0tcL3ZNYThQOWZpMmtjYmN5UUJ2XC9cL2UrbkxQWGJ6RFF6ZzRaTWFCMHZFUTFmejFZWFFJbTJTZEpZVTh3bU5ZMFZRanlVZVpwTklMS2I0dWRBY1NEYzdsdENBSlZUSGwwTjFhZDFCbkNWRlMyOUtUblE3VXBMQnRaWCtvPSIsIm1hYyI6ImNlODg2ZTUxZDlmNmM3OGM5MDViMWM4MWNiYjNmNzg4ZjA3MjAyZmQ4YzUwYmE5MzBjZGZjYjAxZDBmMTBlNDAifQ%3D%3D; final_callback_url=https%3A%2F%2Fweb.quantimo.do%2F%23%2Fapp%2Flogin; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1634695700%7Cdd460739350ce71c52c696ceb4cc9350%7Cquantimodo; XDEBUG_SESSION=PHPSTORM; XDEBUG_PROFILE=1; drift_eid=230; _ga=GA1.2.209528144.1633697713; intended_url=https%3A%2F%2Flocal.quantimo.do%2Fnova%2Fresources%2Ftddd-runs%2F144; arp_scroll_position=0; clockwork-profile=; XSRF-TOKEN=eyJpdiI6IlZIYjZlcDErZmR6bGxXSEgzYWJHSnc9PSIsInZhbHVlIjoiVFZCYkdHRnVhR1orY0p3UUF1TFU2bnZHWW1MTm9NenlobnVrU1dzNXozY0R0YzJaWTd3U244cXdKWHBCRTU2cSIsIm1hYyI6ImMyZGY0NDUzNTAwYmE4N2ZmYTkzZmJhNmU1NmI2MTg3MzI2MWE3YTk0NzQ4Zjk0YjUxN2IzZDEzZjNkODE5ZTMifQ%3D%3D; laravel_session=eyJpdiI6Ildkc1g4SHhXVHRoR1AzRzk5R2g4eVE9PSIsInZhbHVlIjoiODY0SzFPOEFwbFwvc1QydWZVUytcL2FBckI5SUIwS0RJNTlVWjc3MVhZRlFjXC80c0xNMjhLVGJBdTc5Z0h3bm1pZyIsIm1hYyI6ImUxOTM4MTg3Y2Y1ZjVkMjk3ZTRlNzhkYzgzNDIxOWE4NjI5OWM4NmE0YTJhZGRhZjE0MzIxNzg1YjM1ZjlmMGEifQ%3D%3D; login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6InhYeFwvOGRFXC9KS2Q5RHFWXC84dnVJSVE9PSIsInZhbHVlIjoidXQzWWxaa29xUzVFbnR2amhJTnc0QT09IiwibWFjIjoiMzRkZDJiNjAxY2RkZjExMWRiYmIxODRhMDc5ZjA4ZThkZGQ0YjhhYTBlZjhiYjJiYmFhYzljMmYyZGNmNzFjNiJ9',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_SEC_FETCH_DEST' => 'document',
      'HTTP_SEC_FETCH_USER' => '?1',
      'HTTP_SEC_FETCH_MODE' => 'navigate',
      'HTTP_SEC_FETCH_SITE' => 'none',
      'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.71 Safari/537.36',
      'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
      'HTTP_SEC_CH_UA_PLATFORM' => '"Windows"',
      'HTTP_SEC_CH_UA_MOBILE' => '?0',
      'HTTP_SEC_CH_UA' => '"Chromium";v="94", "Google Chrome";v="94", ";Not A Brand";v="99"',
      'HTTP_CACHE_CONTROL' => 'max-age=0',
      'HTTP_CONNECTION' => 'keep-alive',
      'REDIRECT_STATUS' => '200',
      'HTTPS' => 'on',
      'SERVER_NAME' => 'local.quantimo.do',
      'SERVER_PORT' => '443',
      'REMOTE_PORT' => '1030',
      'SERVER_SOFTWARE' => 'nginx/1.15.8',
      'GATEWAY_INTERFACE' => 'CGI/1.1',
      'SERVER_PROTOCOL' => 'HTTP/1.1',
      'DOCUMENT_ROOT' => '/qm-api/public',
      'DOCUMENT_URI' => '/index.php',
      'REQUEST_URI' => $this->REQUEST_URI.'',
      'SCRIPT_NAME' => '/index.php',
      'REQUEST_METHOD' => 'GET',
      'SCRIPT_FILENAME' => '/qm-api/public/index.php',
      'FCGI_ROLE' => 'RESPONDER',
      'PHP_SELF' => '/index.php',
      'REQUEST_TIME_FLOAT' => 1633913244.586942,
      'REQUEST_TIME' => 1633913244,
    ),
  )),
   'cookies' =>
  QMParameterBag::__set_state(array(
     'parameters' =>
    array (
      'u' => '0107f812c049ec0b13a33b06d87e3d15937c69ac',
      '_ga' => 'GA1.1.1696477107.1633314261',
      'drift_aid' => '597ab9ce-8bc9-4024-be90-ace829486562',
      'driftt_aid' => '597ab9ce-8bc9-4024-be90-ace829486562',
      'remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d' => 'eyJpdiI6Ill0Rnc2SWw0MFFWenJEWmhKWnhWZnc9PSIsInZhbHVlIjoiZmJaSEVCUG9qalJ2ZVdKSUNnTVQ2TEJOWGdmcmVvOEx4OWs0OEFVbHpFWHUwMFMxV0tcL3ZNYThQOWZpMmtjYmN5UUJ2XC9cL2UrbkxQWGJ6RFF6ZzRaTWFCMHZFUTFmejFZWFFJbTJTZEpZVTh3bU5ZMFZRanlVZVpwTklMS2I0dWRBY1NEYzdsdENBSlZUSGwwTjFhZDFCbkNWRlMyOUtUblE3VXBMQnRaWCtvPSIsIm1hYyI6ImNlODg2ZTUxZDlmNmM3OGM5MDViMWM4MWNiYjNmNzg4ZjA3MjAyZmQ4YzUwYmE5MzBjZGZjYjAxZDBmMTBlNDAifQ==',
      'final_callback_url' => 'https://web.quantimo.do/#/app/login',
      'quantimodo_logged_in_af6160480df78a3a6d520187243f05c9' => 'mike|1634695700|dd460739350ce71c52c696ceb4cc9350|quantimodo',
      'XDEBUG_SESSION' => 'PHPSTORM',
      'XDEBUG_PROFILE' => '1',
      'drift_eid' => '230',
      'intended_url' => getenv('APP_URL').'/nova/resources/tddd-runs/144',
      'arp_scroll_position' => '0',
      'clockwork-profile' => '',
      'XSRF-TOKEN' => 'eyJpdiI6IlZIYjZlcDErZmR6bGxXSEgzYWJHSnc9PSIsInZhbHVlIjoiVFZCYkdHRnVhR1orY0p3UUF1TFU2bnZHWW1MTm9NenlobnVrU1dzNXozY0R0YzJaWTd3U244cXdKWHBCRTU2cSIsIm1hYyI6ImMyZGY0NDUzNTAwYmE4N2ZmYTkzZmJhNmU1NmI2MTg3MzI2MWE3YTk0NzQ4Zjk0YjUxN2IzZDEzZjNkODE5ZTMifQ==',
      'laravel_session' => 'eyJpdiI6Ildkc1g4SHhXVHRoR1AzRzk5R2g4eVE9PSIsInZhbHVlIjoiODY0SzFPOEFwbFwvc1QydWZVUytcL2FBckI5SUIwS0RJNTlVWjc3MVhZRlFjXC80c0xNMjhLVGJBdTc5Z0h3bm1pZyIsIm1hYyI6ImUxOTM4MTg3Y2Y1ZjVkMjk3ZTRlNzhkYzgzNDIxOWE4NjI5OWM4NmE0YTJhZGRhZjE0MzIxNzg1YjM1ZjlmMGEifQ==',
      'login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d' => 'eyJpdiI6InhYeFwvOGRFXC9KS2Q5RHFWXC84dnVJSVE9PSIsInZhbHVlIjoidXQzWWxaa29xUzVFbnR2amhJTnc0QT09IiwibWFjIjoiMzRkZDJiNjAxY2RkZjExMWRiYmIxODRhMDc5ZjA4ZThkZGQ0YjhhYTBlZjhiYjJiYmFhYzljMmYyZGNmNzFjNiJ9',
    ),
  )),
   'headers' =>
  QMHeaderBag::__set_state(array(
     'headers' =>
    array (
      'cookie' =>
      array (
        0 => 'u=0107f812c049ec0b13a33b06d87e3d15937c69ac; _ga=GA1.1.1696477107.1633314261; drift_aid=597ab9ce-8bc9-4024-be90-ace829486562; driftt_aid=597ab9ce-8bc9-4024-be90-ace829486562; remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6Ill0Rnc2SWw0MFFWenJEWmhKWnhWZnc9PSIsInZhbHVlIjoiZmJaSEVCUG9qalJ2ZVdKSUNnTVQ2TEJOWGdmcmVvOEx4OWs0OEFVbHpFWHUwMFMxV0tcL3ZNYThQOWZpMmtjYmN5UUJ2XC9cL2UrbkxQWGJ6RFF6ZzRaTWFCMHZFUTFmejFZWFFJbTJTZEpZVTh3bU5ZMFZRanlVZVpwTklMS2I0dWRBY1NEYzdsdENBSlZUSGwwTjFhZDFCbkNWRlMyOUtUblE3VXBMQnRaWCtvPSIsIm1hYyI6ImNlODg2ZTUxZDlmNmM3OGM5MDViMWM4MWNiYjNmNzg4ZjA3MjAyZmQ4YzUwYmE5MzBjZGZjYjAxZDBmMTBlNDAifQ%3D%3D; final_callback_url=https%3A%2F%2Fweb.quantimo.do%2F%23%2Fapp%2Flogin; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1634695700%7Cdd460739350ce71c52c696ceb4cc9350%7Cquantimodo; XDEBUG_SESSION=PHPSTORM; XDEBUG_PROFILE=1; drift_eid=230; _ga=GA1.2.209528144.1633697713; intended_url=https%3A%2F%2Flocal.quantimo.do%2Fnova%2Fresources%2Ftddd-runs%2F144; arp_scroll_position=0; clockwork-profile=; XSRF-TOKEN=eyJpdiI6IlZIYjZlcDErZmR6bGxXSEgzYWJHSnc9PSIsInZhbHVlIjoiVFZCYkdHRnVhR1orY0p3UUF1TFU2bnZHWW1MTm9NenlobnVrU1dzNXozY0R0YzJaWTd3U244cXdKWHBCRTU2cSIsIm1hYyI6ImMyZGY0NDUzNTAwYmE4N2ZmYTkzZmJhNmU1NmI2MTg3MzI2MWE3YTk0NzQ4Zjk0YjUxN2IzZDEzZjNkODE5ZTMifQ%3D%3D; laravel_session=eyJpdiI6Ildkc1g4SHhXVHRoR1AzRzk5R2g4eVE9PSIsInZhbHVlIjoiODY0SzFPOEFwbFwvc1QydWZVUytcL2FBckI5SUIwS0RJNTlVWjc3MVhZRlFjXC80c0xNMjhLVGJBdTc5Z0h3bm1pZyIsIm1hYyI6ImUxOTM4MTg3Y2Y1ZjVkMjk3ZTRlNzhkYzgzNDIxOWE4NjI5OWM4NmE0YTJhZGRhZjE0MzIxNzg1YjM1ZjlmMGEifQ%3D%3D; login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6InhYeFwvOGRFXC9KS2Q5RHFWXC84dnVJSVE9PSIsInZhbHVlIjoidXQzWWxaa29xUzVFbnR2amhJTnc0QT09IiwibWFjIjoiMzRkZDJiNjAxY2RkZjExMWRiYmIxODRhMDc5ZjA4ZThkZGQ0YjhhYTBlZjhiYjJiYmFhYzljMmYyZGNmNzFjNiJ9',
      ),
      'accept-language' =>
      array (
        0 => 'en-US,en;q=0.9',
      ),
      'accept-encoding' =>
      array (
        0 => 'gzip, deflate, br',
      ),
      'sec-fetch-dest' =>
      array (
        0 => 'document',
      ),
      'sec-fetch-user' =>
      array (
        0 => '?1',
      ),
      'sec-fetch-mode' =>
      array (
        0 => 'navigate',
      ),
      'sec-fetch-site' =>
      array (
        0 => 'none',
      ),
      'accept' =>
      array (
        0 => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      ),
      'user-agent' =>
      array (
        0 => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.71 Safari/537.36',
      ),
      'upgrade-insecure-requests' =>
      array (
        0 => '1',
      ),
      'sec-ch-ua-platform' =>
      array (
        0 => '"Windows"',
      ),
      'sec-ch-ua-mobile' =>
      array (
        0 => '?0',
      ),
      'sec-ch-ua' =>
      array (
        0 => '"Chromium";v="94", "Google Chrome";v="94", ";Not A Brand";v="99"',
      ),
      'cache-control' =>
      array (
        0 => 'max-age=0',
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
      'max-age' => '0',
    ),
  )),
   'defaultLocale' => 'en',
   'isHostValid' => true,
   'isForwardedValid' => true,
));
		return  $this->callAndCheckResponse($expectedCode, $expectedString);
	}
}
