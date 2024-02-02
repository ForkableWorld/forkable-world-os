<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection SpellCheckingInspection */
namespace Tests\StagingUnitTests\D\DataLab;
use App\Override\GeneratedTestRequest;
use App\Override\QMFileBag;
use App\Override\QMHeaderBag;
use App\Override\QMParameterBag;
use App\Override\QMServerBag;
use App\Properties\User\UserIdProperty;
use App\Storage\DB\QMDB;
use Illuminate\Testing\TestResponse;
use Tests\DatalabTestCase;
class DatalabOAuthClientsTest extends DatalabTestCase
{
    protected $REQUEST_URI = "/datalab/oAuthClients?draw=2&columns%5B0%5D%5Bdata%5D=open_button&columns%5B0%5D%5Bsearchable%5D=false&columns%5B0%5D%5Borderable%5D=&columns%5B1%5D%5Bdata%5D=drop_down_button&columns%5B1%5D%5Bsearchable%5D=false&columns%5B1%5D%5Borderable%5D=&columns%5B2%5D%5Bdata%5D=related_data&columns%5B2%5D%5Bsearchable%5D=false&columns%5B2%5D%5Borderable%5D=&columns%5B3%5D%5Bdata%5D=client_id&columns%5B3%5D%5Borderable%5D=&columns%5B4%5D%5Bdata%5D=user_id_link&columns%5B4%5D%5Bsearchable%5D=false&columns%5B4%5D%5Borderable%5D=&columns%5B5%5D%5Bdata%5D=updated_at&columns%5B5%5D%5Bsearchable%5D=false&columns%5B5%5D%5Borderable%5D=1&columns%5B6%5D%5Bdata%5D=action&columns%5B6%5D%5Bsearchable%5D=false&columns%5B6%5D%5Borderable%5D=false&order%5B0%5D%5Bcolumn%5D=5&order%5B0%5D%5Bdir%5D=asc&start=0&length=10&search%5Bvalue%5D=&_=1592107014607";
    public function testDatalabOAuthClientsAsRegularUser(): void{
        QMDB::assertLogging();
        $this->actAsTestUser();
        $this->stagingRequest(200, 0);
        $response = $this->stagingRequest(200, "");
        $this->assertCount(10, $this->lastResponseData('data'));
        foreach($this->lastResponseData('data') as $datum){$this->assertEquals(UserIdProperty::USER_ID_TEST_USER, $datum->user_id);}
        QMDB::assertLogging();
        $this->assertDataTableQueriesEqual(array (
            0 => 'select * from `oa_access_tokens` where `oa_access_tokens`.`access_token` = ? and `oa_access_tokens`.`deleted_at` is null limit 1',
            1 => 'select * from `wp_users` where `wp_users`.`ID` = ? and `wp_users`.`deleted_at` is null limit 1',
            2 => 'select count(*) as aggregate from (select * from `oa_clients` where `oa_clients`.`user_id` in (?) and `oa_clients`.`deleted_at` is null order by `oa_clients`.`updated_at` asc) count_row_table',
            3 => 'select * from `oa_clients` where `oa_clients`.`user_id` in (?) and `oa_clients`.`deleted_at` is null order by `oa_clients`.`updated_at` asc limit 10 offset 0',
        ));
        QMDB::assertLogging();
        $this->checkTestDuration(9);
        $this->checkQueryCount(13);
    }
    public function testDatalabOAuthClientsAsAdmin(): void{
        //UserNumberOfPatientsProperty::updateAll();
        $this->actAsAdmin();
        $this->assertNumberOfPatients(2);
        $response = $this->stagingRequest(200, "");
        $this->assertCount(10, $this->lastResponseData('data'));
        $this->assertDataTableQueriesEqual(array (
            0 => 'select * from `oa_access_tokens` where `oa_access_tokens`.`access_token` = ? and `oa_access_tokens`.`deleted_at` is null limit 1',
            1 => 'select * from `wp_users` where `wp_users`.`ID` = ? and `wp_users`.`deleted_at` is null limit 1',
            2 => 'select count(*) as aggregate from (select * from `oa_clients` where `oa_clients`.`deleted_at` is null order by `oa_clients`.`updated_at` asc) count_row_table',
            3 => 'select * from `oa_clients` where `oa_clients`.`deleted_at` is null order by `oa_clients`.`updated_at` asc limit 10 offset 0',
        ));
        $this->checkTestDuration(14);
        $this->checkQueryCount(10);
    }
    public function testDatalabOAuthClientsAsWithoutAuth(): void{
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
      'draw' => '2',
      'columns' =>
      array (
        0 =>
        array (
          'data' => 'open_button',
          'searchable' => 'false',
          'orderable' => '',
        ),
        1 =>
        array (
          'data' => 'drop_down_button',
          'searchable' => 'false',
          'orderable' => '',
        ),
        2 =>
        array (
          'data' => 'related_data',
          'searchable' => 'false',
          'orderable' => '',
        ),
        3 =>
        array (
          'data' => 'client_id',
          'orderable' => '',
        ),
        4 =>
        array (
          'data' => 'user_id_link',
          'searchable' => 'false',
          'orderable' => '',
        ),
        5 =>
        array (
          'data' => 'updated_at',
          'searchable' => 'false',
          'orderable' => '1',
        ),
        6 =>
        array (
          'data' => 'action',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
      ),
      'order' =>
      array (
        0 =>
        array (
          'column' => '5',
          'dir' => 'asc',
        ),
      ),
      'start' => '0',
      'length' => '10',
      'search' =>
      array (
        'value' => '',
      ),
      '_' => '1592107014607',
    ),
  )),
   'query' =>
  QMParameterBag::__set_state(array(
     'parameters' =>
    array (
      'draw' => '2',
      'columns' =>
      array (
        0 =>
        array (
          'data' => 'open_button',
          'searchable' => 'false',
          'orderable' => '',
        ),
        1 =>
        array (
          'data' => 'drop_down_button',
          'searchable' => 'false',
          'orderable' => '',
        ),
        2 =>
        array (
          'data' => 'related_data',
          'searchable' => 'false',
          'orderable' => '',
        ),
        3 =>
        array (
          'data' => 'client_id',
          'orderable' => '',
        ),
        4 =>
        array (
          'data' => 'user_id_link',
          'searchable' => 'false',
          'orderable' => '',
        ),
        5 =>
        array (
          'data' => 'updated_at',
          'searchable' => 'false',
          'orderable' => '1',
        ),
        6 =>
        array (
          'data' => 'action',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
      ),
      'order' =>
      array (
        0 =>
        array (
          'column' => '5',
          'dir' => 'asc',
        ),
      ),
      'start' => '0',
      'length' => '10',
      'search' =>
      array (
        'value' => '',
      ),
      '_' => '1592107014607',
    ),
  )),
   'server' =>
  QMServerBag::__set_state(array(
     'parameters' =>
    array (
      'USER' => 'vagrant',
      'HOME' => '/home/vagrant',
      'HTTP_COOKIE' => 'u=1f428f225112e63447dddbc2cdb87d70f597b6bf; _ga=GA1.1.1091482458.1590862194; driftt_aid=d814d39b-f800-483f-8417-92891397ffec; __cfduid=dedfea3f97c3efc49cc7ef45d88b1dbde1590866417; _ga=GA1.2.1319490333.1590871543; DFTT_END_USER_PREV_BOOTSTRAPPED=true; __gads=ID=064cb43f27ada9a8:T=1591295660:S=ALNI_MaXoYSavd6pmww_S4OEYN1zNgfqiQ; driftt_eid=230; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1592930788%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6Ik91Y2dPU3FncXQrQ3VzSXFmblZyQnc9PSIsInZhbHVlIjoiQXdiU0Z0V3JxQUM4RENxN2xEUnllRzRndDl2TzJLTVwvSTNkc3Q2bkFGdEFrUTlGY2xZREpvc21iYUdaK1Vub3RHcjErWUx6OG11S0JWZWhmMDU3R3p1Y0trRjltWmxtZVJOTE1Tb1lDMUtKcEs5RmNUVlhZcVh1a2c5S2k0ZVQ5OVR3SUZXS3lNM05PSFlUVGdQM2p5QT09IiwibWFjIjoiYWVhZjE0MDM3ODg0OTRhNjBlNjIxZWM4NDI0MDVjNTYxMDI2ZTczZmY2YmI1MzMzNzYyNzAwMzU1MWIwMjM0YSJ9; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; _gid=GA1.2.1870841610.1591898680; final_callback_url=https%3A%2F%2Flocal.quantimo.do%2Fdatalab%3Fcountry%3D%26client_id%3Dquantimodo%26quantimodoAccessToken%3Dmike-test-token%26quantimodoUserId%3D230; XSRF-TOKEN=eyJpdiI6IjNFbUJTdU9aRGp3TTlpSElvdThheVE9PSIsInZhbHVlIjoiMlUyU1lsQ1J3akkxQWZHeVNcL3RYNlJqR3FUNGpZWlZtNjFibDJSVVwvaTFQc3h4WmhFUlZ5aW50YmdBSXFlb241IiwibWFjIjoiODliNzhlYWNmMzA2OTU1OTg5N2IzM2QxMDBhNzMyZDcxNzIxYWMxMDEyODRkM2UwNjYzMDQ5ZDc5NWMzNTcxMCJ9; laravel_session=eyJpdiI6IkVZbjhoMEYzKzczNFF2MVB0S1phZ3c9PSIsInZhbHVlIjoicUwrK2xXK3JuZEVaZlYyVlV2THJOaTNhMlIrTTkxcFNSc0dObnZrRXhhclJxb0RRTXdIdHlRdnNmQXJ4RGozdyIsIm1hYyI6ImIxZTFjYmVlNzY0NTliYzg2NWI4NGIwMDZiMGU4NWMyNDU0MDE0MmI3OGM5Nzc5NGZmZDFjZTU0NGYyMWVmNTcifQ%3D%3D',
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
      'REMOTE_PORT' => '61473',
      'SERVER_SOFTWARE' => 'nginx/1.15.8',
      'GATEWAY_INTERFACE' => 'CGI/1.1',
      'SERVER_PROTOCOL' => 'HTTP/1.1',
      'DOCUMENT_URI' => '/index.php',
      'REQUEST_URI' => $this->REQUEST_URI.'',
      'SCRIPT_NAME' => '/index.php',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => '',
      'REQUEST_METHOD' => 'GET',
      'QUERY_STRING' => 'draw=2&columns%5B0%5D%5Bdata%5D=open_button&columns%5B0%5D%5Bsearchable%5D=false&columns%5B0%5D%5Borderable%5D=&columns%5B1%5D%5Bdata%5D=drop_down_button&columns%5B1%5D%5Bsearchable%5D=false&columns%5B1%5D%5Borderable%5D=&columns%5B2%5D%5Bdata%5D=related_data&columns%5B2%5D%5Bsearchable%5D=false&columns%5B2%5D%5Borderable%5D=&columns%5B3%5D%5Bdata%5D=client_id&columns%5B3%5D%5Borderable%5D=&columns%5B4%5D%5Bdata%5D=user_id_link&columns%5B4%5D%5Bsearchable%5D=false&columns%5B4%5D%5Borderable%5D=&columns%5B5%5D%5Bdata%5D=updated_at&columns%5B5%5D%5Bsearchable%5D=false&columns%5B5%5D%5Borderable%5D=1&columns%5B6%5D%5Bdata%5D=action&columns%5B6%5D%5Bsearchable%5D=false&columns%5B6%5D%5Borderable%5D=false&order%5B0%5D%5Bcolumn%5D=5&order%5B0%5D%5Bdir%5D=asc&start=0&length=10&search%5Bvalue%5D=&_=1592107014607',
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
      'XSRF-TOKEN' => 'eyJpdiI6IjNFbUJTdU9aRGp3TTlpSElvdThheVE9PSIsInZhbHVlIjoiMlUyU1lsQ1J3akkxQWZHeVNcL3RYNlJqR3FUNGpZWlZtNjFibDJSVVwvaTFQc3h4WmhFUlZ5aW50YmdBSXFlb241IiwibWFjIjoiODliNzhlYWNmMzA2OTU1OTg5N2IzM2QxMDBhNzMyZDcxNzIxYWMxMDEyODRkM2UwNjYzMDQ5ZDc5NWMzNTcxMCJ9',
      'laravel_session' => 'eyJpdiI6IkVZbjhoMEYzKzczNFF2MVB0S1phZ3c9PSIsInZhbHVlIjoicUwrK2xXK3JuZEVaZlYyVlV2THJOaTNhMlIrTTkxcFNSc0dObnZrRXhhclJxb0RRTXdIdHlRdnNmQXJ4RGozdyIsIm1hYyI6ImIxZTFjYmVlNzY0NTliYzg2NWI4NGIwMDZiMGU4NWMyNDU0MDE0MmI3OGM5Nzc5NGZmZDFjZTU0NGYyMWVmNTcifQ==',
    ),
  )),
   'headers' =>
  QMHeaderBag::__set_state(array(
     'headers' =>
    array (
      'cookie' =>
      array (
        0 => 'u=1f428f225112e63447dddbc2cdb87d70f597b6bf; _ga=GA1.1.1091482458.1590862194; driftt_aid=d814d39b-f800-483f-8417-92891397ffec; __cfduid=dedfea3f97c3efc49cc7ef45d88b1dbde1590866417; _ga=GA1.2.1319490333.1590871543; DFTT_END_USER_PREV_BOOTSTRAPPED=true; __gads=ID=064cb43f27ada9a8:T=1591295660:S=ALNI_MaXoYSavd6pmww_S4OEYN1zNgfqiQ; driftt_eid=230; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1592930788%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6Ik91Y2dPU3FncXQrQ3VzSXFmblZyQnc9PSIsInZhbHVlIjoiQXdiU0Z0V3JxQUM4RENxN2xEUnllRzRndDl2TzJLTVwvSTNkc3Q2bkFGdEFrUTlGY2xZREpvc21iYUdaK1Vub3RHcjErWUx6OG11S0JWZWhmMDU3R3p1Y0trRjltWmxtZVJOTE1Tb1lDMUtKcEs5RmNUVlhZcVh1a2c5S2k0ZVQ5OVR3SUZXS3lNM05PSFlUVGdQM2p5QT09IiwibWFjIjoiYWVhZjE0MDM3ODg0OTRhNjBlNjIxZWM4NDI0MDVjNTYxMDI2ZTczZmY2YmI1MzMzNzYyNzAwMzU1MWIwMjM0YSJ9; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; _gid=GA1.2.1870841610.1591898680; final_callback_url=https%3A%2F%2Flocal.quantimo.do%2Fdatalab%3Fcountry%3D%26client_id%3Dquantimodo%26quantimodoAccessToken%3Dmike-test-token%26quantimodoUserId%3D230; XSRF-TOKEN=eyJpdiI6IjNFbUJTdU9aRGp3TTlpSElvdThheVE9PSIsInZhbHVlIjoiMlUyU1lsQ1J3akkxQWZHeVNcL3RYNlJqR3FUNGpZWlZtNjFibDJSVVwvaTFQc3h4WmhFUlZ5aW50YmdBSXFlb241IiwibWFjIjoiODliNzhlYWNmMzA2OTU1OTg5N2IzM2QxMDBhNzMyZDcxNzIxYWMxMDEyODRkM2UwNjYzMDQ5ZDc5NWMzNTcxMCJ9; laravel_session=eyJpdiI6IkVZbjhoMEYzKzczNFF2MVB0S1phZ3c9PSIsInZhbHVlIjoicUwrK2xXK3JuZEVaZlYyVlV2THJOaTNhMlIrTTkxcFNSc0dObnZrRXhhclJxb0RRTXdIdHlRdnNmQXJ4RGozdyIsIm1hYyI6ImIxZTFjYmVlNzY0NTliYzg2NWI4NGIwMDZiMGU4NWMyNDU0MDE0MmI3OGM5Nzc5NGZmZDFjZTU0NGYyMWVmNTcifQ%3D%3D',
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
