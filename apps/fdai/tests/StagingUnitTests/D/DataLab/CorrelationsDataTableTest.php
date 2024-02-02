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
use Illuminate\Testing\TestResponse;
use Tests\DatalabTestCase;
class CorrelationsDataTableTest extends DatalabTestCase
{
    const USER_ID = '72128';
    protected $REQUEST_URI = "/datalab/user_variable_relationships?correlations_user_id=72128&createTest=1&draw=1&columns%5B0%5D%5Bdata%5D=open_button&columns%5B0%5D%5Bsearchable%5D=false&columns%5B0%5D%5Borderable%5D=&columns%5B1%5D%5Bdata%5D=drop_down_button&columns%5B1%5D%5Bsearchable%5D=false&columns%5B1%5D%5Borderable%5D=&columns%5B2%5D%5Bdata%5D=related_data&columns%5B2%5D%5Bsearchable%5D=false&columns%5B2%5D%5Borderable%5D=&columns%5B3%5D%5Bdata%5D=gauge_link&columns%5B3%5D%5Bsearchable%5D=false&columns%5B3%5D%5Borderable%5D=&columns%5B4%5D%5Bdata%5D=cause_link&columns%5B4%5D%5Bname%5D=cause_variable.name&columns%5B4%5D%5Borderable%5D=&columns%5B5%5D%5Bdata%5D=effect_follow_up_percent_change_from_baseline&columns%5B5%5D%5Borderable%5D=1&columns%5B6%5D%5Bdata%5D=effect_link&columns%5B6%5D%5Bname%5D=effect_variable.name&columns%5B6%5D%5Borderable%5D=&columns%5B7%5D%5Bdata%5D=number_of_pairs&columns%5B7%5D%5Bsearchable%5D=false&columns%5B7%5D%5Borderable%5D=1&columns%5B8%5D%5Bdata%5D=qm_score&columns%5B8%5D%5Borderable%5D=1&columns%5B9%5D%5Bdata%5D=z_score&columns%5B9%5D%5Borderable%5D=1&columns%5B10%5D%5Bdata%5D=analysis_ended_at&columns%5B10%5D%5Bsearchable%5D=false&columns%5B10%5D%5Borderable%5D=1&columns%5B11%5D%5Bdata%5D=errors_link&columns%5B11%5D%5Bsearchable%5D=false&columns%5B11%5D%5Borderable%5D=&columns%5B12%5D%5Bdata%5D=post_link&columns%5B12%5D%5Bsearchable%5D=false&columns%5B12%5D%5Borderable%5D=&columns%5B13%5D%5Bdata%5D=user_id_link&columns%5B13%5D%5Bsearchable%5D=false&columns%5B13%5D%5Borderable%5D=&columns%5B14%5D%5Bdata%5D=updated_at&columns%5B14%5D%5Bsearchable%5D=false&columns%5B14%5D%5Borderable%5D=1&columns%5B15%5D%5Bdata%5D=action&columns%5B15%5D%5Bsearchable%5D=false&columns%5B15%5D%5Borderable%5D=false&order%5B0%5D%5Bcolumn%5D=8&order%5B0%5D%5Bdir%5D=desc&start=0&length=10&search%5Bvalue%5D=&_=1592743261854";
    public function testDatalabCorrelationsAsRegularUser(): void{
        $this->actAsTestUser();
        $response = $this->stagingRequest(200, "");
        $this->assertCount(0, $this->lastResponseData('data'), "Regular user shouldn't be able to get user_variable_relationships for USER_ID");
        foreach($this->lastResponseData('data') as $datum){$this->assertEquals(UserIdProperty::USER_ID_TEST_USER, $datum->user_id);}
        $this->checkTestDuration(5);
        $this->checkQueryCount(6);
    }
    public function testDatalabCorrelationsAsAdmin(): void{
        $this->actAsAdmin();
        $response = $this->stagingRequest(200, "");
        $this->assertCount(10, $this->lastResponseData('data'));
        $this->assertDataTableQueriesEqual([
	        0 => 'select * from `oa_access_tokens` where `oa_access_tokens`.`access_token` = ? and `oa_access_tokens`.`deleted_at` is null limit 1',
	        1 => 'select * from `wp_users` where `wp_users`.`ID` = ? and `wp_users`.`deleted_at` is null limit 1',
	        2 => 'select count(*) as aggregate from (select * from `user_variable_relationships` where `user_variable_relationships`.`user_id` = ? and `user_variable_relationships`.`deleted_at` is null order by `user_variable_relationships`.`qm_score` desc) count_row_table',
	        3 => 'select * from `user_variable_relationships` where `user_variable_relationships`.`user_id` = ? and `user_variable_relationships`.`deleted_at` is null order by `user_variable_relationships`.`qm_score` desc limit 10 offset 0',
	        4 => 'select * from `variables` where `variables`.`id` in (1272, 1280, 1304, 1486, 1906) and `variables`.`deleted_at` is null',
	        5 => 'select * from `variables` where `variables`.`id` in (1272, 1867, 1906, 5211811, 5211821) and `variables`.`deleted_at` is null',]);
        $this->checkTestDuration(10);
        $this->checkQueryCount(14);
    }
    public function testDatalabCorrelationsWithoutAuth(): void{
        $this->assertGuest();
        $response = $this->assertUnauthenticatedResponse();
        $this->checkTestDuration(5);
        $this->checkQueryCount(2);
    }
    /**
     * @param int $expectedCode
     * @param string|null $expectedString
     * @return string|object
     */
    protected function stagingRequest(int $expectedCode = 200, string $expectedString = null): TestResponse {
		$this->serializedRequest = GeneratedTestRequest::__set_state([
   'json' => NULL,
   'convertedFiles' => NULL,
   'userResolver' => NULL,
   'routeResolver' => NULL,
   'attributes' =>
  QMParameterBag::__set_state([
     'parameters' =>
    [],
  ]),
   'request' =>
  QMParameterBag::__set_state([
     'parameters' =>
    [
        'correlations_user_id' => self::USER_ID,
        'draw' => '1',
        'columns' =>
      [
        0 =>
        [
          'data' => 'open_button',
          'searchable' => 'false',
          'orderable' => '',
        ],
        1 =>
        [
          'data' => 'drop_down_button',
          'searchable' => 'false',
          'orderable' => '',
        ],
        2 =>
        [
          'data' => 'related_data',
          'searchable' => 'false',
          'orderable' => '',
        ],
        3 =>
        [
          'data' => 'gauge_link',
          'searchable' => 'false',
          'orderable' => '',
        ],
        4 =>
        [
          'data' => 'cause_link',
          'name' => 'cause_variable.name',
          'orderable' => '',
        ],
        5 =>
        [
          'data' => 'effect_follow_up_percent_change_from_baseline',
          'orderable' => '1',
        ],
        6 =>
        [
          'data' => 'effect_link',
          'name' => 'effect_variable.name',
          'orderable' => '',
        ],
        7 =>
        [
          'data' => 'number_of_pairs',
          'searchable' => 'false',
          'orderable' => '1',
        ],
        8 =>
        [
          'data' => 'qm_score',
          'orderable' => '1',
        ],
        9 =>
        [
          'data' => 'z_score',
          'orderable' => '1',
        ],
        10 =>
        [
          'data' => 'analysis_ended_at',
          'searchable' => 'false',
          'orderable' => '1',
        ],
        11 =>
        [
          'data' => 'errors_link',
          'searchable' => 'false',
          'orderable' => '',
        ],
        12 =>
        [
          'data' => 'post_link',
          'searchable' => 'false',
          'orderable' => '',
        ],
        13 =>
        [
          'data' => 'user_id_link',
          'searchable' => 'false',
          'orderable' => '',
        ],
        14 =>
        [
          'data' => 'updated_at',
          'searchable' => 'false',
          'orderable' => '1',
        ],
        15 =>
        [
          'data' => 'action',
          'searchable' => 'false',
          'orderable' => 'false',
        ],
      ],
        'order' =>
      [
        0 =>
        [
          'column' => '8',
          'dir' => 'desc',
        ],
      ],
        'start' => '0',
        'length' => '10',
        'search' =>
      [
        'value' => '',
      ],
        '_' => '1592743261854',
    ],
  ]),
   'query' =>
  QMParameterBag::__set_state([
     'parameters' =>
    [
        'correlations_user_id' => self::USER_ID,
        'draw' => '1',
        'columns' =>
      [
        0 =>
        [
          'data' => 'open_button',
          'searchable' => 'false',
          'orderable' => '',
        ],
        1 =>
        [
          'data' => 'drop_down_button',
          'searchable' => 'false',
          'orderable' => '',
        ],
        2 =>
        [
          'data' => 'related_data',
          'searchable' => 'false',
          'orderable' => '',
        ],
        3 =>
        [
          'data' => 'gauge_link',
          'searchable' => 'false',
          'orderable' => '',
        ],
        4 =>
        [
          'data' => 'cause_link',
          'name' => 'cause_variable.name',
          'orderable' => '',
        ],
        5 =>
        [
          'data' => 'effect_follow_up_percent_change_from_baseline',
          'orderable' => '1',
        ],
        6 =>
        [
          'data' => 'effect_link',
          'name' => 'effect_variable.name',
          'orderable' => '',
        ],
        7 =>
        [
          'data' => 'number_of_pairs',
          'searchable' => 'false',
          'orderable' => '1',
        ],
        8 =>
        [
          'data' => 'qm_score',
          'orderable' => '1',
        ],
        9 =>
        [
          'data' => 'z_score',
          'orderable' => '1',
        ],
        10 =>
        [
          'data' => 'analysis_ended_at',
          'searchable' => 'false',
          'orderable' => '1',
        ],
        11 =>
        [
          'data' => 'errors_link',
          'searchable' => 'false',
          'orderable' => '',
        ],
        12 =>
        [
          'data' => 'post_link',
          'searchable' => 'false',
          'orderable' => '',
        ],
        13 =>
        [
          'data' => 'user_id_link',
          'searchable' => 'false',
          'orderable' => '',
        ],
        14 =>
        [
          'data' => 'updated_at',
          'searchable' => 'false',
          'orderable' => '1',
        ],
        15 =>
        [
          'data' => 'action',
          'searchable' => 'false',
          'orderable' => 'false',
        ],
      ],
        'order' =>
      [
        0 =>
        [
          'column' => '8',
          'dir' => 'desc',
        ],
      ],
        'start' => '0',
        'length' => '10',
        'search' =>
      [
        'value' => '',
      ],
        '_' => '1592743261854',
    ],
  ]),
   'server' =>
  QMServerBag::__set_state([
     'parameters' =>
    [
      'USER' => 'vagrant',
      'HOME' => '/home/vagrant',
      'HTTP_COOKIE' => '__cfduid=df3bcb60f11c220f65104c37de9ac7ea41592425988; _ga=GA1.1.1415477443.1592425993; driftt_aid=c8735c86-68c0-4de2-94ce-73a43605c5c5; DFTT_END_USER_PREV_BOOTSTRAPPED=true; u=6ec3e62cc0069a10c6759edd5423df738a0fec0b; _ga=GA1.2.2014257657.1592502511; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; driftt_eid=230; final_callback_url=http%3A%2F%2Flocalhost%3A63348%2Fionic%2Fsrc%2Findex.html%23%2Fapp%2Flogin%3Fclient_id%3Dquantimodo%26quantimodoAccessToken%3Dmike-test-token%26quantimodoUserId%3D230; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1593827300%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; XSRF-TOKEN=eyJpdiI6IkoxMjQ5NHpUNHlReGd5Zml6cHJSZlE9PSIsInZhbHVlIjoieitcL2RWNjdFM3ZTT1IxVHVMOElrNHlwbkYzaUlYNU1nMDBsOGl1S2JQWHZUK3pLSGozWFwveHQ4dEhzYlwvTWNabiIsIm1hYyI6Ijc0MzI1MDYwMDJkMDUxMDZiZGMzMjk3ODQ1ZmVkNGNiOTM5NWQ2ODhlZmZlOGQ2NDIwNTI1MmIxMzA1NTljMDQifQ%3D%3D; laravel_session=eyJpdiI6Imk2dzdXc3ZPOEVkYmJnTkQ0Yk1rMnc9PSIsInZhbHVlIjoibk56TXpUUWtDbTM0VmRlXC90cDMzNTRDZ1N4UlBXSG1meEptd1hJamd6VVhRK1wvN2V1WHBzeVV5UU5LZ09Iem9RIiwibWFjIjoiNGUxNDg2ZjY1NmZiNjBlNmVjY2YxNjIzMzY2ZDliZDBjODlkNjZkOGE5NWUzZWNjMzc3ZWViMjA2ZGJlYzM2NiJ9',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_REFERER' => getenv('APP_URL').'/datalab/user_variable_relationships?createTest=1&user_variable_relationships.user_id=72128',
      'HTTP_SEC_FETCH_DEST' => 'empty',
      'HTTP_SEC_FETCH_MODE' => 'cors',
      'HTTP_SEC_FETCH_SITE' => 'same-origin',
      'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.106 Safari/537.36',
      'HTTP_ACCEPT' => 'application/json, text/javascript, */*; q=0.01',
      'HTTP_CACHE_CONTROL' => 'no-cache',
      'HTTP_PRAGMA' => 'no-cache',
      'HTTP_CONNECTION' => 'keep-alive',
      'REDIRECT_STATUS' => '200',
      'HTTPS' => 'on',
      'SERVER_NAME' => 'testing.quantimo.do',
      'SERVER_PORT' => '443',
      'REMOTE_PORT' => '60966',
      'SERVER_SOFTWARE' => 'nginx/1.15.8',
      'GATEWAY_INTERFACE' => 'CGI/1.1',
      'SERVER_PROTOCOL' => 'HTTP/1.1',
      'DOCUMENT_URI' => '/index.php',
      'REQUEST_URI' => $this->REQUEST_URI.'',
      'SCRIPT_NAME' => '/index.php',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => '',
      'REQUEST_METHOD' => 'GET',
      'QUERY_STRING' => 'correlations_user_id=72128&createTest=1&draw=1&columns%5B0%5D%5Bdata%5D=open_button&columns%5B0%5D%5Bsearchable%5D=false&columns%5B0%5D%5Borderable%5D=&columns%5B1%5D%5Bdata%5D=drop_down_button&columns%5B1%5D%5Bsearchable%5D=false&columns%5B1%5D%5Borderable%5D=&columns%5B2%5D%5Bdata%5D=related_data&columns%5B2%5D%5Bsearchable%5D=false&columns%5B2%5D%5Borderable%5D=&columns%5B3%5D%5Bdata%5D=gauge_link&columns%5B3%5D%5Bsearchable%5D=false&columns%5B3%5D%5Borderable%5D=&columns%5B4%5D%5Bdata%5D=cause_link&columns%5B4%5D%5Bname%5D=cause_variable.name&columns%5B4%5D%5Borderable%5D=&columns%5B5%5D%5Bdata%5D=effect_follow_up_percent_change_from_baseline&columns%5B5%5D%5Borderable%5D=1&columns%5B6%5D%5Bdata%5D=effect_link&columns%5B6%5D%5Bname%5D=effect_variable.name&columns%5B6%5D%5Borderable%5D=&columns%5B7%5D%5Bdata%5D=number_of_pairs&columns%5B7%5D%5Bsearchable%5D=false&columns%5B7%5D%5Borderable%5D=1&columns%5B8%5D%5Bdata%5D=qm_score&columns%5B8%5D%5Borderable%5D=1&columns%5B9%5D%5Bdata%5D=z_score&columns%5B9%5D%5Borderable%5D=1&columns%5B10%5D%5Bdata%5D=analysis_ended_at&columns%5B10%5D%5Bsearchable%5D=false&columns%5B10%5D%5Borderable%5D=1&columns%5B11%5D%5Bdata%5D=errors_link&columns%5B11%5D%5Bsearchable%5D=false&columns%5B11%5D%5Borderable%5D=&columns%5B12%5D%5Bdata%5D=post_link&columns%5B12%5D%5Bsearchable%5D=false&columns%5B12%5D%5Borderable%5D=&columns%5B13%5D%5Bdata%5D=user_id_link&columns%5B13%5D%5Bsearchable%5D=false&columns%5B13%5D%5Borderable%5D=&columns%5B14%5D%5Bdata%5D=updated_at&columns%5B14%5D%5Bsearchable%5D=false&columns%5B14%5D%5Borderable%5D=1&columns%5B15%5D%5Bdata%5D=action&columns%5B15%5D%5Bsearchable%5D=false&columns%5B15%5D%5Borderable%5D=false&order%5B0%5D%5Bcolumn%5D=8&order%5B0%5D%5Bdir%5D=desc&start=0&length=10&search%5Bvalue%5D=&_=1592743261854',
      'FCGI_ROLE' => 'RESPONDER',
      'PHP_SELF' => '/index.php',
    ],
  ]),
   'files' =>
  QMFileBag::__set_state([
     'parameters' =>
    [],
  ]),
   'cookies' =>
  QMParameterBag::__set_state([
     'parameters' =>
    [
      '__cfduid' => 'df3bcb60f11c220f65104c37de9ac7ea41592425988',
      '_ga' => 'GA1.1.1415477443.1592425993',
      'driftt_aid' => 'c8735c86-68c0-4de2-94ce-73a43605c5c5',
      'DFTT_END_USER_PREV_BOOTSTRAPPED' => 'true',
      'u' => '6ec3e62cc0069a10c6759edd5423df738a0fec0b',
      'driftt_eid' => '230',
      'final_callback_url' => 'http://localhost:63348/ionic/src/index.html#/app/login?client_id=quantimodo&quantimodoAccessToken=mike-test-token&quantimodoUserId=230',
      'quantimodo_logged_in_af6160480df78a3a6d520187243f05c9' => 'mike|1593827300|de069cf4b3bbf933721060a76259dad7|quantimodo',
      'XSRF-TOKEN' => 'eyJpdiI6IkoxMjQ5NHpUNHlReGd5Zml6cHJSZlE9PSIsInZhbHVlIjoieitcL2RWNjdFM3ZTT1IxVHVMOElrNHlwbkYzaUlYNU1nMDBsOGl1S2JQWHZUK3pLSGozWFwveHQ4dEhzYlwvTWNabiIsIm1hYyI6Ijc0MzI1MDYwMDJkMDUxMDZiZGMzMjk3ODQ1ZmVkNGNiOTM5NWQ2ODhlZmZlOGQ2NDIwNTI1MmIxMzA1NTljMDQifQ==',
      'laravel_session' => 'eyJpdiI6Imk2dzdXc3ZPOEVkYmJnTkQ0Yk1rMnc9PSIsInZhbHVlIjoibk56TXpUUWtDbTM0VmRlXC90cDMzNTRDZ1N4UlBXSG1meEptd1hJamd6VVhRK1wvN2V1WHBzeVV5UU5LZ09Iem9RIiwibWFjIjoiNGUxNDg2ZjY1NmZiNjBlNmVjY2YxNjIzMzY2ZDliZDBjODlkNjZkOGE5NWUzZWNjMzc3ZWViMjA2ZGJlYzM2NiJ9',
    ],
  ]),
   'headers' =>
  QMHeaderBag::__set_state([
     'headers' =>
    [
      'cookie' =>
      [
        0 => '__cfduid=df3bcb60f11c220f65104c37de9ac7ea41592425988; _ga=GA1.1.1415477443.1592425993; driftt_aid=c8735c86-68c0-4de2-94ce-73a43605c5c5; DFTT_END_USER_PREV_BOOTSTRAPPED=true; u=6ec3e62cc0069a10c6759edd5423df738a0fec0b; _ga=GA1.2.2014257657.1592502511; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; driftt_eid=230; final_callback_url=http%3A%2F%2Flocalhost%3A63348%2Fionic%2Fsrc%2Findex.html%23%2Fapp%2Flogin%3Fclient_id%3Dquantimodo%26quantimodoAccessToken%3Dmike-test-token%26quantimodoUserId%3D230; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1593827300%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; XSRF-TOKEN=eyJpdiI6IkoxMjQ5NHpUNHlReGd5Zml6cHJSZlE9PSIsInZhbHVlIjoieitcL2RWNjdFM3ZTT1IxVHVMOElrNHlwbkYzaUlYNU1nMDBsOGl1S2JQWHZUK3pLSGozWFwveHQ4dEhzYlwvTWNabiIsIm1hYyI6Ijc0MzI1MDYwMDJkMDUxMDZiZGMzMjk3ODQ1ZmVkNGNiOTM5NWQ2ODhlZmZlOGQ2NDIwNTI1MmIxMzA1NTljMDQifQ%3D%3D; laravel_session=eyJpdiI6Imk2dzdXc3ZPOEVkYmJnTkQ0Yk1rMnc9PSIsInZhbHVlIjoibk56TXpUUWtDbTM0VmRlXC90cDMzNTRDZ1N4UlBXSG1meEptd1hJamd6VVhRK1wvN2V1WHBzeVV5UU5LZ09Iem9RIiwibWFjIjoiNGUxNDg2ZjY1NmZiNjBlNmVjY2YxNjIzMzY2ZDliZDBjODlkNjZkOGE5NWUzZWNjMzc3ZWViMjA2ZGJlYzM2NiJ9',
      ],
      'accept-language' =>
      [
        0 => 'en-US,en;q=0.9',
      ],
      'accept-encoding' =>
      [
        0 => 'gzip, deflate, br',
      ],
      'referer' =>
      [
        0 => getenv('APP_URL').'/datalab/user_variable_relationships?createTest=1&user_variable_relationships.user_id=72128',
      ],
      'sec-fetch-dest' =>
      [
        0 => 'empty',
      ],
      'sec-fetch-mode' =>
      [
        0 => 'cors',
      ],
      'sec-fetch-site' =>
      [
        0 => 'same-origin',
      ],
      'x-requested-with' =>
      [
        0 => 'XMLHttpRequest',
      ],
      'user-agent' =>
      [
        0 => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.106 Safari/537.36',
      ],
      'accept' =>
      [
        0 => 'application/json, text/javascript, */*; q=0.01',
      ],
      'cache-control' =>
      [
        0 => 'no-cache',
      ],
      'pragma' =>
      [
        0 => 'no-cache',
      ],
      'connection' =>
      [
        0 => 'keep-alive',
      ],
      'host' =>
      [
        0 => 'local.quantimo.do',
      ],
      'content-length' =>
      [
        0 => '',
      ],
      'content-type' =>
      [
        0 => '',
      ],
    ],
     'cacheControl' =>
    [
      'no-cache' => true,
    ],
  ]),
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
		]);
		return  $this->callAndCheckResponse($expectedCode, $expectedString);
	}
}
