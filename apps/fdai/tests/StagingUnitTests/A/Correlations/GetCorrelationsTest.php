<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\A\Correlations;
use App\Properties\Base\BaseAccessTokenProperty;
use Tests\SlimStagingTestCase;

class GetCorrelationsTest extends SlimStagingTestCase {
    public $maximumResponseArrayLength = 10;
    public $minimumResponseArrayLength = 10;
    public function testGetCorrelations(){
        $this->callAndCheckResponse(' Predicts ');
        $this->checkTestDuration(9);
        $this->checkQueryCount(7);
    }
    public $expectedResponseSizes = [];
    public $slimEnvironmentSettings = array (
        'REQUEST_METHOD' => 'GET',
        'REMOTE_ADDR' => '10.190.186.209',
        'SCRIPT_NAME' => '',
        'PATH_INFO' => '/api/v3/user_variable_relationships',
        'SERVER_NAME' => '_',
        'SERVER_PORT' => '443',
        'HTTP_X_FORWARDED_FOR' => '35.202.145.110',
        'HTTP_AUTHORIZATION' => 'Bearer '.BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,
        'HTTP_CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_USER_AGENT' => 'Swagger-Codegen/1.0.0/php',
        'CONTENT_LENGTH' => '',
        'CONTENT_TYPE' => 'application/json',
        'slim.url_scheme' => 'http',
        'slim.input' => '',
        'slim.request.query_hash' =>
            array (
                'limit' => '10',
                'clientId' => 'laravel',
                'commonOnly' => '1',
            ),
        'slim.request.form_hash' =>
            array (
            ),
        'responseStatusCode' => NULL,
        'unixtime' => 1542427263,
        'requestDuration' => 1.885002851486206,
    );
}
