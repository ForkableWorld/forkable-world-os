<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection SpellCheckingInspection */
namespace Tests\StagingUnitTests\D\DataLab;
use App\Override\GeneratedTestRequest;
use App\Override\QMHeaderBag;
use App\Override\QMParameterBag;
use App\Override\QMServerBag;
use Illuminate\Testing\TestResponse;
use Tests\LaravelStagingTestCase;

class DataTableUnitCategoriesTest extends LaravelStagingTestCase
{
    protected $REQUEST_URI = "/datalab/unitCategories?_=1639958714901&columns%5B0%5D%5Bdata%5D=open_button&columns%5B0%5D%5Bsearchable%5D=false&columns%5B0%5D%5Borderable%5D=false&columns%5B1%5D%5Bdata%5D=name&columns%5B2%5D%5Bdata%5D=drop_down_button&columns%5B2%5D%5Bsearchable%5D=false&columns%5B2%5D%5Borderable%5D=false&columns%5B3%5D%5Bdata%5D=related_data&columns%5B3%5D%5Bsearchable%5D=false&columns%5B3%5D%5Borderable%5D=false&columns%5B4%5D%5Bdata%5D=action&columns%5B4%5D%5Bsearchable%5D=false&columns%5B4%5D%5Borderable%5D=false&draw=2&length=10&order%5B0%5D%5Bcolumn%5D=1&order%5B0%5D%5Bdir%5D=asc&search%5Bvalue%5D=&start=0";
    public function testDataTableUnitCategoriesAsRegularUser(): void{
        $this->actAsTestUser();
        $this->stagingRequest(200, "");
        $response = $this->getTestResponse();
		$response->assertSee("unitCategories");
        $this->checkTestDuration(5);
        $this->checkQueryCount(9);
        $this->assertCount(10, $response->json()['data']);
        $this->assertDataTableQueriesEqual([]);
    }
    public function testDataTableUnitCategoriesWithoutAuth(): void{
        $this->assertGuestRedirectToLogin();
    }
    /**
     * @param string|null $expectedString
     * @param int|null $expectedCode
     * @return TestResponse
     */
    protected function stagingRequest(int $expectedCode = 200, string $expectedString = null): TestResponse {
		$this->serializedRequest = GeneratedTestRequest::__set_state(array(
   'request' => 
  QMParameterBag::__set_state(array(
     'parameters' => 
    array (
      '_' => '1639958714901',
      'columns' => 
      array (
        0 => 
        array (
          'data' => 'open_button',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        1 => 
        array (
          'data' => 'name',
        ),
        2 => 
        array (
          'data' => 'drop_down_button',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        3 => 
        array (
          'data' => 'related_data',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        4 => 
        array (
          'data' => 'action',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
      ),
      'draw' => '2',
      'length' => '10',
      'order' => 
      array (
        0 => 
        array (
          'column' => '1',
          'dir' => 'asc',
        ),
      ),
      'search' => 
      array (
        'value' => '',
      ),
      'start' => '0',
    ),
  )),
   'query' => 
  QMParameterBag::__set_state(array(
     'parameters' => 
    array (
      '_' => '1639958714901',
      'columns' => 
      array (
        0 => 
        array (
          'data' => 'open_button',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        1 => 
        array (
          'data' => 'name',
        ),
        2 => 
        array (
          'data' => 'drop_down_button',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        3 => 
        array (
          'data' => 'related_data',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        4 => 
        array (
          'data' => 'action',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
      ),
      'draw' => '2',
      'length' => '10',
      'order' => 
      array (
        0 => 
        array (
          'column' => '1',
          'dir' => 'asc',
        ),
      ),
      'search' => 
      array (
        'value' => '',
      ),
      'start' => '0',
    ),
  )),
   'server' => 
  QMServerBag::__set_state(array(
     'parameters' => 
    array (
      'USER' => 'vagrant',
      'HOME' => '/home/vagrant',
      'HTTP_COOKIE' => 'XDEBUG_PROFILE=; _ga=GA1.1.1030053326.1639862310; _gid=GA1.1.1241745838.1639862310; driftt_aid=387439d7-b8f9-4f47-a0fa-002d41e908c0; drift_aid=387439d7-b8f9-4f47-a0fa-002d41e908c0; drift_eid=230; XDEBUG_SESSION=PHPSTORM; QuAu7hHoJzjgSyqBBQTXRecnXpL6FjM2c0R9EGVj=eyJpdiI6IjJRUjU1ZExZdTNyUDczeEs0R1FGZVE9PSIsInZhbHVlIjoiSHVSRWt5QWJTSlVnVENqWFJSSG1TYU4zeEs3TEVnYmpZM01LWEtyYTl1NGlTdG1nRkNpY0k0b1UzOUN3aHExaDB1eEpkYmdGUlQ4UWtaRTBTN2ZoUzU2WUZqRTVrZ3puaWxZZ2ZHZzY1OHF2ZXpkXC91S202YW5KdHFJMWppTnZTZ2VOaHY0M1JqWXZBSHkrT0pVakhcLzVGK015azFPUU9Zb1wvbHJMWTQyUDlqK3ZRSUhkeXNcLzNlbTlYRlIrMjEwaEpQZ1BwVXIrWm40YUFiWk5MY29pSUNmSUdsR0plYXJ2N2pVUW1yUXJ4d2x0ZGV5aFE0NUxpYlB5VFhGZndKRlF5dFQwRllxUU9EOFNMM2VWS2R1MSt2U3g5bjFpWldRRVBqbHl5bEdoVG1rSkdqQ2lpYVZuTlBKUHlENGJsRlZtU3JmcndHeHVpMTBTd2RlZ0h2aVpTeExzT1VhYnAzVnNxS3I5YVduMDdWNUV5VlFTZEd5SVorNjNHWUhYOFZxZUFHaE1Pb1RUY2JBUFdSdVZYWWRWRWpxc2pYMUc1S1VkTjlpczdqUE15bjdDSVpmR2Q4ZmJEY3lMUkRhVWxsSFNSUlpicHVMaEhDck42UThOc1N2TUM5RldYRW90dXVHSitOXC9Bck5MUkZ2VlwvY0g5dnNmeStOVFlUS2dyQVZtNFJvRTY1SE5kWGh1RjBkRjZnMm1CeTBkUHJNaGVEMFNVdjJ2MENRTnRZS0RiZEN4UGJYaG5FXC9hb0IrWEJ6ZmtROTBHc25uVWdGa0ZYY1JTK1pMbE5ZaWpkQ0NkOXdZVytiWklVOStOVldnUW1FSmJYS2FJRDVnaG4xbnpOeUZyRkp3TXBuT0tSMjZ5XC9PbnM3QkNrZmxcL0V4b0UyeTIrZUFRQXdEZ0Y5S0RqTGhXVDRGVkNRMnhXbTRnVHFQUHhlU0h6OE1OXC9SZ2pVYUVxanQzZjN6VGRPOTRMdTAzNURHWDBkVU1RSG9PWTlmWEJmV2RXOE5zTmN3cFVrSE9PS1NGUVRXMENXc3poc1BwNFZiSXdPY2ptTGphaHc2dmVVQTVOVnlQaWVidDJGektWdDlIVWRJaXVQZDBqdHlHS2xTZm90TXpSUDVGQzVuN29DQ3pBOVFKZWhNVWp5S1I2dDRcL3ZvdUZtRlpUZ1Y1SEoyOU9sUk1KUVFuUWMreUZ4MzdWNzljSVc3Y2U4VFJBVHc2M25jeVlJTWlzNWVQdXZ0OEJkNVNOWWZ1WWV4UVVqUHllWXk4Z0Nma1lMQmdJazRLOGtlZFhSanQ1OXMyTkpGQm1aM0lVMzNDSHRpelJsb1Q4TENlZk9pbW1FcGNpcXVHdlpDZDNlZFJJV1R0eUlaMlwvRmJkZDJydU9zVkEzSFU0a1pDU3M0bndHaDFVRXdnejYremZ2SXNrdUtPMUtRTnAzQzJQWHFQMGU3b1BiOThzcVAzY2R1R0xEbjNqZGt4S3hMcXZiN09nRkxNMWZEeUV6WHVMeDBQalhZeTBvVitMWGNQU2tBcDZPM0dBWTh3cDd0K3h1M282YTFZRmVXUHR2MUtmd2FcL2kzN2F5YVAxMjRGUXBxOE1QVFpcL2pcL2RycUVsVU1oVTZEYjlMRlBXc1NXZDBFVVc5OTZwbHJqYTZDWWpHNlNTY3JuVmJ2d3MzYWw3Y09NQkxQQlBqWGsyUGJwN1BIUno1V1wvcEM2RGhiTHNUd2tUekpUTVRnZERTT01wbVJ0M2p6cFk2NzZEODhVaUl0cVJ6K3NFUjR0eWk4QkR1TlY2Y0VFVjdOSElwRlRQWmM1eGFYRlBCd0cwUmZiRFUxdEc1ZkxhU2hLSWhsaXB6enJDYkk2Qk92SFFRMVh6TEF2RzlFWVJHb1A0NXhVV0xadFg1bzEwTWJKOG1CSzRoSnJCa2kzc0tjNjM0bDd0UVM2T0hzVGJ3VkNQaFJtZ0ZQTXhcL0NycElCMFI0VU5LWG16b1d6TXNKTWM3cmpiYUJERzNNdUZGaHBXS3FQbWVvME80a3lmc1N6TmNBYk5HVlFYOXF0ZlwveVNPYkFnVmhUMUVPcXFET2ZZVXRIaGJaYm1xMHFiaUdKSWN0NDJIdGRraW82UE1cL1dRbkN4MzN6TmEyTFdnTWhsUjNjenBnTzIiLCJtYWMiOiJiMzhhZTMzMjJjOGY0OGU0MTNlZmZjYTU5MWJiOTFhMzQxOWY0MjY0ZDhhNmEyOWZmMzY4YTExZGNlMWZjMDg1In0%3D; drift_campaign_refresh=4ceb8b09-6bb5-4e2d-8728-5016144b4fc8; intended_url=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Fv4%2Fstatic%3Fbucket%3Dqm-private%26path%3Dtesting%252Fusers%252F256%252Froot-cause-analysis%252Foverall-mood-for-zain-root-cause-analysis.pdf%26state%3DeyJ1c2VyX2lkIjo5MjcxNywiY2xpZW50X2lkIjoicXVhbnRpbW9kbyIsImludGVuZGVkX3VybCI6Imh0dHBzOlwvXC9sb2NhbC5xdWFudGltby5kb1wvYXBpXC92NFwvc3RhdGljP2J1Y2tldD1xbS1wcml2YXRlJnBhdGg9dGVzdGluZyUyRnVzZXJzJTJGMjU2JTJGcm9vdC1jYXVzZS1hbmFseXNpcyUyRm92ZXJhbGwtbW9vZC1mb3ItemFpbi1yb290LWNhdXNlLWFuYWx5c2lzLnBkZiJ9%26code%3D4%252F0AX4XfWgbKXOYMg-1fDh-orU3YgOaMlBzMpbgvQbWpTEJw7HxrQMg5V-Y_856UBIqN_8nzw%26scope%3Demail%2Bprofile%2Bopenid%2Bhttps%253A%252F%252Fwww.googleapis.com%252Fauth%252Fuserinfo.profile%2Bhttps%253A%252F%252Fwww.googleapis.com%252Fauth%252Fuserinfo.email%26authuser%3D0%26hd%3Dthinkbynumbers.org%26prompt%3Dconsent%26sessionToken%3De2df703997d1240d7aca8859b49c3481f66e08be%26quantimodoUserId%3D92717%26quantimodoClientId%3Dquantimodo%26accessToken%3De2df703997d1240d7aca8859b49c3481f66e08be; final_callback_url=eyJpdiI6IldjWTNMbElRaVNEU2lleG9QS05Ycmc9PSIsInZhbHVlIjoiQ2lFTVdiZUhFYUxNUFdQMzZqcWRqcmdPejNUWWkwaWxuZTVDNzRBcCtXc3UrSGY0RXJcL2JUc1EwN29TdDd2dWpwbGNvSDZSTERsQ0tBeTlEYytPenZUTncxSnlncXkydGxBRDJHV2Ixc0kxTDF2bHdrV2xzYjBHQ1ZXaVpTUE9MVTFKSm9sRlJiOUVYK3BBRWFvdjZnT2JRcUszOGlFalI5aVgyS0hSaFwvcXhlcFVkN0dhVlVcL2RqMCsxcXJ6S2VZMGJpMUp1clJ1dGRjNmY2bk9walZQTGRtd0JNK0tnME1ZWVZrTFV4dWhPb0RPZGxhYmVsVXJIUVwvaXZwOTFXdWRSelh3c0RRa3hOU2pTcCt3MW9aK3VcL2tHaTJuXC9qMlhlNkxzSFlMM2Z2YzE5MTRnRlBzR0RWRnRxMEpxVUNxbkxzNnZ0d1ZBaFJNM1Z6Z0xqZjdkS2Rwak9EMW5GYWw0RU52MTVVSjJIT3FETjB1VlFscjZjb001WENFZlE4UWhweERoa0NFdnhFV0w2NVVYK2Z3eG8yblwvQWx4TlI2WXdURWpFQ1pXVmNJd0IydHFYcm03NGxxZHZTSE91TEN4Sm91SjBcL2hBQXhGSDNkMlJPajJnaWZYWlA3YlJCanFncTh0cHB5SFh6Tm9BSlU0RE9Ma1hCcUJZK1dKZmNUSXBnSHU0MWVuSGYwZ3ZQdGtsXC9BcTRnUVRCN0ZDME85QUlKZW01UDFYNGdkNWR0MWY3S3dpNE9LdHZjTkcyOHNPSE9MSFlCcXl3OERjSjlwVTU0STU5UmtYem1LTmwyUWRSSFFuaGhiVmxjbkpaejNMV3M3OWpSdDZXN3FcL1hVUUdlZUx6SXpJaGtQUGNYUmJVWUJoejZZSE1vUlVOdE9VZnNQUERFT0ZaZ21ldE5qTUpSSUh4MUg2R2ZkYTk5ekJBMnBHXC92NEM4cXJSVFwvUVNBbVpuTmJ0R1ZSdnY2eHBBMVlNakhScTZSQ2F0QVNYTU5OdkcwWUUzdFhNQ1wvQUxjMURyRnVneFZLMDVoK1RrTklXRk5DM1wvdGwrS2N1Y1pEdzU4czBCeWNKXC9GYTFYOTlPdWZhTGpETWFoTjRCNHNFd0tmalpWSzAzMUZyTDhEaFF2a0dBSkFsaW9ydFlvMDkwU25jN0pHWE5YaTR2WkFRWlBUTmZWK0QyekFKY1pMY2hZN09kUnFHNGdteEdSb2xUZmdVVlo0M2FKMlwvSzR2cWU2cjZMbzhHNkdSdllWYlBSUWJJdVBQZkY3Vjh4RGpMVXBvR05ERGtndFBDVFM1cE5pdFhtTFBiSnlYTWtkcHM2amY1a0pMaDVBODNSNjR5cUdoVkExRGFWVUxzYVJWbDU5b0JkM216MHpieDBwU3h6azBVWWtWWmdWNFpzZzBVQldGejJ5V2N4SHpvTE82aEJWQTBJaUMwT3ZPdVdxV0s5aDk1UUFFTDJ5U3JFSURNcW5kblhWTVlpeG9EQ0xkZU13cU9rcWlWZWxqOGY5NmhTSW5aWHQ2UlN6TkNmQ3VuXC8ycktmMmJoOSt5TVNkTnlDMHEybVwvUUtwR2xkMHBib04xXC9ZQ1JXWEpINUd5a3c9IiwibWFjIjoiNTU3ZmZmMGNkZWZhMGMxZTA4ZDVhN2IwMWY3NTk0OTVmZTA3NWQ5ZTNiYmZjNWM5Y2M5YzBhOTk3MTdlN2VlMSJ9; XSRF-TOKEN=eyJpdiI6IkoxSFFqdlhZNkU1aGkrOUZ4WEhjU3c9PSIsInZhbHVlIjoiTjZwN1RVdUtoYmw5cUIxWmd1NU5TbzNFZHRrVVcxRTR4VDZSY3lRSk5kbGdDOUhHN3cwaEQ0SmZaQStsXC9SN1UiLCJtYWMiOiI1MTI0ODk0NzJkNDRmZWEzMmNlMTY4Y2E3ZWYyYjJlN2FhMDRmYzNhYjRkYmMyYWE1OTc3M2QxNGY4ZDI5OGZkIn0%3D; laravel_session=eyJpdiI6IlwvSUpqczlKWlF0aWdwVzNaR0hsc0t3PT0iLCJ2YWx1ZSI6IjkrMGxqNkRnNnNzWW9URUlZUHRoXC9MbVJDVWxEN1QzdVwvVnlZWXVaeHhjaGhvNm1lRHhIQklac2FKV3puOHRPTiIsIm1hYyI6IjJkMTI1MzVkZWUxY2IzZDgyZDFiMTAyMTViMzRjOTM0OTgzMTQ3MzBkZWM5NDQwNzIzZGUxZWQ2MDk2ZWIyMmUifQ%3D%3D; login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6IitkNW4yZGxyQTFFa2xldFwvZElFNU5RPT0iLCJ2YWx1ZSI6IkF4QVMwRXZWWDhZN3RWRnVOck5Ccmc9PSIsIm1hYyI6IjNiOWRlMmYwNGMyZmNjMzIxZDBjNjI4YjM2OTEzN2E2ZjE2OWEyNzAwNzgwNWE2MTZjODE4NDZkYzhmNmNjYTYifQ%3D%3D; remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6Ikl0Y0lwRzlzXC9IQ0hJVW0xVUtvYUhRPT0iLCJ2YWx1ZSI6IlN1N0c0NTI1aDlkcDBYNWV4MnJDS2VcL2NWR2lZb2Q2MUJQbTU1ZSthcGlWMUFma1lheXRHeWxYdjdjdzVWczlZcmREdVNnZ3ZKQU1xbmhYRVRtUUlkSmhBZE1nWFJPZFZpQUtZdVc1czJVQndqRDQwajAwcW9PcVY2V1wvS2lYSzNmcDg2SktcL2NDRkNFbzBzMUgzTzNlQnFocjRGdllnek5IYytCdDFTU1hvQT0iLCJtYWMiOiI4NDQxYmVhZDg1MDRmMGYzNzliMDNhZWQzODAyMzg2MmQzMTgwMGNjOTFmMTM1MzJiZjY5NGM2MzMyZmNiYmRhIn0%3D; clockwork-profile=null',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_SEC_FETCH_DEST' => 'document',
      'HTTP_SEC_FETCH_USER' => '?1',
      'HTTP_SEC_FETCH_MODE' => 'navigate',
      'HTTP_SEC_FETCH_SITE' => 'cross-site',
      'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36 Edg/96.0.1054.57',
      'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
      'HTTP_SEC_CH_UA_PLATFORM' => '"Windows"',
      'HTTP_SEC_CH_UA_MOBILE' => '?0',
      'HTTP_SEC_CH_UA' => '" Not A;Brand";v="99", "Chromium";v="96", "Microsoft Edge";v="96"',
      'HTTP_CACHE_CONTROL' => 'max-age=0',
      'HTTP_HOST' => 'local.quantimo.do',
      'REDIRECT_STATUS' => '200',
      'SERVER_NAME' => 'local.quantimo.do',
      'SERVER_PORT' => '443',
      'SERVER_ADDR' => '127.0.0.1',
      'REMOTE_PORT' => '57156',
      'REMOTE_ADDR' => '127.0.0.1',
      'SERVER_SOFTWARE' => 'nginx/1.19.7',
      'GATEWAY_INTERFACE' => 'CGI/1.1',
      'HTTPS' => 'on',
      'REQUEST_SCHEME' => 'https',
      'SERVER_PROTOCOL' => 'HTTP/2.0',
      'DOCUMENT_ROOT' => '/www/wwwroot/qm-api/public',
      'DOCUMENT_URI' => '/index.php',
      'REQUEST_URI' => $this->REQUEST_URI.'',
      'SCRIPT_NAME' => '/index.php',
      'REQUEST_METHOD' => 'GET',
      'QUERY_STRING' => '_=1639958714901&columns%5B0%5D%5Bdata%5D=open_button&columns%5B0%5D%5Bsearchable%5D=false&columns%5B0%5D%5Borderable%5D=false&columns%5B1%5D%5Bdata%5D=name&columns%5B2%5D%5Bdata%5D=drop_down_button&columns%5B2%5D%5Bsearchable%5D=false&columns%5B2%5D%5Borderable%5D=false&columns%5B3%5D%5Bdata%5D=related_data&columns%5B3%5D%5Bsearchable%5D=false&columns%5B3%5D%5Borderable%5D=false&columns%5B4%5D%5Bdata%5D=action&columns%5B4%5D%5Bsearchable%5D=false&columns%5B4%5D%5Borderable%5D=false&draw=2&length=10&order%5B0%5D%5Bcolumn%5D=1&order%5B0%5D%5Bdir%5D=asc&search%5Bvalue%5D=&start=0',
      'SCRIPT_FILENAME' => '/www/wwwroot/qm-api/public/index.php',
      'FCGI_ROLE' => 'RESPONDER',
      'PHP_SELF' => '/index.php',
      'REQUEST_TIME_FLOAT' => 1639959589.450791,
      'REQUEST_TIME' => 1639959589,
      'CLOCKWORK_ENABLE' => 'true',
      'CLOCKWORK_TESTS_COLLECT' => 'true',
      'CONNECTOR_RESCUETIME_REDIRECT_URI' => 'https://staging.quantimo.do/api/v1/connectors/rescuetime/connect',
      'QM_CONNECTOR_HOST' => 'local.quantimo.do',
    ),
  )),
   'cookies' => 
  QMParameterBag::__set_state(array(
     'parameters' => 
    array (
      'XDEBUG_PROFILE' => '',
      '_ga' => 'GA1.1.1030053326.1639862310',
      '_gid' => 'GA1.1.1241745838.1639862310',
      'driftt_aid' => '387439d7-b8f9-4f47-a0fa-002d41e908c0',
      'drift_aid' => '387439d7-b8f9-4f47-a0fa-002d41e908c0',
      'drift_eid' => '230',
      'XDEBUG_SESSION' => 'PHPSTORM',
      'QuAu7hHoJzjgSyqBBQTXRecnXpL6FjM2c0R9EGVj' => 'eyJpdiI6IjJRUjU1ZExZdTNyUDczeEs0R1FGZVE9PSIsInZhbHVlIjoiSHVSRWt5QWJTSlVnVENqWFJSSG1TYU4zeEs3TEVnYmpZM01LWEtyYTl1NGlTdG1nRkNpY0k0b1UzOUN3aHExaDB1eEpkYmdGUlQ4UWtaRTBTN2ZoUzU2WUZqRTVrZ3puaWxZZ2ZHZzY1OHF2ZXpkXC91S202YW5KdHFJMWppTnZTZ2VOaHY0M1JqWXZBSHkrT0pVakhcLzVGK015azFPUU9Zb1wvbHJMWTQyUDlqK3ZRSUhkeXNcLzNlbTlYRlIrMjEwaEpQZ1BwVXIrWm40YUFiWk5MY29pSUNmSUdsR0plYXJ2N2pVUW1yUXJ4d2x0ZGV5aFE0NUxpYlB5VFhGZndKRlF5dFQwRllxUU9EOFNMM2VWS2R1MSt2U3g5bjFpWldRRVBqbHl5bEdoVG1rSkdqQ2lpYVZuTlBKUHlENGJsRlZtU3JmcndHeHVpMTBTd2RlZ0h2aVpTeExzT1VhYnAzVnNxS3I5YVduMDdWNUV5VlFTZEd5SVorNjNHWUhYOFZxZUFHaE1Pb1RUY2JBUFdSdVZYWWRWRWpxc2pYMUc1S1VkTjlpczdqUE15bjdDSVpmR2Q4ZmJEY3lMUkRhVWxsSFNSUlpicHVMaEhDck42UThOc1N2TUM5RldYRW90dXVHSitOXC9Bck5MUkZ2VlwvY0g5dnNmeStOVFlUS2dyQVZtNFJvRTY1SE5kWGh1RjBkRjZnMm1CeTBkUHJNaGVEMFNVdjJ2MENRTnRZS0RiZEN4UGJYaG5FXC9hb0IrWEJ6ZmtROTBHc25uVWdGa0ZYY1JTK1pMbE5ZaWpkQ0NkOXdZVytiWklVOStOVldnUW1FSmJYS2FJRDVnaG4xbnpOeUZyRkp3TXBuT0tSMjZ5XC9PbnM3QkNrZmxcL0V4b0UyeTIrZUFRQXdEZ0Y5S0RqTGhXVDRGVkNRMnhXbTRnVHFQUHhlU0h6OE1OXC9SZ2pVYUVxanQzZjN6VGRPOTRMdTAzNURHWDBkVU1RSG9PWTlmWEJmV2RXOE5zTmN3cFVrSE9PS1NGUVRXMENXc3poc1BwNFZiSXdPY2ptTGphaHc2dmVVQTVOVnlQaWVidDJGektWdDlIVWRJaXVQZDBqdHlHS2xTZm90TXpSUDVGQzVuN29DQ3pBOVFKZWhNVWp5S1I2dDRcL3ZvdUZtRlpUZ1Y1SEoyOU9sUk1KUVFuUWMreUZ4MzdWNzljSVc3Y2U4VFJBVHc2M25jeVlJTWlzNWVQdXZ0OEJkNVNOWWZ1WWV4UVVqUHllWXk4Z0Nma1lMQmdJazRLOGtlZFhSanQ1OXMyTkpGQm1aM0lVMzNDSHRpelJsb1Q4TENlZk9pbW1FcGNpcXVHdlpDZDNlZFJJV1R0eUlaMlwvRmJkZDJydU9zVkEzSFU0a1pDU3M0bndHaDFVRXdnejYremZ2SXNrdUtPMUtRTnAzQzJQWHFQMGU3b1BiOThzcVAzY2R1R0xEbjNqZGt4S3hMcXZiN09nRkxNMWZEeUV6WHVMeDBQalhZeTBvVitMWGNQU2tBcDZPM0dBWTh3cDd0K3h1M282YTFZRmVXUHR2MUtmd2FcL2kzN2F5YVAxMjRGUXBxOE1QVFpcL2pcL2RycUVsVU1oVTZEYjlMRlBXc1NXZDBFVVc5OTZwbHJqYTZDWWpHNlNTY3JuVmJ2d3MzYWw3Y09NQkxQQlBqWGsyUGJwN1BIUno1V1wvcEM2RGhiTHNUd2tUekpUTVRnZERTT01wbVJ0M2p6cFk2NzZEODhVaUl0cVJ6K3NFUjR0eWk4QkR1TlY2Y0VFVjdOSElwRlRQWmM1eGFYRlBCd0cwUmZiRFUxdEc1ZkxhU2hLSWhsaXB6enJDYkk2Qk92SFFRMVh6TEF2RzlFWVJHb1A0NXhVV0xadFg1bzEwTWJKOG1CSzRoSnJCa2kzc0tjNjM0bDd0UVM2T0hzVGJ3VkNQaFJtZ0ZQTXhcL0NycElCMFI0VU5LWG16b1d6TXNKTWM3cmpiYUJERzNNdUZGaHBXS3FQbWVvME80a3lmc1N6TmNBYk5HVlFYOXF0ZlwveVNPYkFnVmhUMUVPcXFET2ZZVXRIaGJaYm1xMHFiaUdKSWN0NDJIdGRraW82UE1cL1dRbkN4MzN6TmEyTFdnTWhsUjNjenBnTzIiLCJtYWMiOiJiMzhhZTMzMjJjOGY0OGU0MTNlZmZjYTU5MWJiOTFhMzQxOWY0MjY0ZDhhNmEyOWZmMzY4YTExZGNlMWZjMDg1In0=',
      'drift_campaign_refresh' => '4ceb8b09-6bb5-4e2d-8728-5016144b4fc8',
      'intended_url' => getenv('APP_URL').'/api/v4/static?bucket=qm-private&path=testing%2Fusers%2F256%2Froot-cause-analysis%2Foverall-mood-for-zain-root-cause-analysis.pdf&state=eyJ1c2VyX2lkIjo5MjcxNywiY2xpZW50X2lkIjoicXVhbnRpbW9kbyIsImludGVuZGVkX3VybCI6Imh0dHBzOlwvXC9sb2NhbC5xdWFudGltby5kb1wvYXBpXC92NFwvc3RhdGljP2J1Y2tldD1xbS1wcml2YXRlJnBhdGg9dGVzdGluZyUyRnVzZXJzJTJGMjU2JTJGcm9vdC1jYXVzZS1hbmFseXNpcyUyRm92ZXJhbGwtbW9vZC1mb3ItemFpbi1yb290LWNhdXNlLWFuYWx5c2lzLnBkZiJ9&code=4%2F0AX4XfWgbKXOYMg-1fDh-orU3YgOaMlBzMpbgvQbWpTEJw7HxrQMg5V-Y_856UBIqN_8nzw&scope=email+profile+openid+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.profile+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.email&authuser=0&hd=thinkbynumbers.org&prompt=consent&sessionToken=e2df703997d1240d7aca8859b49c3481f66e08be&quantimodoUserId=92717&quantimodoClientId=quantimodo&accessToken=e2df703997d1240d7aca8859b49c3481f66e08be',
      'final_callback_url' => 'eyJpdiI6IldjWTNMbElRaVNEU2lleG9QS05Ycmc9PSIsInZhbHVlIjoiQ2lFTVdiZUhFYUxNUFdQMzZqcWRqcmdPejNUWWkwaWxuZTVDNzRBcCtXc3UrSGY0RXJcL2JUc1EwN29TdDd2dWpwbGNvSDZSTERsQ0tBeTlEYytPenZUTncxSnlncXkydGxBRDJHV2Ixc0kxTDF2bHdrV2xzYjBHQ1ZXaVpTUE9MVTFKSm9sRlJiOUVYK3BBRWFvdjZnT2JRcUszOGlFalI5aVgyS0hSaFwvcXhlcFVkN0dhVlVcL2RqMCsxcXJ6S2VZMGJpMUp1clJ1dGRjNmY2bk9walZQTGRtd0JNK0tnME1ZWVZrTFV4dWhPb0RPZGxhYmVsVXJIUVwvaXZwOTFXdWRSelh3c0RRa3hOU2pTcCt3MW9aK3VcL2tHaTJuXC9qMlhlNkxzSFlMM2Z2YzE5MTRnRlBzR0RWRnRxMEpxVUNxbkxzNnZ0d1ZBaFJNM1Z6Z0xqZjdkS2Rwak9EMW5GYWw0RU52MTVVSjJIT3FETjB1VlFscjZjb001WENFZlE4UWhweERoa0NFdnhFV0w2NVVYK2Z3eG8yblwvQWx4TlI2WXdURWpFQ1pXVmNJd0IydHFYcm03NGxxZHZTSE91TEN4Sm91SjBcL2hBQXhGSDNkMlJPajJnaWZYWlA3YlJCanFncTh0cHB5SFh6Tm9BSlU0RE9Ma1hCcUJZK1dKZmNUSXBnSHU0MWVuSGYwZ3ZQdGtsXC9BcTRnUVRCN0ZDME85QUlKZW01UDFYNGdkNWR0MWY3S3dpNE9LdHZjTkcyOHNPSE9MSFlCcXl3OERjSjlwVTU0STU5UmtYem1LTmwyUWRSSFFuaGhiVmxjbkpaejNMV3M3OWpSdDZXN3FcL1hVUUdlZUx6SXpJaGtQUGNYUmJVWUJoejZZSE1vUlVOdE9VZnNQUERFT0ZaZ21ldE5qTUpSSUh4MUg2R2ZkYTk5ekJBMnBHXC92NEM4cXJSVFwvUVNBbVpuTmJ0R1ZSdnY2eHBBMVlNakhScTZSQ2F0QVNYTU5OdkcwWUUzdFhNQ1wvQUxjMURyRnVneFZLMDVoK1RrTklXRk5DM1wvdGwrS2N1Y1pEdzU4czBCeWNKXC9GYTFYOTlPdWZhTGpETWFoTjRCNHNFd0tmalpWSzAzMUZyTDhEaFF2a0dBSkFsaW9ydFlvMDkwU25jN0pHWE5YaTR2WkFRWlBUTmZWK0QyekFKY1pMY2hZN09kUnFHNGdteEdSb2xUZmdVVlo0M2FKMlwvSzR2cWU2cjZMbzhHNkdSdllWYlBSUWJJdVBQZkY3Vjh4RGpMVXBvR05ERGtndFBDVFM1cE5pdFhtTFBiSnlYTWtkcHM2amY1a0pMaDVBODNSNjR5cUdoVkExRGFWVUxzYVJWbDU5b0JkM216MHpieDBwU3h6azBVWWtWWmdWNFpzZzBVQldGejJ5V2N4SHpvTE82aEJWQTBJaUMwT3ZPdVdxV0s5aDk1UUFFTDJ5U3JFSURNcW5kblhWTVlpeG9EQ0xkZU13cU9rcWlWZWxqOGY5NmhTSW5aWHQ2UlN6TkNmQ3VuXC8ycktmMmJoOSt5TVNkTnlDMHEybVwvUUtwR2xkMHBib04xXC9ZQ1JXWEpINUd5a3c9IiwibWFjIjoiNTU3ZmZmMGNkZWZhMGMxZTA4ZDVhN2IwMWY3NTk0OTVmZTA3NWQ5ZTNiYmZjNWM5Y2M5YzBhOTk3MTdlN2VlMSJ9',
      'XSRF-TOKEN' => 'eyJpdiI6IkoxSFFqdlhZNkU1aGkrOUZ4WEhjU3c9PSIsInZhbHVlIjoiTjZwN1RVdUtoYmw5cUIxWmd1NU5TbzNFZHRrVVcxRTR4VDZSY3lRSk5kbGdDOUhHN3cwaEQ0SmZaQStsXC9SN1UiLCJtYWMiOiI1MTI0ODk0NzJkNDRmZWEzMmNlMTY4Y2E3ZWYyYjJlN2FhMDRmYzNhYjRkYmMyYWE1OTc3M2QxNGY4ZDI5OGZkIn0=',
      'laravel_session' => 'eyJpdiI6IlwvSUpqczlKWlF0aWdwVzNaR0hsc0t3PT0iLCJ2YWx1ZSI6IjkrMGxqNkRnNnNzWW9URUlZUHRoXC9MbVJDVWxEN1QzdVwvVnlZWXVaeHhjaGhvNm1lRHhIQklac2FKV3puOHRPTiIsIm1hYyI6IjJkMTI1MzVkZWUxY2IzZDgyZDFiMTAyMTViMzRjOTM0OTgzMTQ3MzBkZWM5NDQwNzIzZGUxZWQ2MDk2ZWIyMmUifQ==',
      'login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d' => 'eyJpdiI6IitkNW4yZGxyQTFFa2xldFwvZElFNU5RPT0iLCJ2YWx1ZSI6IkF4QVMwRXZWWDhZN3RWRnVOck5Ccmc9PSIsIm1hYyI6IjNiOWRlMmYwNGMyZmNjMzIxZDBjNjI4YjM2OTEzN2E2ZjE2OWEyNzAwNzgwNWE2MTZjODE4NDZkYzhmNmNjYTYifQ==',
      'remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d' => 'eyJpdiI6Ikl0Y0lwRzlzXC9IQ0hJVW0xVUtvYUhRPT0iLCJ2YWx1ZSI6IlN1N0c0NTI1aDlkcDBYNWV4MnJDS2VcL2NWR2lZb2Q2MUJQbTU1ZSthcGlWMUFma1lheXRHeWxYdjdjdzVWczlZcmREdVNnZ3ZKQU1xbmhYRVRtUUlkSmhBZE1nWFJPZFZpQUtZdVc1czJVQndqRDQwajAwcW9PcVY2V1wvS2lYSzNmcDg2SktcL2NDRkNFbzBzMUgzTzNlQnFocjRGdllnek5IYytCdDFTU1hvQT0iLCJtYWMiOiI4NDQxYmVhZDg1MDRmMGYzNzliMDNhZWQzODAyMzg2MmQzMTgwMGNjOTFmMTM1MzJiZjY5NGM2MzMyZmNiYmRhIn0=',
      'clockwork-profile' => 'null',
    ),
  )),
   'headers' => 
  QMHeaderBag::__set_state(array(
     'headers' => 
    array (
      'cookie' => 
      array (
        0 => 'XDEBUG_PROFILE=; _ga=GA1.1.1030053326.1639862310; _gid=GA1.1.1241745838.1639862310; driftt_aid=387439d7-b8f9-4f47-a0fa-002d41e908c0; drift_aid=387439d7-b8f9-4f47-a0fa-002d41e908c0; drift_eid=230; XDEBUG_SESSION=PHPSTORM; QuAu7hHoJzjgSyqBBQTXRecnXpL6FjM2c0R9EGVj=eyJpdiI6IjJRUjU1ZExZdTNyUDczeEs0R1FGZVE9PSIsInZhbHVlIjoiSHVSRWt5QWJTSlVnVENqWFJSSG1TYU4zeEs3TEVnYmpZM01LWEtyYTl1NGlTdG1nRkNpY0k0b1UzOUN3aHExaDB1eEpkYmdGUlQ4UWtaRTBTN2ZoUzU2WUZqRTVrZ3puaWxZZ2ZHZzY1OHF2ZXpkXC91S202YW5KdHFJMWppTnZTZ2VOaHY0M1JqWXZBSHkrT0pVakhcLzVGK015azFPUU9Zb1wvbHJMWTQyUDlqK3ZRSUhkeXNcLzNlbTlYRlIrMjEwaEpQZ1BwVXIrWm40YUFiWk5MY29pSUNmSUdsR0plYXJ2N2pVUW1yUXJ4d2x0ZGV5aFE0NUxpYlB5VFhGZndKRlF5dFQwRllxUU9EOFNMM2VWS2R1MSt2U3g5bjFpWldRRVBqbHl5bEdoVG1rSkdqQ2lpYVZuTlBKUHlENGJsRlZtU3JmcndHeHVpMTBTd2RlZ0h2aVpTeExzT1VhYnAzVnNxS3I5YVduMDdWNUV5VlFTZEd5SVorNjNHWUhYOFZxZUFHaE1Pb1RUY2JBUFdSdVZYWWRWRWpxc2pYMUc1S1VkTjlpczdqUE15bjdDSVpmR2Q4ZmJEY3lMUkRhVWxsSFNSUlpicHVMaEhDck42UThOc1N2TUM5RldYRW90dXVHSitOXC9Bck5MUkZ2VlwvY0g5dnNmeStOVFlUS2dyQVZtNFJvRTY1SE5kWGh1RjBkRjZnMm1CeTBkUHJNaGVEMFNVdjJ2MENRTnRZS0RiZEN4UGJYaG5FXC9hb0IrWEJ6ZmtROTBHc25uVWdGa0ZYY1JTK1pMbE5ZaWpkQ0NkOXdZVytiWklVOStOVldnUW1FSmJYS2FJRDVnaG4xbnpOeUZyRkp3TXBuT0tSMjZ5XC9PbnM3QkNrZmxcL0V4b0UyeTIrZUFRQXdEZ0Y5S0RqTGhXVDRGVkNRMnhXbTRnVHFQUHhlU0h6OE1OXC9SZ2pVYUVxanQzZjN6VGRPOTRMdTAzNURHWDBkVU1RSG9PWTlmWEJmV2RXOE5zTmN3cFVrSE9PS1NGUVRXMENXc3poc1BwNFZiSXdPY2ptTGphaHc2dmVVQTVOVnlQaWVidDJGektWdDlIVWRJaXVQZDBqdHlHS2xTZm90TXpSUDVGQzVuN29DQ3pBOVFKZWhNVWp5S1I2dDRcL3ZvdUZtRlpUZ1Y1SEoyOU9sUk1KUVFuUWMreUZ4MzdWNzljSVc3Y2U4VFJBVHc2M25jeVlJTWlzNWVQdXZ0OEJkNVNOWWZ1WWV4UVVqUHllWXk4Z0Nma1lMQmdJazRLOGtlZFhSanQ1OXMyTkpGQm1aM0lVMzNDSHRpelJsb1Q4TENlZk9pbW1FcGNpcXVHdlpDZDNlZFJJV1R0eUlaMlwvRmJkZDJydU9zVkEzSFU0a1pDU3M0bndHaDFVRXdnejYremZ2SXNrdUtPMUtRTnAzQzJQWHFQMGU3b1BiOThzcVAzY2R1R0xEbjNqZGt4S3hMcXZiN09nRkxNMWZEeUV6WHVMeDBQalhZeTBvVitMWGNQU2tBcDZPM0dBWTh3cDd0K3h1M282YTFZRmVXUHR2MUtmd2FcL2kzN2F5YVAxMjRGUXBxOE1QVFpcL2pcL2RycUVsVU1oVTZEYjlMRlBXc1NXZDBFVVc5OTZwbHJqYTZDWWpHNlNTY3JuVmJ2d3MzYWw3Y09NQkxQQlBqWGsyUGJwN1BIUno1V1wvcEM2RGhiTHNUd2tUekpUTVRnZERTT01wbVJ0M2p6cFk2NzZEODhVaUl0cVJ6K3NFUjR0eWk4QkR1TlY2Y0VFVjdOSElwRlRQWmM1eGFYRlBCd0cwUmZiRFUxdEc1ZkxhU2hLSWhsaXB6enJDYkk2Qk92SFFRMVh6TEF2RzlFWVJHb1A0NXhVV0xadFg1bzEwTWJKOG1CSzRoSnJCa2kzc0tjNjM0bDd0UVM2T0hzVGJ3VkNQaFJtZ0ZQTXhcL0NycElCMFI0VU5LWG16b1d6TXNKTWM3cmpiYUJERzNNdUZGaHBXS3FQbWVvME80a3lmc1N6TmNBYk5HVlFYOXF0ZlwveVNPYkFnVmhUMUVPcXFET2ZZVXRIaGJaYm1xMHFiaUdKSWN0NDJIdGRraW82UE1cL1dRbkN4MzN6TmEyTFdnTWhsUjNjenBnTzIiLCJtYWMiOiJiMzhhZTMzMjJjOGY0OGU0MTNlZmZjYTU5MWJiOTFhMzQxOWY0MjY0ZDhhNmEyOWZmMzY4YTExZGNlMWZjMDg1In0%3D; drift_campaign_refresh=4ceb8b09-6bb5-4e2d-8728-5016144b4fc8; intended_url=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Fv4%2Fstatic%3Fbucket%3Dqm-private%26path%3Dtesting%252Fusers%252F256%252Froot-cause-analysis%252Foverall-mood-for-zain-root-cause-analysis.pdf%26state%3DeyJ1c2VyX2lkIjo5MjcxNywiY2xpZW50X2lkIjoicXVhbnRpbW9kbyIsImludGVuZGVkX3VybCI6Imh0dHBzOlwvXC9sb2NhbC5xdWFudGltby5kb1wvYXBpXC92NFwvc3RhdGljP2J1Y2tldD1xbS1wcml2YXRlJnBhdGg9dGVzdGluZyUyRnVzZXJzJTJGMjU2JTJGcm9vdC1jYXVzZS1hbmFseXNpcyUyRm92ZXJhbGwtbW9vZC1mb3ItemFpbi1yb290LWNhdXNlLWFuYWx5c2lzLnBkZiJ9%26code%3D4%252F0AX4XfWgbKXOYMg-1fDh-orU3YgOaMlBzMpbgvQbWpTEJw7HxrQMg5V-Y_856UBIqN_8nzw%26scope%3Demail%2Bprofile%2Bopenid%2Bhttps%253A%252F%252Fwww.googleapis.com%252Fauth%252Fuserinfo.profile%2Bhttps%253A%252F%252Fwww.googleapis.com%252Fauth%252Fuserinfo.email%26authuser%3D0%26hd%3Dthinkbynumbers.org%26prompt%3Dconsent%26sessionToken%3De2df703997d1240d7aca8859b49c3481f66e08be%26quantimodoUserId%3D92717%26quantimodoClientId%3Dquantimodo%26accessToken%3De2df703997d1240d7aca8859b49c3481f66e08be; final_callback_url=eyJpdiI6IldjWTNMbElRaVNEU2lleG9QS05Ycmc9PSIsInZhbHVlIjoiQ2lFTVdiZUhFYUxNUFdQMzZqcWRqcmdPejNUWWkwaWxuZTVDNzRBcCtXc3UrSGY0RXJcL2JUc1EwN29TdDd2dWpwbGNvSDZSTERsQ0tBeTlEYytPenZUTncxSnlncXkydGxBRDJHV2Ixc0kxTDF2bHdrV2xzYjBHQ1ZXaVpTUE9MVTFKSm9sRlJiOUVYK3BBRWFvdjZnT2JRcUszOGlFalI5aVgyS0hSaFwvcXhlcFVkN0dhVlVcL2RqMCsxcXJ6S2VZMGJpMUp1clJ1dGRjNmY2bk9walZQTGRtd0JNK0tnME1ZWVZrTFV4dWhPb0RPZGxhYmVsVXJIUVwvaXZwOTFXdWRSelh3c0RRa3hOU2pTcCt3MW9aK3VcL2tHaTJuXC9qMlhlNkxzSFlMM2Z2YzE5MTRnRlBzR0RWRnRxMEpxVUNxbkxzNnZ0d1ZBaFJNM1Z6Z0xqZjdkS2Rwak9EMW5GYWw0RU52MTVVSjJIT3FETjB1VlFscjZjb001WENFZlE4UWhweERoa0NFdnhFV0w2NVVYK2Z3eG8yblwvQWx4TlI2WXdURWpFQ1pXVmNJd0IydHFYcm03NGxxZHZTSE91TEN4Sm91SjBcL2hBQXhGSDNkMlJPajJnaWZYWlA3YlJCanFncTh0cHB5SFh6Tm9BSlU0RE9Ma1hCcUJZK1dKZmNUSXBnSHU0MWVuSGYwZ3ZQdGtsXC9BcTRnUVRCN0ZDME85QUlKZW01UDFYNGdkNWR0MWY3S3dpNE9LdHZjTkcyOHNPSE9MSFlCcXl3OERjSjlwVTU0STU5UmtYem1LTmwyUWRSSFFuaGhiVmxjbkpaejNMV3M3OWpSdDZXN3FcL1hVUUdlZUx6SXpJaGtQUGNYUmJVWUJoejZZSE1vUlVOdE9VZnNQUERFT0ZaZ21ldE5qTUpSSUh4MUg2R2ZkYTk5ekJBMnBHXC92NEM4cXJSVFwvUVNBbVpuTmJ0R1ZSdnY2eHBBMVlNakhScTZSQ2F0QVNYTU5OdkcwWUUzdFhNQ1wvQUxjMURyRnVneFZLMDVoK1RrTklXRk5DM1wvdGwrS2N1Y1pEdzU4czBCeWNKXC9GYTFYOTlPdWZhTGpETWFoTjRCNHNFd0tmalpWSzAzMUZyTDhEaFF2a0dBSkFsaW9ydFlvMDkwU25jN0pHWE5YaTR2WkFRWlBUTmZWK0QyekFKY1pMY2hZN09kUnFHNGdteEdSb2xUZmdVVlo0M2FKMlwvSzR2cWU2cjZMbzhHNkdSdllWYlBSUWJJdVBQZkY3Vjh4RGpMVXBvR05ERGtndFBDVFM1cE5pdFhtTFBiSnlYTWtkcHM2amY1a0pMaDVBODNSNjR5cUdoVkExRGFWVUxzYVJWbDU5b0JkM216MHpieDBwU3h6azBVWWtWWmdWNFpzZzBVQldGejJ5V2N4SHpvTE82aEJWQTBJaUMwT3ZPdVdxV0s5aDk1UUFFTDJ5U3JFSURNcW5kblhWTVlpeG9EQ0xkZU13cU9rcWlWZWxqOGY5NmhTSW5aWHQ2UlN6TkNmQ3VuXC8ycktmMmJoOSt5TVNkTnlDMHEybVwvUUtwR2xkMHBib04xXC9ZQ1JXWEpINUd5a3c9IiwibWFjIjoiNTU3ZmZmMGNkZWZhMGMxZTA4ZDVhN2IwMWY3NTk0OTVmZTA3NWQ5ZTNiYmZjNWM5Y2M5YzBhOTk3MTdlN2VlMSJ9; XSRF-TOKEN=eyJpdiI6IkoxSFFqdlhZNkU1aGkrOUZ4WEhjU3c9PSIsInZhbHVlIjoiTjZwN1RVdUtoYmw5cUIxWmd1NU5TbzNFZHRrVVcxRTR4VDZSY3lRSk5kbGdDOUhHN3cwaEQ0SmZaQStsXC9SN1UiLCJtYWMiOiI1MTI0ODk0NzJkNDRmZWEzMmNlMTY4Y2E3ZWYyYjJlN2FhMDRmYzNhYjRkYmMyYWE1OTc3M2QxNGY4ZDI5OGZkIn0%3D; laravel_session=eyJpdiI6IlwvSUpqczlKWlF0aWdwVzNaR0hsc0t3PT0iLCJ2YWx1ZSI6IjkrMGxqNkRnNnNzWW9URUlZUHRoXC9MbVJDVWxEN1QzdVwvVnlZWXVaeHhjaGhvNm1lRHhIQklac2FKV3puOHRPTiIsIm1hYyI6IjJkMTI1MzVkZWUxY2IzZDgyZDFiMTAyMTViMzRjOTM0OTgzMTQ3MzBkZWM5NDQwNzIzZGUxZWQ2MDk2ZWIyMmUifQ%3D%3D; login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6IitkNW4yZGxyQTFFa2xldFwvZElFNU5RPT0iLCJ2YWx1ZSI6IkF4QVMwRXZWWDhZN3RWRnVOck5Ccmc9PSIsIm1hYyI6IjNiOWRlMmYwNGMyZmNjMzIxZDBjNjI4YjM2OTEzN2E2ZjE2OWEyNzAwNzgwNWE2MTZjODE4NDZkYzhmNmNjYTYifQ%3D%3D; remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6Ikl0Y0lwRzlzXC9IQ0hJVW0xVUtvYUhRPT0iLCJ2YWx1ZSI6IlN1N0c0NTI1aDlkcDBYNWV4MnJDS2VcL2NWR2lZb2Q2MUJQbTU1ZSthcGlWMUFma1lheXRHeWxYdjdjdzVWczlZcmREdVNnZ3ZKQU1xbmhYRVRtUUlkSmhBZE1nWFJPZFZpQUtZdVc1czJVQndqRDQwajAwcW9PcVY2V1wvS2lYSzNmcDg2SktcL2NDRkNFbzBzMUgzTzNlQnFocjRGdllnek5IYytCdDFTU1hvQT0iLCJtYWMiOiI4NDQxYmVhZDg1MDRmMGYzNzliMDNhZWQzODAyMzg2MmQzMTgwMGNjOTFmMTM1MzJiZjY5NGM2MzMyZmNiYmRhIn0%3D; clockwork-profile=null',
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
        0 => 'cross-site',
      ),
      'accept' => 
      array (
        0 => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      ),
      'user-agent' => 
      array (
        0 => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36 Edg/96.0.1054.57',
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
        0 => '" Not A;Brand";v="99", "Chromium";v="96", "Microsoft Edge";v="96"',
      ),
      'cache-control' => 
      array (
        0 => 'max-age=0',
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
		$responseBody = $this->callAndCheckResponse($expectedCode, $expectedString);
		return $responseBody;
	}
}
