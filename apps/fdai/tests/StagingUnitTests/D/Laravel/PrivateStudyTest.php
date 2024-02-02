<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection SpellCheckingInspection */
namespace Tests\StagingUnitTests\D\Laravel;
use App\Override\GeneratedTestRequest;
use App\Override\QMFileBag;
use App\Override\QMHeaderBag;
use App\Override\QMParameterBag;
use App\Override\QMServerBag;
use Illuminate\Testing\TestResponse;
use Tests\LaravelStagingTestCase;
class PrivateStudyTest extends LaravelStagingTestCase
{
    protected $REQUEST_URI = "/studies/cause-1310-effect-1258-user-65181-user-study?phpunit=1";
    public function testPrivateStudyAsRegularUser(): void{
        $this->actAsTestUser();
		$this->expectUnauthorizedException();
        $response = $this->stagingRequest(403, "authorized");
        $this->checkTestDuration(5);
        $this->checkQueryCount(8);
    }
    public function testPrivateStudyAsAdmin(): void{
        $this->actAsAdmin();
        $response = $this->stagingRequest(200, "");
        $this->checkTestDuration(15);
        $this->checkQueryCount(28);
    }
    public function testPrivateStudyWithoutAuth(): void{
        $this->assertGuestRedirectToLogin();
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
      'HTTP_COOKIE' => '_ga=GA1.2.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; DFTT_END_USER_PREV_BOOTSTRAPPED=true; u=084521ae39828198127bd5b3d1d7fe9ccf4eca35; _ga=GA1.1.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; driftt_eid=230; drift_eid=230; __gads=ID=baf1b7342d7fe58c-22cb087a70c50002:T=1608928372:RT=1608928372:S=ALNI_Mb_nA1jEnsvWYINYNpOi0KgcoaQEA; dsq__=8ppa63u27cufhv; XDEBUG_SESSION=PHPSTORM; remember_web_3dc7a913ef5fd4b890ecabe3487085573e16cf82=eyJpdiI6InlXTUtxdVhUSlZCVHpOT21wOTJWckE9PSIsInZhbHVlIjoiMFNLUjc0eUtKeTJtWXZPb2pLRzNLSUZ1RGxObTg0SFl6XC9BWXllY1lPcExXMTh0d1djWW1kMG5RUXZUcEo5UExwcEVRMkZLSk5seE54WkxEQzluM0J3cFlxWitBTWlWMmE3eEdDekN6RFowb2FYT2drWTZGRFQzSTZRMkRZclJDYzdQS0R3SWpCd3ZpaVRoTWVsUzdBNk1rWXlqZ2hwR3YwVGR2MXNYajBsRT0iLCJtYWMiOiI0OTE0NGQyNzBlZjYzYzAxOGM0NzczNjY0NjVhZDUyN2JmZGFlYTIwNDg5NjI3ZWYxOGVjZGJhOTJmZTI0YzYyIn0%3D; __utmc=109117957; __utma=109117957.644404966.1601578880.1611547594.1611547594.1; __utmz=109117957.1611547594.1.1.utmcsr=local.quantimo.do|utmccn=(referral)|utmcmd=referral|utmcct=/; __cfduid=d1f31d1b341afb2182e9a1083f6efb6b01611948020; _gid=GA1.2.1189744768.1612026143; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1613257998%7Cd225ca89ba4363977b988316c20dc1b2%7Cquantimodo; _gid=GA1.1.1189744768.1612026143; drift_campaign_refresh=bed792c0-aaef-4203-8a2d-77f9dcf4ef1f; XSRF-TOKEN=eyJpdiI6Iitpbmo0c013dUN1NXNxVjFFdGh6bHc9PSIsInZhbHVlIjoiMkdyZnNQbG5XR3gwOWhUUEMrbzdhbkxrU3dUV2tBMFBGXC9qZWJZVkxDTTJmUEd0RFdrdkE4eDBXQ0dDMDZUV0UiLCJtYWMiOiJmZTlkY2VmMzdhZTg3NjVhOWQ2MmUzYzBjM2Q2ZTBiMTM4MjIzMTg2YTc3MDY5MzQ4MTQxOTdmNWI3MzRhMDBkIn0%3D; laravel_session=eyJpdiI6Ik9LeFlVZzBja3BQNUZabEtoXC94MmhBPT0iLCJ2YWx1ZSI6IlRBeDJpdVZ5M01Fd2NvZk90b084cHNnYXdyaXpmTytvbjhhRXdmaDVGN2R5Vm1sbE9rb2FHTDdzMUpDYnJhWEgiLCJtYWMiOiJjOWM4YTY4OTkwYWE4YzAxNjQ5ZGM4MWZiNzgzMDFkMTIyMzMyOTkwNmNmMzg2MWRmMDZjMDkwM2IwNjA1NjUxIn0%3D',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_SEC_FETCH_DEST' => 'document',
      'HTTP_SEC_FETCH_USER' => '?1',
      'HTTP_SEC_FETCH_MODE' => 'navigate',
      'HTTP_SEC_FETCH_SITE' => 'none',
      'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.104 Safari/537.36',
      'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
      'HTTP_SEC_CH_UA_MOBILE' => '?0',
      'HTTP_SEC_CH_UA' => '"Chromium";v="88", "Google Chrome";v="88", ";Not A Brand";v="99"',
      'HTTP_CONNECTION' => 'keep-alive',
      'REDIRECT_STATUS' => '200',
      'HTTPS' => 'on',
      'SERVER_NAME' => 'studies.crowdsourcingcures.org',
      'SERVER_PORT' => '443',
      'REMOTE_PORT' => '40333',
      'SERVER_SOFTWARE' => 'nginx/1.15.8',
      'GATEWAY_INTERFACE' => 'CGI/1.1',
      'SERVER_PROTOCOL' => 'HTTP/1.1',
      'DOCUMENT_URI' => '/index.php',
      'REQUEST_URI' => $this->REQUEST_URI.'',
      'SCRIPT_NAME' => '/index.php',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => '',
      'REQUEST_METHOD' => 'GET',
      'QUERY_STRING' => 'phpunit=1',
      'FCGI_ROLE' => 'RESPONDER',
      'PHP_SELF' => '/index.php',
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
      '_ga' => 'GA1.2.644404966.1601578880',
      'driftt_aid' => 'df29ce65-369a-440c-9d5e-a1888f0cd13d',
      'DFTT_END_USER_PREV_BOOTSTRAPPED' => 'true',
      'u' => '084521ae39828198127bd5b3d1d7fe9ccf4eca35',
      'driftt_eid' => '230',
      'drift_eid' => '230',
      '__gads' => 'ID=baf1b7342d7fe58c-22cb087a70c50002:T=1608928372:RT=1608928372:S=ALNI_Mb_nA1jEnsvWYINYNpOi0KgcoaQEA',
      'dsq__' => '8ppa63u27cufhv',
      'XDEBUG_SESSION' => 'PHPSTORM',
      'remember_web_3dc7a913ef5fd4b890ecabe3487085573e16cf82' => 'eyJpdiI6InlXTUtxdVhUSlZCVHpOT21wOTJWckE9PSIsInZhbHVlIjoiMFNLUjc0eUtKeTJtWXZPb2pLRzNLSUZ1RGxObTg0SFl6XC9BWXllY1lPcExXMTh0d1djWW1kMG5RUXZUcEo5UExwcEVRMkZLSk5seE54WkxEQzluM0J3cFlxWitBTWlWMmE3eEdDekN6RFowb2FYT2drWTZGRFQzSTZRMkRZclJDYzdQS0R3SWpCd3ZpaVRoTWVsUzdBNk1rWXlqZ2hwR3YwVGR2MXNYajBsRT0iLCJtYWMiOiI0OTE0NGQyNzBlZjYzYzAxOGM0NzczNjY0NjVhZDUyN2JmZGFlYTIwNDg5NjI3ZWYxOGVjZGJhOTJmZTI0YzYyIn0=',
      '__utmc' => '109117957',
      '__utma' => '109117957.644404966.1601578880.1611547594.1611547594.1',
      '__utmz' => '109117957.1611547594.1.1.utmcsr=local.quantimo.do|utmccn=(referral)|utmcmd=referral|utmcct=/',
      '__cfduid' => 'd1f31d1b341afb2182e9a1083f6efb6b01611948020',
      '_gid' => 'GA1.2.1189744768.1612026143',
      'quantimodo_logged_in_af6160480df78a3a6d520187243f05c9' => 'mike|1613257998|d225ca89ba4363977b988316c20dc1b2|quantimodo',
      'drift_campaign_refresh' => 'bed792c0-aaef-4203-8a2d-77f9dcf4ef1f',
      'XSRF-TOKEN' => 'eyJpdiI6Iitpbmo0c013dUN1NXNxVjFFdGh6bHc9PSIsInZhbHVlIjoiMkdyZnNQbG5XR3gwOWhUUEMrbzdhbkxrU3dUV2tBMFBGXC9qZWJZVkxDTTJmUEd0RFdrdkE4eDBXQ0dDMDZUV0UiLCJtYWMiOiJmZTlkY2VmMzdhZTg3NjVhOWQ2MmUzYzBjM2Q2ZTBiMTM4MjIzMTg2YTc3MDY5MzQ4MTQxOTdmNWI3MzRhMDBkIn0=',
      'laravel_session' => 'eyJpdiI6Ik9LeFlVZzBja3BQNUZabEtoXC94MmhBPT0iLCJ2YWx1ZSI6IlRBeDJpdVZ5M01Fd2NvZk90b084cHNnYXdyaXpmTytvbjhhRXdmaDVGN2R5Vm1sbE9rb2FHTDdzMUpDYnJhWEgiLCJtYWMiOiJjOWM4YTY4OTkwYWE4YzAxNjQ5ZGM4MWZiNzgzMDFkMTIyMzMyOTkwNmNmMzg2MWRmMDZjMDkwM2IwNjA1NjUxIn0=',
    ),
  )),
   'headers' =>
  QMHeaderBag::__set_state(array(
     'headers' =>
    array (
      'cookie' =>
      array (
        0 => '_ga=GA1.2.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; DFTT_END_USER_PREV_BOOTSTRAPPED=true; u=084521ae39828198127bd5b3d1d7fe9ccf4eca35; _ga=GA1.1.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; driftt_eid=230; drift_eid=230; __gads=ID=baf1b7342d7fe58c-22cb087a70c50002:T=1608928372:RT=1608928372:S=ALNI_Mb_nA1jEnsvWYINYNpOi0KgcoaQEA; dsq__=8ppa63u27cufhv; XDEBUG_SESSION=PHPSTORM; remember_web_3dc7a913ef5fd4b890ecabe3487085573e16cf82=eyJpdiI6InlXTUtxdVhUSlZCVHpOT21wOTJWckE9PSIsInZhbHVlIjoiMFNLUjc0eUtKeTJtWXZPb2pLRzNLSUZ1RGxObTg0SFl6XC9BWXllY1lPcExXMTh0d1djWW1kMG5RUXZUcEo5UExwcEVRMkZLSk5seE54WkxEQzluM0J3cFlxWitBTWlWMmE3eEdDekN6RFowb2FYT2drWTZGRFQzSTZRMkRZclJDYzdQS0R3SWpCd3ZpaVRoTWVsUzdBNk1rWXlqZ2hwR3YwVGR2MXNYajBsRT0iLCJtYWMiOiI0OTE0NGQyNzBlZjYzYzAxOGM0NzczNjY0NjVhZDUyN2JmZGFlYTIwNDg5NjI3ZWYxOGVjZGJhOTJmZTI0YzYyIn0%3D; __utmc=109117957; __utma=109117957.644404966.1601578880.1611547594.1611547594.1; __utmz=109117957.1611547594.1.1.utmcsr=local.quantimo.do|utmccn=(referral)|utmcmd=referral|utmcct=/; __cfduid=d1f31d1b341afb2182e9a1083f6efb6b01611948020; _gid=GA1.2.1189744768.1612026143; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1613257998%7Cd225ca89ba4363977b988316c20dc1b2%7Cquantimodo; _gid=GA1.1.1189744768.1612026143; drift_campaign_refresh=bed792c0-aaef-4203-8a2d-77f9dcf4ef1f; XSRF-TOKEN=eyJpdiI6Iitpbmo0c013dUN1NXNxVjFFdGh6bHc9PSIsInZhbHVlIjoiMkdyZnNQbG5XR3gwOWhUUEMrbzdhbkxrU3dUV2tBMFBGXC9qZWJZVkxDTTJmUEd0RFdrdkE4eDBXQ0dDMDZUV0UiLCJtYWMiOiJmZTlkY2VmMzdhZTg3NjVhOWQ2MmUzYzBjM2Q2ZTBiMTM4MjIzMTg2YTc3MDY5MzQ4MTQxOTdmNWI3MzRhMDBkIn0%3D; laravel_session=eyJpdiI6Ik9LeFlVZzBja3BQNUZabEtoXC94MmhBPT0iLCJ2YWx1ZSI6IlRBeDJpdVZ5M01Fd2NvZk90b084cHNnYXdyaXpmTytvbjhhRXdmaDVGN2R5Vm1sbE9rb2FHTDdzMUpDYnJhWEgiLCJtYWMiOiJjOWM4YTY4OTkwYWE4YzAxNjQ5ZGM4MWZiNzgzMDFkMTIyMzMyOTkwNmNmMzg2MWRmMDZjMDkwM2IwNjA1NjUxIn0%3D',
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
        0 => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.104 Safari/537.36',
      ),
      'upgrade-insecure-requests' =>
      array (
        0 => '1',
      ),
      'sec-ch-ua-mobile' =>
      array (
        0 => '?0',
      ),
      'sec-ch-ua' =>
      array (
        0 => '"Chromium";v="88", "Google Chrome";v="88", ";Not A Brand";v="99"',
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
