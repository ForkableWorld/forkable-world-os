<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\B\OAuth;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Slim\View\Request\QMRequest;
use Tests\SlimStagingTestCase;
class GetAuthorizePageWithAccessTokenInUrlTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testGetAuthorizePageWithAccessTokenInUrlButNotTheOtherRequiredParams(): void{
		$this->slimEnvironmentSettings = [
            'REQUEST_METHOD' => 'GET',
            'REMOTE_ADDR' => '192.168.10.1',
            'SCRIPT_NAME' => '',
            'PATH_INFO' => '/oauth/authorize',
            'SERVER_NAME' => QMRequest::host(),
            'SERVER_PORT' => '443',
            'HTTP_COOKIE' => 'driftt_aid=2b1f6143-9a1b-42d5-90df-2ba843629430; PHPSESSID=3r7ofnrbrbfo2app7vfj00kdu7; driftt_sid=a1716bb7-bea4-42aa-b93c-83e8b2ce73d4; DFTT_END_USER_PREV_BOOTSTRAPPED=true; __cypress.initial=true; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=testuser1575940847333%7C1577150468%7Cfb6593db26e8602a3045d97c90651488%7Clocal; XSRF-TOKEN=eyJpdiI6IlMzNThIQ2dHNXA4TytcL05ENDlYbzBRPT0iLCJ2YWx1ZSI6IkRLdGlsNFc3VDV2alVLaUZEcUNuQmdcLytSdVJzUXJNejZnYmdhTk01dEdIWHBtK2dRRG5vaDNIekNkbnJQR2tNIiwibWFjIjoiYzViNjdmNTk3MjM2MTQwMWI5ODM1NmY3MTBiNDZmOTRiMjcyNTgzZDI1Y2JhY2RlYWZlZGEzMGQ4NTIzNGNhOSJ9; laravel_session=eyJpdiI6IlRmRlZaamxGT2ozSnN2UWxMVitwMHc9PSIsInZhbHVlIjoiOEhIaHJ5aUVDcExWZE5SS3BqalRFSUJuQWxscUoxMk83Q3lWSW9Hd0JiUk1TVkkwRkxXMGFsUmNYcm9PQ1g5MyIsIm1hYyI6IjljZTliNmEwMjVlMGYyOWQ5YjM3YzY4MzQ5MmRiYWVlMDhkMDI3Mjk1ZTEzZWU3OGJmZGI4MTAzNTA4YTRlMDkifQ%3D%3D',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            'HTTP_ACCEPT_ENCODING' => 'gzip',
            'HTTP_REFERER' => getenv('APP_URL').'/auth/register?redirectTo='.urlencode(getenv('APP_URL'))
                              .'%2Fapi%2Foauth2%2Fauthorize&XDEBUG_SESSION_START=PHPSTORM',
            'HTTP_SEC_FETCH_MODE' => 'nested-navigate',
            'HTTP_SEC_FETCH_SITE' => 'same-origin',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36',
            'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
            'HTTP_CACHE_CONTROL' => 'max-age=0',
            'HTTP_CONNECTION' => 'keep-alive',
            'CONTENT_LENGTH' => '',
            'CONTENT_TYPE' => '',
            'slim.url_scheme' => 'https',
            'slim.input' => '',
            'slim.request.query_hash' => ['quantimodoAccessToken' => BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,],
            'responseStatusCode' => NULL,
            'unixtime' => 1575940874,
            'requestDuration' => 0.20860791206359863,
        ];
		$clientIdForAccessToken = "pImUNsI6T5Ysd81k";
		$this->expectBadRequestException();
	    $response = $this->callAndCheckResponse('Please provide your QuantiModo client_id', false, null);
		$this->checkTestDuration(5);
		$this->checkQueryCount(4);
	}
}
