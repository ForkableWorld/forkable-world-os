<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection SpellCheckingInspection */
namespace Tests\StagingUnitTests\D\Laravel;
use App\Exceptions\AuthenticationException;
use App\Exceptions\UnauthorizedException;
use App\Override\GeneratedTestRequest;
use App\Override\QMFileBag;
use App\Override\QMHeaderBag;
use App\Override\QMParameterBag;
use App\Override\QMServerBag;
use Illuminate\Testing\TestResponse;
use Tests\LaravelStagingTestCase;
use Tests\QMBaseTestCase;
class UserVariablesTest extends LaravelStagingTestCase
{
    protected $REQUEST_URI = "/user-variables/222073";
    public function testUserVariableAsUnsharedRegularUser(): void{
        $this->actAsTestUser();
        QMBaseTestCase::setExpectedRequestException(UnauthorizedException::class);
        $response = $this->stagingRequest(403, "authorized");
        $this->checkTestDuration(5);
        $this->checkQueryCount(6);
    }
    public function testUserVariablesAsAdmin(): void{
        $this->actAsAdmin();
        $response = $this->stagingRequest(200, "");
        $this->compareHtmlPage("admin", $response->getContent());
        $this->checkTestDuration(16);
        $this->checkQueryCount(31);
    }
    public function testUserVariableWithoutAuth(): void{
		self::setExpectedRequestException(\Illuminate\Auth\AuthenticationException::class);
        $r = $this->stagingRequest(302);
		$r->assertLocation('https://staging.quantimo.do/auth/register?intended_url=https%3A%2F%2Fstaging.quantimo.do%2Fuser-variables%2F222073');
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
      'HTTP_COOKIE' => '_ga=GA1.2.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; DFTT_END_USER_PREV_BOOTSTRAPPED=true; u=084521ae39828198127bd5b3d1d7fe9ccf4eca35; _ga=GA1.1.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; driftt_eid=230; __gads=ID=baf1b7342d7fe58c-22cb087a70c50002:T=1608928372:RT=1608928372:S=ALNI_Mb_nA1jEnsvWYINYNpOi0KgcoaQEA; dsq__=8ppa63u27cufhv; __utmc=109117957; __utmz=109117957.1611547594.1.1.utmcsr=local.quantimo.do|utmccn=(referral)|utmcmd=referral|utmcct=/; drift_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; drift_eid=230; __utma=109117957.644404966.1601578880.1611547594.1620319036.2; drift_eid=230; XDEBUG_SESSION=XDEBUG_ECLIPSE; _gid=GA1.2.964726826.1623942016; _gid=GA1.1.964726826.1623942016; _lr_tabs_-mkcthl%2Fquantimodo={%22sessionID%22:0%2C%22recordingID%22:%224-5aea1d2c-adc1-4fb8-8bdd-38344b8f9911%22%2C%22lastActivity%22:1623942021856}; _lr_hb_-mkcthl%2Fquantimodo={%22heartbeat%22:1623942021856}; _lr_uf_-mkcthl=47d7d06c-64bd-405d-9537-fa0fa5bf6c41; final_callback_url=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Fv2%2Fauth%2Fregister%3FintendedUrl%3Dhttps%253A%252F%252Flocal.quantimo.do%252Fstudies%252Fcause-5211821-effect-1906-user-60811-user-stud%26client_id%3Dquantimodo%26quantimodoAccessToken%3Ddd07057db32ef058ac76d5afc69d8a09f04cc047%26quantimodoUserId%3D230; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1625151644%7Cdd460739350ce71c52c696ceb4cc9350%7Cquantimodo; XSRF-TOKEN=eyJpdiI6ImFlY3ZHempTYTVBWFwvN1JpeDl4V1FnPT0iLCJ2YWx1ZSI6IklnKzBtRllFTVJQYWVkaE1xc1NPazk2ZzNON0hcL0FXMEg2NDJsS2FURndFT0I0eFZkVGtKRHlcL08rd1FqbW4ySSIsIm1hYyI6IjM4OWUzYjM1OWRjOTM4Y2QxM2VjNDU4YjM2N2Q3MzMyZTNhMTU3YTQ4MWVhZDA4N2M3MTVjZThmZjhmY2Y5YmQifQ%3D%3D; laravel_session=eyJpdiI6InVoZ1wvY3NsS0dpYVNCQzNYcjMrMFJRPT0iLCJ2YWx1ZSI6ImdPTzl6WHBTbzlvYzhBT0VhWGVyYW1mMlQwQ0lpYjl5dXdZM0swc3pwQ0F5ckE1Vm9odU01cXoybEh2dkdzWUYiLCJtYWMiOiJiMjgzODM3M2M0OGY1MmIzODMwZjMzNGZmMzgzZGY2Y2ZiZDg5YmVhZWE2MTEwNDFmNmNmNzIwNDlmZWQyNTU3In0%3D; drift_campaign_refresh=5bd46fbb-f6d7-4607-8c2b-30209b6eb873',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_SEC_FETCH_DEST' => 'document',
      'HTTP_SEC_FETCH_USER' => '?1',
      'HTTP_SEC_FETCH_MODE' => 'navigate',
      'HTTP_SEC_FETCH_SITE' => 'none',
      'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.106 Safari/537.36',
      'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
      'HTTP_SEC_CH_UA_MOBILE' => '?0',
      'HTTP_SEC_CH_UA' => '" Not;A Brand";v="99", "Google Chrome";v="91", "Chromium";v="91"',
      'HTTP_CACHE_CONTROL' => 'no-cache',
      'HTTP_PRAGMA' => 'no-cache',
      'HTTP_CONNECTION' => 'keep-alive',
      'REDIRECT_STATUS' => '200',
      'HTTPS' => 'on',
      'SERVER_NAME' => 'studies.crowdsourcingcures.org',
      'SERVER_PORT' => '443',
      'REMOTE_PORT' => '24916',
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
      '__gads' => 'ID=baf1b7342d7fe58c-22cb087a70c50002:T=1608928372:RT=1608928372:S=ALNI_Mb_nA1jEnsvWYINYNpOi0KgcoaQEA',
      'dsq__' => '8ppa63u27cufhv',
      '__utmc' => '109117957',
      '__utmz' => '109117957.1611547594.1.1.utmcsr=local.quantimo.do|utmccn=(referral)|utmcmd=referral|utmcct=/',
      'drift_aid' => 'df29ce65-369a-440c-9d5e-a1888f0cd13d',
      'drift_eid' => '230',
      '__utma' => '109117957.644404966.1601578880.1611547594.1620319036.2',
      'XDEBUG_SESSION' => 'XDEBUG_ECLIPSE',
      '_gid' => 'GA1.2.964726826.1623942016',
      '_lr_tabs_-mkcthl%2Fquantimodo' => '{"sessionID":0,"recordingID":"4-5aea1d2c-adc1-4fb8-8bdd-38344b8f9911","lastActivity":1623942021856}',
      '_lr_hb_-mkcthl%2Fquantimodo' => '{"heartbeat":1623942021856}',
      '_lr_uf_-mkcthl' => '47d7d06c-64bd-405d-9537-fa0fa5bf6c41',
      'XSRF-TOKEN' => 'eyJpdiI6ImFlY3ZHempTYTVBWFwvN1JpeDl4V1FnPT0iLCJ2YWx1ZSI6IklnKzBtRllFTVJQYWVkaE1xc1NPazk2ZzNON0hcL0FXMEg2NDJsS2FURndFT0I0eFZkVGtKRHlcL08rd1FqbW4ySSIsIm1hYyI6IjM4OWUzYjM1OWRjOTM4Y2QxM2VjNDU4YjM2N2Q3MzMyZTNhMTU3YTQ4MWVhZDA4N2M3MTVjZThmZjhmY2Y5YmQifQ==',
      'drift_campaign_refresh' => '5bd46fbb-f6d7-4607-8c2b-30209b6eb873',
    ),
  )),
   'headers' =>
  QMHeaderBag::__set_state(array(
     'headers' =>
    array (
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
        0 => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.106 Safari/537.36',
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
        0 => '" Not;A Brand";v="99", "Google Chrome";v="91", "Chromium";v="91"',
      ),
      'cache-control' =>
      array (
        0 => 'no-cache',
      ),
      'pragma' =>
      array (
        0 => 'no-cache',
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
      'no-cache' => true,
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
