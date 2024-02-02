<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection SpellCheckingInspection */
namespace Tests\StagingUnitTests\D\Laravel;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\Override\GeneratedTestRequest;
use App\Override\QMFileBag;
use App\Override\QMHeaderBag;
use App\Override\QMParameterBag;
use App\Override\QMServerBag;
use App\Properties\User\UserIdProperty;
use Illuminate\Testing\TestResponse;
use Tests\LaravelStagingTestCase;
class UserStudyWithoutEnoughMeasurementsTest extends LaravelStagingTestCase
{
    protected $REQUEST_URI = "/studies/cause-6059957-effect-1273-user-".UserIdProperty::USER_ID_TEST_USER."-user-study";
    public function testCauseNameLink(){
        $c  = QMGlobalVariableRelationship::find(65648488);
        $this->assertEquals('<a href="https://staging.quantimo.do/variables/1692" title="See Plus - Almond Walnut Macadamia + Protein, With Peanuts Details" target="_blank">Plus - Almond Walnut Macadamia + Protein, With Peanuts</a>',
            $c->getCauseNameLink());
    }
    public function testUserStudyWithoutEnoughMeasurementsAsRegularUser(): void{
        $this->actAsTestUser();
        $response = $this->stagingRequest(200, "");
        $this->compareHtmlPage("regular-user", $response->getContent());
        $this->checkTestDuration(12);
        $this->checkQueryCount(30);
    }
    public function testUserStudyWithoutEnoughMeasurementsAsAdmin(): void{
        $this->actAsAdmin();
        $response = $this->stagingRequest(200, "");
        $this->compareHtmlPage("admin", $response->getContent());
        $this->checkTestDuration(10);
        $this->checkQueryCount(34);
    }
    public function testUserStudyWithoutEnoughMeasurementsWithoutAuth(): void{
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
      'HTTP_COOKIE' => '_ga=GA1.2.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; DFTT_END_USER_PREV_BOOTSTRAPPED=true; u=084521ae39828198127bd5b3d1d7fe9ccf4eca35; _ga=GA1.1.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; driftt_eid=230; drift_eid=230; __gads=ID=baf1b7342d7fe58c-22cb087a70c50002:T=1608928372:RT=1608928372:S=ALNI_Mb_nA1jEnsvWYINYNpOi0KgcoaQEA; dsq__=8ppa63u27cufhv; XDEBUG_SESSION=PHPSTORM; __utmc=109117957; __utma=109117957.644404966.1601578880.1611547594.1611547594.1; __utmz=109117957.1611547594.1.1.utmcsr=local.quantimo.do|utmccn=(referral)|utmcmd=referral|utmcct=/; __cfduid=d1f31d1b341afb2182e9a1083f6efb6b01611948020; _gid=GA1.2.944866099.1612489248; _gid=GA1.1.944866099.1612489248; final_callback_url=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Fv2%2Fauth%2Flogin%3FintendedUrl%3Dhttps%253A%252F%252Flocal.quantimo.do%252Fnova%252Fresources%252Fvariables%26client_id%3Dquantimodo%26quantimodoAccessToken%3Dmike-test-token%26quantimodoUserId%3D230; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1614012998%7Cb86ab88c2f236b6cb304be67366d5860%7Cquantimodo; drift_campaign_refresh=f3c295b2-5683-4c43-ad3a-a8bc10781f7f; clockwork-profile=; XSRF-TOKEN=eyJpdiI6InFiM1ZBZ2hWUkRPaVFmWUgzTDRUb0E9PSIsInZhbHVlIjoiazZJQnc4UFc1VjFJV2JhZ1wvbGlCWDhXRFJPRkJtZ0JKSngxNjdOSUVnTzg1SWhIUThkNkx2VFowYnNCZE4wR2oiLCJtYWMiOiJjODViY2VlZTRmMTE4ZDZkMGNiZjYxNWViMmYwZmY2MDA4ODk2ZDg4ZmUzZmVhZGEzYjE1YmEyMDc5ZmIyMDE4In0%3D; laravel_session=eyJpdiI6IjVRVVhFXC9vWE9HMHB1MnhYT0Rlcm1RPT0iLCJ2YWx1ZSI6Im9lZkU0SnFGN1BCSEpZa0RFQldHcVI2U0RyYmhQRnJHbjVkQU51UE5vSVlpN0FuYmV4RklYNXBMcjJyQTY5NmciLCJtYWMiOiI4ZjQ1NWZmZDAwZjZjYTNjYmVjNjM3NGNmOWRjNmMxOGI3NGE5NzU4ZjIwYTBkMzk5MGI2ZGQ3YmVhNjFmNDNlIn0%3D',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_SEC_FETCH_DEST' => 'document',
      'HTTP_SEC_FETCH_USER' => '?1',
      'HTTP_SEC_FETCH_MODE' => 'navigate',
      'HTTP_SEC_FETCH_SITE' => 'none',
      'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.146 Safari/537.36',
      'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
      'HTTP_SEC_CH_UA_MOBILE' => '?0',
      'HTTP_SEC_CH_UA' => '"Chromium";v="88", "Google Chrome";v="88", ";Not A Brand";v="99"',
      'HTTP_CONNECTION' => 'keep-alive',
      'REDIRECT_STATUS' => '200',
      'HTTPS' => 'on',
      'SERVER_NAME' => 'studies.crowdsourcingcures.org',
      'SERVER_PORT' => '443',
      'REMOTE_PORT' => '35564',
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
      'drift_eid' => '230',
      '__gads' => 'ID=baf1b7342d7fe58c-22cb087a70c50002:T=1608928372:RT=1608928372:S=ALNI_Mb_nA1jEnsvWYINYNpOi0KgcoaQEA',
      'dsq__' => '8ppa63u27cufhv',
      'XDEBUG_SESSION' => 'PHPSTORM',
      '__utmc' => '109117957',
      '__utma' => '109117957.644404966.1601578880.1611547594.1611547594.1',
      '__utmz' => '109117957.1611547594.1.1.utmcsr=local.quantimo.do|utmccn=(referral)|utmcmd=referral|utmcct=/',
      '__cfduid' => 'd1f31d1b341afb2182e9a1083f6efb6b01611948020',
      '_gid' => 'GA1.2.944866099.1612489248',
      'final_callback_url' => getenv('APP_URL').'/auth/login?intendedUrl=https%3A%2F%2Flocal.quantimo.do%2Fnova%2Fresources%2Fvariables&client_id=quantimodo&quantimodoAccessToken=mike-test-token&quantimodoUserId=230',
      'quantimodo_logged_in_af6160480df78a3a6d520187243f05c9' => 'mike|1614012998|b86ab88c2f236b6cb304be67366d5860|quantimodo',
      'drift_campaign_refresh' => 'f3c295b2-5683-4c43-ad3a-a8bc10781f7f',
      'clockwork-profile' => '',
      'XSRF-TOKEN' => 'eyJpdiI6InFiM1ZBZ2hWUkRPaVFmWUgzTDRUb0E9PSIsInZhbHVlIjoiazZJQnc4UFc1VjFJV2JhZ1wvbGlCWDhXRFJPRkJtZ0JKSngxNjdOSUVnTzg1SWhIUThkNkx2VFowYnNCZE4wR2oiLCJtYWMiOiJjODViY2VlZTRmMTE4ZDZkMGNiZjYxNWViMmYwZmY2MDA4ODk2ZDg4ZmUzZmVhZGEzYjE1YmEyMDc5ZmIyMDE4In0=',
      'laravel_session' => 'eyJpdiI6IjVRVVhFXC9vWE9HMHB1MnhYT0Rlcm1RPT0iLCJ2YWx1ZSI6Im9lZkU0SnFGN1BCSEpZa0RFQldHcVI2U0RyYmhQRnJHbjVkQU51UE5vSVlpN0FuYmV4RklYNXBMcjJyQTY5NmciLCJtYWMiOiI4ZjQ1NWZmZDAwZjZjYTNjYmVjNjM3NGNmOWRjNmMxOGI3NGE5NzU4ZjIwYTBkMzk5MGI2ZGQ3YmVhNjFmNDNlIn0=',
    ),
  )),
   'headers' =>
  QMHeaderBag::__set_state(array(
     'headers' =>
    array (
      'cookie' =>
      array (
        0 => '_ga=GA1.2.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; DFTT_END_USER_PREV_BOOTSTRAPPED=true; u=084521ae39828198127bd5b3d1d7fe9ccf4eca35; _ga=GA1.1.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; driftt_eid=230; drift_eid=230; __gads=ID=baf1b7342d7fe58c-22cb087a70c50002:T=1608928372:RT=1608928372:S=ALNI_Mb_nA1jEnsvWYINYNpOi0KgcoaQEA; dsq__=8ppa63u27cufhv; XDEBUG_SESSION=PHPSTORM; __utmc=109117957; __utma=109117957.644404966.1601578880.1611547594.1611547594.1; __utmz=109117957.1611547594.1.1.utmcsr=local.quantimo.do|utmccn=(referral)|utmcmd=referral|utmcct=/; __cfduid=d1f31d1b341afb2182e9a1083f6efb6b01611948020; _gid=GA1.2.944866099.1612489248; _gid=GA1.1.944866099.1612489248; final_callback_url=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Fv2%2Fauth%2Flogin%3FintendedUrl%3Dhttps%253A%252F%252Flocal.quantimo.do%252Fnova%252Fresources%252Fvariables%26client_id%3Dquantimodo%26quantimodoAccessToken%3Dmike-test-token%26quantimodoUserId%3D230; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1614012998%7Cb86ab88c2f236b6cb304be67366d5860%7Cquantimodo; drift_campaign_refresh=f3c295b2-5683-4c43-ad3a-a8bc10781f7f; clockwork-profile=; XSRF-TOKEN=eyJpdiI6InFiM1ZBZ2hWUkRPaVFmWUgzTDRUb0E9PSIsInZhbHVlIjoiazZJQnc4UFc1VjFJV2JhZ1wvbGlCWDhXRFJPRkJtZ0JKSngxNjdOSUVnTzg1SWhIUThkNkx2VFowYnNCZE4wR2oiLCJtYWMiOiJjODViY2VlZTRmMTE4ZDZkMGNiZjYxNWViMmYwZmY2MDA4ODk2ZDg4ZmUzZmVhZGEzYjE1YmEyMDc5ZmIyMDE4In0%3D; laravel_session=eyJpdiI6IjVRVVhFXC9vWE9HMHB1MnhYT0Rlcm1RPT0iLCJ2YWx1ZSI6Im9lZkU0SnFGN1BCSEpZa0RFQldHcVI2U0RyYmhQRnJHbjVkQU51UE5vSVlpN0FuYmV4RklYNXBMcjJyQTY5NmciLCJtYWMiOiI4ZjQ1NWZmZDAwZjZjYTNjYmVjNjM3NGNmOWRjNmMxOGI3NGE5NzU4ZjIwYTBkMzk5MGI2ZGQ3YmVhNjFmNDNlIn0%3D',
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
        0 => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.146 Safari/537.36',
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
