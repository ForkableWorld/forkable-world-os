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
class DatalabCorrelationsByVariableIdsTest extends LaravelStagingTestCase
{
    protected $REQUEST_URI = "/datalab/user_variable_relationships?cause_variable_id=89305&effect_variable_id=1874";
    public function testDatalabCorrelationsByVariableIdsAsRegularUser(): void{
        $this->actAsTestUser();
        $response = $this->stagingRequest(200, "");
        $this->checkTestDuration(7);
        $this->checkQueryCount(4);
    }
    public function testDatalabCorrelationsByVariableIdsAsAdmin(): void{
        $this->actAsAdmin();
        $response = $this->stagingRequest(200, "");
        $this->checkTestDuration(13);
        $this->checkQueryCount(4);
    }
    public function testDatalabCorrelationsByVariableIdsWithoutAuth(): void{
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
      'cause_variable_id' => '89305',
      'effect_variable_id' => '1874',
    ),
  )),
   'query' =>
  QMParameterBag::__set_state(array(
     'parameters' =>
    array (
      'cause_variable_id' => '89305',
      'effect_variable_id' => '1874',
    ),
  )),
   'server' =>
  QMServerBag::__set_state(array(
     'parameters' =>
    array (
      'USER' => 'vagrant',
      'HOME' => '/home/vagrant',
      'HTTP_COOKIE' => '_ga=GA1.1.1415477443.1592425993; driftt_aid=c8735c86-68c0-4de2-94ce-73a43605c5c5; DFTT_END_USER_PREV_BOOTSTRAPPED=true; u=6ec3e62cc0069a10c6759edd5423df738a0fec0b; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; XDEBUG_SESSION=PHPSTORM; php-console-server=5; php-console-client=eyJwaHAtY29uc29sZS1jbGllbnQiOjV9; __gads=ID=747672481a4ab161:T=1593795481:S=ALNI_MZhRWXjPuKtS5vGg1WXOPNKdVmSVA; __utmz=109117957.1593836440.1.1.utmcsr=local.quantimo.do|utmccn=(referral)|utmcmd=referral|utmcct=/; __utmc=109117957; __utma=109117957.2014257657.1592502511.1593836440.1593836440.1; _ga=GA1.2.1415477443.1592425993; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1596214177%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; __cfduid=d78c81ea746227e1437c0203e9b6018d71595018221; _gid=GA1.2.1979930966.1595354005; driftt_eid=87709; XSRF-TOKEN=eyJpdiI6ImNBeitIYnlQXC8ydTFIWEI4M3NqVVJ3PT0iLCJ2YWx1ZSI6Im1GZ1phZURFeWtZRzEyU0RuTk1TWThycFVnUlwvUGZxUGJxOXJRQVZjam81V1gwMVJTYlpuRzduN2pDNjhOTGpiIiwibWFjIjoiN2E0N2ZkYTkxNmIyMDVmYjJjMTQ4Mzg5YTFkNWNmZjRjZTQ5MjQ3ZDk1ODFkNDkwYzExMmJiZjNkZmM1ZDc0ZSJ9; laravel_session=eyJpdiI6ImdcLzNMbUFFV0t1emdFMjJQS0szUVwvUT09IiwidmFsdWUiOiJTSWFGc3h5aGNTVmtHdUJJYUI0R3VSYjVBQzc3QnpROXlyY2xPYkFEVUh4azdcL1kxZzAxcHQ2XC96elN0a0hoNE8iLCJtYWMiOiIwZDI0NzgxZTg1MGZlMDhhMzNkYWY0MTY0MjNlZDJlOGQ0ZTcwNTY2ZmRiM2IzOGQ5OGJkNTE1NTczZmZmODAxIn0%3D',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_REFERER' => getenv('APP_URL').'/datalab/user_variable_relationships?cause_variable_id=89305&effect_variable_id=1874',
      'HTTP_SEC_FETCH_DEST' => 'document',
      'HTTP_SEC_FETCH_USER' => '?1',
      'HTTP_SEC_FETCH_MODE' => 'navigate',
      'HTTP_SEC_FETCH_SITE' => 'same-origin',
      'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36',
      'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
      'HTTP_CONNECTION' => 'keep-alive',
      'REDIRECT_STATUS' => '200',
      'HTTPS' => 'on',
      'SERVER_NAME' => 'testing.quantimo.do',
      'SERVER_PORT' => '443',
      'REMOTE_PORT' => '55161',
      'SERVER_SOFTWARE' => 'nginx/1.15.8',
      'GATEWAY_INTERFACE' => 'CGI/1.1',
      'SERVER_PROTOCOL' => 'HTTP/1.1',
      'DOCUMENT_URI' => '/index.php',
      'REQUEST_URI' => $this->REQUEST_URI.'',
      'SCRIPT_NAME' => '/index.php',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => '',
      'REQUEST_METHOD' => 'GET',
      'QUERY_STRING' => 'cause_variable_id=89305&effect_variable_id=1874',
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
      '_ga' => 'GA1.1.1415477443.1592425993',
      'driftt_aid' => 'c8735c86-68c0-4de2-94ce-73a43605c5c5',
      'DFTT_END_USER_PREV_BOOTSTRAPPED' => 'true',
      'u' => '6ec3e62cc0069a10c6759edd5423df738a0fec0b',
      'XDEBUG_SESSION' => 'PHPSTORM',
      'php-console-server' => '5',
      'php-console-client' => 'eyJwaHAtY29uc29sZS1jbGllbnQiOjV9',
      '__gads' => 'ID=747672481a4ab161:T=1593795481:S=ALNI_MZhRWXjPuKtS5vGg1WXOPNKdVmSVA',
      '__utmz' => '109117957.1593836440.1.1.utmcsr=local.quantimo.do|utmccn=(referral)|utmcmd=referral|utmcct=/',
      '__utmc' => '109117957',
      '__utma' => '109117957.2014257657.1592502511.1593836440.1593836440.1',
      'quantimodo_logged_in_af6160480df78a3a6d520187243f05c9' => 'mike|1596214177|de069cf4b3bbf933721060a76259dad7|quantimodo',
      '__cfduid' => 'd78c81ea746227e1437c0203e9b6018d71595018221',
      '_gid' => 'GA1.2.1979930966.1595354005',
      'driftt_eid' => '87709',
      'XSRF-TOKEN' => 'eyJpdiI6ImNBeitIYnlQXC8ydTFIWEI4M3NqVVJ3PT0iLCJ2YWx1ZSI6Im1GZ1phZURFeWtZRzEyU0RuTk1TWThycFVnUlwvUGZxUGJxOXJRQVZjam81V1gwMVJTYlpuRzduN2pDNjhOTGpiIiwibWFjIjoiN2E0N2ZkYTkxNmIyMDVmYjJjMTQ4Mzg5YTFkNWNmZjRjZTQ5MjQ3ZDk1ODFkNDkwYzExMmJiZjNkZmM1ZDc0ZSJ9',
      'laravel_session' => 'eyJpdiI6ImdcLzNMbUFFV0t1emdFMjJQS0szUVwvUT09IiwidmFsdWUiOiJTSWFGc3h5aGNTVmtHdUJJYUI0R3VSYjVBQzc3QnpROXlyY2xPYkFEVUh4azdcL1kxZzAxcHQ2XC96elN0a0hoNE8iLCJtYWMiOiIwZDI0NzgxZTg1MGZlMDhhMzNkYWY0MTY0MjNlZDJlOGQ0ZTcwNTY2ZmRiM2IzOGQ5OGJkNTE1NTczZmZmODAxIn0=',
    ),
  )),
   'headers' =>
  QMHeaderBag::__set_state(array(
     'headers' =>
    array (
      'cookie' =>
      array (
        0 => '_ga=GA1.1.1415477443.1592425993; driftt_aid=c8735c86-68c0-4de2-94ce-73a43605c5c5; DFTT_END_USER_PREV_BOOTSTRAPPED=true; u=6ec3e62cc0069a10c6759edd5423df738a0fec0b; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; XDEBUG_SESSION=PHPSTORM; php-console-server=5; php-console-client=eyJwaHAtY29uc29sZS1jbGllbnQiOjV9; __gads=ID=747672481a4ab161:T=1593795481:S=ALNI_MZhRWXjPuKtS5vGg1WXOPNKdVmSVA; __utmz=109117957.1593836440.1.1.utmcsr=local.quantimo.do|utmccn=(referral)|utmcmd=referral|utmcct=/; __utmc=109117957; __utma=109117957.2014257657.1592502511.1593836440.1593836440.1; _ga=GA1.2.1415477443.1592425993; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1596214177%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; __cfduid=d78c81ea746227e1437c0203e9b6018d71595018221; _gid=GA1.2.1979930966.1595354005; driftt_eid=87709; XSRF-TOKEN=eyJpdiI6ImNBeitIYnlQXC8ydTFIWEI4M3NqVVJ3PT0iLCJ2YWx1ZSI6Im1GZ1phZURFeWtZRzEyU0RuTk1TWThycFVnUlwvUGZxUGJxOXJRQVZjam81V1gwMVJTYlpuRzduN2pDNjhOTGpiIiwibWFjIjoiN2E0N2ZkYTkxNmIyMDVmYjJjMTQ4Mzg5YTFkNWNmZjRjZTQ5MjQ3ZDk1ODFkNDkwYzExMmJiZjNkZmM1ZDc0ZSJ9; laravel_session=eyJpdiI6ImdcLzNMbUFFV0t1emdFMjJQS0szUVwvUT09IiwidmFsdWUiOiJTSWFGc3h5aGNTVmtHdUJJYUI0R3VSYjVBQzc3QnpROXlyY2xPYkFEVUh4azdcL1kxZzAxcHQ2XC96elN0a0hoNE8iLCJtYWMiOiIwZDI0NzgxZTg1MGZlMDhhMzNkYWY0MTY0MjNlZDJlOGQ0ZTcwNTY2ZmRiM2IzOGQ5OGJkNTE1NTczZmZmODAxIn0%3D',
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
        0 => getenv('APP_URL').'/datalab/user_variable_relationships?cause_variable_id=89305&effect_variable_id=1874',
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
        0 => 'same-origin',
      ),
      'accept' =>
      array (
        0 => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      ),
      'user-agent' =>
      array (
        0 => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36',
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
