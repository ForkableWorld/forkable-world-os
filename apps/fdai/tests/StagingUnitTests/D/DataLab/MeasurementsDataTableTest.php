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
use Tests\LaravelStagingTestCase;
class MeasurementsDataTableTest extends LaravelStagingTestCase
{
    protected $REQUEST_URI = "/datalab/measurements?draw=1&columns%5B0%5D%5Bdata%5D=open_button&columns%5B0%5D%5Bsearchable%5D=false&columns%5B0%5D%5Borderable%5D=&columns%5B1%5D%5Bdata%5D=drop_down_button&columns%5B1%5D%5Bsearchable%5D=false&columns%5B1%5D%5Borderable%5D=&columns%5B2%5D%5Bdata%5D=related_data&columns%5B2%5D%5Bsearchable%5D=false&columns%5B2%5D%5Borderable%5D=&columns%5B3%5D%5Bdata%5D=value&columns%5B3%5D%5Bsearchable%5D=false&columns%5B4%5D%5Bdata%5D=unit_link&columns%5B4%5D%5Bsearchable%5D=false&columns%5B4%5D%5Borderable%5D=&columns%5B5%5D%5Bdata%5D=variable_name_link&columns%5B5%5D%5Bname%5D=variable.name&columns%5B5%5D%5Borderable%5D=&columns%5B6%5D%5Bdata%5D=start_time&columns%5B6%5D%5Borderable%5D=1&columns%5B7%5D%5Bdata%5D=user_variable_link&columns%5B7%5D%5Bsearchable%5D=false&columns%5B7%5D%5Borderable%5D=&columns%5B8%5D%5Bdata%5D=variable_category_link&columns%5B8%5D%5Bsearchable%5D=false&columns%5B8%5D%5Borderable%5D=&columns%5B9%5D%5Bdata%5D=data_source_link&columns%5B9%5D%5Bsearchable%5D=false&columns%5B9%5D%5Borderable%5D=&columns%5B10%5D%5Bdata%5D=note&columns%5B10%5D%5Borderable%5D=&columns%5B11%5D%5Bdata%5D=errors_link&columns%5B11%5D%5Bsearchable%5D=false&columns%5B11%5D%5Borderable%5D=&columns%5B12%5D%5Bdata%5D=user_id_link&columns%5B12%5D%5Bsearchable%5D=false&columns%5B12%5D%5Borderable%5D=&columns%5B13%5D%5Bdata%5D=updated_at&columns%5B13%5D%5Bsearchable%5D=false&columns%5B13%5D%5Borderable%5D=1&columns%5B14%5D%5Bdata%5D=action&columns%5B14%5D%5Bsearchable%5D=false&columns%5B14%5D%5Borderable%5D=false&order%5B0%5D%5Bcolumn%5D=6&order%5B0%5D%5Bdir%5D=desc&start=0&length=10&search%5Bvalue%5D=&_=1592447070999";
    public function testDatalabMeasurementsAsRegularUser(): void{
        $this->actAsTestUser();
        $response = $this->stagingRequest(200, "");
        $this->assertCount(10, $this->lastResponseData('data'));
        foreach($this->lastResponseData('data') as $datum){$this->assertEquals(UserIdProperty::USER_ID_TEST_USER, $datum->user_id);}
        $this->assertDataTableQueryCount(10); // Queries change
        $this->checkTestDuration(5);
        $this->checkQueryCount(11);
    }
    public function testDatalabMeasurementsAsAdmin(): void{
        $this->actAsAdmin();
        $response = $this->stagingRequest(200, "");
        $this->assertCount(10, $this->lastResponseData('data'));
        $this->assertDataTableQueryCount(10); // Can't compare queries because they change
        $this->checkTestDuration(10);
        $this->checkQueryCount(12);
    }
    public function testDatalabMeasurementsWithoutAuth(): void{
        $this->unauthenticated();
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
          'data' => 'value',
          'searchable' => 'false',
        ],
        4 =>
        [
          'data' => 'unit_link',
          'searchable' => 'false',
          'orderable' => '',
        ],
        5 =>
        [
          'data' => 'variable_name_link',
          'name' => 'variable.name',
          'orderable' => '',
        ],
        6 =>
        [
          'data' => 'start_time',
          'orderable' => '1',
        ],
        7 =>
        [
          'data' => 'user_variable_link',
          'searchable' => 'false',
          'orderable' => '',
        ],
        8 =>
        [
          'data' => 'variable_category_link',
          'searchable' => 'false',
          'orderable' => '',
        ],
        9 =>
        [
          'data' => 'data_source_link',
          'searchable' => 'false',
          'orderable' => '',
        ],
        10 =>
        [
          'data' => 'note',
          'orderable' => '',
        ],
        11 =>
        [
          'data' => 'errors_link',
          'searchable' => 'false',
          'orderable' => '',
        ],
        12 =>
        [
          'data' => 'user_id_link',
          'searchable' => 'false',
          'orderable' => '',
        ],
        13 =>
        [
          'data' => 'updated_at',
          'searchable' => 'false',
          'orderable' => '1',
        ],
        14 =>
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
          'column' => '6',
          'dir' => 'desc',
        ],
      ],
      'start' => '0',
      'length' => '10',
      'search' =>
      [
        'value' => '',
      ],
      '_' => '1592447070999',
    ],
                              ]),
   'query' =>
  QMParameterBag::__set_state([
     'parameters' =>
    [
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
          'data' => 'value',
          'searchable' => 'false',
        ],
        4 =>
        [
          'data' => 'unit_link',
          'searchable' => 'false',
          'orderable' => '',
        ],
        5 =>
        [
          'data' => 'variable_name_link',
          'name' => 'variable.name',
          'orderable' => '',
        ],
        6 =>
        [
          'data' => 'start_time',
          'orderable' => '1',
        ],
        7 =>
        [
          'data' => 'user_variable_link',
          'searchable' => 'false',
          'orderable' => '',
        ],
        8 =>
        [
          'data' => 'variable_category_link',
          'searchable' => 'false',
          'orderable' => '',
        ],
        9 =>
        [
          'data' => 'data_source_link',
          'searchable' => 'false',
          'orderable' => '',
        ],
        10 =>
        [
          'data' => 'note',
          'orderable' => '',
        ],
        11 =>
        [
          'data' => 'errors_link',
          'searchable' => 'false',
          'orderable' => '',
        ],
        12 =>
        [
          'data' => 'user_id_link',
          'searchable' => 'false',
          'orderable' => '',
        ],
        13 =>
        [
          'data' => 'updated_at',
          'searchable' => 'false',
          'orderable' => '1',
        ],
        14 =>
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
          'column' => '6',
          'dir' => 'desc',
        ],
      ],
      'start' => '0',
      'length' => '10',
      'search' =>
      [
        'value' => '',
      ],
      '_' => '1592447070999',
    ],
                              ]),
   'server' =>
  QMServerBag::__set_state([
     'parameters' =>
    [
      'USER' => 'vagrant',
      'HOME' => '/home/vagrant',
      'HTTP_COOKIE' => '__cfduid=df3bcb60f11c220f65104c37de9ac7ea41592425988; _ga=GA1.1.1415477443.1592425993; _gid=GA1.1.489049337.1592425993; driftt_aid=c8735c86-68c0-4de2-94ce-73a43605c5c5; driftt_aid=c8735c86-68c0-4de2-94ce-73a43605c5c5; DFTT_END_USER_PREV_BOOTSTRAPPED=true; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1593635613%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; XDEBUG_PROFILE=1; u=6ec3e62cc0069a10c6759edd5423df738a0fec0b; XSRF-TOKEN=eyJpdiI6IituZ3VOYURyWFZCYzAyWUVOU3cyMUE9PSIsInZhbHVlIjoiU2dxbXhhRmVkRjE5Z2Z1bkFEamg3U2VLNkYwQ0FmYlcrWkhKeGt1cmVjbmxTejg0bTNHY0x4NnZjUjd6bitIRSIsIm1hYyI6ImI1YTY3ZDc0MzVjZTNhNmZlZmI5YzUwMDgwMmYyMDg1NDg3NzI3YmRjOWMwODQ4ZDA4MzYwY2ZmNGZhMTdhOWYifQ%3D%3D; laravel_session=eyJpdiI6InRyQnFUV2xQQnkyeE1mVWc1cnY3NkE9PSIsInZhbHVlIjoicHdEYTZDc01sNlc4M3Y5bzdZVGRoXC9SQWxzZks0R21ybHFRMWZjN2lLQU5MNHNsYnYxUVY0R2ZCUkgwVnBqY1giLCJtYWMiOiJjNWQ1MjhjYTJkMGQ2MzliMGU1MjU4ODRiNGQ2ZDc0OGYzYmI2ZjU0OGMwOTM1YzBjYTkxYzJjMzRiMDhjMzlhIn0%3D',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_SEC_FETCH_DEST' => 'document',
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
      'REMOTE_PORT' => '54662',
      'SERVER_SOFTWARE' => 'nginx/1.15.8',
      'GATEWAY_INTERFACE' => 'CGI/1.1',
      'SERVER_PROTOCOL' => 'HTTP/1.1',
      'DOCUMENT_URI' => '/index.php',
      'REQUEST_URI' => $this->REQUEST_URI.'',
      'SCRIPT_NAME' => '/index.php',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => '',
      'REQUEST_METHOD' => 'GET',
      'QUERY_STRING' => 'draw=1&columns%5B0%5D%5Bdata%5D=open_button&columns%5B0%5D%5Bsearchable%5D=false&columns%5B0%5D%5Borderable%5D=&columns%5B1%5D%5Bdata%5D=drop_down_button&columns%5B1%5D%5Bsearchable%5D=false&columns%5B1%5D%5Borderable%5D=&columns%5B2%5D%5Bdata%5D=related_data&columns%5B2%5D%5Bsearchable%5D=false&columns%5B2%5D%5Borderable%5D=&columns%5B3%5D%5Bdata%5D=value&columns%5B3%5D%5Bsearchable%5D=false&columns%5B4%5D%5Bdata%5D=unit_link&columns%5B4%5D%5Bsearchable%5D=false&columns%5B4%5D%5Borderable%5D=&columns%5B5%5D%5Bdata%5D=variable_name_link&columns%5B5%5D%5Bname%5D=variable.name&columns%5B5%5D%5Borderable%5D=&columns%5B6%5D%5Bdata%5D=start_time&columns%5B6%5D%5Borderable%5D=1&columns%5B7%5D%5Bdata%5D=user_variable_link&columns%5B7%5D%5Bsearchable%5D=false&columns%5B7%5D%5Borderable%5D=&columns%5B8%5D%5Bdata%5D=variable_category_link&columns%5B8%5D%5Bsearchable%5D=false&columns%5B8%5D%5Borderable%5D=&columns%5B9%5D%5Bdata%5D=data_source_link&columns%5B9%5D%5Bsearchable%5D=false&columns%5B9%5D%5Borderable%5D=&columns%5B10%5D%5Bdata%5D=note&columns%5B10%5D%5Borderable%5D=&columns%5B11%5D%5Bdata%5D=errors_link&columns%5B11%5D%5Bsearchable%5D=false&columns%5B11%5D%5Borderable%5D=&columns%5B12%5D%5Bdata%5D=user_id_link&columns%5B12%5D%5Bsearchable%5D=false&columns%5B12%5D%5Borderable%5D=&columns%5B13%5D%5Bdata%5D=updated_at&columns%5B13%5D%5Bsearchable%5D=false&columns%5B13%5D%5Borderable%5D=1&columns%5B14%5D%5Bdata%5D=action&columns%5B14%5D%5Bsearchable%5D=false&columns%5B14%5D%5Borderable%5D=false&order%5B0%5D%5Bcolumn%5D=6&order%5B0%5D%5Bdir%5D=desc&start=0&length=10&search%5Bvalue%5D=&_=1592447070999',
      'FCGI_ROLE' => 'RESPONDER',
      'PHP_SELF' => '/index.php',
      'HTTP_CONTENT_LENGTH' => '',
      'HTTP_CONTENT_TYPE' => '',
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
      '_gid' => 'GA1.1.489049337.1592425993',
      'driftt_aid' => 'c8735c86-68c0-4de2-94ce-73a43605c5c5',
      'DFTT_END_USER_PREV_BOOTSTRAPPED' => 'true',
      'quantimodo_logged_in_af6160480df78a3a6d520187243f05c9' => 'mike|1593635613|de069cf4b3bbf933721060a76259dad7|quantimodo',
      'XDEBUG_PROFILE' => '1',
      'u' => '6ec3e62cc0069a10c6759edd5423df738a0fec0b',
      'XSRF-TOKEN' => 'eyJpdiI6IituZ3VOYURyWFZCYzAyWUVOU3cyMUE9PSIsInZhbHVlIjoiU2dxbXhhRmVkRjE5Z2Z1bkFEamg3U2VLNkYwQ0FmYlcrWkhKeGt1cmVjbmxTejg0bTNHY0x4NnZjUjd6bitIRSIsIm1hYyI6ImI1YTY3ZDc0MzVjZTNhNmZlZmI5YzUwMDgwMmYyMDg1NDg3NzI3YmRjOWMwODQ4ZDA4MzYwY2ZmNGZhMTdhOWYifQ==',
      'laravel_session' => 'eyJpdiI6InRyQnFUV2xQQnkyeE1mVWc1cnY3NkE9PSIsInZhbHVlIjoicHdEYTZDc01sNlc4M3Y5bzdZVGRoXC9SQWxzZks0R21ybHFRMWZjN2lLQU5MNHNsYnYxUVY0R2ZCUkgwVnBqY1giLCJtYWMiOiJjNWQ1MjhjYTJkMGQ2MzliMGU1MjU4ODRiNGQ2ZDc0OGYzYmI2ZjU0OGMwOTM1YzBjYTkxYzJjMzRiMDhjMzlhIn0=',
    ],
                              ]),
   'headers' =>
  QMHeaderBag::__set_state([
     'headers' =>
    [
      'cookie' =>
      [
        0 => '__cfduid=df3bcb60f11c220f65104c37de9ac7ea41592425988; _ga=GA1.1.1415477443.1592425993; _gid=GA1.1.489049337.1592425993; driftt_aid=c8735c86-68c0-4de2-94ce-73a43605c5c5; driftt_aid=c8735c86-68c0-4de2-94ce-73a43605c5c5; DFTT_END_USER_PREV_BOOTSTRAPPED=true; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1593635613%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; XDEBUG_PROFILE=1; u=6ec3e62cc0069a10c6759edd5423df738a0fec0b; XSRF-TOKEN=eyJpdiI6IituZ3VOYURyWFZCYzAyWUVOU3cyMUE9PSIsInZhbHVlIjoiU2dxbXhhRmVkRjE5Z2Z1bkFEamg3U2VLNkYwQ0FmYlcrWkhKeGt1cmVjbmxTejg0bTNHY0x4NnZjUjd6bitIRSIsIm1hYyI6ImI1YTY3ZDc0MzVjZTNhNmZlZmI5YzUwMDgwMmYyMDg1NDg3NzI3YmRjOWMwODQ4ZDA4MzYwY2ZmNGZhMTdhOWYifQ%3D%3D; laravel_session=eyJpdiI6InRyQnFUV2xQQnkyeE1mVWc1cnY3NkE9PSIsInZhbHVlIjoicHdEYTZDc01sNlc4M3Y5bzdZVGRoXC9SQWxzZks0R21ybHFRMWZjN2lLQU5MNHNsYnYxUVY0R2ZCUkgwVnBqY1giLCJtYWMiOiJjNWQ1MjhjYTJkMGQ2MzliMGU1MjU4ODRiNGQ2ZDc0OGYzYmI2ZjU0OGMwOTM1YzBjYTkxYzJjMzRiMDhjMzlhIn0%3D',
      ],
      'accept-language' =>
      [
        0 => 'en-US,en;q=0.9',
      ],
      'accept-encoding' =>
      [
        0 => 'gzip, deflate, br',
      ],
      'sec-fetch-dest' =>
      [
        0 => 'document',
      ],
      'sec-fetch-mode' =>
      [
        0 => 'navigate',
      ],
      'sec-fetch-site' =>
      [
        0 => 'none',
      ],
      'accept' =>
      [
        0 => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      ],
      'user-agent' =>
      [
        0 => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.106 Safari/537.36',
      ],
      'upgrade-insecure-requests' =>
      [
        0 => '1',
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
    [],
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
