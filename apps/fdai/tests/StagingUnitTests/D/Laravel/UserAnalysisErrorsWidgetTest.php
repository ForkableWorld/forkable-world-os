<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection SpellCheckingInspection */
namespace Tests\StagingUnitTests\D\Laravel;
use App\Exceptions\UnauthorizedException;
use App\Override\GeneratedTestRequest;
use App\Override\QMFileBag;
use App\Override\QMHeaderBag;
use App\Override\QMParameterBag;
use App\Override\QMServerBag;
use Illuminate\Testing\TestResponse;
use Tests\LaravelStagingTestCase;
class UserAnalysisErrorsWidgetTest extends LaravelStagingTestCase
{
    protected $REQUEST_URI = "/arrilot/load-widget?id=3&name=ProgressBox&params=eyJpdiI6IlM5d3dzNnFPdkp2TUt5eThtVFwvaHV3PT0iLCJ2YWx1ZSI6Ik4rZVZ0SmdnakVldXhmMmR3RTlJbyt1YmJUVm1TT2NOXC9NdXM0Vll1OXdxRmM1ZUxvRjlqQmRYb0NtZWMxeUlmOTI4ZFlqOEw0R3J5bTdvUDd6ZmducHNGQ0ZWK2pzVWdHYjJ2SlwvOXFUaWxyZWRNdDB1b0w3ZStTaVltYk9ob3dLQUJ3N2I1N1pVVCtZRnpaT0hnQ0U3SEZUUGpTVjVmQmdKTm05ejdYcGRoaVR1T0lRQzd5U2VRSXZhZ3c0M1wvXC82OWE3bCtjcjZsQlBpTWN3V3N6dGhnRCtXb3NvZDRBdEtPdG1DTGtSY3FseFdGcVBGZjRpXC8yZGM2eTlGODlOWEZaOHVDMnJUMkVidHhucHpmT0VaTWJsSUZOR0M3dEVqb2FmZEt1NVdVZ1c3YzFieDlneGYwRVpuUUxTb2loOVNzdlwveG5MS3ZmR0tiMWx1dkl0cGFhb3ZUSmZZZ0dRMVhlVHRnNDh5Y3ZTejNQXC9ZVTAyV0JTRE9KaVFkXC80QXNJNnRHbjFXYXNjc1pBNnEzbWtUOHpQWDIwSUE2ZFFtXC9sXC9xTjVPM0pEc1BGd1hINTlMZ255ejdBaklNNGtUeDBHTlM3VEg3SnluUVZ2ZlhcL3o3KzFMU3dwM0JBZUdRWVMxNmN2MUJhSmVUMmdJQndLTk9ScERrVk51OHVJOWNydXAzQXgrUllBVkpYK3NybVEwMFwvVkVUZEJwODVaOVpXelFoK2E3K1I2VE9WaWJKZjBXK25EUXg2SWFyZ2xxd2ZxaHhBekl1aHU2RlwvVjBzU3JJVURsclNcL280ZENweFQ0RHNoZlwvUjFwc3VMUlFcL2tUWTlyTDFZTkJadzFCUktCS3lRdE5YUEJTNUwxZEJYSDVReHp6bWVcLzh0enJCREY4RHlydllUVFJRQWJKSGdkSGV3Q3BUc1RkM2gxQ1wveEptcGlWNWVCdkY5ckZ4dzQwVTMzYzRrTEZlaHpcLzdCZGpFTzQrMkl1WENxdGJPNWtVNTRUQ3l3cU5Delcrellac2pBbnprajY5bm90bFpibUxKcU82U1pcL3JrUT09IiwibWFjIjoiNGZhNTcyZTNhNThkOGQ4ZWY5NTY4NWRiMzVlNmRjMWU5YzIyZjlkNTJhMjE5ZmZmOTRjNWMyODZjMWY1MzEzMyJ9";
    public function testFailedUserAnalysesAsRegularUser(): void{
        $this->actAsTestUser();
        $response = $this->stagingRequest(200, "Failed Analyses");
        $this->checkTestDuration(5);
        $this->checkQueryCount(6);
    }
    public function testFailedUserAnalysesAsAdmin(): void{
        $this->actAsAdmin();
        $response = $this->stagingRequest(200, "Failed Analyses");
        $this->checkTestDuration(5);
        $this->checkQueryCount(6);
    }
    public function testFailedUserAnalysesWithoutAuth(): void{
		self::setExpectedRequestException(UnauthorizedException::class);
	    $this->stagingRequest(302, "Redirecting");
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
      'id' => '3',
      'name' => 'ProgressBox',
      'params' => 'eyJpdiI6IlM5d3dzNnFPdkp2TUt5eThtVFwvaHV3PT0iLCJ2YWx1ZSI6Ik4rZVZ0SmdnakVldXhmMmR3RTlJbyt1YmJUVm1TT2NOXC9NdXM0Vll1OXdxRmM1ZUxvRjlqQmRYb0NtZWMxeUlmOTI4ZFlqOEw0R3J5bTdvUDd6ZmducHNGQ0ZWK2pzVWdHYjJ2SlwvOXFUaWxyZWRNdDB1b0w3ZStTaVltYk9ob3dLQUJ3N2I1N1pVVCtZRnpaT0hnQ0U3SEZUUGpTVjVmQmdKTm05ejdYcGRoaVR1T0lRQzd5U2VRSXZhZ3c0M1wvXC82OWE3bCtjcjZsQlBpTWN3V3N6dGhnRCtXb3NvZDRBdEtPdG1DTGtSY3FseFdGcVBGZjRpXC8yZGM2eTlGODlOWEZaOHVDMnJUMkVidHhucHpmT0VaTWJsSUZOR0M3dEVqb2FmZEt1NVdVZ1c3YzFieDlneGYwRVpuUUxTb2loOVNzdlwveG5MS3ZmR0tiMWx1dkl0cGFhb3ZUSmZZZ0dRMVhlVHRnNDh5Y3ZTejNQXC9ZVTAyV0JTRE9KaVFkXC80QXNJNnRHbjFXYXNjc1pBNnEzbWtUOHpQWDIwSUE2ZFFtXC9sXC9xTjVPM0pEc1BGd1hINTlMZ255ejdBaklNNGtUeDBHTlM3VEg3SnluUVZ2ZlhcL3o3KzFMU3dwM0JBZUdRWVMxNmN2MUJhSmVUMmdJQndLTk9ScERrVk51OHVJOWNydXAzQXgrUllBVkpYK3NybVEwMFwvVkVUZEJwODVaOVpXelFoK2E3K1I2VE9WaWJKZjBXK25EUXg2SWFyZ2xxd2ZxaHhBekl1aHU2RlwvVjBzU3JJVURsclNcL280ZENweFQ0RHNoZlwvUjFwc3VMUlFcL2tUWTlyTDFZTkJadzFCUktCS3lRdE5YUEJTNUwxZEJYSDVReHp6bWVcLzh0enJCREY4RHlydllUVFJRQWJKSGdkSGV3Q3BUc1RkM2gxQ1wveEptcGlWNWVCdkY5ckZ4dzQwVTMzYzRrTEZlaHpcLzdCZGpFTzQrMkl1WENxdGJPNWtVNTRUQ3l3cU5Delcrellac2pBbnprajY5bm90bFpibUxKcU82U1pcL3JrUT09IiwibWFjIjoiNGZhNTcyZTNhNThkOGQ4ZWY5NTY4NWRiMzVlNmRjMWU5YzIyZjlkNTJhMjE5ZmZmOTRjNWMyODZjMWY1MzEzMyJ9',
    ),
  )),
   'query' =>
  QMParameterBag::__set_state(array(
     'parameters' =>
    array (
      'id' => '3',
      'name' => 'ProgressBox',
      'params' => 'eyJpdiI6IlM5d3dzNnFPdkp2TUt5eThtVFwvaHV3PT0iLCJ2YWx1ZSI6Ik4rZVZ0SmdnakVldXhmMmR3RTlJbyt1YmJUVm1TT2NOXC9NdXM0Vll1OXdxRmM1ZUxvRjlqQmRYb0NtZWMxeUlmOTI4ZFlqOEw0R3J5bTdvUDd6ZmducHNGQ0ZWK2pzVWdHYjJ2SlwvOXFUaWxyZWRNdDB1b0w3ZStTaVltYk9ob3dLQUJ3N2I1N1pVVCtZRnpaT0hnQ0U3SEZUUGpTVjVmQmdKTm05ejdYcGRoaVR1T0lRQzd5U2VRSXZhZ3c0M1wvXC82OWE3bCtjcjZsQlBpTWN3V3N6dGhnRCtXb3NvZDRBdEtPdG1DTGtSY3FseFdGcVBGZjRpXC8yZGM2eTlGODlOWEZaOHVDMnJUMkVidHhucHpmT0VaTWJsSUZOR0M3dEVqb2FmZEt1NVdVZ1c3YzFieDlneGYwRVpuUUxTb2loOVNzdlwveG5MS3ZmR0tiMWx1dkl0cGFhb3ZUSmZZZ0dRMVhlVHRnNDh5Y3ZTejNQXC9ZVTAyV0JTRE9KaVFkXC80QXNJNnRHbjFXYXNjc1pBNnEzbWtUOHpQWDIwSUE2ZFFtXC9sXC9xTjVPM0pEc1BGd1hINTlMZ255ejdBaklNNGtUeDBHTlM3VEg3SnluUVZ2ZlhcL3o3KzFMU3dwM0JBZUdRWVMxNmN2MUJhSmVUMmdJQndLTk9ScERrVk51OHVJOWNydXAzQXgrUllBVkpYK3NybVEwMFwvVkVUZEJwODVaOVpXelFoK2E3K1I2VE9WaWJKZjBXK25EUXg2SWFyZ2xxd2ZxaHhBekl1aHU2RlwvVjBzU3JJVURsclNcL280ZENweFQ0RHNoZlwvUjFwc3VMUlFcL2tUWTlyTDFZTkJadzFCUktCS3lRdE5YUEJTNUwxZEJYSDVReHp6bWVcLzh0enJCREY4RHlydllUVFJRQWJKSGdkSGV3Q3BUc1RkM2gxQ1wveEptcGlWNWVCdkY5ckZ4dzQwVTMzYzRrTEZlaHpcLzdCZGpFTzQrMkl1WENxdGJPNWtVNTRUQ3l3cU5Delcrellac2pBbnprajY5bm90bFpibUxKcU82U1pcL3JrUT09IiwibWFjIjoiNGZhNTcyZTNhNThkOGQ4ZWY5NTY4NWRiMzVlNmRjMWU5YzIyZjlkNTJhMjE5ZmZmOTRjNWMyODZjMWY1MzEzMyJ9',
    ),
  )),
   'server' =>
  QMServerBag::__set_state(array(
     'parameters' =>
    array (
      'USER' => 'vagrant',
      'HOME' => '/home/vagrant',
      'HTTP_COOKIE' => 'u=1f428f225112e63447dddbc2cdb87d70f597b6bf; _ga=GA1.1.1091482458.1590862194; driftt_aid=d814d39b-f800-483f-8417-92891397ffec; __cfduid=dedfea3f97c3efc49cc7ef45d88b1dbde1590866417; _ga=GA1.2.1319490333.1590871543; DFTT_END_USER_PREV_BOOTSTRAPPED=true; __gads=ID=064cb43f27ada9a8:T=1591295660:S=ALNI_MaXoYSavd6pmww_S4OEYN1zNgfqiQ; driftt_eid=230; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1592930788%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6Ik91Y2dPU3FncXQrQ3VzSXFmblZyQnc9PSIsInZhbHVlIjoiQXdiU0Z0V3JxQUM4RENxN2xEUnllRzRndDl2TzJLTVwvSTNkc3Q2bkFGdEFrUTlGY2xZREpvc21iYUdaK1Vub3RHcjErWUx6OG11S0JWZWhmMDU3R3p1Y0trRjltWmxtZVJOTE1Tb1lDMUtKcEs5RmNUVlhZcVh1a2c5S2k0ZVQ5OVR3SUZXS3lNM05PSFlUVGdQM2p5QT09IiwibWFjIjoiYWVhZjE0MDM3ODg0OTRhNjBlNjIxZWM4NDI0MDVjNTYxMDI2ZTczZmY2YmI1MzMzNzYyNzAwMzU1MWIwMjM0YSJ9; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; _gid=GA1.2.1870841610.1591898680; final_callback_url=https%3A%2F%2Flocal.quantimo.do%2Fdatalab%3Fcountry%3D%26client_id%3Dquantimodo%26quantimodoAccessToken%3Dmike-test-token%26quantimodoUserId%3D230; XSRF-TOKEN=eyJpdiI6Ijh2TUtNVEVtXC9RUWpFbHluc2ZZVlwvdz09IiwidmFsdWUiOiJUR3BEVVIxcnlpUjFTOVhCc2ZMN2RlWFViU0kzVVhEZ05rRlRRS3VHN0hkclY0cWVkVHRWdGxFNklyOEQ3bE8zIiwibWFjIjoiMGJlMjk0OTUxMzczZGIyMTc0NzkyMzcxYjQxMDE3NTcwMzRjMWJmYjkzZjhmYjA0NWM4YzZhNWIxMzZiMWEzOCJ9; laravel_session=eyJpdiI6Img0cFUrMHdTMzljQ204aVFRMTA0dEE9PSIsInZhbHVlIjoiUWh5WFU0cW1JNUM1OU1jOVZITHRhWXZZajJkdzhWQzFiWTkwNjhpYzYzWVMyY0JYMG5pbVh6Wmg3RkdtV1BMbSIsIm1hYyI6IjhlNWQ2Y2MzNzRhZjU4N2ZhMGRlYmMyODU3MGYzODE1ODcxM2YyNDA4OTJiYmViMzg3ZGVhMzdiNjQ1ODYxMTcifQ%3D%3D',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_SEC_FETCH_DEST' => 'document',
      'HTTP_SEC_FETCH_MODE' => 'navigate',
      'HTTP_SEC_FETCH_SITE' => 'none',
      'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.97 Safari/537.36',
      'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
      'HTTP_CONNECTION' => 'keep-alive',
      'REDIRECT_STATUS' => '200',
      'HTTPS' => 'on',
      'SERVER_NAME' => 'testing.quantimo.do',
      'SERVER_PORT' => '443',
      'REMOTE_PORT' => '62421',
      'SERVER_SOFTWARE' => 'nginx/1.15.8',
      'GATEWAY_INTERFACE' => 'CGI/1.1',
      'SERVER_PROTOCOL' => 'HTTP/1.1',
      'DOCUMENT_URI' => '/index.php',
      'REQUEST_URI' => $this->REQUEST_URI.'',
      'SCRIPT_NAME' => '/index.php',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => '',
      'REQUEST_METHOD' => 'GET',
      'QUERY_STRING' => 'id=3&name=ProgressBox&params=eyJpdiI6IlM5d3dzNnFPdkp2TUt5eThtVFwvaHV3PT0iLCJ2YWx1ZSI6Ik4rZVZ0SmdnakVldXhmMmR3RTlJbyt1YmJUVm1TT2NOXC9NdXM0Vll1OXdxRmM1ZUxvRjlqQmRYb0NtZWMxeUlmOTI4ZFlqOEw0R3J5bTdvUDd6ZmducHNGQ0ZWK2pzVWdHYjJ2SlwvOXFUaWxyZWRNdDB1b0w3ZStTaVltYk9ob3dLQUJ3N2I1N1pVVCtZRnpaT0hnQ0U3SEZUUGpTVjVmQmdKTm05ejdYcGRoaVR1T0lRQzd5U2VRSXZhZ3c0M1wvXC82OWE3bCtjcjZsQlBpTWN3V3N6dGhnRCtXb3NvZDRBdEtPdG1DTGtSY3FseFdGcVBGZjRpXC8yZGM2eTlGODlOWEZaOHVDMnJUMkVidHhucHpmT0VaTWJsSUZOR0M3dEVqb2FmZEt1NVdVZ1c3YzFieDlneGYwRVpuUUxTb2loOVNzdlwveG5MS3ZmR0tiMWx1dkl0cGFhb3ZUSmZZZ0dRMVhlVHRnNDh5Y3ZTejNQXC9ZVTAyV0JTRE9KaVFkXC80QXNJNnRHbjFXYXNjc1pBNnEzbWtUOHpQWDIwSUE2ZFFtXC9sXC9xTjVPM0pEc1BGd1hINTlMZ255ejdBaklNNGtUeDBHTlM3VEg3SnluUVZ2ZlhcL3o3KzFMU3dwM0JBZUdRWVMxNmN2MUJhSmVUMmdJQndLTk9ScERrVk51OHVJOWNydXAzQXgrUllBVkpYK3NybVEwMFwvVkVUZEJwODVaOVpXelFoK2E3K1I2VE9WaWJKZjBXK25EUXg2SWFyZ2xxd2ZxaHhBekl1aHU2RlwvVjBzU3JJVURsclNcL280ZENweFQ0RHNoZlwvUjFwc3VMUlFcL2tUWTlyTDFZTkJadzFCUktCS3lRdE5YUEJTNUwxZEJYSDVReHp6bWVcLzh0enJCREY4RHlydllUVFJRQWJKSGdkSGV3Q3BUc1RkM2gxQ1wveEptcGlWNWVCdkY5ckZ4dzQwVTMzYzRrTEZlaHpcLzdCZGpFTzQrMkl1WENxdGJPNWtVNTRUQ3l3cU5Delcrellac2pBbnprajY5bm90bFpibUxKcU82U1pcL3JrUT09IiwibWFjIjoiNGZhNTcyZTNhNThkOGQ4ZWY5NTY4NWRiMzVlNmRjMWU5YzIyZjlkNTJhMjE5ZmZmOTRjNWMyODZjMWY1MzEzMyJ9',
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
      'final_callback_url' => getenv('APP_URL').'/datalab?country=&client_id=quantimodo&quantimodoAccessToken=mike-test-token&quantimodoUserId=230',
      'XSRF-TOKEN' => 'eyJpdiI6Ijh2TUtNVEVtXC9RUWpFbHluc2ZZVlwvdz09IiwidmFsdWUiOiJUR3BEVVIxcnlpUjFTOVhCc2ZMN2RlWFViU0kzVVhEZ05rRlRRS3VHN0hkclY0cWVkVHRWdGxFNklyOEQ3bE8zIiwibWFjIjoiMGJlMjk0OTUxMzczZGIyMTc0NzkyMzcxYjQxMDE3NTcwMzRjMWJmYjkzZjhmYjA0NWM4YzZhNWIxMzZiMWEzOCJ9',
      'laravel_session' => 'eyJpdiI6Img0cFUrMHdTMzljQ204aVFRMTA0dEE9PSIsInZhbHVlIjoiUWh5WFU0cW1JNUM1OU1jOVZITHRhWXZZajJkdzhWQzFiWTkwNjhpYzYzWVMyY0JYMG5pbVh6Wmg3RkdtV1BMbSIsIm1hYyI6IjhlNWQ2Y2MzNzRhZjU4N2ZhMGRlYmMyODU3MGYzODE1ODcxM2YyNDA4OTJiYmViMzg3ZGVhMzdiNjQ1ODYxMTcifQ==',
    ),
  )),
   'headers' =>
  QMHeaderBag::__set_state(array(
     'headers' =>
    array (
      'cookie' =>
      array (
        0 => 'u=1f428f225112e63447dddbc2cdb87d70f597b6bf; _ga=GA1.1.1091482458.1590862194; driftt_aid=d814d39b-f800-483f-8417-92891397ffec; __cfduid=dedfea3f97c3efc49cc7ef45d88b1dbde1590866417; _ga=GA1.2.1319490333.1590871543; DFTT_END_USER_PREV_BOOTSTRAPPED=true; __gads=ID=064cb43f27ada9a8:T=1591295660:S=ALNI_MaXoYSavd6pmww_S4OEYN1zNgfqiQ; driftt_eid=230; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1592930788%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6Ik91Y2dPU3FncXQrQ3VzSXFmblZyQnc9PSIsInZhbHVlIjoiQXdiU0Z0V3JxQUM4RENxN2xEUnllRzRndDl2TzJLTVwvSTNkc3Q2bkFGdEFrUTlGY2xZREpvc21iYUdaK1Vub3RHcjErWUx6OG11S0JWZWhmMDU3R3p1Y0trRjltWmxtZVJOTE1Tb1lDMUtKcEs5RmNUVlhZcVh1a2c5S2k0ZVQ5OVR3SUZXS3lNM05PSFlUVGdQM2p5QT09IiwibWFjIjoiYWVhZjE0MDM3ODg0OTRhNjBlNjIxZWM4NDI0MDVjNTYxMDI2ZTczZmY2YmI1MzMzNzYyNzAwMzU1MWIwMjM0YSJ9; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; _gid=GA1.2.1870841610.1591898680; final_callback_url=https%3A%2F%2Flocal.quantimo.do%2Fdatalab%3Fcountry%3D%26client_id%3Dquantimodo%26quantimodoAccessToken%3Dmike-test-token%26quantimodoUserId%3D230; XSRF-TOKEN=eyJpdiI6Ijh2TUtNVEVtXC9RUWpFbHluc2ZZVlwvdz09IiwidmFsdWUiOiJUR3BEVVIxcnlpUjFTOVhCc2ZMN2RlWFViU0kzVVhEZ05rRlRRS3VHN0hkclY0cWVkVHRWdGxFNklyOEQ3bE8zIiwibWFjIjoiMGJlMjk0OTUxMzczZGIyMTc0NzkyMzcxYjQxMDE3NTcwMzRjMWJmYjkzZjhmYjA0NWM4YzZhNWIxMzZiMWEzOCJ9; laravel_session=eyJpdiI6Img0cFUrMHdTMzljQ204aVFRMTA0dEE9PSIsInZhbHVlIjoiUWh5WFU0cW1JNUM1OU1jOVZITHRhWXZZajJkdzhWQzFiWTkwNjhpYzYzWVMyY0JYMG5pbVh6Wmg3RkdtV1BMbSIsIm1hYyI6IjhlNWQ2Y2MzNzRhZjU4N2ZhMGRlYmMyODU3MGYzODE1ODcxM2YyNDA4OTJiYmViMzg3ZGVhMzdiNjQ1ODYxMTcifQ%3D%3D',
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
