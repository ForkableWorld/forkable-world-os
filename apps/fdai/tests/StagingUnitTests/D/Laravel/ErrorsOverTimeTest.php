<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection SpellCheckingInspection */
namespace Tests\StagingUnitTests\D\Laravel;
use App\Exceptions\UnauthorizedException;
use App\Models\UserVariableRelationship;
use App\Override\GeneratedTestRequest;
use App\Override\QMFileBag;
use App\Override\QMHeaderBag;
use App\Override\QMParameterBag;
use App\Override\QMServerBag;
use Illuminate\Testing\TestResponse;
use Tests\LaravelStagingTestCase;
use Tests\QMBaseTestCase;
class ErrorsOverTimeTest extends LaravelStagingTestCase
{
    protected $REQUEST_URI = "/arrilot/load-widget?id=2&name=OverTimeCardChartWidget&params=eyJpdiI6Ik9PS0FHOWFuR3lNQ1Z3Y2VnOW9GNHc9PSIsInZhbHVlIjoibEpUTjdwbjV0UUJNMjJpWVU0WHRyZUtiNlIyXC9iUXFvXC9CeFwvYTBkN3pMTEc1dzh1djFLN1owXC9USktkZHR2Q0V3Y1pyaFwvcDkrMGkrS3MwdmVwYmEwZnFiZ0VKSXRiMzVtTWI3SDVYN1dlM2lGYzVOVW5hbHNiZUQwWWhaM0tqN1hWMTlyZThrdmVDRXlmclJVTTlwY3dpZEpUNGF3aG4wcWpTeEFRMkN6WkhWRDFWOTFDVjNDWHVsdjc0czI1UWtHODFLbFwvMzUrd0tPNjFQYktObEVnZ0V5QzRLSzBzS3krWjJBYnFQdElZcFg0SW5DNjl3R05uRjFWcUxLS0FVNytrQWZid1k5SW0ydFNpemt1ZjNcL05rSnlVR3AyVUZXUHJqS2phMzZNcE1MVHpvOFwvbzkzN2l4NmpadmxTZHArNTlJTkp6bWNCSHR6ZmZYdXhTdkdFOVUxY3M4TDZpM0hsMjR4WHdMVzdcL09LWjJ2bmQ1S3N1TE81XC9UWUZldGJxWXhvUm1cL2xUSTU0S2xFMk82UVAzbDNzdHpaUTI2Zlhidzd6emYxcndadHQzXC9ac2YyRW9BVVdXRUQ2T0JvUVViXC9aVVRWK1Rra0p1OHEyVHN0ZzhyS1BUTG1GaXdkSUdBOVBnWjF2ZkdTNFwvMWtrbnYrMWYzN0xRSW5KVTlcL0NCMFRvYlo1VTAxd0Z6ZVJyWGlJUkJqdE1XbkdnS0c3eE9jK2NqQkE3RUI5N0x3NkJDWml2a0xOSmc5XC9hWnBOc2JPM3FrNE5PaW9uM3BoZEQxZ3pIemtaa1p4aGVlTXdhWnZvK3lpSUZVTkV5U0JQek1PeUtJWkx2UU1HM1h4TVZFbjFIUVRKeTNLRWJsak53eTM1aytOVGQxU2xNNk45blo3ZlNNdzRIUUFlR0oza25wZGJvTXNhTlNwNzh3aVNXTkxGS2IwT1lJTlREUytwQ2I2N3ZZTWdwcCtPRXRrV2RpZFwvYTRiSTJ6TXVMSnNJTFBcL3MrOVdBQ0h4MVRZbWlBMDJyRG9JOG85dVBNbDVld0t5ZERcL2Faeit3SE51TUpIZlRDK2hOYVJMNEQyUkk9IiwibWFjIjoiZWFmNjk1NjY2ZjEzMGIxZTEzMGQyNGE4NWNjOThmMDA3MTI4ZWExYjRhMmU2ODk0ZGRiOTM0NzRhOGI3MTFmNSJ9";
    public function testErrorsOverTimeWidgetAsRegularUser(): void{
        $widget = UserVariableRelationship::getErrorsOverTimeWidget();
        $chart = $widget->getHighchart();
        $description = $chart->getSubtitleAttribute();
        $this->assertEquals("Individual Case Studies that have an error", $description);
        $this->actAsTestUser();
        $response = $this->stagingRequest(200, "See Individual Case Studies that have an error");
        $this->checkTestDuration(10);
        $this->checkQueryCount(6);
    }
    public function testErrorsOverTimeWidgetAsAdmin(): void{
        $this->actAsAdmin();
        $response = $this->stagingRequest(200, "See Individual Case Studies that have an error");
        $this->checkTestDuration(15);
        $this->checkQueryCount(5);
    }
    public function testErrorsOverTimeWidgetWithoutAuth(): void{
        $this->assertGuest();
        QMBaseTestCase::setExpectedRequestException(UnauthorizedException::class);
        $response = $this->stagingRequest(302, null);
        $this->checkTestDuration(9);
        $this->checkQueryCount(3);
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
      'id' => '2',
      'name' => 'OverTimeCardChartWidget',
      'params' => 'eyJpdiI6Ik9PS0FHOWFuR3lNQ1Z3Y2VnOW9GNHc9PSIsInZhbHVlIjoibEpUTjdwbjV0UUJNMjJpWVU0WHRyZUtiNlIyXC9iUXFvXC9CeFwvYTBkN3pMTEc1dzh1djFLN1owXC9USktkZHR2Q0V3Y1pyaFwvcDkrMGkrS3MwdmVwYmEwZnFiZ0VKSXRiMzVtTWI3SDVYN1dlM2lGYzVOVW5hbHNiZUQwWWhaM0tqN1hWMTlyZThrdmVDRXlmclJVTTlwY3dpZEpUNGF3aG4wcWpTeEFRMkN6WkhWRDFWOTFDVjNDWHVsdjc0czI1UWtHODFLbFwvMzUrd0tPNjFQYktObEVnZ0V5QzRLSzBzS3krWjJBYnFQdElZcFg0SW5DNjl3R05uRjFWcUxLS0FVNytrQWZid1k5SW0ydFNpemt1ZjNcL05rSnlVR3AyVUZXUHJqS2phMzZNcE1MVHpvOFwvbzkzN2l4NmpadmxTZHArNTlJTkp6bWNCSHR6ZmZYdXhTdkdFOVUxY3M4TDZpM0hsMjR4WHdMVzdcL09LWjJ2bmQ1S3N1TE81XC9UWUZldGJxWXhvUm1cL2xUSTU0S2xFMk82UVAzbDNzdHpaUTI2Zlhidzd6emYxcndadHQzXC9ac2YyRW9BVVdXRUQ2T0JvUVViXC9aVVRWK1Rra0p1OHEyVHN0ZzhyS1BUTG1GaXdkSUdBOVBnWjF2ZkdTNFwvMWtrbnYrMWYzN0xRSW5KVTlcL0NCMFRvYlo1VTAxd0Z6ZVJyWGlJUkJqdE1XbkdnS0c3eE9jK2NqQkE3RUI5N0x3NkJDWml2a0xOSmc5XC9hWnBOc2JPM3FrNE5PaW9uM3BoZEQxZ3pIemtaa1p4aGVlTXdhWnZvK3lpSUZVTkV5U0JQek1PeUtJWkx2UU1HM1h4TVZFbjFIUVRKeTNLRWJsak53eTM1aytOVGQxU2xNNk45blo3ZlNNdzRIUUFlR0oza25wZGJvTXNhTlNwNzh3aVNXTkxGS2IwT1lJTlREUytwQ2I2N3ZZTWdwcCtPRXRrV2RpZFwvYTRiSTJ6TXVMSnNJTFBcL3MrOVdBQ0h4MVRZbWlBMDJyRG9JOG85dVBNbDVld0t5ZERcL2Faeit3SE51TUpIZlRDK2hOYVJMNEQyUkk9IiwibWFjIjoiZWFmNjk1NjY2ZjEzMGIxZTEzMGQyNGE4NWNjOThmMDA3MTI4ZWExYjRhMmU2ODk0ZGRiOTM0NzRhOGI3MTFmNSJ9',
    ),
  )),
   'query' =>
  QMParameterBag::__set_state(array(
     'parameters' =>
    array (
      'id' => '2',
      'name' => 'OverTimeCardChartWidget',
      'params' => 'eyJpdiI6Ik9PS0FHOWFuR3lNQ1Z3Y2VnOW9GNHc9PSIsInZhbHVlIjoibEpUTjdwbjV0UUJNMjJpWVU0WHRyZUtiNlIyXC9iUXFvXC9CeFwvYTBkN3pMTEc1dzh1djFLN1owXC9USktkZHR2Q0V3Y1pyaFwvcDkrMGkrS3MwdmVwYmEwZnFiZ0VKSXRiMzVtTWI3SDVYN1dlM2lGYzVOVW5hbHNiZUQwWWhaM0tqN1hWMTlyZThrdmVDRXlmclJVTTlwY3dpZEpUNGF3aG4wcWpTeEFRMkN6WkhWRDFWOTFDVjNDWHVsdjc0czI1UWtHODFLbFwvMzUrd0tPNjFQYktObEVnZ0V5QzRLSzBzS3krWjJBYnFQdElZcFg0SW5DNjl3R05uRjFWcUxLS0FVNytrQWZid1k5SW0ydFNpemt1ZjNcL05rSnlVR3AyVUZXUHJqS2phMzZNcE1MVHpvOFwvbzkzN2l4NmpadmxTZHArNTlJTkp6bWNCSHR6ZmZYdXhTdkdFOVUxY3M4TDZpM0hsMjR4WHdMVzdcL09LWjJ2bmQ1S3N1TE81XC9UWUZldGJxWXhvUm1cL2xUSTU0S2xFMk82UVAzbDNzdHpaUTI2Zlhidzd6emYxcndadHQzXC9ac2YyRW9BVVdXRUQ2T0JvUVViXC9aVVRWK1Rra0p1OHEyVHN0ZzhyS1BUTG1GaXdkSUdBOVBnWjF2ZkdTNFwvMWtrbnYrMWYzN0xRSW5KVTlcL0NCMFRvYlo1VTAxd0Z6ZVJyWGlJUkJqdE1XbkdnS0c3eE9jK2NqQkE3RUI5N0x3NkJDWml2a0xOSmc5XC9hWnBOc2JPM3FrNE5PaW9uM3BoZEQxZ3pIemtaa1p4aGVlTXdhWnZvK3lpSUZVTkV5U0JQek1PeUtJWkx2UU1HM1h4TVZFbjFIUVRKeTNLRWJsak53eTM1aytOVGQxU2xNNk45blo3ZlNNdzRIUUFlR0oza25wZGJvTXNhTlNwNzh3aVNXTkxGS2IwT1lJTlREUytwQ2I2N3ZZTWdwcCtPRXRrV2RpZFwvYTRiSTJ6TXVMSnNJTFBcL3MrOVdBQ0h4MVRZbWlBMDJyRG9JOG85dVBNbDVld0t5ZERcL2Faeit3SE51TUpIZlRDK2hOYVJMNEQyUkk9IiwibWFjIjoiZWFmNjk1NjY2ZjEzMGIxZTEzMGQyNGE4NWNjOThmMDA3MTI4ZWExYjRhMmU2ODk0ZGRiOTM0NzRhOGI3MTFmNSJ9',
    ),
  )),
   'server' =>
  QMServerBag::__set_state(array(
     'parameters' =>
    array (
      'USER' => 'vagrant',
      'HOME' => '/home/vagrant',
      'HTTP_COOKIE' => '__cfduid=df3bcb60f11c220f65104c37de9ac7ea41592425988; _ga=GA1.1.1415477443.1592425993; driftt_aid=c8735c86-68c0-4de2-94ce-73a43605c5c5; DFTT_END_USER_PREV_BOOTSTRAPPED=true; u=6ec3e62cc0069a10c6759edd5423df738a0fec0b; _ga=GA1.2.2014257657.1592502511; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; driftt_eid=230; final_callback_url=http%3A%2F%2Flocalhost%3A63348%2Fionic%2Fsrc%2Findex.html%23%2Fapp%2Flogin%3Fclient_id%3Dquantimodo%26quantimodoAccessToken%3Dmike-test-token%26quantimodoUserId%3D230; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1593827300%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; XDEBUG_PROFILE=XDEBUG_ECLIPSE; XSRF-TOKEN=eyJpdiI6IitoXC9oUHhGMzVOTDlsQmZcL2RlTVBzZz09IiwidmFsdWUiOiJoejlpTVQ1RlloVWJaQThta3hOa0tpc3JJb0tHdTZoTkhiXC9TbFJ1cDBNWktMcXYxTG9MeWpHclp5NHJzdDJVaiIsIm1hYyI6ImFiMDBhMmJhNTc4NGY0NmUyNzZiZmNmNjVjMGY0ZjVkNmNkNDZjM2ZiMTcxYWE1NTc5YTQwNWU4MjM5M2U3NTgifQ%3D%3D; laravel_session=eyJpdiI6InQyaVwveTA2QUNVeVdSNktSOFduRjl3PT0iLCJ2YWx1ZSI6ImxneXBtXC9pS0pzcitUZHpiNU1xc1dtYjAxMU4yVDVPb0d0TmQxTUd6UW1PVjF0dEJNWjJFaHdnTmtjODYwMVRyIiwibWFjIjoiODJjYTc1YjlmZTA0MWY2MzJlYzFkMGZlNTg1MGMwMTVmNmQyZmI2ZDY1Mjg3ODU4YzljY2E1MzBkNDVjNzM2NSJ9',
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
      'REMOTE_PORT' => '57417',
      'SERVER_SOFTWARE' => 'nginx/1.15.8',
      'GATEWAY_INTERFACE' => 'CGI/1.1',
      'SERVER_PROTOCOL' => 'HTTP/1.1',
      'DOCUMENT_URI' => '/index.php',
      'REQUEST_URI' => $this->REQUEST_URI.'',
      'SCRIPT_NAME' => '/index.php',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => '',
      'REQUEST_METHOD' => 'GET',
      'QUERY_STRING' => 'id=2&name=OverTimeCardChartWidget&params=eyJpdiI6Ik9PS0FHOWFuR3lNQ1Z3Y2VnOW9GNHc9PSIsInZhbHVlIjoibEpUTjdwbjV0UUJNMjJpWVU0WHRyZUtiNlIyXC9iUXFvXC9CeFwvYTBkN3pMTEc1dzh1djFLN1owXC9USktkZHR2Q0V3Y1pyaFwvcDkrMGkrS3MwdmVwYmEwZnFiZ0VKSXRiMzVtTWI3SDVYN1dlM2lGYzVOVW5hbHNiZUQwWWhaM0tqN1hWMTlyZThrdmVDRXlmclJVTTlwY3dpZEpUNGF3aG4wcWpTeEFRMkN6WkhWRDFWOTFDVjNDWHVsdjc0czI1UWtHODFLbFwvMzUrd0tPNjFQYktObEVnZ0V5QzRLSzBzS3krWjJBYnFQdElZcFg0SW5DNjl3R05uRjFWcUxLS0FVNytrQWZid1k5SW0ydFNpemt1ZjNcL05rSnlVR3AyVUZXUHJqS2phMzZNcE1MVHpvOFwvbzkzN2l4NmpadmxTZHArNTlJTkp6bWNCSHR6ZmZYdXhTdkdFOVUxY3M4TDZpM0hsMjR4WHdMVzdcL09LWjJ2bmQ1S3N1TE81XC9UWUZldGJxWXhvUm1cL2xUSTU0S2xFMk82UVAzbDNzdHpaUTI2Zlhidzd6emYxcndadHQzXC9ac2YyRW9BVVdXRUQ2T0JvUVViXC9aVVRWK1Rra0p1OHEyVHN0ZzhyS1BUTG1GaXdkSUdBOVBnWjF2ZkdTNFwvMWtrbnYrMWYzN0xRSW5KVTlcL0NCMFRvYlo1VTAxd0Z6ZVJyWGlJUkJqdE1XbkdnS0c3eE9jK2NqQkE3RUI5N0x3NkJDWml2a0xOSmc5XC9hWnBOc2JPM3FrNE5PaW9uM3BoZEQxZ3pIemtaa1p4aGVlTXdhWnZvK3lpSUZVTkV5U0JQek1PeUtJWkx2UU1HM1h4TVZFbjFIUVRKeTNLRWJsak53eTM1aytOVGQxU2xNNk45blo3ZlNNdzRIUUFlR0oza25wZGJvTXNhTlNwNzh3aVNXTkxGS2IwT1lJTlREUytwQ2I2N3ZZTWdwcCtPRXRrV2RpZFwvYTRiSTJ6TXVMSnNJTFBcL3MrOVdBQ0h4MVRZbWlBMDJyRG9JOG85dVBNbDVld0t5ZERcL2Faeit3SE51TUpIZlRDK2hOYVJMNEQyUkk9IiwibWFjIjoiZWFmNjk1NjY2ZjEzMGIxZTEzMGQyNGE4NWNjOThmMDA3MTI4ZWExYjRhMmU2ODk0ZGRiOTM0NzRhOGI3MTFmNSJ9',
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
      'driftt_aid' => 'c8735c86-68c0-4de2-94ce-73a43605c5c5',
      'DFTT_END_USER_PREV_BOOTSTRAPPED' => 'true',
      'u' => '6ec3e62cc0069a10c6759edd5423df738a0fec0b',
      'driftt_eid' => '230',
      'final_callback_url' => 'http://localhost:63348/ionic/src/index.html#/app/login?client_id=quantimodo&quantimodoAccessToken=mike-test-token&quantimodoUserId=230',
      'quantimodo_logged_in_af6160480df78a3a6d520187243f05c9' => 'mike|1593827300|de069cf4b3bbf933721060a76259dad7|quantimodo',
      'XDEBUG_PROFILE' => 'XDEBUG_ECLIPSE',
      'XSRF-TOKEN' => 'eyJpdiI6IitoXC9oUHhGMzVOTDlsQmZcL2RlTVBzZz09IiwidmFsdWUiOiJoejlpTVQ1RlloVWJaQThta3hOa0tpc3JJb0tHdTZoTkhiXC9TbFJ1cDBNWktMcXYxTG9MeWpHclp5NHJzdDJVaiIsIm1hYyI6ImFiMDBhMmJhNTc4NGY0NmUyNzZiZmNmNjVjMGY0ZjVkNmNkNDZjM2ZiMTcxYWE1NTc5YTQwNWU4MjM5M2U3NTgifQ==',
      'laravel_session' => 'eyJpdiI6InQyaVwveTA2QUNVeVdSNktSOFduRjl3PT0iLCJ2YWx1ZSI6ImxneXBtXC9pS0pzcitUZHpiNU1xc1dtYjAxMU4yVDVPb0d0TmQxTUd6UW1PVjF0dEJNWjJFaHdnTmtjODYwMVRyIiwibWFjIjoiODJjYTc1YjlmZTA0MWY2MzJlYzFkMGZlNTg1MGMwMTVmNmQyZmI2ZDY1Mjg3ODU4YzljY2E1MzBkNDVjNzM2NSJ9',
    ),
  )),
   'headers' =>
  QMHeaderBag::__set_state(array(
     'headers' =>
    array (
      'cookie' =>
      array (
        0 => '__cfduid=df3bcb60f11c220f65104c37de9ac7ea41592425988; _ga=GA1.1.1415477443.1592425993; driftt_aid=c8735c86-68c0-4de2-94ce-73a43605c5c5; DFTT_END_USER_PREV_BOOTSTRAPPED=true; u=6ec3e62cc0069a10c6759edd5423df738a0fec0b; _ga=GA1.2.2014257657.1592502511; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; driftt_eid=230; final_callback_url=http%3A%2F%2Flocalhost%3A63348%2Fionic%2Fsrc%2Findex.html%23%2Fapp%2Flogin%3Fclient_id%3Dquantimodo%26quantimodoAccessToken%3Dmike-test-token%26quantimodoUserId%3D230; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1593827300%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; XDEBUG_PROFILE=XDEBUG_ECLIPSE; XSRF-TOKEN=eyJpdiI6IitoXC9oUHhGMzVOTDlsQmZcL2RlTVBzZz09IiwidmFsdWUiOiJoejlpTVQ1RlloVWJaQThta3hOa0tpc3JJb0tHdTZoTkhiXC9TbFJ1cDBNWktMcXYxTG9MeWpHclp5NHJzdDJVaiIsIm1hYyI6ImFiMDBhMmJhNTc4NGY0NmUyNzZiZmNmNjVjMGY0ZjVkNmNkNDZjM2ZiMTcxYWE1NTc5YTQwNWU4MjM5M2U3NTgifQ%3D%3D; laravel_session=eyJpdiI6InQyaVwveTA2QUNVeVdSNktSOFduRjl3PT0iLCJ2YWx1ZSI6ImxneXBtXC9pS0pzcitUZHpiNU1xc1dtYjAxMU4yVDVPb0d0TmQxTUd6UW1PVjF0dEJNWjJFaHdnTmtjODYwMVRyIiwibWFjIjoiODJjYTc1YjlmZTA0MWY2MzJlYzFkMGZlNTg1MGMwMTVmNmQyZmI2ZDY1Mjg3ODU4YzljY2E1MzBkNDVjNzM2NSJ9',
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
