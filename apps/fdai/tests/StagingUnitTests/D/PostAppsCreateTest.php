<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection SpellCheckingInspection */
namespace Tests\StagingUnitTests\D;
use App\Models\Application;
use App\Properties\User\UserIdProperty;
use Tests\LaravelStagingTestCase;
use App\Override\QMHeaderBag;
use App\Override\QMParameterBag;
use App\Override\GeneratedTestRequest;
use App\Override\QMServerBag;
use Illuminate\Testing\TestResponse;
class PostAppsCreateTest extends LaravelStagingTestCase
{
    protected $REQUEST_URI = "/api/v2/apps/create";
	private string $clientId;   
	public function setUp(): void{
		$time = time();
		$this->clientId = 'test-app'.$time;
		parent::setUp(); 
	}
	public function testPostAppsCreateAsRegularUser(): void{
        $this->actAsTestUser();
		$path = '/api/v2/apps/'.$this->clientId."/edit";
        $this->stagingRequest(302, $path);
        $response = $this->getTestResponse();
		$response->isRedirect($path);
		$response = $this->get($response->headers->get('Location'));
		$response->assertStatus(200);
		$response->assertSee('Integration');
        $this->checkTestDuration(7);
        $this->checkQueryCount(24);
		$newApp = Application::whereUserId(UserIdProperty::USER_ID_TEST_USER)
		                     ->orderBy(Application::CREATED_AT, 'desc')
		                     ->first();
		$users = $this->getApiV6('users', ['client_id' => $newApp->client_id]);
		$this->assertEquals(2, count($users));
		foreach($users as $user){
			$this->assertNotNull($user['access_token']);
		}
    }
    public function testPostAppsCreateWithoutAuth(): void{
        $this->expectAuthenticationException();
		$this->stagingRequest(401, 'Unauthenticated');
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
      '_token' => 'test-token',
      'app_display_name' => $this->clientId,
      'client_id' => $this->clientId,
      'app_description' => $this->clientId,
      'homepage_url' => 'https://'.$this->clientId.'.com',
      'redirect_uris' => 'http://localhost:8080/callback',
    ),
  )),
   'server' => 
  QMServerBag::__set_state(array(
     'parameters' => 
    array (
      'USER' => 'vagrant',
      'HOME' => '/home/vagrant',
      'HTTP_COOKIE' => 'XDEBUG_SESSION=PHPSTORM; intended_url=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Fv2%2Fapps%3FXDEBUG_SESSION_START%3DPHPSTORM; _ga=GA1.1.1659613996.1639418721; _gid=GA1.1.2105417521.1639418721; _gat=1; drift_campaign_refresh=7ba53de6-9f7b-4aa4-a9a1-302cb3de7437; drift_aid=3116b55e-759c-422a-be0c-c35f6238066e; driftt_aid=3116b55e-759c-422a-be0c-c35f6238066e; remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6IkhsWGtcL2pMSDNuVkl1TzU3cGJpdzJnPT0iLCJ2YWx1ZSI6ImtId0pXKzd1RWorcmFja3ZjWWU5T2RNRERjbjlUeFZ3MSs1V0pOVFVLRzJ3V3llcTVldFwvWTNtbEVxMFlYOElvWGFkd2ZhcW5QXC92cnlCSUt4Q1Zndk9zb0RhZG1neUN0Z3pJVUFxWm1IUDk2TzFpVFVOcXU2ek5rU2YxRzJ4N281VlZlM29MWVQxVG9rcWlmemtDVHBGcWt2SjJHM1o4U2ppVlAyWWxYRm5JPSIsIm1hYyI6IjI0YjhkMzBmZTc2YzJmMTY0ZDY1ZTg2ZGVjN2YzYjY1NjQ3OTE0ZjY2NDQ5ODJlMDlhMjM1MWRjMmE5Njk4YWYifQ%3D%3D; RgIm3Lnophs0tQoyMoNJofSWQ54Zky7m9u1tZnjl=eyJpdiI6Ink5MnpqMDB5Yk93SklMMmFUQ1JqSEE9PSIsInZhbHVlIjoicVhFTGRDYmI0Y1RtRnJHZ2R5Q1BtaHBPRFwvMFwvTzh3eFl0bFg2bExRM1cwRjVQUWZkVjNlZXJtM0tUT1Ixb2JhZFM4NnFNQmlnT3Q0OE1kdThOM0x0T1ZET1BTdmQyWGtjaUJad2t1NDZMNHMrUTY3V0djYnhLQ3g2azRxWmtEUm5PaUtXY1FydEJHeVwvZnJcL0JBcW5QTTJDQ3ZrMzJURlpzSzk2aVo5VUJuTW9mWVwvOFEwTHBreXIzSnVwa2tYN25hUWtGWUEzNkNMSXUxd0RWWnVxeG9XQ0pzNnZqcDRsZTJmU1hOZEdOSkJZRjVBMUI0YVhGbmh5MjAzTUtzc1NDUXUrQnBrcWM1SnhiZXA3bGhHeEpCekFKeWdBOXBFeDU3VTg0YkE0VlpvMGRWT1ZxcEJjZm9heFdMc1MzcVZHeEN6N0ZEQndJNU5jYUdsVVwvQkE4RUp2aVwvdmZlSkNaczlJdWttQ2RONDJHaDVocDhoSHA0ZUc2dTUyMXdjeER3eVlHUDJLa2FueW1uV3RBUzJXMlNKYkI3S2puUTg3TTY4YzJVazJcL0k2YVpqS0lZSkQzNkhqZ095cEZXMEZ5UWZBV2c1WGZhekxQTEYxNnd0eDJjY21rNXBBZW42S1VmbkppUHA1bk9GZGNCRkl6Mzd3ZjkxR0tcL3VMOXNEY056ZW9WTE0xNGtqZzg0eGQ3WnVPdUp3RW5kSzg3ZFp3Z2hEdnhlWTY3MCsrdEVYR01PZFpTQ1wvNktQQ3N6KzdFWEtuTGFLT3E5WUxRcEFidStVRllwZGtqRW9icUFKV1ZoWUdINEJyOUlMTzRPS3lpV29yeW03aFhUQW9rZllacEcybzl0c2lVeFVxRTZLNUI1SDBlQ1RlRjhBQ2hnS3N3OTNRRHBEKzJhVGFqck52TEp0a2dUYXdob0R3WVdsWENTWXpcL0FTT1ArWFwvVzR1cjgxeGVSM2Ntc1Vva0Joam0ySjdmRnowb245U2lTVVhia1Y0a29DTUZ1RWlNSnVoY0JqUk4yOFZXT2xvMEFBeUFXSVZRQ3F4ZE1wZXBMSVhNSEhiM1B0S0luRzBldGhEdTg0RUtSbU9nNDdhUndmZnJOS1IwdFVQVm5vdldzUXFvaVdWcXU4TVlXYVFnaHY4eGJrUjRuYTd5K2dpb3Jaa29jRWFKYUQzNTRBSXNnMHRCV3JJKzR4VTJkVENuRUZuZjd5UEFYejVEVW1ZWnZcL21IYnZCMnc4ak5pTXlPaWY3K2JSUFcrRTI5Sms4aW8rbVpVcHZCRjBqdlNIdGNSdzEyNTFTTitXVTE4Ym9mc0krdndtXC9NT1A5V3V2YVZlb1YwanA2QUtPcnNyQ3hDWkdNT1dpdmNsNFh1NWtONXRra1YwZjIzRDdmTGpXcUdyODVnUWFxWHBRSUVkajJBZVFzN0UwaGdwczVxMUtVV2V1eTJrODRwK29cL3VyRlY5N0pSbHltblJaY21LSHFwVEJUeU5NbnVFXC9yV2doemtlUEQzRDNJUUhGaWlEb25JOWplMGxlTkpwK0dWdERmYWlZR1l2c1pkZkFJY1dVQSs0b2lReEtycUlHS3duVzA2aWpyTkI1bGJab0ViWE1UVGoxWWlQMHRBQTJGMlJcLzU3YTQrTU51Unl4aXF2NUQ3ZkJRcW9pQnF2WGZaYlNMUU5iRzFlaTF5OWc2aHE4MTU1UmJpQUs2NGcrZklHUDlyWmlrQm85WnJjWThOclFyNHczUE12cFJ3VE1yOXdCUzBuOGNXWUJwdGRtUW13dW1XY2xBOTRUMGxDUjhRMXg2cmE1TXpqbVBERW01YWVjMElKUVwvZEdcL2tSZFRjYUVRdzJzK0xvRFBVOFJzSnJkZUhVRGlzeXgrZTRMOEpqZzRIY3BndW9GOW84aktEZnNmTDlVNmpCdTBacjZYVnRKY3RqSFVQd0xkcXRSTkE3Sm5WYXBGVTlpRG5DYmExNzE2WTFhMFpLNm9SZWptQmxOS0NtZit5NmRWSXBuVE1hcTRBT0tDWlwvOE1BT3F2VWVhd242RDZIcDE0M016WkV3MTZFM3BkaXFEUVFOeExSZXNqN0pMMXNqa0wzdTQ2WkJhMUswXC9na25tckVBck5wMm4wbEVScmg5bmt1WFQ1SXl4czFSbUV5aTEySkFkUFwvY1ZtTUlaQUdYZFZMallnRnlaTG9IT2Fvaktra3FJV3haY3NNSG1UZHI4OXAzQ1daSHhTTlVMSk95NTMyRnNqemp5bitBVUlScFZ6YzlPTW5yNzJmQ0V0czkrRHZvZjRzVnNuc1NGamp4VHZrNUI0dWlINHNkSGlTeGpXYlwvNlVnV2F3bTJqbk5oRVhnNVVEUmdqWkpxaE00UkVjT09VWGxYclpkS0VyV3VCb3MwbE1jNHdpMU15QkZ1QXpOcE83NkJzSnFkTTBqdFRmd1RFc3cwNlBHbkZMNnZJM3FCSENBckp6aG9aSjFCYllYaGpDSDMwcjNmT3BnM0s0SmcwclJRNEQ5ODdxaExIVHBWOGd1QzZXZWliZFhFZWtUdDdnUStFK0R0UE9WZ05JT1Z0aVwvQ1VmSVwvRE1cL0lNbUlcL0dtVmc1MVwvblVoYjNQaHdMdmwrc2lZbURPcHNoekJ1Qjk1azBtc3BTZmQwWnROMVlRVUZBblV5blBBVk5cL0tyRWdEeGNQSWRzcUlGcUpFMHNxVytnXC9FbmsxblJrU0NLcllRNzVDSEJSSm1Ia0Vrbm5FQmJEb0xtaTh6U2Z1U052NEV2azBhUTlDTWc3TFhyTVhRYXFXQzl0M2JHZkJLZjFHTDRpZlBPeEx4WTVHRmtXRWg1Y1NybUhyd3VrcXM2U1NacU1WUTNtdXdBMHZvcTZYWGdIUTI1ODhNUWxcL0VWc0hheXRWM1dKWnNPcGdEbkM2TnBGakpMYlFiWldRQWlHeUhEQnZiRGlTNE1BK3ZtYTRXd1VDeUF4WUZzU0Q1WTl3Qm1FZWR3Q3hPeEVPQW9RUVNNYU91bnFUUkU5R1M5SXhueFF5OCtQZGQ4cjlxVEF6M0NXMFZhdjZDb0srMUNYTUNDTkJneTE4OHM1MGo2ZisycWdLMWlzbE9ZME5RSWp1KzJhamt2K1NkQzdxWnByeGZ0MEFvOGt3QlNRNDl6Nk0rbTNhUnl5N3lzMkhGWlMxN2swQzlHQmdUN2J3N3VEdVhSNWtUVFFnbitZdWE1SE1LXC9ReEhKb3lrbjBaVlhoanlsY1VDa3NhUzQrYkV6cWk0Y1czeDFlWER3Z01GMG1yVXUzNllmNHJheE55MUtKdnJmU2xPY3FnRGpiSmx3b2pRXC9QOCtIcXplYWJmOXRSaDgxRjU4WGtoY0JYU1FDeWFxeXVZRDhaV3dYTlpZTWlxd3hibFJPQVwvRDY4emkwcmNIdFhsR05lXC9KWlRFUnV1VWpMQk5xZ3laTU5HVlwvOXZYZW1oXC91SlJZYU9PNk9ZSUxiTG9xTmdCZUx5QTFVd1BTYVN6eVVqWVJreUVQS2F3TG5BTU13cUxCV1BycDFqV0FhYXRIZVlBR1ZWTFloS0ljaDRjdWhjUWRKRk5WdWJDVEJaZ0dxeHZlWFJzcDZ3MnlaXC9yYWtYc3RcL0JsbkxhTmFcL1lxS2FQUXc9PSIsIm1hYyI6ImVhYjNiNjkzYTQxNzlhMDMyNTA5YTk4MWE4NDkzNzdhZjE1ZjYzZGYwNWQ2MTA0ZjgwZGEwYmQ4ZjJhOWQyMDgifQ%3D%3D; XSRF-TOKEN=eyJpdiI6IkhcL2ZsTFNmeDZ1QVBLXC9KWjFmMGs3QT09IiwidmFsdWUiOiJhNXB1NVZ1MEt3ckx1REp4ZzRwdFJQWnpBWTRTZXNiWm5teDJXaFN4V2hUR1hrRVFBVTlKUFJnYXg5eWs1MUp1IiwibWFjIjoiN2VkMzZhNTM0MGNkNmUyMDM3OTI5NmRhODY5NDhjNzQzZjkxYTM2YTlhMWJhZDcyNGE4OGZlM2NhMTc3OWM3YSJ9; laravel_session=eyJpdiI6ImNZMXZXMnhJR0Q5SHcxUUpmYm03d2c9PSIsInZhbHVlIjoiUEdid0V0RytidmhvSlpiSWlLRGdUZ1RBeVRCWkJiYUV2Y2drOXZQaG1qOE80OWVhcmpcL0cwdU1McG0xMldvOWwiLCJtYWMiOiI3MTU4YzRmNDgzYjc1MGQ3YTRlZDQyOWRiNjRjOGVlMmEwY2Q2N2I5NjA5YWFhZjZkNDNmNGFiN2FmNzc4OWRlIn0%3D; login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6Iml1bjlZb1FNSEdJS3Vwa0o1bGRRd1E9PSIsInZhbHVlIjoiOStobkpKbDd1ZFBLbzVcL2NENElZZEE9PSIsIm1hYyI6IjExZjcyZjIzOTcwMzk2M2NmNTMwNDM0NWQwODUxOWRiMGViNmQzYWI5NTQ4YjE2MmQyMjYwMGMwYWM5OGIxNWUifQ%3D%3D; __cypress.initial=true',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip',
      'HTTP_REFERER' => getenv('APP_URL').$this->REQUEST_URI.'',
      'HTTP_SEC_FETCH_DEST' => 'iframe',
      'HTTP_SEC_FETCH_MODE' => 'navigate',
      'HTTP_SEC_FETCH_SITE' => 'same-origin',
      'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.93 Safari/537.36',
      'HTTP_CONTENT_TYPE' => 'multipart/form-data; boundary=----WebKitFormBoundaryKzk1usS7Gg2T21vo',
      'HTTP_ORIGIN' => getenv('APP_URL').'',
      'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
      'HTTP_SEC_CH_UA_PLATFORM' => '"Windows"',
      'HTTP_SEC_CH_UA_MOBILE' => '?0',
      'HTTP_SEC_CH_UA' => '" Not A;Brand";v="99", "Chromium";v="96", "Google Chrome";v="96"',
      'HTTP_CACHE_CONTROL' => 'max-age=0',
      'HTTP_CONTENT_LENGTH' => '794',
      'HTTP_HOST' => 'local.quantimo.do',
      'HTTP_CONNECTION' => 'keep-alive',
      'REDIRECT_STATUS' => '200',
      'SERVER_NAME' => 'local.quantimo.do',
      'SERVER_PORT' => '443',
      'SERVER_ADDR' => '127.0.0.1',
      'REMOTE_PORT' => '52224',
      'REMOTE_ADDR' => '127.0.0.1',
      'SERVER_SOFTWARE' => 'nginx/1.19.7',
      'GATEWAY_INTERFACE' => 'CGI/1.1',
      'HTTPS' => 'on',
      'REQUEST_SCHEME' => 'https',
      'SERVER_PROTOCOL' => 'HTTP/1.1',
      'DOCUMENT_ROOT' => '/www/wwwroot/qm-api/public',
      'DOCUMENT_URI' => '/index.php',
      'REQUEST_URI' => $this->REQUEST_URI.'',
      'SCRIPT_NAME' => '/index.php',
      'CONTENT_LENGTH' => '794',
      'CONTENT_TYPE' => 'multipart/form-data; boundary=----WebKitFormBoundaryKzk1usS7Gg2T21vo',
      'REQUEST_METHOD' => 'POST',
      'SCRIPT_FILENAME' => '/www/wwwroot/qm-api/public/index.php',
      'FCGI_ROLE' => 'RESPONDER',
      'PHP_SELF' => '/index.php',
      'REQUEST_TIME_FLOAT' => 1639418733.934387,
      'REQUEST_TIME' => 1639418733,
    ),
  )),
   'cookies' => 
  QMParameterBag::__set_state(array(
     'parameters' => 
    array (
      'XDEBUG_SESSION' => 'PHPSTORM',
      'intended_url' => getenv('APP_URL').'/api/v2/apps?XDEBUG_SESSION_START=PHPSTORM',
      '_ga' => 'GA1.1.1659613996.1639418721',
      '_gid' => 'GA1.1.2105417521.1639418721',
      '_gat' => '1',
      'drift_campaign_refresh' => '7ba53de6-9f7b-4aa4-a9a1-302cb3de7437',
      'drift_aid' => '3116b55e-759c-422a-be0c-c35f6238066e',
      'driftt_aid' => '3116b55e-759c-422a-be0c-c35f6238066e',
      'remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d' => 'eyJpdiI6IkhsWGtcL2pMSDNuVkl1TzU3cGJpdzJnPT0iLCJ2YWx1ZSI6ImtId0pXKzd1RWorcmFja3ZjWWU5T2RNRERjbjlUeFZ3MSs1V0pOVFVLRzJ3V3llcTVldFwvWTNtbEVxMFlYOElvWGFkd2ZhcW5QXC92cnlCSUt4Q1Zndk9zb0RhZG1neUN0Z3pJVUFxWm1IUDk2TzFpVFVOcXU2ek5rU2YxRzJ4N281VlZlM29MWVQxVG9rcWlmemtDVHBGcWt2SjJHM1o4U2ppVlAyWWxYRm5JPSIsIm1hYyI6IjI0YjhkMzBmZTc2YzJmMTY0ZDY1ZTg2ZGVjN2YzYjY1NjQ3OTE0ZjY2NDQ5ODJlMDlhMjM1MWRjMmE5Njk4YWYifQ==',
      'RgIm3Lnophs0tQoyMoNJofSWQ54Zky7m9u1tZnjl' => 'eyJpdiI6Ink5MnpqMDB5Yk93SklMMmFUQ1JqSEE9PSIsInZhbHVlIjoicVhFTGRDYmI0Y1RtRnJHZ2R5Q1BtaHBPRFwvMFwvTzh3eFl0bFg2bExRM1cwRjVQUWZkVjNlZXJtM0tUT1Ixb2JhZFM4NnFNQmlnT3Q0OE1kdThOM0x0T1ZET1BTdmQyWGtjaUJad2t1NDZMNHMrUTY3V0djYnhLQ3g2azRxWmtEUm5PaUtXY1FydEJHeVwvZnJcL0JBcW5QTTJDQ3ZrMzJURlpzSzk2aVo5VUJuTW9mWVwvOFEwTHBreXIzSnVwa2tYN25hUWtGWUEzNkNMSXUxd0RWWnVxeG9XQ0pzNnZqcDRsZTJmU1hOZEdOSkJZRjVBMUI0YVhGbmh5MjAzTUtzc1NDUXUrQnBrcWM1SnhiZXA3bGhHeEpCekFKeWdBOXBFeDU3VTg0YkE0VlpvMGRWT1ZxcEJjZm9heFdMc1MzcVZHeEN6N0ZEQndJNU5jYUdsVVwvQkE4RUp2aVwvdmZlSkNaczlJdWttQ2RONDJHaDVocDhoSHA0ZUc2dTUyMXdjeER3eVlHUDJLa2FueW1uV3RBUzJXMlNKYkI3S2puUTg3TTY4YzJVazJcL0k2YVpqS0lZSkQzNkhqZ095cEZXMEZ5UWZBV2c1WGZhekxQTEYxNnd0eDJjY21rNXBBZW42S1VmbkppUHA1bk9GZGNCRkl6Mzd3ZjkxR0tcL3VMOXNEY056ZW9WTE0xNGtqZzg0eGQ3WnVPdUp3RW5kSzg3ZFp3Z2hEdnhlWTY3MCsrdEVYR01PZFpTQ1wvNktQQ3N6KzdFWEtuTGFLT3E5WUxRcEFidStVRllwZGtqRW9icUFKV1ZoWUdINEJyOUlMTzRPS3lpV29yeW03aFhUQW9rZllacEcybzl0c2lVeFVxRTZLNUI1SDBlQ1RlRjhBQ2hnS3N3OTNRRHBEKzJhVGFqck52TEp0a2dUYXdob0R3WVdsWENTWXpcL0FTT1ArWFwvVzR1cjgxeGVSM2Ntc1Vva0Joam0ySjdmRnowb245U2lTVVhia1Y0a29DTUZ1RWlNSnVoY0JqUk4yOFZXT2xvMEFBeUFXSVZRQ3F4ZE1wZXBMSVhNSEhiM1B0S0luRzBldGhEdTg0RUtSbU9nNDdhUndmZnJOS1IwdFVQVm5vdldzUXFvaVdWcXU4TVlXYVFnaHY4eGJrUjRuYTd5K2dpb3Jaa29jRWFKYUQzNTRBSXNnMHRCV3JJKzR4VTJkVENuRUZuZjd5UEFYejVEVW1ZWnZcL21IYnZCMnc4ak5pTXlPaWY3K2JSUFcrRTI5Sms4aW8rbVpVcHZCRjBqdlNIdGNSdzEyNTFTTitXVTE4Ym9mc0krdndtXC9NT1A5V3V2YVZlb1YwanA2QUtPcnNyQ3hDWkdNT1dpdmNsNFh1NWtONXRra1YwZjIzRDdmTGpXcUdyODVnUWFxWHBRSUVkajJBZVFzN0UwaGdwczVxMUtVV2V1eTJrODRwK29cL3VyRlY5N0pSbHltblJaY21LSHFwVEJUeU5NbnVFXC9yV2doemtlUEQzRDNJUUhGaWlEb25JOWplMGxlTkpwK0dWdERmYWlZR1l2c1pkZkFJY1dVQSs0b2lReEtycUlHS3duVzA2aWpyTkI1bGJab0ViWE1UVGoxWWlQMHRBQTJGMlJcLzU3YTQrTU51Unl4aXF2NUQ3ZkJRcW9pQnF2WGZaYlNMUU5iRzFlaTF5OWc2aHE4MTU1UmJpQUs2NGcrZklHUDlyWmlrQm85WnJjWThOclFyNHczUE12cFJ3VE1yOXdCUzBuOGNXWUJwdGRtUW13dW1XY2xBOTRUMGxDUjhRMXg2cmE1TXpqbVBERW01YWVjMElKUVwvZEdcL2tSZFRjYUVRdzJzK0xvRFBVOFJzSnJkZUhVRGlzeXgrZTRMOEpqZzRIY3BndW9GOW84aktEZnNmTDlVNmpCdTBacjZYVnRKY3RqSFVQd0xkcXRSTkE3Sm5WYXBGVTlpRG5DYmExNzE2WTFhMFpLNm9SZWptQmxOS0NtZit5NmRWSXBuVE1hcTRBT0tDWlwvOE1BT3F2VWVhd242RDZIcDE0M016WkV3MTZFM3BkaXFEUVFOeExSZXNqN0pMMXNqa0wzdTQ2WkJhMUswXC9na25tckVBck5wMm4wbEVScmg5bmt1WFQ1SXl4czFSbUV5aTEySkFkUFwvY1ZtTUlaQUdYZFZMallnRnlaTG9IT2Fvaktra3FJV3haY3NNSG1UZHI4OXAzQ1daSHhTTlVMSk95NTMyRnNqemp5bitBVUlScFZ6YzlPTW5yNzJmQ0V0czkrRHZvZjRzVnNuc1NGamp4VHZrNUI0dWlINHNkSGlTeGpXYlwvNlVnV2F3bTJqbk5oRVhnNVVEUmdqWkpxaE00UkVjT09VWGxYclpkS0VyV3VCb3MwbE1jNHdpMU15QkZ1QXpOcE83NkJzSnFkTTBqdFRmd1RFc3cwNlBHbkZMNnZJM3FCSENBckp6aG9aSjFCYllYaGpDSDMwcjNmT3BnM0s0SmcwclJRNEQ5ODdxaExIVHBWOGd1QzZXZWliZFhFZWtUdDdnUStFK0R0UE9WZ05JT1Z0aVwvQ1VmSVwvRE1cL0lNbUlcL0dtVmc1MVwvblVoYjNQaHdMdmwrc2lZbURPcHNoekJ1Qjk1azBtc3BTZmQwWnROMVlRVUZBblV5blBBVk5cL0tyRWdEeGNQSWRzcUlGcUpFMHNxVytnXC9FbmsxblJrU0NLcllRNzVDSEJSSm1Ia0Vrbm5FQmJEb0xtaTh6U2Z1U052NEV2azBhUTlDTWc3TFhyTVhRYXFXQzl0M2JHZkJLZjFHTDRpZlBPeEx4WTVHRmtXRWg1Y1NybUhyd3VrcXM2U1NacU1WUTNtdXdBMHZvcTZYWGdIUTI1ODhNUWxcL0VWc0hheXRWM1dKWnNPcGdEbkM2TnBGakpMYlFiWldRQWlHeUhEQnZiRGlTNE1BK3ZtYTRXd1VDeUF4WUZzU0Q1WTl3Qm1FZWR3Q3hPeEVPQW9RUVNNYU91bnFUUkU5R1M5SXhueFF5OCtQZGQ4cjlxVEF6M0NXMFZhdjZDb0srMUNYTUNDTkJneTE4OHM1MGo2ZisycWdLMWlzbE9ZME5RSWp1KzJhamt2K1NkQzdxWnByeGZ0MEFvOGt3QlNRNDl6Nk0rbTNhUnl5N3lzMkhGWlMxN2swQzlHQmdUN2J3N3VEdVhSNWtUVFFnbitZdWE1SE1LXC9ReEhKb3lrbjBaVlhoanlsY1VDa3NhUzQrYkV6cWk0Y1czeDFlWER3Z01GMG1yVXUzNllmNHJheE55MUtKdnJmU2xPY3FnRGpiSmx3b2pRXC9QOCtIcXplYWJmOXRSaDgxRjU4WGtoY0JYU1FDeWFxeXVZRDhaV3dYTlpZTWlxd3hibFJPQVwvRDY4emkwcmNIdFhsR05lXC9KWlRFUnV1VWpMQk5xZ3laTU5HVlwvOXZYZW1oXC91SlJZYU9PNk9ZSUxiTG9xTmdCZUx5QTFVd1BTYVN6eVVqWVJreUVQS2F3TG5BTU13cUxCV1BycDFqV0FhYXRIZVlBR1ZWTFloS0ljaDRjdWhjUWRKRk5WdWJDVEJaZ0dxeHZlWFJzcDZ3MnlaXC9yYWtYc3RcL0JsbkxhTmFcL1lxS2FQUXc9PSIsIm1hYyI6ImVhYjNiNjkzYTQxNzlhMDMyNTA5YTk4MWE4NDkzNzdhZjE1ZjYzZGYwNWQ2MTA0ZjgwZGEwYmQ4ZjJhOWQyMDgifQ==',
      'XSRF-TOKEN' => 'eyJpdiI6IkhcL2ZsTFNmeDZ1QVBLXC9KWjFmMGs3QT09IiwidmFsdWUiOiJhNXB1NVZ1MEt3ckx1REp4ZzRwdFJQWnpBWTRTZXNiWm5teDJXaFN4V2hUR1hrRVFBVTlKUFJnYXg5eWs1MUp1IiwibWFjIjoiN2VkMzZhNTM0MGNkNmUyMDM3OTI5NmRhODY5NDhjNzQzZjkxYTM2YTlhMWJhZDcyNGE4OGZlM2NhMTc3OWM3YSJ9',
      'laravel_session' => 'eyJpdiI6ImNZMXZXMnhJR0Q5SHcxUUpmYm03d2c9PSIsInZhbHVlIjoiUEdid0V0RytidmhvSlpiSWlLRGdUZ1RBeVRCWkJiYUV2Y2drOXZQaG1qOE80OWVhcmpcL0cwdU1McG0xMldvOWwiLCJtYWMiOiI3MTU4YzRmNDgzYjc1MGQ3YTRlZDQyOWRiNjRjOGVlMmEwY2Q2N2I5NjA5YWFhZjZkNDNmNGFiN2FmNzc4OWRlIn0=',
      'login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d' => 'eyJpdiI6Iml1bjlZb1FNSEdJS3Vwa0o1bGRRd1E9PSIsInZhbHVlIjoiOStobkpKbDd1ZFBLbzVcL2NENElZZEE9PSIsIm1hYyI6IjExZjcyZjIzOTcwMzk2M2NmNTMwNDM0NWQwODUxOWRiMGViNmQzYWI5NTQ4YjE2MmQyMjYwMGMwYWM5OGIxNWUifQ==',
      '__cypress_initial' => 'true',
    ),
  )),
   'headers' => 
  QMHeaderBag::__set_state(array(
     'headers' => 
    array (
      'cookie' => 
      array (
        0 => 'XDEBUG_SESSION=PHPSTORM; intended_url=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Fv2%2Fapps%3FXDEBUG_SESSION_START%3DPHPSTORM; _ga=GA1.1.1659613996.1639418721; _gid=GA1.1.2105417521.1639418721; _gat=1; drift_campaign_refresh=7ba53de6-9f7b-4aa4-a9a1-302cb3de7437; drift_aid=3116b55e-759c-422a-be0c-c35f6238066e; driftt_aid=3116b55e-759c-422a-be0c-c35f6238066e; remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6IkhsWGtcL2pMSDNuVkl1TzU3cGJpdzJnPT0iLCJ2YWx1ZSI6ImtId0pXKzd1RWorcmFja3ZjWWU5T2RNRERjbjlUeFZ3MSs1V0pOVFVLRzJ3V3llcTVldFwvWTNtbEVxMFlYOElvWGFkd2ZhcW5QXC92cnlCSUt4Q1Zndk9zb0RhZG1neUN0Z3pJVUFxWm1IUDk2TzFpVFVOcXU2ek5rU2YxRzJ4N281VlZlM29MWVQxVG9rcWlmemtDVHBGcWt2SjJHM1o4U2ppVlAyWWxYRm5JPSIsIm1hYyI6IjI0YjhkMzBmZTc2YzJmMTY0ZDY1ZTg2ZGVjN2YzYjY1NjQ3OTE0ZjY2NDQ5ODJlMDlhMjM1MWRjMmE5Njk4YWYifQ%3D%3D; RgIm3Lnophs0tQoyMoNJofSWQ54Zky7m9u1tZnjl=eyJpdiI6Ink5MnpqMDB5Yk93SklMMmFUQ1JqSEE9PSIsInZhbHVlIjoicVhFTGRDYmI0Y1RtRnJHZ2R5Q1BtaHBPRFwvMFwvTzh3eFl0bFg2bExRM1cwRjVQUWZkVjNlZXJtM0tUT1Ixb2JhZFM4NnFNQmlnT3Q0OE1kdThOM0x0T1ZET1BTdmQyWGtjaUJad2t1NDZMNHMrUTY3V0djYnhLQ3g2azRxWmtEUm5PaUtXY1FydEJHeVwvZnJcL0JBcW5QTTJDQ3ZrMzJURlpzSzk2aVo5VUJuTW9mWVwvOFEwTHBreXIzSnVwa2tYN25hUWtGWUEzNkNMSXUxd0RWWnVxeG9XQ0pzNnZqcDRsZTJmU1hOZEdOSkJZRjVBMUI0YVhGbmh5MjAzTUtzc1NDUXUrQnBrcWM1SnhiZXA3bGhHeEpCekFKeWdBOXBFeDU3VTg0YkE0VlpvMGRWT1ZxcEJjZm9heFdMc1MzcVZHeEN6N0ZEQndJNU5jYUdsVVwvQkE4RUp2aVwvdmZlSkNaczlJdWttQ2RONDJHaDVocDhoSHA0ZUc2dTUyMXdjeER3eVlHUDJLa2FueW1uV3RBUzJXMlNKYkI3S2puUTg3TTY4YzJVazJcL0k2YVpqS0lZSkQzNkhqZ095cEZXMEZ5UWZBV2c1WGZhekxQTEYxNnd0eDJjY21rNXBBZW42S1VmbkppUHA1bk9GZGNCRkl6Mzd3ZjkxR0tcL3VMOXNEY056ZW9WTE0xNGtqZzg0eGQ3WnVPdUp3RW5kSzg3ZFp3Z2hEdnhlWTY3MCsrdEVYR01PZFpTQ1wvNktQQ3N6KzdFWEtuTGFLT3E5WUxRcEFidStVRllwZGtqRW9icUFKV1ZoWUdINEJyOUlMTzRPS3lpV29yeW03aFhUQW9rZllacEcybzl0c2lVeFVxRTZLNUI1SDBlQ1RlRjhBQ2hnS3N3OTNRRHBEKzJhVGFqck52TEp0a2dUYXdob0R3WVdsWENTWXpcL0FTT1ArWFwvVzR1cjgxeGVSM2Ntc1Vva0Joam0ySjdmRnowb245U2lTVVhia1Y0a29DTUZ1RWlNSnVoY0JqUk4yOFZXT2xvMEFBeUFXSVZRQ3F4ZE1wZXBMSVhNSEhiM1B0S0luRzBldGhEdTg0RUtSbU9nNDdhUndmZnJOS1IwdFVQVm5vdldzUXFvaVdWcXU4TVlXYVFnaHY4eGJrUjRuYTd5K2dpb3Jaa29jRWFKYUQzNTRBSXNnMHRCV3JJKzR4VTJkVENuRUZuZjd5UEFYejVEVW1ZWnZcL21IYnZCMnc4ak5pTXlPaWY3K2JSUFcrRTI5Sms4aW8rbVpVcHZCRjBqdlNIdGNSdzEyNTFTTitXVTE4Ym9mc0krdndtXC9NT1A5V3V2YVZlb1YwanA2QUtPcnNyQ3hDWkdNT1dpdmNsNFh1NWtONXRra1YwZjIzRDdmTGpXcUdyODVnUWFxWHBRSUVkajJBZVFzN0UwaGdwczVxMUtVV2V1eTJrODRwK29cL3VyRlY5N0pSbHltblJaY21LSHFwVEJUeU5NbnVFXC9yV2doemtlUEQzRDNJUUhGaWlEb25JOWplMGxlTkpwK0dWdERmYWlZR1l2c1pkZkFJY1dVQSs0b2lReEtycUlHS3duVzA2aWpyTkI1bGJab0ViWE1UVGoxWWlQMHRBQTJGMlJcLzU3YTQrTU51Unl4aXF2NUQ3ZkJRcW9pQnF2WGZaYlNMUU5iRzFlaTF5OWc2aHE4MTU1UmJpQUs2NGcrZklHUDlyWmlrQm85WnJjWThOclFyNHczUE12cFJ3VE1yOXdCUzBuOGNXWUJwdGRtUW13dW1XY2xBOTRUMGxDUjhRMXg2cmE1TXpqbVBERW01YWVjMElKUVwvZEdcL2tSZFRjYUVRdzJzK0xvRFBVOFJzSnJkZUhVRGlzeXgrZTRMOEpqZzRIY3BndW9GOW84aktEZnNmTDlVNmpCdTBacjZYVnRKY3RqSFVQd0xkcXRSTkE3Sm5WYXBGVTlpRG5DYmExNzE2WTFhMFpLNm9SZWptQmxOS0NtZit5NmRWSXBuVE1hcTRBT0tDWlwvOE1BT3F2VWVhd242RDZIcDE0M016WkV3MTZFM3BkaXFEUVFOeExSZXNqN0pMMXNqa0wzdTQ2WkJhMUswXC9na25tckVBck5wMm4wbEVScmg5bmt1WFQ1SXl4czFSbUV5aTEySkFkUFwvY1ZtTUlaQUdYZFZMallnRnlaTG9IT2Fvaktra3FJV3haY3NNSG1UZHI4OXAzQ1daSHhTTlVMSk95NTMyRnNqemp5bitBVUlScFZ6YzlPTW5yNzJmQ0V0czkrRHZvZjRzVnNuc1NGamp4VHZrNUI0dWlINHNkSGlTeGpXYlwvNlVnV2F3bTJqbk5oRVhnNVVEUmdqWkpxaE00UkVjT09VWGxYclpkS0VyV3VCb3MwbE1jNHdpMU15QkZ1QXpOcE83NkJzSnFkTTBqdFRmd1RFc3cwNlBHbkZMNnZJM3FCSENBckp6aG9aSjFCYllYaGpDSDMwcjNmT3BnM0s0SmcwclJRNEQ5ODdxaExIVHBWOGd1QzZXZWliZFhFZWtUdDdnUStFK0R0UE9WZ05JT1Z0aVwvQ1VmSVwvRE1cL0lNbUlcL0dtVmc1MVwvblVoYjNQaHdMdmwrc2lZbURPcHNoekJ1Qjk1azBtc3BTZmQwWnROMVlRVUZBblV5blBBVk5cL0tyRWdEeGNQSWRzcUlGcUpFMHNxVytnXC9FbmsxblJrU0NLcllRNzVDSEJSSm1Ia0Vrbm5FQmJEb0xtaTh6U2Z1U052NEV2azBhUTlDTWc3TFhyTVhRYXFXQzl0M2JHZkJLZjFHTDRpZlBPeEx4WTVHRmtXRWg1Y1NybUhyd3VrcXM2U1NacU1WUTNtdXdBMHZvcTZYWGdIUTI1ODhNUWxcL0VWc0hheXRWM1dKWnNPcGdEbkM2TnBGakpMYlFiWldRQWlHeUhEQnZiRGlTNE1BK3ZtYTRXd1VDeUF4WUZzU0Q1WTl3Qm1FZWR3Q3hPeEVPQW9RUVNNYU91bnFUUkU5R1M5SXhueFF5OCtQZGQ4cjlxVEF6M0NXMFZhdjZDb0srMUNYTUNDTkJneTE4OHM1MGo2ZisycWdLMWlzbE9ZME5RSWp1KzJhamt2K1NkQzdxWnByeGZ0MEFvOGt3QlNRNDl6Nk0rbTNhUnl5N3lzMkhGWlMxN2swQzlHQmdUN2J3N3VEdVhSNWtUVFFnbitZdWE1SE1LXC9ReEhKb3lrbjBaVlhoanlsY1VDa3NhUzQrYkV6cWk0Y1czeDFlWER3Z01GMG1yVXUzNllmNHJheE55MUtKdnJmU2xPY3FnRGpiSmx3b2pRXC9QOCtIcXplYWJmOXRSaDgxRjU4WGtoY0JYU1FDeWFxeXVZRDhaV3dYTlpZTWlxd3hibFJPQVwvRDY4emkwcmNIdFhsR05lXC9KWlRFUnV1VWpMQk5xZ3laTU5HVlwvOXZYZW1oXC91SlJZYU9PNk9ZSUxiTG9xTmdCZUx5QTFVd1BTYVN6eVVqWVJreUVQS2F3TG5BTU13cUxCV1BycDFqV0FhYXRIZVlBR1ZWTFloS0ljaDRjdWhjUWRKRk5WdWJDVEJaZ0dxeHZlWFJzcDZ3MnlaXC9yYWtYc3RcL0JsbkxhTmFcL1lxS2FQUXc9PSIsIm1hYyI6ImVhYjNiNjkzYTQxNzlhMDMyNTA5YTk4MWE4NDkzNzdhZjE1ZjYzZGYwNWQ2MTA0ZjgwZGEwYmQ4ZjJhOWQyMDgifQ%3D%3D; XSRF-TOKEN=eyJpdiI6IkhcL2ZsTFNmeDZ1QVBLXC9KWjFmMGs3QT09IiwidmFsdWUiOiJhNXB1NVZ1MEt3ckx1REp4ZzRwdFJQWnpBWTRTZXNiWm5teDJXaFN4V2hUR1hrRVFBVTlKUFJnYXg5eWs1MUp1IiwibWFjIjoiN2VkMzZhNTM0MGNkNmUyMDM3OTI5NmRhODY5NDhjNzQzZjkxYTM2YTlhMWJhZDcyNGE4OGZlM2NhMTc3OWM3YSJ9; laravel_session=eyJpdiI6ImNZMXZXMnhJR0Q5SHcxUUpmYm03d2c9PSIsInZhbHVlIjoiUEdid0V0RytidmhvSlpiSWlLRGdUZ1RBeVRCWkJiYUV2Y2drOXZQaG1qOE80OWVhcmpcL0cwdU1McG0xMldvOWwiLCJtYWMiOiI3MTU4YzRmNDgzYjc1MGQ3YTRlZDQyOWRiNjRjOGVlMmEwY2Q2N2I5NjA5YWFhZjZkNDNmNGFiN2FmNzc4OWRlIn0%3D; login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6Iml1bjlZb1FNSEdJS3Vwa0o1bGRRd1E9PSIsInZhbHVlIjoiOStobkpKbDd1ZFBLbzVcL2NENElZZEE9PSIsIm1hYyI6IjExZjcyZjIzOTcwMzk2M2NmNTMwNDM0NWQwODUxOWRiMGViNmQzYWI5NTQ4YjE2MmQyMjYwMGMwYWM5OGIxNWUifQ%3D%3D; __cypress.initial=true',
      ),
      'accept-language' => 
      array (
        0 => 'en-US,en;q=0.9',
      ),
      'accept-encoding' => 
      array (
        0 => 'gzip',
      ),
      'referer' => 
      array (
        0 => getenv('APP_URL').$this->REQUEST_URI.'',
      ),
      'sec-fetch-dest' => 
      array (
        0 => 'iframe',
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
        0 => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      ),
      'user-agent' => 
      array (
        0 => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.93 Safari/537.36',
      ),
      'content-type' => 
      array (
        0 => 'multipart/form-data; boundary=----WebKitFormBoundaryKzk1usS7Gg2T21vo',
      ),
      'origin' => 
      array (
        0 => getenv('APP_URL').'',
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
        0 => '" Not A;Brand";v="99", "Chromium";v="96", "Google Chrome";v="96"',
      ),
      'cache-control' => 
      array (
        0 => 'max-age=0',
      ),
      'content-length' => 
      array (
        0 => '794',
      ),
      'host' => 
      array (
        0 => 'local.quantimo.do',
      ),
      'connection' => 
      array (
        0 => 'keep-alive',
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
