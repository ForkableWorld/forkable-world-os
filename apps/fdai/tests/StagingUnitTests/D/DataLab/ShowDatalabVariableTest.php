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
class ShowDatalabVariableTest extends LaravelStagingTestCase
{
    protected $REQUEST_URI = "/datalab/variables/1885";
    public function testDatalabVariableDetailsAsRegularUser(): void{
        $this->actAsTestUser();
        $response = $this->stagingRequest(200, "");
        $this->assertStringNotContainsString("See UserVariables", $response->getContent());
        $this->checkTestDuration(10);
        $this->checkQueryCount(14);
    }
    public function testDatalabVariableDetailsAsAdmin(): void{
        $this->actAsAdmin();
        $response = $this->stagingRequest(200, "");
        $this->checkTestDuration(10);
        $this->checkQueryCount(13);
    }
    public function testDatalabVariableDetailsWithoutAuth(): void{
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
      'HTTP_COOKIE' => 'u=1f428f225112e63447dddbc2cdb87d70f597b6bf; _ga=GA1.1.1091482458.1590862194; driftt_aid=d814d39b-f800-483f-8417-92891397ffec; __cfduid=dedfea3f97c3efc49cc7ef45d88b1dbde1590866417; _ga=GA1.2.1319490333.1590871543; DFTT_END_USER_PREV_BOOTSTRAPPED=true; __gads=ID=064cb43f27ada9a8:T=1591295660:S=ALNI_MaXoYSavd6pmww_S4OEYN1zNgfqiQ; driftt_eid=230; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1592930788%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6Ik91Y2dPU3FncXQrQ3VzSXFmblZyQnc9PSIsInZhbHVlIjoiQXdiU0Z0V3JxQUM4RENxN2xEUnllRzRndDl2TzJLTVwvSTNkc3Q2bkFGdEFrUTlGY2xZREpvc21iYUdaK1Vub3RHcjErWUx6OG11S0JWZWhmMDU3R3p1Y0trRjltWmxtZVJOTE1Tb1lDMUtKcEs5RmNUVlhZcVh1a2c5S2k0ZVQ5OVR3SUZXS3lNM05PSFlUVGdQM2p5QT09IiwibWFjIjoiYWVhZjE0MDM3ODg0OTRhNjBlNjIxZWM4NDI0MDVjNTYxMDI2ZTczZmY2YmI1MzMzNzYyNzAwMzU1MWIwMjM0YSJ9; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; _gid=GA1.2.1870841610.1591898680; XSRF-TOKEN=eyJpdiI6IkhRUWVidllCeUZqeGFrQThkXC9DeTdnPT0iLCJ2YWx1ZSI6IjBldk8xRHdtOXZZc2pGVkNPY1hrcFE0U0Rpb3NnMHVGWExoeU5ObWY5U1pjeVwvcUNyd09nclwvT2hiQlwva0xHZjYiLCJtYWMiOiJjN2IxZjQxY2UwNTZmMTNlM2Y5YzgxM2VhZTYwMjM4NjU3NGRjNzdkZmUzNDFiZDNlYWQxN2E1YzM1MDllNWQ2In0%3D; laravel_session=eyJpdiI6IlJCWHZoWVQrTzNrcU1JWjNuZDllRlE9PSIsInZhbHVlIjoiSGhHVjU2a3hoblNIQnVaS0JzTUxRSnJPaG1xZUdobmRTbFwvWGxqRUxwRTAzTU1FY0hubzJzckV0T3JSTVFPT2wiLCJtYWMiOiI1NGFlNjA5ZDQyMzFlNWY3ZDljY2ZjNmFiNjFhMDU5ZjNmNTA1MTA5N2Q5YWYxNDI3OGE0NjQ5ZTJhODM3MTk1In0%3D',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_REFERER' => getenv('APP_URL').'/datalab/variables',
      'HTTP_SEC_FETCH_DEST' => 'document',
      'HTTP_SEC_FETCH_USER' => '?1',
      'HTTP_SEC_FETCH_MODE' => 'navigate',
      'HTTP_SEC_FETCH_SITE' => 'same-origin',
      'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.97 Safari/537.36',
      'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
      'HTTP_CONNECTION' => 'keep-alive',
      'REDIRECT_STATUS' => '200',
      'HTTPS' => 'on',
      'SERVER_NAME' => 'testing.quantimo.do',
      'SERVER_PORT' => '443',
      'REMOTE_PORT' => '58596',
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
      'u' => '1f428f225112e63447dddbc2cdb87d70f597b6bf',
      '_ga' => 'GA1.1.1091482458.1590862194',
      'driftt_aid' => 'd814d39b-f800-483f-8417-92891397ffec',
      '__cfduid' => 'dedfea3f97c3efc49cc7ef45d88b1dbde1590866417',
      'DFTT_END_USER_PREV_BOOTSTRAPPED' => 'true',
      '__gads' => 'ID=064cb43f27ada9a8:T=1591295660:S=ALNI_MaXoYSavd6pmww_S4OEYN1zNgfqiQ',
      'driftt_eid' => '230',
      'quantimodo_logged_in_af6160480df78a3a6d520187243f05c9' => 'mike|1592930788|de069cf4b3bbf933721060a76259dad7|quantimodo',
      'remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d' => 'eyJpdiI6Ik91Y2dPU3FncXQrQ3VzSXFmblZyQnc9PSIsInZhbHVlIjoiQXdiU0Z0V3JxQUM4RENxN2xEUnllRzRndDl2TzJLTVwvSTNkc3Q2bkFGdEFrUTlGY2xZREpvc21iYUdaK1Vub3RHcjErWUx6OG11S0JWZWhmMDU3R3p1Y0trRjltWmxtZVJOTE1Tb1lDMUtKcEs5RmNUVlhZcVh1a2c5S2k0ZVQ5OVR3SUZXS3lNM05PSFlUVGdQM2p5QT09IiwibWFjIjoiYWVhZjE0MDM3ODg0OTRhNjBlNjIxZWM4NDI0MDVjNTYxMDI2ZTczZmY2YmI1MzMzNzYyNzAwMzU1MWIwMjM0YSJ9',
      '_gid' => 'GA1.2.1870841610.1591898680',
      'XSRF-TOKEN' => 'eyJpdiI6IkhRUWVidllCeUZqeGFrQThkXC9DeTdnPT0iLCJ2YWx1ZSI6IjBldk8xRHdtOXZZc2pGVkNPY1hrcFE0U0Rpb3NnMHVGWExoeU5ObWY5U1pjeVwvcUNyd09nclwvT2hiQlwva0xHZjYiLCJtYWMiOiJjN2IxZjQxY2UwNTZmMTNlM2Y5YzgxM2VhZTYwMjM4NjU3NGRjNzdkZmUzNDFiZDNlYWQxN2E1YzM1MDllNWQ2In0=',
      'laravel_session' => 'eyJpdiI6IlJCWHZoWVQrTzNrcU1JWjNuZDllRlE9PSIsInZhbHVlIjoiSGhHVjU2a3hoblNIQnVaS0JzTUxRSnJPaG1xZUdobmRTbFwvWGxqRUxwRTAzTU1FY0hubzJzckV0T3JSTVFPT2wiLCJtYWMiOiI1NGFlNjA5ZDQyMzFlNWY3ZDljY2ZjNmFiNjFhMDU5ZjNmNTA1MTA5N2Q5YWYxNDI3OGE0NjQ5ZTJhODM3MTk1In0=',
    ),
  )),
   'headers' =>
  QMHeaderBag::__set_state(array(
     'headers' =>
    array (
      'cookie' =>
      array (
        0 => 'u=1f428f225112e63447dddbc2cdb87d70f597b6bf; _ga=GA1.1.1091482458.1590862194; driftt_aid=d814d39b-f800-483f-8417-92891397ffec; __cfduid=dedfea3f97c3efc49cc7ef45d88b1dbde1590866417; _ga=GA1.2.1319490333.1590871543; DFTT_END_USER_PREV_BOOTSTRAPPED=true; __gads=ID=064cb43f27ada9a8:T=1591295660:S=ALNI_MaXoYSavd6pmww_S4OEYN1zNgfqiQ; driftt_eid=230; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1592930788%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6Ik91Y2dPU3FncXQrQ3VzSXFmblZyQnc9PSIsInZhbHVlIjoiQXdiU0Z0V3JxQUM4RENxN2xEUnllRzRndDl2TzJLTVwvSTNkc3Q2bkFGdEFrUTlGY2xZREpvc21iYUdaK1Vub3RHcjErWUx6OG11S0JWZWhmMDU3R3p1Y0trRjltWmxtZVJOTE1Tb1lDMUtKcEs5RmNUVlhZcVh1a2c5S2k0ZVQ5OVR3SUZXS3lNM05PSFlUVGdQM2p5QT09IiwibWFjIjoiYWVhZjE0MDM3ODg0OTRhNjBlNjIxZWM4NDI0MDVjNTYxMDI2ZTczZmY2YmI1MzMzNzYyNzAwMzU1MWIwMjM0YSJ9; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; _gid=GA1.2.1870841610.1591898680; XSRF-TOKEN=eyJpdiI6IkhRUWVidllCeUZqeGFrQThkXC9DeTdnPT0iLCJ2YWx1ZSI6IjBldk8xRHdtOXZZc2pGVkNPY1hrcFE0U0Rpb3NnMHVGWExoeU5ObWY5U1pjeVwvcUNyd09nclwvT2hiQlwva0xHZjYiLCJtYWMiOiJjN2IxZjQxY2UwNTZmMTNlM2Y5YzgxM2VhZTYwMjM4NjU3NGRjNzdkZmUzNDFiZDNlYWQxN2E1YzM1MDllNWQ2In0%3D; laravel_session=eyJpdiI6IlJCWHZoWVQrTzNrcU1JWjNuZDllRlE9PSIsInZhbHVlIjoiSGhHVjU2a3hoblNIQnVaS0JzTUxRSnJPaG1xZUdobmRTbFwvWGxqRUxwRTAzTU1FY0hubzJzckV0T3JSTVFPT2wiLCJtYWMiOiI1NGFlNjA5ZDQyMzFlNWY3ZDljY2ZjNmFiNjFhMDU5ZjNmNTA1MTA5N2Q5YWYxNDI3OGE0NjQ5ZTJhODM3MTk1In0%3D',
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
        0 => getenv('APP_URL').'/datalab/variables',
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
        0 => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.97 Safari/537.36',
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
