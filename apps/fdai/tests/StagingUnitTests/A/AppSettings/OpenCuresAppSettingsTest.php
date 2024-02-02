<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\A\AppSettings;
use Tests\SlimStagingTestCase;
class OpenCuresAppSettingsTest extends SlimStagingTestCase {
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testAppSettings(): void{
		$this->setAdminUser();
		$expectedString = 'Open Cures';
		$this->slimEnvironmentSettings = array (
  'REQUEST_METHOD' => 'GET',
  'SCRIPT_NAME' => '',
  'PATH_INFO' => '/api/v1/appSettings',
  'SERVER_NAME' => 'studies.crowdsourcingcures.org',
  'SERVER_PORT' => '443',
  'HTTP_COOKIE' => '_ga=GA1.2.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; DFTT_END_USER_PREV_BOOTSTRAPPED=true; u=084521ae39828198127bd5b3d1d7fe9ccf4eca35; _ga=GA1.1.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; driftt_eid=230; __gads=ID=baf1b7342d7fe58c-22cb087a70c50002:T=1608928372:RT=1608928372:S=ALNI_Mb_nA1jEnsvWYINYNpOi0KgcoaQEA; dsq__=8ppa63u27cufhv; __utmc=109117957; __utmz=109117957.1611547594.1.1.utmcsr=local.quantimo.do|utmccn=(referral)|utmcmd=referral|utmcct=/; drift_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; XDEBUG_SESSION=XDEBUG_ECLIPSE; drift_eid=230; __utma=109117957.644404966.1601578880.1611547594.1620319036.2; _gid=GA1.2.2056068301.1622800367; drift_eid=230; _gid=GA1.1.2056068301.1622800367; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1624257793%7Cb86ab88c2f236b6cb304be67366d5860%7Cquantimodo; XSRF-TOKEN=eyJpdiI6IlpCNklTMUhDeTVoZ09NaTEwdDU3Zmc9PSIsInZhbHVlIjoib2h0T09rSVNLVWQwalUzRlVaTkpBTmMxemdGaEM2NXRNSDAyUGc4N1NRbjdiZmFMbnRjSmlsWTNobmRINTJzSyIsIm1hYyI6IjQ5YjliZGIyMDU4M2NmNzBjMTllNDgxMDcwYTQ1N2JkMjU3MTc5NzI1M2IxMjg2MmZhZDFlOWY5YWIzNmFiMjkifQ%3D%3D; laravel_session=eyJpdiI6Im9CS1Q3VHhhSmkxa0srQWZYK2g4b2c9PSIsInZhbHVlIjoiVmJrVG9LOUVFbjlxa0ZnaDJOa1ZpeTJlVjRLZHlxa0dZSkFkXC95OURncEZpQkltUFIrSnQ2K3JUcWZReG5hK0EiLCJtYWMiOiIxYzkyZGFiNWQxMDIzNjMwOThkNjViMjgxMTY1YTI0ZTE5NDU3N2FhMzU4MGRiNGUyYWU4Y2U1NWMzMDRhMGI5In0%3D; final_callback_url=https%3A%2F%2Fapp.quantimo.do%2Fapi%2Fv2%2Fapps%2Fopen-cures%2Fintegration%3FquantimodoAccessToken%3Dmike-test-token%26quantimodoUserId%3D230',
  'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
  'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
  'HTTP_SEC_FETCH_DEST' => 'document',
  'HTTP_SEC_FETCH_USER' => '?1',
  'HTTP_SEC_FETCH_MODE' => 'navigate',
  'HTTP_SEC_FETCH_SITE' => 'none',
  'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
  'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.212 Safari/537.36',
  'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
  'HTTP_SEC_CH_UA_MOBILE' => '?0',
  'HTTP_SEC_CH_UA' => '" Not A;Brand";v="99", "Chromium";v="90", "Google Chrome";v="90"',
  'HTTP_CONNECTION' => 'keep-alive',
  'CONTENT_LENGTH' => '',
  'CONTENT_TYPE' => '',
  'slim.url_scheme' => 'https',
  'slim.input' => '',
  'slim.request.query_hash' =>
  array (
    'clientId' => 'open-cures',
    'designMode' => 'true',
    'phpunit' => '1',
  ),
  'responseStatusCode' => NULL,
  'unixtime' => 1623106500,
  'requestDuration' => 0,
);
		$responseBody = $this->callAndCheckResponse($expectedString, 200);
		$this->checkTestDuration(5);
		$this->checkQueryCount(8);
	}
}
