<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection SpellCheckingInspection */
namespace Tests\StagingUnitTests\D\DataLab;
use App\Override\GeneratedTestRequest;
use App\Override\QMFileBag;
use App\Override\QMHeaderBag;
use App\Override\QMParameterBag;
use App\Override\QMServerBag;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Testing\TestResponse;
use Tests\LaravelStagingTestCase;
use Tests\QMBaseTestCase;
class DatalabUnitsTest extends LaravelStagingTestCase
{
    protected $REQUEST_URI = "/datalab/units?draw=1&columns%5B0%5D%5Bdata%5D=open_button&columns%5B0%5D%5Bsearchable%5D=false&columns%5B0%5D%5Borderable%5D=false&columns%5B1%5D%5Bdata%5D=name&columns%5B2%5D%5Bdata%5D=category_link&columns%5B2%5D%5Bsearchable%5D=false&columns%5B2%5D%5Borderable%5D=false&columns%5B3%5D%5Bdata%5D=abbreviated_name&columns%5B4%5D%5Bdata%5D=drop_down_button&columns%5B4%5D%5Bsearchable%5D=false&columns%5B4%5D%5Borderable%5D=false&columns%5B5%5D%5Bdata%5D=related_data&columns%5B5%5D%5Bsearchable%5D=false&columns%5B5%5D%5Borderable%5D=false&columns%5B6%5D%5Bdata%5D=id_link&columns%5B6%5D%5Bsearchable%5D=false&columns%5B6%5D%5Borderable%5D=false&columns%5B7%5D%5Bdata%5D=advanced&columns%5B7%5D%5Borderable%5D=false&columns%5B8%5D%5Bdata%5D=action&columns%5B8%5D%5Bsearchable%5D=false&columns%5B8%5D%5Borderable%5D=false&order%5B0%5D%5Bcolumn%5D=7&order%5B0%5D%5Bdir%5D=asc&start=0&length=10&search%5Bvalue%5D=&_=1626035044151";
    public function testDataTableUnitsAsRegularUser(): void{
        $this->actAsTestUser();
        $response = $this->stagingRequest(200, "");
	    $lastResponseData = $this->lastResponseData('data');
        $this->checkTestDuration(5);
        $this->checkQueryCount(10);
        $this->assertCount(10, $this->lastResponseData('data'));
        $this->assertDataTableQueriesEqual([]);
    }
    public function testDataTableUnitsAsAdmin(): void{
        $this->actAsAdmin();
        $response = $this->stagingRequest(200, "");

        $this->checkTestDuration(5);
        $this->checkQueryCount(10);
	    $lastResponseData = $this->lastResponseData('data');
        $this->assertCount(10, $lastResponseData);
        $this->assertDataTableQueriesEqual([]);
    }
    public function testDataTableUnitsWithoutAuth(): void{
	    QMBaseTestCase::setExpectedRequestException(AuthenticationException::class);
	    $this->stagingRequest(401, "Unauthenticated");
	    $response = $this->getTestResponse();
	    $this->checkTestDuration(5);
	    $this->checkQueryCount(3);
	    $this->assertDataTableQueriesEqual([]);
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
      'draw' => '1',
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
          'data' => 'category_link',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        3 =>
        array (
          'data' => 'abbreviated_name',
        ),
        4 =>
        array (
          'data' => 'drop_down_button',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        5 =>
        array (
          'data' => 'related_data',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        6 =>
        array (
          'data' => 'id_link',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        7 =>
        array (
          'data' => 'advanced',
          'orderable' => 'false',
        ),
        8 =>
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
          'column' => '7',
          'dir' => 'asc',
        ),
      ),
      'start' => '0',
      'length' => '10',
      'search' =>
      array (
        'value' => '',
      ),
      '_' => '1626035044151',
    ),
  )),
   'query' =>
  QMParameterBag::__set_state(array(
     'parameters' =>
    array (
      'draw' => '1',
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
          'data' => 'category_link',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        3 =>
        array (
          'data' => 'abbreviated_name',
        ),
        4 =>
        array (
          'data' => 'drop_down_button',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        5 =>
        array (
          'data' => 'related_data',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        6 =>
        array (
          'data' => 'id_link',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        7 =>
        array (
          'data' => 'advanced',
          'orderable' => 'false',
        ),
        8 =>
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
          'column' => '7',
          'dir' => 'asc',
        ),
      ),
      'start' => '0',
      'length' => '10',
      'search' =>
      array (
        'value' => '',
      ),
      '_' => '1626035044151',
    ),
  )),
   'server' =>
  QMServerBag::__set_state(array(
     'parameters' =>
    array (
      'HOME' => '/var/www',
      'HTTP_CF_WORKER' => 'quantimo.do',
      'HTTP_X_XSRF_TOKEN' => 'eyJpdiI6IkpXaTFHSCtBZ1IrbzNEQThyRURldEE9PSIsInZhbHVlIjoiZjBuMmNlcE1yVSsrUXdzYUZHTFk0ajc0aDNHYWtOY0JVY0dsQXJuMnFWTnFlM0RSUHlpcWQ3Y05hZEt1RXpTRSIsIm1hYyI6ImM4MWM1M2E1MTBiNzg2NDQzNDY2ZmZkYmRjNzAzZjUwYjYwODBlOTZhMzk2OTE1NWE3ZDFiMzMwYzk0ZTNjYjgifQ==',
      'HTTP_SEC_FETCH_SITE' => 'same-origin',
      'HTTP_SEC_FETCH_MODE' => 'cors',
      'HTTP_SEC_FETCH_DEST' => 'empty',
      'HTTP_SEC_CH_UA_MOBILE' => '?0',
      'HTTP_SEC_CH_UA' => '" Not;A Brand";v="99", "Google Chrome";v="91", "Chromium";v="91"',
      'HTTP_CF_CONNECTING_IP' => '97.91.131.8',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
      'HTTP_REFERER' => getenv('APP_URL').$this->REQUEST_URI.'',
      'HTTP_COOKIE' => 'driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; _lr_uf_-mkcthl=c99d669d-4c31-4e67-a0b5-dc3bbb30c78e; u=a203429b3dc08aca3cfcd3ffe7dea51e7591a093; _ga=GA1.2.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; DFTT_END_USER_PREV_BOOTSTRAPPED=true; driftt_eid=230; __gads=ID=baf1b7342d7fe58c-22cb087a70c50002:T=1608928372:RT=1608928372:S=ALNI_Mb_nA1jEnsvWYINYNpOi0KgcoaQEA; __utmc=109117957; __utmz=109117957.1611547594.1.1.utmcsr=local.quantimo.do|utmccn=(referral)|utmcmd=referral|utmcct=/; drift_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; drift_eid=230; __utma=109117957.644404966.1601578880.1611547594.1620319036.2; drift_eid=230; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1626811067%7Cdd460739350ce71c52c696ceb4cc9350%7Cquantimodo; final_callback_url=https%3A%2F%2Fstaging.quantimo.do%2Fapi%2Fv1%2Fconnect%2Fmobile%3Flog%3Dtestuser%26pwd%3Dtesting123%26client_id%3Dquantimodo%26quantimodoAccessToken%3Dmike-test-token%26quantimodoUserId%3D230%26code%3DonZ7vAZ6zUMVzfIqOi7HStVPrX9KzXCG9j7e5J09yzU%26state%3DeyJ1c2VyX2lkIjoyMzAsImNsaWVudF9pZCI6InF1YW50aW1vZG8iLCJmaW5hbF9jYWxsYmFja191cmwiOiJodHRwczpcL1wvc3RhZ2luZy5xdWFudGltby5kb1wvYXBpXC92MVwvY29ubmVjdFwvbW9iaWxlP2xvZz10ZXN0dXNlciZwd2Q9dGVzdGluZzEyMyZjbGllbnRfaWQ9cXVhbnRpbW9kbyZxdWFudGltb2RvQWNjZXNzVG9rZW49MDFkMzk3ZmQwZTkyMDY3YWFjMzgxNWQ5OWE1YTBhMzM0YWU3MTI2YSZxdWFudGltb2RvVXNlcklkPTIzMCJ9%26sessionToken%3Dmike-test-token%26quantimodoClientId%3Dquantimodo%26accessToken%3Dmike-test-token; drift_campaign_refresh=0da38c86-eeaa-41e2-b168-9acf822b9856; _gid=GA1.2.1141281637.1626034995; intendedUrl=eyJpdiI6IktNc0xnRXg2MWg4dmhxekk1dXdEeGc9PSIsInZhbHVlIjoiZVp6ZzREczRYSVhoNThETXdNT0hhRGI4bXlua3V1VDZPU1RiQW9aalVQUGZhSVNjY3Rqa3ljcmFNcnVnVUFFUDdNbVF1WWxaNEVCZml5NWlEVkRkcW9RR1wvVGxDSFoyY2xyWUZcL0Z4Tytkbz0iLCJtYWMiOiJlODg3ZmEzMmQ3Zjc5ZTA0YTcwNGQ0MDcyM2M2MjY4NzE1YjcwNjA5MDQ4NDJkYTYxMDY4ZDEzNTBkZGRkMmYwIn0%3D; _lr_tabs_-mkcthl%2Fquantimodo={%22sessionID%22:0%2C%22recordingID%22:%224-9509bccd-bf12-468a-ac1b-9bc7ea4b1f64%22%2C%22lastActivity%22:1626035045156}; _lr_hb_-mkcthl%2Fquantimodo={%22heartbeat%22:1626035045157}; XSRF-TOKEN=eyJpdiI6IkpXaTFHSCtBZ1IrbzNEQThyRURldEE9PSIsInZhbHVlIjoiZjBuMmNlcE1yVSsrUXdzYUZHTFk0ajc0aDNHYWtOY0JVY0dsQXJuMnFWTnFlM0RSUHlpcWQ3Y05hZEt1RXpTRSIsIm1hYyI6ImM4MWM1M2E1MTBiNzg2NDQzNDY2ZmZkYmRjNzAzZjUwYjYwODBlOTZhMzk2OTE1NWE3ZDFiMzMwYzk0ZTNjYjgifQ%3D%3D; laravel_session=eyJpdiI6InlmamFjR05lVEFKXC81NlFhV1NtNUxBPT0iLCJ2YWx1ZSI6ImpGUTl0c0VBNUdPcm9BWmdBZFZcL1wvdzBPZ1ZNY1ZGRkw3NEdzU1VjMDB0VmVaUFlISXBxWXB6K0YwXC9MK0RjVXQiLCJtYWMiOiJkYzY5MmNkYzhjNDAwNGIwNDY4ZmJhZmYzZjYyYmYyMGFjZjM2MTBmMGIwZmZiN2JiZGJlY2EzZGIyMGQ0ODNmIn0%3D',
      'HTTP_ACCEPT' => 'application/json, text/plain, */*',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_CDN_LOOP' => 'cloudflare; subreqs=1',
      'HTTP_CF_EW_VIA' => '15',
      'HTTP_CF_VISITOR' => '{"scheme":"https"}',
      'HTTP_X_FORWARDED_PROTO' => 'https',
      'HTTP_CF_RAY' => '66d4c11ee3d7296e-ORD',
      'HTTP_X_FORWARDED_FOR' => '97.91.131.8',
      'HTTP_CF_IPCOUNTRY' => 'US',
      'HTTP_ACCEPT_ENCODING' => 'gzip',
      'HTTP_CONNECTION' => 'Keep-Alive',
      'REDIRECT_STATUS' => '200',
      'HTTPS' => 'on',
      'SERVER_NAME' => 'app.quantimo.do',
      'SERVER_PORT' => '443',
      'SERVER_ADDR' => '172.26.3.145',
      'REMOTE_PORT' => '22996',
      'REMOTE_ADDR' => '172.70.127.30',
      'SERVER_SOFTWARE' => 'nginx/1.15.8',
      'GATEWAY_INTERFACE' => 'CGI/1.1',
      'SERVER_PROTOCOL' => 'HTTP/1.1',
      'DOCUMENT_ROOT' => '/home/ubuntu/releases/ab6443386e15e2383c614308a690328ece0faaff/public',
      'DOCUMENT_URI' => '/index.php',
      'REQUEST_URI' => $this->REQUEST_URI.'',
      'SCRIPT_NAME' => '/index.php',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => '',
      'REQUEST_METHOD' => 'GET',
      'QUERY_STRING' => 'draw=1&columns%5B0%5D%5Bdata%5D=open_button&columns%5B0%5D%5Bsearchable%5D=false&columns%5B0%5D%5Borderable%5D=false&columns%5B1%5D%5Bdata%5D=name&columns%5B2%5D%5Bdata%5D=category_link&columns%5B2%5D%5Bsearchable%5D=false&columns%5B2%5D%5Borderable%5D=false&columns%5B3%5D%5Bdata%5D=abbreviated_name&columns%5B4%5D%5Bdata%5D=drop_down_button&columns%5B4%5D%5Bsearchable%5D=false&columns%5B4%5D%5Borderable%5D=false&columns%5B5%5D%5Bdata%5D=related_data&columns%5B5%5D%5Bsearchable%5D=false&columns%5B5%5D%5Borderable%5D=false&columns%5B6%5D%5Bdata%5D=id_link&columns%5B6%5D%5Bsearchable%5D=false&columns%5B6%5D%5Borderable%5D=false&columns%5B7%5D%5Bdata%5D=advanced&columns%5B7%5D%5Borderable%5D=false&columns%5B8%5D%5Bdata%5D=action&columns%5B8%5D%5Bsearchable%5D=false&columns%5B8%5D%5Borderable%5D=false&order%5B0%5D%5Bcolumn%5D=7&order%5B0%5D%5Bdir%5D=asc&start=0&length=10&search%5Bvalue%5D=&_=1626035044151',
      'SCRIPT_FILENAME' => '/home/ubuntu/releases/ab6443386e15e2383c614308a690328ece0faaff/public/index.php',
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
      'driftt_aid' => 'df29ce65-369a-440c-9d5e-a1888f0cd13d',
      '_lr_uf_-mkcthl' => 'c99d669d-4c31-4e67-a0b5-dc3bbb30c78e',
      'u' => 'a203429b3dc08aca3cfcd3ffe7dea51e7591a093',
      '_ga' => 'GA1.2.644404966.1601578880',
      'DFTT_END_USER_PREV_BOOTSTRAPPED' => 'true',
      'driftt_eid' => '230',
      '__gads' => 'ID=baf1b7342d7fe58c-22cb087a70c50002:T=1608928372:RT=1608928372:S=ALNI_Mb_nA1jEnsvWYINYNpOi0KgcoaQEA',
      '__utmc' => '109117957',
      '__utmz' => '109117957.1611547594.1.1.utmcsr=local.quantimo.do|utmccn=(referral)|utmcmd=referral|utmcct=/',
      'drift_aid' => 'df29ce65-369a-440c-9d5e-a1888f0cd13d',
      'drift_eid' => '230',
      '__utma' => '109117957.644404966.1601578880.1611547594.1620319036.2',
      'quantimodo_logged_in_af6160480df78a3a6d520187243f05c9' => 'mike|1626811067|dd460739350ce71c52c696ceb4cc9350|quantimodo',
      'final_callback_url' => 'https://staging.quantimo.do/api/v1/connect/mobile?log=testuser&pwd=testing123&client_id=quantimodo&quantimodoAccessToken=mike-test-token&quantimodoUserId=230&code=onZ7vAZ6zUMVzfIqOi7HStVPrX9KzXCG9j7e5J09yzU&state=eyJ1c2VyX2lkIjoyMzAsImNsaWVudF9pZCI6InF1YW50aW1vZG8iLCJmaW5hbF9jYWxsYmFja191cmwiOiJodHRwczpcL1wvc3RhZ2luZy5xdWFudGltby5kb1wvYXBpXC92MVwvY29ubmVjdFwvbW9iaWxlP2xvZz10ZXN0dXNlciZwd2Q9dGVzdGluZzEyMyZjbGllbnRfaWQ9cXVhbnRpbW9kbyZxdWFudGltb2RvQWNjZXNzVG9rZW49MDFkMzk3ZmQwZTkyMDY3YWFjMzgxNWQ5OWE1YTBhMzM0YWU3MTI2YSZxdWFudGltb2RvVXNlcklkPTIzMCJ9&sessionToken=mike-test-token&quantimodoClientId=quantimodo&accessToken=mike-test-token',
      'drift_campaign_refresh' => '0da38c86-eeaa-41e2-b168-9acf822b9856',
      '_gid' => 'GA1.2.1141281637.1626034995',
      'intendedUrl' => 'eyJpdiI6IktNc0xnRXg2MWg4dmhxekk1dXdEeGc9PSIsInZhbHVlIjoiZVp6ZzREczRYSVhoNThETXdNT0hhRGI4bXlua3V1VDZPU1RiQW9aalVQUGZhSVNjY3Rqa3ljcmFNcnVnVUFFUDdNbVF1WWxaNEVCZml5NWlEVkRkcW9RR1wvVGxDSFoyY2xyWUZcL0Z4Tytkbz0iLCJtYWMiOiJlODg3ZmEzMmQ3Zjc5ZTA0YTcwNGQ0MDcyM2M2MjY4NzE1YjcwNjA5MDQ4NDJkYTYxMDY4ZDEzNTBkZGRkMmYwIn0=',
      '_lr_tabs_-mkcthl%2Fquantimodo' => '{"sessionID":0,"recordingID":"4-9509bccd-bf12-468a-ac1b-9bc7ea4b1f64","lastActivity":1626035045156}',
      '_lr_hb_-mkcthl%2Fquantimodo' => '{"heartbeat":1626035045157}',
      'XSRF-TOKEN' => 'eyJpdiI6IkpXaTFHSCtBZ1IrbzNEQThyRURldEE9PSIsInZhbHVlIjoiZjBuMmNlcE1yVSsrUXdzYUZHTFk0ajc0aDNHYWtOY0JVY0dsQXJuMnFWTnFlM0RSUHlpcWQ3Y05hZEt1RXpTRSIsIm1hYyI6ImM4MWM1M2E1MTBiNzg2NDQzNDY2ZmZkYmRjNzAzZjUwYjYwODBlOTZhMzk2OTE1NWE3ZDFiMzMwYzk0ZTNjYjgifQ==',
      'laravel_session' => 'eyJpdiI6InlmamFjR05lVEFKXC81NlFhV1NtNUxBPT0iLCJ2YWx1ZSI6ImpGUTl0c0VBNUdPcm9BWmdBZFZcL1wvdzBPZ1ZNY1ZGRkw3NEdzU1VjMDB0VmVaUFlISXBxWXB6K0YwXC9MK0RjVXQiLCJtYWMiOiJkYzY5MmNkYzhjNDAwNGIwNDY4ZmJhZmYzZjYyYmYyMGFjZjM2MTBmMGIwZmZiN2JiZGJlY2EzZGIyMGQ0ODNmIn0=',
    ),
  )),
   'headers' =>
  QMHeaderBag::__set_state(array(
     'headers' =>
    array (
      'cf-worker' =>
      array (
        0 => 'quantimo.do',
      ),
      'x-xsrf-token' =>
      array (
        0 => 'eyJpdiI6IkpXaTFHSCtBZ1IrbzNEQThyRURldEE9PSIsInZhbHVlIjoiZjBuMmNlcE1yVSsrUXdzYUZHTFk0ajc0aDNHYWtOY0JVY0dsQXJuMnFWTnFlM0RSUHlpcWQ3Y05hZEt1RXpTRSIsIm1hYyI6ImM4MWM1M2E1MTBiNzg2NDQzNDY2ZmZkYmRjNzAzZjUwYjYwODBlOTZhMzk2OTE1NWE3ZDFiMzMwYzk0ZTNjYjgifQ==',
      ),
      'sec-fetch-site' =>
      array (
        0 => 'same-origin',
      ),
      'sec-fetch-mode' =>
      array (
        0 => 'cors',
      ),
      'sec-fetch-dest' =>
      array (
        0 => 'empty',
      ),
      'sec-ch-ua-mobile' =>
      array (
        0 => '?0',
      ),
      'sec-ch-ua' =>
      array (
        0 => '" Not;A Brand";v="99", "Google Chrome";v="91", "Chromium";v="91"',
      ),
      'cf-connecting-ip' =>
      array (
        0 => '97.91.131.8',
      ),
      'user-agent' =>
      array (
        0 => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
      ),
      'referer' =>
      array (
        0 => getenv('APP_URL').$this->REQUEST_URI.'',
      ),
      'cookie' =>
      array (
        0 => 'driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; _lr_uf_-mkcthl=c99d669d-4c31-4e67-a0b5-dc3bbb30c78e; u=a203429b3dc08aca3cfcd3ffe7dea51e7591a093; _ga=GA1.2.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; DFTT_END_USER_PREV_BOOTSTRAPPED=true; driftt_eid=230; __gads=ID=baf1b7342d7fe58c-22cb087a70c50002:T=1608928372:RT=1608928372:S=ALNI_Mb_nA1jEnsvWYINYNpOi0KgcoaQEA; __utmc=109117957; __utmz=109117957.1611547594.1.1.utmcsr=local.quantimo.do|utmccn=(referral)|utmcmd=referral|utmcct=/; drift_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; drift_eid=230; __utma=109117957.644404966.1601578880.1611547594.1620319036.2; drift_eid=230; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1626811067%7Cdd460739350ce71c52c696ceb4cc9350%7Cquantimodo; final_callback_url=https%3A%2F%2Fstaging.quantimo.do%2Fapi%2Fv1%2Fconnect%2Fmobile%3Flog%3Dtestuser%26pwd%3Dtesting123%26client_id%3Dquantimodo%26quantimodoAccessToken%3Dmike-test-token%26quantimodoUserId%3D230%26code%3DonZ7vAZ6zUMVzfIqOi7HStVPrX9KzXCG9j7e5J09yzU%26state%3DeyJ1c2VyX2lkIjoyMzAsImNsaWVudF9pZCI6InF1YW50aW1vZG8iLCJmaW5hbF9jYWxsYmFja191cmwiOiJodHRwczpcL1wvc3RhZ2luZy5xdWFudGltby5kb1wvYXBpXC92MVwvY29ubmVjdFwvbW9iaWxlP2xvZz10ZXN0dXNlciZwd2Q9dGVzdGluZzEyMyZjbGllbnRfaWQ9cXVhbnRpbW9kbyZxdWFudGltb2RvQWNjZXNzVG9rZW49MDFkMzk3ZmQwZTkyMDY3YWFjMzgxNWQ5OWE1YTBhMzM0YWU3MTI2YSZxdWFudGltb2RvVXNlcklkPTIzMCJ9%26sessionToken%3Dmike-test-token%26quantimodoClientId%3Dquantimodo%26accessToken%3Dmike-test-token; drift_campaign_refresh=0da38c86-eeaa-41e2-b168-9acf822b9856; _gid=GA1.2.1141281637.1626034995; intendedUrl=eyJpdiI6IktNc0xnRXg2MWg4dmhxekk1dXdEeGc9PSIsInZhbHVlIjoiZVp6ZzREczRYSVhoNThETXdNT0hhRGI4bXlua3V1VDZPU1RiQW9aalVQUGZhSVNjY3Rqa3ljcmFNcnVnVUFFUDdNbVF1WWxaNEVCZml5NWlEVkRkcW9RR1wvVGxDSFoyY2xyWUZcL0Z4Tytkbz0iLCJtYWMiOiJlODg3ZmEzMmQ3Zjc5ZTA0YTcwNGQ0MDcyM2M2MjY4NzE1YjcwNjA5MDQ4NDJkYTYxMDY4ZDEzNTBkZGRkMmYwIn0%3D; _lr_tabs_-mkcthl%2Fquantimodo={%22sessionID%22:0%2C%22recordingID%22:%224-9509bccd-bf12-468a-ac1b-9bc7ea4b1f64%22%2C%22lastActivity%22:1626035045156}; _lr_hb_-mkcthl%2Fquantimodo={%22heartbeat%22:1626035045157}; XSRF-TOKEN=eyJpdiI6IkpXaTFHSCtBZ1IrbzNEQThyRURldEE9PSIsInZhbHVlIjoiZjBuMmNlcE1yVSsrUXdzYUZHTFk0ajc0aDNHYWtOY0JVY0dsQXJuMnFWTnFlM0RSUHlpcWQ3Y05hZEt1RXpTRSIsIm1hYyI6ImM4MWM1M2E1MTBiNzg2NDQzNDY2ZmZkYmRjNzAzZjUwYjYwODBlOTZhMzk2OTE1NWE3ZDFiMzMwYzk0ZTNjYjgifQ%3D%3D; laravel_session=eyJpdiI6InlmamFjR05lVEFKXC81NlFhV1NtNUxBPT0iLCJ2YWx1ZSI6ImpGUTl0c0VBNUdPcm9BWmdBZFZcL1wvdzBPZ1ZNY1ZGRkw3NEdzU1VjMDB0VmVaUFlISXBxWXB6K0YwXC9MK0RjVXQiLCJtYWMiOiJkYzY5MmNkYzhjNDAwNGIwNDY4ZmJhZmYzZjYyYmYyMGFjZjM2MTBmMGIwZmZiN2JiZGJlY2EzZGIyMGQ0ODNmIn0%3D',
      ),
      'accept' =>
      array (
        0 => 'application/json, text/plain, */*',
      ),
      'accept-language' =>
      array (
        0 => 'en-US,en;q=0.9',
      ),
      'cdn-loop' =>
      array (
        0 => 'cloudflare; subreqs=1',
      ),
      'cf-ew-via' =>
      array (
        0 => '15',
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
        0 => '66d4c11ee3d7296e-ORD',
      ),
      'x-forwarded-for' =>
      array (
        0 => '97.91.131.8',
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
        0 => 'app.quantimo.do',
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
