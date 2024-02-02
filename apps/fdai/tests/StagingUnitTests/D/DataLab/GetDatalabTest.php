<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection SpellCheckingInspection */
namespace Tests\StagingUnitTests\D\DataLab;
use App\Override\GeneratedTestRequest;
use App\Override\QMFileBag;
use App\Override\QMHeaderBag;
use App\Override\QMParameterBag;
use App\Override\QMServerBag;
use Illuminate\Testing\TestResponse;
use Tests\LaravelStagingTestCase;
class GetDatalabTest extends LaravelStagingTestCase
{
    protected $REQUEST_URI = "/datalab";
    public function testDatalabAsRegularUser(): void{
        $this->actAsTestUser();
        $response = $this->stagingRequest(200, "");
        $this->checkTestDuration(5);
        $this->checkQueryCount(4);
    }
    public function testDatalabAsAdmin(): void{
        $this->actAsAdmin();
        $response = $this->stagingRequest(200, "");
        $this->checkTestDuration(5);
        $this->checkQueryCount(4);
    }
    public function testDatalabWithoutAuth(): void{
        $this->unauthenticated();
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
      'HTTP_COOKIE' => '__cfduid=df3bcb60f11c220f65104c37de9ac7ea41592425988; _ga=GA1.1.1415477443.1592425993; _gid=GA1.1.489049337.1592425993; driftt_aid=c8735c86-68c0-4de2-94ce-73a43605c5c5; driftt_aid=c8735c86-68c0-4de2-94ce-73a43605c5c5; DFTT_END_USER_PREV_BOOTSTRAPPED=true; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1593635613%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; XSRF-TOKEN=eyJpdiI6IjVqVktsMEJodEhpdmdxT3VVOUdSR0E9PSIsInZhbHVlIjoiVk1OUCtYYlZqQXB2aG9DTzJ4TGtlOU9VVmxrTlwvM2RkdzlMWU1nR1lRV3NHb3ZtbWxHWVdnWTYreGZnUXJ2Wk0iLCJtYWMiOiJmMmQwYzYwNzQ0YjIxMTlkYmFiMzAyMjgyYzRjNjNjNjYzMDM3NDM3ZjIxYzc0MTg4YWQ2ZGE4ZDNhZjU5NjdlIn0%3D; laravel_session=eyJpdiI6IjU5UkIrOU9lWEUyS3ZRR1RDWlhwNEE9PSIsInZhbHVlIjoidncxRmczU3RUemwrTEtRMFlqOVQyWDBcLzlwaGFuZVl5TlFZazVMVldnTTRoOVAzUUhvZGZndWlzQTNmT3pMQlwvIiwibWFjIjoiZTZjNDZjNDJhZTBhY2Y2Mjg5MDU0MjI4N2YyOGQ2MTQxMmYyZDkxMTU5MmZlNjYyOTQ3ZjJlOWZmYzE3Mjg1NCJ9; XDEBUG_PROFILE=1',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_SEC_FETCH_DEST' => 'document',
      'HTTP_SEC_FETCH_USER' => '?1',
      'HTTP_SEC_FETCH_MODE' => 'navigate',
      'HTTP_SEC_FETCH_SITE' => 'none',
      'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.106 Safari/537.36',
      'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
      'HTTP_CONNECTION' => 'keep-alive',
      'REDIRECT_STATUS' => '200',
      'HTTPS' => 'on',
      'SERVER_NAME' => 'testing.quantimo.do',
      'SERVER_PORT' => '443',
      'REMOTE_PORT' => '64273',
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
      '__cfduid' => 'df3bcb60f11c220f65104c37de9ac7ea41592425988',
      '_ga' => 'GA1.1.1415477443.1592425993',
      '_gid' => 'GA1.1.489049337.1592425993',
      'driftt_aid' => 'c8735c86-68c0-4de2-94ce-73a43605c5c5',
      'DFTT_END_USER_PREV_BOOTSTRAPPED' => 'true',
      'quantimodo_logged_in_af6160480df78a3a6d520187243f05c9' => 'mike|1593635613|de069cf4b3bbf933721060a76259dad7|quantimodo',
      'XSRF-TOKEN' => 'eyJpdiI6IjVqVktsMEJodEhpdmdxT3VVOUdSR0E9PSIsInZhbHVlIjoiVk1OUCtYYlZqQXB2aG9DTzJ4TGtlOU9VVmxrTlwvM2RkdzlMWU1nR1lRV3NHb3ZtbWxHWVdnWTYreGZnUXJ2Wk0iLCJtYWMiOiJmMmQwYzYwNzQ0YjIxMTlkYmFiMzAyMjgyYzRjNjNjNjYzMDM3NDM3ZjIxYzc0MTg4YWQ2ZGE4ZDNhZjU5NjdlIn0=',
      'laravel_session' => 'eyJpdiI6IjU5UkIrOU9lWEUyS3ZRR1RDWlhwNEE9PSIsInZhbHVlIjoidncxRmczU3RUemwrTEtRMFlqOVQyWDBcLzlwaGFuZVl5TlFZazVMVldnTTRoOVAzUUhvZGZndWlzQTNmT3pMQlwvIiwibWFjIjoiZTZjNDZjNDJhZTBhY2Y2Mjg5MDU0MjI4N2YyOGQ2MTQxMmYyZDkxMTU5MmZlNjYyOTQ3ZjJlOWZmYzE3Mjg1NCJ9',
      'XDEBUG_PROFILE' => '1',
    ),
  )),
   'headers' =>
  QMHeaderBag::__set_state(array(
     'headers' =>
    array (
      'cookie' =>
      array (
        0 => '__cfduid=df3bcb60f11c220f65104c37de9ac7ea41592425988; _ga=GA1.1.1415477443.1592425993; _gid=GA1.1.489049337.1592425993; driftt_aid=c8735c86-68c0-4de2-94ce-73a43605c5c5; driftt_aid=c8735c86-68c0-4de2-94ce-73a43605c5c5; DFTT_END_USER_PREV_BOOTSTRAPPED=true; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1593635613%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; XSRF-TOKEN=eyJpdiI6IjVqVktsMEJodEhpdmdxT3VVOUdSR0E9PSIsInZhbHVlIjoiVk1OUCtYYlZqQXB2aG9DTzJ4TGtlOU9VVmxrTlwvM2RkdzlMWU1nR1lRV3NHb3ZtbWxHWVdnWTYreGZnUXJ2Wk0iLCJtYWMiOiJmMmQwYzYwNzQ0YjIxMTlkYmFiMzAyMjgyYzRjNjNjNjYzMDM3NDM3ZjIxYzc0MTg4YWQ2ZGE4ZDNhZjU5NjdlIn0%3D; laravel_session=eyJpdiI6IjU5UkIrOU9lWEUyS3ZRR1RDWlhwNEE9PSIsInZhbHVlIjoidncxRmczU3RUemwrTEtRMFlqOVQyWDBcLzlwaGFuZVl5TlFZazVMVldnTTRoOVAzUUhvZGZndWlzQTNmT3pMQlwvIiwibWFjIjoiZTZjNDZjNDJhZTBhY2Y2Mjg5MDU0MjI4N2YyOGQ2MTQxMmYyZDkxMTU5MmZlNjYyOTQ3ZjJlOWZmYzE3Mjg1NCJ9; XDEBUG_PROFILE=1',
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
        0 => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      ),
      'user-agent' =>
      array (
        0 => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.106 Safari/537.36',
      ),
      'upgrade-insecure-requests' =>
      array (
        0 => '1',
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
