<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\D\Users;
use App\Slim\Model\User\QMUser;
use Tests\SlimStagingTestCase;
class GetDemoUserTest extends SlimStagingTestCase {
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public $expectedResponseSizes = [];
    public $slimEnvironmentSettings = [
        'REQUEST_METHOD'                 => 'GET',
        'REMOTE_ADDR'                    => '192.168.10.1',
        'SCRIPT_NAME'                    => '',
        'PATH_INFO'                      => '/api/v3/user',
        'SERVER_NAME'                    => \App\Computers\ThisComputer::LOCAL_HOST_NAME,
        'SERVER_PORT'                    => '443',
        'HTTP_COOKIE'                    => 'driftt_aid=98a8a3d2-1d31-4327-9838-6eaec7f8a19f; DFTT_END_USER_PREV_BOOTSTRAPPED=true; __cfduid=dcdb09420c75424a77f4f65d19ff030c01548823023; _ga=GA1.2.1344114312.1548823044; fbm_225078261031461=base_domain=.quantimo.do; __utma=109117957.1344114312.1548823044.1550087604.1550091488.2; quantimodo-_zldp=DAZDRQtOaPcXH8HX%2BOqgkjjGotu1HZpYg1gRXUtQw%2BA4DMxo%2B%2FTZ2QBNmYCEtq2D; DFTT_END_USER_PREV_BOOTSTRAPPED=true; quantimodo-_siqid=DAZDRQtOaPczbhKinEnCHK3z9pRolhmyjvfeO%252B8YacTQTzsd46klmd54mMMk9cZwXApI11xAy10T%250AScYJrfjC4FmL7GtWyeHWCQVYZpk98wnfTzZGzm6Dbg%253D%253D; driftt_aid=98a8a3d2-1d31-4327-9838-6eaec7f8a19f; driftt_eid=230; PHPSESSID=6mk9sbiqknui97pijdlqe11iau; _gid=GA1.2.1477794239.1570666981; XSRF-TOKEN=eyJpdiI6InJ5aVZxTGJOMkFUNVVUNjRqWEtncmc9PSIsInZhbHVlIjoicys5RkVWaXJidnNsT0lhNnR4TmFEV0R1TnpNZUdiMFk1M0dNbWZ6Q0dOKzVTTE13UWhxWVZqUklScHhLNjNVNiIsIm1hYyI6ImNlMDlmN2MxNjYwZGRkMWZmMDMxOTE0MTdkYjQxMjVmMTRmYWM4YjgxYzAxODZiNzg0ZTc1NDdhNTdhZDQyYTQifQ%3D%3D; laravel_session=eyJpdiI6IkxPRHV1c2h5dzlVS2hFNTk1dm82U0E9PSIsInZhbHVlIjoidGNUelBWZUpqMzg4dEE2bVNiNThxWW84SzFFMmpGT2h4UWhjRWpUdlFRS2tKcVdmQ0d5bGZvZkxmd0hSeTByTCIsIm1hYyI6Ijk5ZWFiZjZhZGY0MDIxYWJjNmFkMTg1ZjYyNGI2MzA0YWUxZTM3Y2EyYmZjOTIyODIzM2RmZTNlOTA2YzlhODIifQ%3D%3D; final_callback_url=https%3A%2F%2Fweb.quantimo.do%2F%23%2Fapp%2Flogin%3Fclient_id%3Dquantimodo%26message%3DConnected%2BGoogle%2521%26quantimodoAccessToken%3Dmike-test-token%26quantimodoUserId%3D230; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1571948671%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo',
        'HTTP_ACCEPT_LANGUAGE'           => 'en-US,en;q=0.9',
        'HTTP_ACCEPT_ENCODING'           => 'gzip, deflate, br',
        'HTTP_SEC_FETCH_SITE'            => 'none',
        'HTTP_ACCEPT'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
        'HTTP_USER_AGENT'                => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36',
        'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
        'HTTP_CONNECTION'                => 'keep-alive',
        'CONTENT_LENGTH'                 => '',
        'CONTENT_TYPE'                   => '',
        'slim.url_scheme'                => 'https',
        'slim.input'                     => '',
        'slim.request.query_hash'        => [
            'clientId' => 'demo',
            'platform' => 'web',
        ],
        'slim.request.form_hash'         => [],
        'responseStatusCode'             => 200,
        'unixtime'                       => 1570800736,
        'requestDuration'                => 6.630111932754517,
    ];
    public function testGetDemoUser(): void {
        $expectedString = '';
        /** @var QMUser $responseBody */
        $responseBody = $this->callAndCheckResponse($expectedString);
        $this->assertEquals(1, $responseBody->id);
        $this->assertEquals("demo", $responseBody->accessToken);
        $this->assertEquals("Demo User", $responseBody->displayName);
        $this->checkTestDuration(8);
        $this->checkQueryCount(3);
    }
}
