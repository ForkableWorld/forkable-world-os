<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\A\AppSettings;
use App\Computers\ThisComputer;
use App\Exceptions\ClientNotFoundException;
use Tests\QMBaseTestCase;
use Tests\SlimStagingTestCase;
class AppSettingsForNonExistentClientTest extends SlimStagingTestCase {
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    /**
     * @group Production
     */
    public function testAppSettingsForNonExistentClient(): void{
        QMBaseTestCase::setExpectedRequestException(ClientNotFoundException::class);
        $this->setExpectedStatusCode(404);
        $this->callAndCheckResponse("Client with id heroku not found");
        $this->checkTestDuration(10);
        $this->checkQueryCount(3);
    }
    public $expectedResponseSizes = [];
    public $slimEnvironmentSettings = [
        'REQUEST_METHOD'                 => 'GET',
        'REMOTE_ADDR'                    => '10.0.2.2',
        'SCRIPT_NAME'                    => '',
        'PATH_INFO'                      => '/api/v1/appSettings',
        'SERVER_NAME'                    => ThisComputer::LOCAL_HOST_NAME,
        'SERVER_PORT'                    => '443',
        'HTTP_X_FIRELOGGER'              => '1.3',
        'HTTP_COOKIE'                    => 'driftt_aid=98a8a3d2-1d31-4327-9838-6eaec7f8a19f; DFTT_END_USER_PREV_BOOTSTRAPPED=true; PHPSESSID=4a0fo1tb87a790rkp5b66858av; __cfduid=dcec1b9e130ca6aaddab330d247f70aac1544585515; _ga=GA1.2.76774008.1544585528; XDEBUG_SESSION=PHPSTORM; XSRF-TOKEN=eyJpdiI6IkZ6RTNLU0FBMUhHbEIwbjFxSHNkckE9PSIsInZhbHVlIjoieFBNTThQWVA3d3VwVkYyS0lTbGFDXC9GZFc1ZGViM1lwbXZFajA4R3VscnBLVkVHS01NaytIYmhTaHVtcCtxU0oiLCJtYWMiOiI5NThlMWJhYzljNWM3ZmJhN2M4MzgzNDZhNzFhM2VhYTI2NTJlOWQzOWEyN2VmNGRlYjA3Y2VkYzZlZGIzZWMzIn0%3D; laravel_session=eyJpdiI6ImhEYklPTWM2Sk9iZ1pkanR0WnZcL0lBPT0iLCJ2YWx1ZSI6Ikpyc3VxUitYNk5FbFdvd0FoYmZoUm5QaFwvblhqVEVXQVJQdnJKcEtDaE5rRDVnQmRhcEQ3WHNNcjFlSU82dytOIiwibWFjIjoiNjM3MDhkZWE4YmIyNTRiNzA0ODhjNWZiMTFhMGZiNzcxZDk2NTljNDlkZGMxMGMyYzgxNTU2YmQzMDA5NThlNyJ9; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1546414410%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo',
        'HTTP_ACCEPT_LANGUAGE'           => 'en-US,en;q=0.9',
        'HTTP_ACCEPT_ENCODING'           => 'gzip, deflate, br',
        'HTTP_ACCEPT'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
        'HTTP_USER_AGENT'                => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36',
        'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
        'HTTP_CACHE_CONTROL'             => 'max-age=0',
        'HTTP_CONNECTION'                => 'keep-alive',
        'CONTENT_LENGTH'                 => '',
        'CONTENT_TYPE'                   => '',
        'slim.url_scheme'                => 'https',
        'slim.input'                     => '',
        'slim.request.query_hash'        => [
            'clientId' => 'heroku',
        ],
        'responseStatusCode'             => NULL,
        'unixtime'                       => 1545338142,
        'requestDuration'                => 0.7974050045013428,
    ];
}
