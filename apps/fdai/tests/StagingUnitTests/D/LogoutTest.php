<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection SpellCheckingInspection */
namespace Tests\StagingUnitTests\D;
use App\Buttons\Auth\LoginButton;
use App\Override\GeneratedTestRequest;
use App\Override\QMHeaderBag;
use App\Override\QMParameterBag;
use App\Override\QMServerBag;
use Illuminate\Testing\TestResponse;
use Tests\LaravelStagingTestCase;
class LogoutTest extends LaravelStagingTestCase
{
    protected $REQUEST_URI = "/auth/logout";
    public function testLogoutAsRegularUser(): void{
        $this->actAsTestUser();
	    $r = $this->assertCanAccessDatalab();
	    $this->stagingRequest(302, "");
        $response = $this->getTestResponse();
        $response->isRedirect(LoginButton::PATH);
	    $this->assertCannotAccessDatalab();
	    $this->checkTestDuration(15);
        $this->checkQueryCount(9);
    }
    public function testLogoutAsAdmin(): void{
        $this->actAsAdmin();
	    $r = $this->assertCanAccessDatalab();
	    $this->stagingRequest(302, "");
        $response = $this->getTestResponse();
	    $response->isRedirect(LoginButton::PATH);
	    $this->assertCannotAccessDatalab();
	    $this->checkTestDuration(15);
        $this->checkQueryCount(10);
    }
    public function testLogoutWithoutAuth(): void{
	    $r = $this->stagingRequest(302);
	    $r->assertLocation('https://staging.quantimo.do/app/public/#/app/intro?logout=1');
	    $this->assertCannotAccessDatalab();
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
      'HTTP_COOKIE' => 'u=23d371768d0a8f0f65c69331060a512a0049ad3f; remember_web_3dc7a913ef5fd4b890ecabe3487085573e16cf82=eyJpdiI6IkprSWJiZlFqZ1VHUDJoTmpENG43a1E9PSIsInZhbHVlIjoiXC9NQlA2dUcyZnhzcGdUNDl1N2c5b0wrOXptVWg1U2wxODZCQUdjV0Fnc1FZTFZEQ1N0U0hWSXVrRlBnaXZzeVArUzlzTHRwTmtTQmZaRE85QjRVa2VGYTEyeDJVRmpoZnZaK0tXZU11ZCtnOXU1ZFhuNndRRDFvUk93MFBQMnRxSWJLaTV2aGdDeWRGakN3N08wc3NuUT09IiwibWFjIjoiNjcyMjBiN2FhMDY2ZjhhYzdjYTAyYmM0ZTAwMmE1YzlhNTc3MGU4Y2VlOThhMDJkOWZlNmEwODcyYWI4MTU5YyJ9; XDEBUG_SESSION=PHPSTORM; laravel_session_testing=eyJpdiI6IkVJY21KMzNsdXVRMSsrckIzQkpEbnc9PSIsInZhbHVlIjoiYkNSV0ZKXC9lWDYyTVJDNHZwRTZHQVVBK3F1UU9Pc2RkXC9WRWEyeHdDMnhWdXkyenRERXlUVmIwQVhhdnRTQVVLIiwibWFjIjoiNjA1YTQyZTVkNzI2YmIwOGFlZGU5YTA4ZmIzNWM0YmU3OWI2ODQ0YmE2ODExOGQ4Y2Q2YTAwMjI5ZDBiMGNmNCJ9; remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6IlRYS0dUUXBIMjA4R2xpbjNEMzJpOHc9PSIsInZhbHVlIjoiQUtreDl4WnJ3OVFMOFgzK01zQ1AyUk5nVkxCQkZiK1VtaDZhU1dKWkRjRVpLcGQ0NW10aVU1dFR3XC83VlMwZWhTa2NOZlBMZWdMTG1oVElPZFwvWWpBWE1ydUF1d2g5YjQrcDRJamdPMkZycVVsaDQ4bWp3VXlmcUh2TEg2NlRuSzdMQjR5TDhzZUxvZFJmUjIyK1VVNHc9PSIsIm1hYyI6ImQ5OTEwYWRhNTk5N2NiNjlkZDVlMzZjYTY1NGQ4ZGFmODkzNzJiZDA4ZjM3ZDg0ZmRkYmNkMzhjNjUzOTdmZDMifQ%3D%3D; drift_aid=597ab9ce-8bc9-4024-be90-ace829486562; driftt_aid=597ab9ce-8bc9-4024-be90-ace829486562; intended_url=https%3A%2F%2Ftesting.quantimo.do%2Fnova%2Fresources%2Fconnector-requests%2F8; drift_eid=230; XDEBUG_PROFILE=1; XSRF-TOKEN=eyJpdiI6IlVxM0xKTVVobUxQUTNBaEtidTErZlE9PSIsInZhbHVlIjoiOGVNUlAyZ0QyRnZIdkJrSGErY0NuUnVRS0xnSXVNemFRMFBGaEVEQlEzMWlWYzZqMCs5NU1DbUZQQVVNK0JjcCIsIm1hYyI6ImIyZTVmMzNkNmE2NzE1MzA0MzRiMmIxMzk4YWM2YzgyNzMzMzhkOGQzZWUxN2Y4MTM2YzJjMjQwNDQ0MzJjZjQifQ%3D%3D; laravel_session=eyJpdiI6InRBRFEyRjlHZXJZQnA5WWtLMDJucmc9PSIsInZhbHVlIjoiKzM3YVRaWkJTc0YzQXdSdzFhVGc2VnpGZUxUbWE2UzNNQm5GeURvMU5saEV0eXRrZTNEeTBQR1RHV1QyQlBaUCIsIm1hYyI6ImExODI1YTliMDc3YjVmNTk3NzdkM2JmMzEzYjYzYTI0ODI0OWU4ZTliYTYxMDhjYzc2ZDY4MTM3ZjlmYTFkM2QifQ%3D%3D; login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6IlpOeVlnWG04OUI3MHdZVGo2TU1Id3c9PSIsInZhbHVlIjoiV2ZnRmFic3hyS0pBdzhBbnJtVmh4UT09IiwibWFjIjoiYzcxMjA5MGUyZTY2MDVhODYzOWZhZDJhZmRhZGRjNDdiYTVjOTg0NDAxNjJhMThjODI4MTU0NzVmNmZjMzQyNCJ9',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_REFERER' => getenv('APP_URL').'/logout',
      'HTTP_SEC_FETCH_DEST' => 'document',
      'HTTP_SEC_FETCH_MODE' => 'navigate',
      'HTTP_SEC_FETCH_SITE' => 'same-origin',
      'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.61 Safari/537.36',
      'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
      'HTTP_SEC_CH_UA_PLATFORM' => '"Windows"',
      'HTTP_SEC_CH_UA_MOBILE' => '?0',
      'HTTP_SEC_CH_UA' => '"Chromium";v="94", "Google Chrome";v="94", ";Not A Brand";v="99"',
      'HTTP_CONNECTION' => 'keep-alive',
      'REDIRECT_STATUS' => '200',
      'HTTPS' => 'on',
      'SERVER_NAME' => 'local.quantimo.do',
      'SERVER_PORT' => '443',
      'REMOTE_PORT' => '1100',
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
      'REQUEST_TIME_FLOAT' => 1633484564.590495,
      'REQUEST_TIME' => 1633484564,
      'CACHE_DRIVER' => 'array',
    ),
  )),
   'cookies' =>
  QMParameterBag::__set_state(array(
     'parameters' =>
    array (
      'u' => '23d371768d0a8f0f65c69331060a512a0049ad3f',
      'remember_web_3dc7a913ef5fd4b890ecabe3487085573e16cf82' => 'eyJpdiI6IkprSWJiZlFqZ1VHUDJoTmpENG43a1E9PSIsInZhbHVlIjoiXC9NQlA2dUcyZnhzcGdUNDl1N2c5b0wrOXptVWg1U2wxODZCQUdjV0Fnc1FZTFZEQ1N0U0hWSXVrRlBnaXZzeVArUzlzTHRwTmtTQmZaRE85QjRVa2VGYTEyeDJVRmpoZnZaK0tXZU11ZCtnOXU1ZFhuNndRRDFvUk93MFBQMnRxSWJLaTV2aGdDeWRGakN3N08wc3NuUT09IiwibWFjIjoiNjcyMjBiN2FhMDY2ZjhhYzdjYTAyYmM0ZTAwMmE1YzlhNTc3MGU4Y2VlOThhMDJkOWZlNmEwODcyYWI4MTU5YyJ9',
      'XDEBUG_SESSION' => 'PHPSTORM',
      'laravel_session_testing' => 'eyJpdiI6IkVJY21KMzNsdXVRMSsrckIzQkpEbnc9PSIsInZhbHVlIjoiYkNSV0ZKXC9lWDYyTVJDNHZwRTZHQVVBK3F1UU9Pc2RkXC9WRWEyeHdDMnhWdXkyenRERXlUVmIwQVhhdnRTQVVLIiwibWFjIjoiNjA1YTQyZTVkNzI2YmIwOGFlZGU5YTA4ZmIzNWM0YmU3OWI2ODQ0YmE2ODExOGQ4Y2Q2YTAwMjI5ZDBiMGNmNCJ9',
      'remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d' => 'eyJpdiI6IlRYS0dUUXBIMjA4R2xpbjNEMzJpOHc9PSIsInZhbHVlIjoiQUtreDl4WnJ3OVFMOFgzK01zQ1AyUk5nVkxCQkZiK1VtaDZhU1dKWkRjRVpLcGQ0NW10aVU1dFR3XC83VlMwZWhTa2NOZlBMZWdMTG1oVElPZFwvWWpBWE1ydUF1d2g5YjQrcDRJamdPMkZycVVsaDQ4bWp3VXlmcUh2TEg2NlRuSzdMQjR5TDhzZUxvZFJmUjIyK1VVNHc9PSIsIm1hYyI6ImQ5OTEwYWRhNTk5N2NiNjlkZDVlMzZjYTY1NGQ4ZGFmODkzNzJiZDA4ZjM3ZDg0ZmRkYmNkMzhjNjUzOTdmZDMifQ==',
      'drift_aid' => '597ab9ce-8bc9-4024-be90-ace829486562',
      'driftt_aid' => '597ab9ce-8bc9-4024-be90-ace829486562',
      'intended_url' => getenv('APP_URL').'/nova/resources/connector-requests/8',
      'drift_eid' => '230',
      'XDEBUG_PROFILE' => '1',
      'XSRF-TOKEN' => 'eyJpdiI6IlVxM0xKTVVobUxQUTNBaEtidTErZlE9PSIsInZhbHVlIjoiOGVNUlAyZ0QyRnZIdkJrSGErY0NuUnVRS0xnSXVNemFRMFBGaEVEQlEzMWlWYzZqMCs5NU1DbUZQQVVNK0JjcCIsIm1hYyI6ImIyZTVmMzNkNmE2NzE1MzA0MzRiMmIxMzk4YWM2YzgyNzMzMzhkOGQzZWUxN2Y4MTM2YzJjMjQwNDQ0MzJjZjQifQ==',
      'laravel_session' => 'eyJpdiI6InRBRFEyRjlHZXJZQnA5WWtLMDJucmc9PSIsInZhbHVlIjoiKzM3YVRaWkJTc0YzQXdSdzFhVGc2VnpGZUxUbWE2UzNNQm5GeURvMU5saEV0eXRrZTNEeTBQR1RHV1QyQlBaUCIsIm1hYyI6ImExODI1YTliMDc3YjVmNTk3NzdkM2JmMzEzYjYzYTI0ODI0OWU4ZTliYTYxMDhjYzc2ZDY4MTM3ZjlmYTFkM2QifQ==',
      'login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d' => 'eyJpdiI6IlpOeVlnWG04OUI3MHdZVGo2TU1Id3c9PSIsInZhbHVlIjoiV2ZnRmFic3hyS0pBdzhBbnJtVmh4UT09IiwibWFjIjoiYzcxMjA5MGUyZTY2MDVhODYzOWZhZDJhZmRhZGRjNDdiYTVjOTg0NDAxNjJhMThjODI4MTU0NzVmNmZjMzQyNCJ9',
    ),
  )),
   'headers' =>
  QMHeaderBag::__set_state(array(
     'headers' =>
    array (
      'cookie' =>
      array (
        0 => 'u=23d371768d0a8f0f65c69331060a512a0049ad3f; remember_web_3dc7a913ef5fd4b890ecabe3487085573e16cf82=eyJpdiI6IkprSWJiZlFqZ1VHUDJoTmpENG43a1E9PSIsInZhbHVlIjoiXC9NQlA2dUcyZnhzcGdUNDl1N2c5b0wrOXptVWg1U2wxODZCQUdjV0Fnc1FZTFZEQ1N0U0hWSXVrRlBnaXZzeVArUzlzTHRwTmtTQmZaRE85QjRVa2VGYTEyeDJVRmpoZnZaK0tXZU11ZCtnOXU1ZFhuNndRRDFvUk93MFBQMnRxSWJLaTV2aGdDeWRGakN3N08wc3NuUT09IiwibWFjIjoiNjcyMjBiN2FhMDY2ZjhhYzdjYTAyYmM0ZTAwMmE1YzlhNTc3MGU4Y2VlOThhMDJkOWZlNmEwODcyYWI4MTU5YyJ9; XDEBUG_SESSION=PHPSTORM; laravel_session_testing=eyJpdiI6IkVJY21KMzNsdXVRMSsrckIzQkpEbnc9PSIsInZhbHVlIjoiYkNSV0ZKXC9lWDYyTVJDNHZwRTZHQVVBK3F1UU9Pc2RkXC9WRWEyeHdDMnhWdXkyenRERXlUVmIwQVhhdnRTQVVLIiwibWFjIjoiNjA1YTQyZTVkNzI2YmIwOGFlZGU5YTA4ZmIzNWM0YmU3OWI2ODQ0YmE2ODExOGQ4Y2Q2YTAwMjI5ZDBiMGNmNCJ9; remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6IlRYS0dUUXBIMjA4R2xpbjNEMzJpOHc9PSIsInZhbHVlIjoiQUtreDl4WnJ3OVFMOFgzK01zQ1AyUk5nVkxCQkZiK1VtaDZhU1dKWkRjRVpLcGQ0NW10aVU1dFR3XC83VlMwZWhTa2NOZlBMZWdMTG1oVElPZFwvWWpBWE1ydUF1d2g5YjQrcDRJamdPMkZycVVsaDQ4bWp3VXlmcUh2TEg2NlRuSzdMQjR5TDhzZUxvZFJmUjIyK1VVNHc9PSIsIm1hYyI6ImQ5OTEwYWRhNTk5N2NiNjlkZDVlMzZjYTY1NGQ4ZGFmODkzNzJiZDA4ZjM3ZDg0ZmRkYmNkMzhjNjUzOTdmZDMifQ%3D%3D; drift_aid=597ab9ce-8bc9-4024-be90-ace829486562; driftt_aid=597ab9ce-8bc9-4024-be90-ace829486562; intended_url=https%3A%2F%2Ftesting.quantimo.do%2Fnova%2Fresources%2Fconnector-requests%2F8; drift_eid=230; XDEBUG_PROFILE=1; XSRF-TOKEN=eyJpdiI6IlVxM0xKTVVobUxQUTNBaEtidTErZlE9PSIsInZhbHVlIjoiOGVNUlAyZ0QyRnZIdkJrSGErY0NuUnVRS0xnSXVNemFRMFBGaEVEQlEzMWlWYzZqMCs5NU1DbUZQQVVNK0JjcCIsIm1hYyI6ImIyZTVmMzNkNmE2NzE1MzA0MzRiMmIxMzk4YWM2YzgyNzMzMzhkOGQzZWUxN2Y4MTM2YzJjMjQwNDQ0MzJjZjQifQ%3D%3D; laravel_session=eyJpdiI6InRBRFEyRjlHZXJZQnA5WWtLMDJucmc9PSIsInZhbHVlIjoiKzM3YVRaWkJTc0YzQXdSdzFhVGc2VnpGZUxUbWE2UzNNQm5GeURvMU5saEV0eXRrZTNEeTBQR1RHV1QyQlBaUCIsIm1hYyI6ImExODI1YTliMDc3YjVmNTk3NzdkM2JmMzEzYjYzYTI0ODI0OWU4ZTliYTYxMDhjYzc2ZDY4MTM3ZjlmYTFkM2QifQ%3D%3D; login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6IlpOeVlnWG04OUI3MHdZVGo2TU1Id3c9PSIsInZhbHVlIjoiV2ZnRmFic3hyS0pBdzhBbnJtVmh4UT09IiwibWFjIjoiYzcxMjA5MGUyZTY2MDVhODYzOWZhZDJhZmRhZGRjNDdiYTVjOTg0NDAxNjJhMThjODI4MTU0NzVmNmZjMzQyNCJ9',
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
        0 => getenv('APP_URL').'/logout',
      ),
      'sec-fetch-dest' =>
      array (
        0 => 'document',
      ),
      'sec-fetch-mode' =>
      array (
        0 => 'navigate',
      ),
      'sec-fetch-site' =>
      array (
        0 => 'same-origin',
      ),
      'accept' =>
      array (
        0 => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      ),
      'user-agent' =>
      array (
        0 => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.61 Safari/537.36',
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
      'connection' =>
      array (
        0 => 'keep-alive',
      ),
      'host' =>
      array (
        0 => 'testing.quantimo.do',
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
   'defaultLocale' => 'en',
   'isHostValid' => true,
   'isForwardedValid' => true,
));
		return  $this->callAndCheckResponse($expectedCode, $expectedString);
	}
}
