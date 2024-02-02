<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\C\Studies\UserStudies;
use App\Properties\Base\BaseAccessTokenProperty;
use Tests\SlimStagingTestCase;

class UserStudyWithLittleDataCreationTest extends SlimStagingTestCase {
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public $retry = true;
    public function testUserStudyWithLittleDataCreation(){
        $expectedString = '';
        $responseBody = $this->callAndCheckResponse($expectedString);
        $this->checkTestDuration(16, "This needs to be fast.  It queues analysis so it's ready for GetStudy request");
        $this->checkQueryCount(25);
    }
    public $expectedResponseSizes = [
        'study' => 250, // We should not be returning charts from create study controller. It's too slow. That's done in GetStudyController
    ];
    public $slimEnvironmentSettings = [
        'REQUEST_METHOD'          => 'POST',
        'REMOTE_ADDR'             => '10.0.2.2',
        'SCRIPT_NAME'             => '',
        'PATH_INFO'               => '/api/v3/study/create',
        'SERVER_NAME'             => '_',
        'SERVER_PORT'             => '443',
        'HTTP_X_FIRELOGGER'       => '1.3',
        'HTTP_COOKIE'             => '_ga=GA1.2.956197214.1538009354; __utmc=109117957; __utmz=109117957.1538493640.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); fbm_225078261031461=base_domain=.quantimo.do; heateorSsSLOptin=1; PHPSESSID=cache-sync-status; redirect=1; testing=1; sid=49b5db98a7d73aeed4abd91787eef508; tk_tc=Kx%2BnLDE5JBN3ngi1; __cfduid=d53bafe7d8296608d7b879d87ce1442af1539920894; _gid=GA1.2.537874804.1540136447; __utma=109117957.956197214.1538009354.1538493640.1540352295.2; final_callback_url=https%3A%2F%2Flocal.quantimo.do%2Fionic%2FModo%2Fsrc%2Fconfiguration-index.html',
        'HTTP_ACCEPT_LANGUAGE'    => 'en-US,en;q=0.9',
        'HTTP_ACCEPT_ENCODING'    => 'gzip, deflate, br',
        'HTTP_REFERER'            => 'https://local.quantimo.do/ionic/Modo/www/index.html',
        'HTTP_X_FRAMEWORK'        => 'ionic',
        'HTTP_X_PLATFORM'         => 'web',
        'HTTP_USER_AGENT'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
        'HTTP_X_CLIENT_ID'        => 'quantimodo',
        'HTTP_ACCEPT'             => 'application/json',
        'HTTP_CONTENT_TYPE'       => 'application/json',
        'HTTP_AUTHORIZATION'      => 'Bearer ' . BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,
        'HTTP_ORIGIN'             => 'https://local.quantimo.do',
        'HTTP_CACHE_CONTROL'      => 'no-cache',
        'HTTP_PRAGMA'             => 'no-cache',
        'HTTP_CONNECTION'         => 'keep-alive',
        'CONTENT_LENGTH'          => '91',
        'CONTENT_TYPE'            => 'application/json',
        'slim.url_scheme'         => 'https',
        'slim.input'              => '{"causeVariableName":"Adderall Xr","effectVariableName":"Overall Mood","type":"individual"}',
        'slim.request.form_hash'  => [],
        'slim.request.query_hash' => [
            'clientId' => 'quantimodo',
            'platform' => 'web',
        ],
        'responseStatusCode'      => 201,
        'unixtime'                => 1540433614,
        'requestDuration'         => 28.10706901550293,
    ];
}
