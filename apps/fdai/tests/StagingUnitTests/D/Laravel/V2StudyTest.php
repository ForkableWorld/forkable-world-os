<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection SpellCheckingInspection */
namespace Tests\StagingUnitTests\D\Laravel;
use App\Override\GeneratedTestRequest;
use App\Override\QMFileBag;
use App\Override\QMHeaderBag;
use App\Override\QMParameterBag;
use App\Override\QMServerBag;
use App\Utils\AppMode;
use Tests\LaravelStagingTestCase;
class V2StudyTest extends LaravelStagingTestCase
{
    protected $REQUEST_URI = "/api/v2/study?logLevel=info&effectVariableName=Excitability&causeVariableName=Flaxseed%20Oil";
    public function testStudyAsRegularUser(): void{
        $this->validateAssetUrls();
        $this->actAsTestUser();
        $response = $this->stagingRequest(200, "Higher Flaxseed Oil Intake Predicts");
        $this->assertFalse(AppMode::isLaravelAPIRequest());
        //$this->assertHtmlContains("userId=18535", $response);
        $this->compareHtmlPage("user", $response->getContent());
        $this->checkTestDuration(15);
        $this->checkQueryCount(36);
    }
    public function testStudyAsAdmin(): void{
        $this->actAsAdmin();
        $response = $this->stagingRequest(200, "Higher Flaxseed Oil Intake Predicts");
        $this->compareHtmlPage("admin", $response->getContent());
        $this->checkTestDuration(19);
        $this->checkQueryCount(23);
    }
    public function testStudyWithoutAuth(): void{
        $this->assertGuest();
        $response = $this->stagingRequest(200, "Higher Flaxseed Oil Intake Predicts");
        $this->compareHtmlPage("no-auth", $response->getContent());
        $this->checkTestDuration(16);
        $this->checkQueryCount(30);
    }
    /**
     * @param int $expectedCode
     * @param string|null $expectedString
     * @return string|object
     */
    protected function stagingRequest(int $expectedCode = 200, string $expectedString = null):\Illuminate\Testing\TestResponse {
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
      'logLevel' => 'info',
      'effectVariableName' => 'Excitability',
      'causeVariableName' => 'Flaxseed Oil',
    ),
  )),
   'query' =>
  QMParameterBag::__set_state(array(
     'parameters' =>
    array (
      'logLevel' => 'info',
      'effectVariableName' => 'Excitability',
      'causeVariableName' => 'Flaxseed Oil',
    ),
  )),
   'server' =>
  QMServerBag::__set_state(array(
     'parameters' =>
    array (
      'HOME' => '/var/www',
      'HTTP_CDN_LOOP' => 'cloudflare',
      'HTTP_CF_CONNECTING_IP' => '24.216.168.142',
      'HTTP_CF_REQUEST_ID' => '05eac12383000030feb0a1d000000001',
      'HTTP_COOKIE' => 'driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; _lr_uf_-mkcthl=5aa03d76-feaf-428d-806f-48fb62953450; u=637445b5b185f4809c9b9081bca785c21b2ee6aa; XDEBUG_SESSION=PHPSTORM; __cfduid=d0b262ec60d9dad143da74009904d74921601569701; _ga=GA1.2.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; DFTT_END_USER_PREV_BOOTSTRAPPED=true; __gads=ID=baf1b7342d7fe58c:T=1601862028:S=ALNI_MZ1pa8rvTAuTSgPwbsR16DZSDT1bw; _gid=GA1.2.1636261980.1602777261; XSRF-TOKEN=eyJpdiI6InFKS0hFU2R4MTN5Q0p0SFhrWXlKcFE9PSIsInZhbHVlIjoidk5qalZFUlBnYnlXZ1wva3ZaTmFMVE1zblJtZnhnUWxlUjFaSlR6eU1rWXVpbDBcLzJPUUNvQ3BteU5ZWXdlMStzIiwibWFjIjoiZWQ0YzlhZDI5NTc2YjcxY2ZmOWE4ZDQ1ODgyODYwMTYzZjZmMTgzZmRlYTk1ZjRhN2VkZDIyNjQyYTQwNjc1ZCJ9; laravel_session=eyJpdiI6InllRDZZSzhTWXhMUWt0Um9BRmVcLzNBPT0iLCJ2YWx1ZSI6InRrcDhmRTZwdjVzdjJuaXVsYWE1cmVucHJBcVwvNWdmRnVJNlpmNGloMkZ3VGhDYjJSV2VnWVwvWVRBRU9KUndoUyIsIm1hYyI6IjFkMjVkZDgxZTBmZTU0OWRhMWU5YTA2MTQzYjYzYzA4NDFlNTFlYTE1MDcyMmRmZTgwNGM4NmQyMzE3NzdhOGMifQ%3D%3D; driftt_eid=230; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1604455989%7C7bbd02cce0932081841094cc76a908c8%7Cquantimodo',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_SEC_FETCH_DEST' => 'document',
      'HTTP_SEC_FETCH_MODE' => 'navigate',
      'HTTP_SEC_FETCH_SITE' => 'none',
      'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36',
      'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
      'HTTP_CF_VISITOR' => '{"scheme":"https"}',
      'HTTP_X_FORWARDED_PROTO' => 'https',
      'HTTP_CF_RAY' => '5e57d14c0b5a30fe-ORD',
      'HTTP_X_FORWARDED_FOR' => '24.216.168.142',
      'HTTP_CF_IPCOUNTRY' => 'US',
      'HTTP_ACCEPT_ENCODING' => 'gzip',
      'HTTP_CONNECTION' => 'Keep-Alive',
      'REDIRECT_STATUS' => '200',
      'HTTPS' => 'on',
      'SERVER_NAME' => 'app.quantimo.do',
      'SERVER_PORT' => '443',
      'SERVER_ADDR' => '172.26.8.41',
      'REMOTE_PORT' => '35714',
      'REMOTE_ADDR' => '162.158.74.112',
      'SERVER_SOFTWARE' => 'nginx/1.15.8',
      'GATEWAY_INTERFACE' => 'CGI/1.1',
      'SERVER_PROTOCOL' => 'HTTP/1.1',
      'DOCUMENT_ROOT' => '/home/ubuntu/releases/dad1382f309d35a3bbc7756073c478e339d11d6d/public',
      'DOCUMENT_URI' => '/index.php',
      'REQUEST_URI' => $this->REQUEST_URI.'',
      'SCRIPT_NAME' => '/index.php',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => '',
      'REQUEST_METHOD' => 'GET',
      'QUERY_STRING' => 'logLevel=info&effectVariableName=Excitability&causeVariableName=Flaxseed%20Oil',
      'SCRIPT_FILENAME' => '/home/ubuntu/releases/dad1382f309d35a3bbc7756073c478e339d11d6d/public/index.php',
      'FCGI_ROLE' => 'RESPONDER',
      'PHP_SELF' => '/index.php',
      'APP_LOG_LEVEL' => 'INFO',

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
      'driftt_aid' => 'df29ce65-369a-440c-9d5e-a1888f0cd13d',
      '_lr_uf_-mkcthl' => '5aa03d76-feaf-428d-806f-48fb62953450',
      'u' => '637445b5b185f4809c9b9081bca785c21b2ee6aa',
      'XDEBUG_SESSION' => 'PHPSTORM',
      '__cfduid' => 'd0b262ec60d9dad143da74009904d74921601569701',
      '_ga' => 'GA1.2.644404966.1601578880',
      'DFTT_END_USER_PREV_BOOTSTRAPPED' => 'true',
      '__gads' => 'ID=baf1b7342d7fe58c:T=1601862028:S=ALNI_MZ1pa8rvTAuTSgPwbsR16DZSDT1bw',
      '_gid' => 'GA1.2.1636261980.1602777261',
      'XSRF-TOKEN' => 'eyJpdiI6InFKS0hFU2R4MTN5Q0p0SFhrWXlKcFE9PSIsInZhbHVlIjoidk5qalZFUlBnYnlXZ1wva3ZaTmFMVE1zblJtZnhnUWxlUjFaSlR6eU1rWXVpbDBcLzJPUUNvQ3BteU5ZWXdlMStzIiwibWFjIjoiZWQ0YzlhZDI5NTc2YjcxY2ZmOWE4ZDQ1ODgyODYwMTYzZjZmMTgzZmRlYTk1ZjRhN2VkZDIyNjQyYTQwNjc1ZCJ9',
      'laravel_session' => 'eyJpdiI6InllRDZZSzhTWXhMUWt0Um9BRmVcLzNBPT0iLCJ2YWx1ZSI6InRrcDhmRTZwdjVzdjJuaXVsYWE1cmVucHJBcVwvNWdmRnVJNlpmNGloMkZ3VGhDYjJSV2VnWVwvWVRBRU9KUndoUyIsIm1hYyI6IjFkMjVkZDgxZTBmZTU0OWRhMWU5YTA2MTQzYjYzYzA4NDFlNTFlYTE1MDcyMmRmZTgwNGM4NmQyMzE3NzdhOGMifQ==',
      'driftt_eid' => '230',
      'quantimodo_logged_in_af6160480df78a3a6d520187243f05c9' => 'mike|1604455989|7bbd02cce0932081841094cc76a908c8|quantimodo',
    ),
  )),
   'headers' =>
  QMHeaderBag::__set_state(array(
     'headers' =>
    array (
      'cdn-loop' =>
      array (
        0 => 'cloudflare',
      ),
      'cf-connecting-ip' =>
      array (
        0 => '24.216.168.142',
      ),
      'cf-request-id' =>
      array (
        0 => '05eac12383000030feb0a1d000000001',
      ),
      'cookie' =>
      array (
        0 => 'driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; _lr_uf_-mkcthl=5aa03d76-feaf-428d-806f-48fb62953450; u=637445b5b185f4809c9b9081bca785c21b2ee6aa; XDEBUG_SESSION=PHPSTORM; __cfduid=d0b262ec60d9dad143da74009904d74921601569701; _ga=GA1.2.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; DFTT_END_USER_PREV_BOOTSTRAPPED=true; __gads=ID=baf1b7342d7fe58c:T=1601862028:S=ALNI_MZ1pa8rvTAuTSgPwbsR16DZSDT1bw; _gid=GA1.2.1636261980.1602777261; XSRF-TOKEN=eyJpdiI6InFKS0hFU2R4MTN5Q0p0SFhrWXlKcFE9PSIsInZhbHVlIjoidk5qalZFUlBnYnlXZ1wva3ZaTmFMVE1zblJtZnhnUWxlUjFaSlR6eU1rWXVpbDBcLzJPUUNvQ3BteU5ZWXdlMStzIiwibWFjIjoiZWQ0YzlhZDI5NTc2YjcxY2ZmOWE4ZDQ1ODgyODYwMTYzZjZmMTgzZmRlYTk1ZjRhN2VkZDIyNjQyYTQwNjc1ZCJ9; laravel_session=eyJpdiI6InllRDZZSzhTWXhMUWt0Um9BRmVcLzNBPT0iLCJ2YWx1ZSI6InRrcDhmRTZwdjVzdjJuaXVsYWE1cmVucHJBcVwvNWdmRnVJNlpmNGloMkZ3VGhDYjJSV2VnWVwvWVRBRU9KUndoUyIsIm1hYyI6IjFkMjVkZDgxZTBmZTU0OWRhMWU5YTA2MTQzYjYzYzA4NDFlNTFlYTE1MDcyMmRmZTgwNGM4NmQyMzE3NzdhOGMifQ%3D%3D; driftt_eid=230; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1604455989%7C7bbd02cce0932081841094cc76a908c8%7Cquantimodo',
      ),
      'accept-language' =>
      array (
        0 => 'en-US,en;q=0.9',
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
        0 => 'none',
      ),
      'accept' =>
      array (
        0 => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      ),
      'user-agent' =>
      array (
        0 => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36',
      ),
      'upgrade-insecure-requests' =>
      array (
        0 => '1',
      ),
      'cf-visitor' =>
      array (
        0 => '{"scheme":"https"}',
      ),
      'x-forwarded-proto' =>
      array (
        0 => 'https',
      ),
      'cf-ray' =>
      array (
        0 => '5e57d14c0b5a30fe-ORD',
      ),
      'x-forwarded-for' =>
      array (
        0 => '24.216.168.142',
      ),
      'cf-ipcountry' =>
      array (
        0 => 'US',
      ),
      'accept-encoding' =>
      array (
        0 => 'gzip',
      ),
      'connection' =>
      array (
        0 => 'Keep-Alive',
      ),
      'host' =>
      array (
        0 => 'staging.quantimo.do',
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
    protected function validateAssetUrls(): void{
        $this->assertFalse(AppMode::isLaravelAPIRequest());
        $this->assertEquals("https://staging.quantimo.do/js/qmLog.js", qm_api_asset('js/qmLog.js'));
        $this->assertEquals("https://staging.quantimo.do/js/qmLog.js", asset('js/qmLog.js'));
    }
}
