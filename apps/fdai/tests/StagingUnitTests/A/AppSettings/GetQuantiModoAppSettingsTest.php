<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\A\AppSettings;
use App\AppSettings\AppSettings;
use App\AppSettings\AppSettingsResponse;
use App\Computers\ThisComputer;
use App\Properties\Base\BaseClientIdProperty;
use Tests\SlimStagingTestCase;
class GetQuantiModoAppSettingsTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testGetQuantiModoAppSettings(): void{
		$expectedString = 'QuantiModo';
        /** @var AppSettingsResponse $responseBody */
        $responseBody = $this->callAndCheckResponse($expectedString);
        /** @var AppSettings $settings */
        $settings = $responseBody->appSettings;
        $additional = $settings->additionalSettings;
        $links = $additional->downloadLinks;
		$this->assertStringNotContainsString(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT, json_encode($links));
        $this->assertStringNotContainsString(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT, json_encode($settings));
		$this->checkTestDuration(10);
		$this->checkQueryCount(2);
	}
	public $expectedResponseSizes = [];
	public $slimEnvironmentSettings = array (
        'REQUEST_METHOD'                 => 'GET',
        'REMOTE_ADDR'                    => '192.168.10.1',
        'SCRIPT_NAME'                    => '',
        'PATH_INFO'                      => '/api/v1/appSettings',
        'SERVER_NAME'                    => ThisComputer::LOCAL_HOST_NAME,
        'SERVER_PORT'                    => '443',
        'HTTP_COOKIE'                    => 'driftt_aid=c1d703da-05d4-42a9-8f9a-3e0a5f5e9d1f; DFTT_END_USER_PREV_BOOTSTRAPPED=true; _ga=GA1.2.997902526.1572964729; _gid=GA1.2.1035758363.1572964729; driftt_sid=2ea3d99f-3054-4566-be0b-87b5547f4ee1; final_callback_url=https%3A%2F%2Fdev-web.quantimo.do%2F%23%2Fapp%2Flogin%3Fclient_id%3Doauth_test_client%26message%3DConnected%2BQuantiModo%2521; PHPSESSID=hm6haadrbql3gkj1oga550ta74; intendedUrl=eyJpdiI6IkRQeGtlYjhHTmRPd0pSZ1JyMVBkbHc9PSIsInZhbHVlIjoiSWR4VUhxM0RxWlJBMCtqU253VGVXdHdScW9QektOUlwvbEplSG4rams1ZW1DV3p1QlZSSXArNWl2Y0lGYkE1N2htVGVrVGJGRFRUSGw5TnFYQnJBVmtnPT0iLCJtYWMiOiJlMDBkYWEwODY3ODdlOWEzN2M1MGFlNTkwYmI1ZGRhYTU1MmEyMTUwYTI3OTM4MThiN2ZiMzdmMDZiZGU3MDI2In0%3D; __cfduid=d24c815476832073c024928476e6e599e1572964763; driftt_aid=c1d703da-05d4-42a9-8f9a-3e0a5f5e9d1f; driftt_sid=2ea3d99f-3054-4566-be0b-87b5547f4ee1; __cypress.initial=true; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=testuser1572964794139%7C1574174418%7Cb295f1aab928b585abe426e03092acca%7Clocal; XSRF-TOKEN=eyJpdiI6ImxHYkVMQXd6dlFxdGlwcTJ6RFdXOFE9PSIsInZhbHVlIjoiUkZ3Z0srYUdsb3dxVXJRZDhUb3M3K1orOW9kdERNamFtd3J1M0RJU3pVTFFibkRQa2NhYW5LeThiR3F2MnBYNSIsIm1hYyI6IjVjZWZlOGFlOTg2MTQ2ZjIzYmRhMjMyYzIxN2I2ZWE4ZWVjY2NhYjU3MGFkMjhhZjkyN2RkM2I3ZGQ2OGVjMzgifQ%3D%3D; laravel_session=eyJpdiI6IllsMmZNcU1PaWNKc2l0XC9SN1pSV1JRPT0iLCJ2YWx1ZSI6ImxmSkt0aWNNWW5aQlNjbGpTTkRvbUtTUFB1dUJ2YUNaQnhMamo1cnBlRVJLYkdMZkJVWWhyT2s5STNzKzhzaG4iLCJtYWMiOiI5ODgwMjI1NmQyYWIxYjliODgyNjdmMzc0NmE1NWU1MWIyZjFhNTBmZGI5ZGY5ZDBiMjMwNzhiYWFlOTViMjA4In0%3D',
        'HTTP_ACCEPT_LANGUAGE'           => 'en-US,en;q=0.9',
        'HTTP_ACCEPT_ENCODING'           => 'gzip',
        'HTTP_REFERER'                   => 'https://local.quantimo.do/auth/register?redirectTo=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Fv3%2Foauth2%2Fauthorize%3Fregister%3D1%26state%3DeyJjbGllbnRfaWQiOiJvYXV0aF90ZXN0X2NsaWVudCIsImZpbmFsX2NhbGxiYWNrX3VybCI6Imh0dHBzOlwvXC9kZXYtd2ViLnF1YW50aW1vLmRvXC8jXC9hcHBcL2xvZ2luIn0-%26type%3Dweb_server%26client_id%3Doauth_test_client%26redirect_uri%3Dhttps%253A%252F%252Flocal.quantimo.do%252Fapi%252Fv1%252Fconnectors%252Fquantimodo%252Fconnect%26response_type%3Dcode%26scope%3Dreadmeasurements%2Bwritemeasurements',
        'HTTP_SEC_FETCH_SITE'            => 'same-origin',
        'HTTP_ACCEPT'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
        'HTTP_SEC_FETCH_MODE'            => 'nested-navigate',
        'HTTP_USER_AGENT'                => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.120 Safari/537.36',
        'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
        'HTTP_CACHE_CONTROL'             => 'max-age=0',
        'HTTP_CONNECTION'                => 'keep-alive',
        'CONTENT_LENGTH' => '',
        'CONTENT_TYPE' => '',
        'slim.url_scheme' => 'https',
        'slim.input' => '',
        'slim.request.query_hash' =>
  array (
    'client_id' => BaseClientIdProperty::CLIENT_ID_QUANTIMODO,
  ),
        'responseStatusCode' => NULL,
        'unixtime' => 1572964823,
        'requestDuration' => 0.27256298065185547,
);
}
