<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\B\Measurements;
use Tests\SlimStagingTestCase;

class GetMoodMeasurementsForNewRelicTest extends SlimStagingTestCase {
    public function testGetMoodMeasurementsForNewRelic(): void{
        $expectedString = '';
        $this->slimEnvironmentSettings = [
            'REQUEST_METHOD'             => 'GET',
            'REMOTE_ADDR'                => '34.241.198.127',
            'SCRIPT_NAME'                => '',
            'PATH_INFO'                  => '/api/measurements',
            'SERVER_NAME'                => 'app.quantimo.do',
            'SERVER_PORT'                => '443',
            'HTTP_ACCEPT_ENCODING'       => 'gzip,deflate',
            'HTTP_USER_AGENT'            => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.67 Safari/537.36',
            'HTTP_DNT'                   => '1',
            'HTTP_ACCEPT'                => 'text/html,application/xhtml+xml,application/json;q=0.9,application/javascript;q=0.9,text/javascript;q=0.9,application/xml;q=0.9,text/plain;q=0.8,*/*;q=0.7',
            'HTTP_X_NEWRELIC_SYNTHETICS' => 'PwcbUFZXCAEHU01ABlQFBV0HAwJPVVJXBE8MAQoIGVMDBglMU1YEVwUCVlJWUQEHEkhDVA4JAlYHVFAeV1JaBx1WDVdcFVYLVwcUBVJRBFsABlIDUFFeERxGUVRTBwcCXQIbCgEAUE8EAQpWFQACC1dICgABUQVSBQ4OBwcAGm4=',
            'HTTP_X_ABUSE_INFO'          => 'Request sent by a New Relic Synthetics Monitor (https://docs.newrelic.com/docs/synthetics/new-relic-synthetics/administration/identify-synthetics-requests-your-app) - monitor id: 06d60d93-9cf3-4c24-869d-3a0060a89fac | account id: 1040277',
            'HTTP_CONNECTION'            => 'Keep-Alive',
            'CONTENT_LENGTH'             => '',
            'CONTENT_TYPE'               => '',
            'slim.url_scheme'            => 'https',
            'slim.input'                 => '',
            'slim.request.query_hash'    => [
                'variableName' => 'Overall Mood',
                'lastUpdated'  => '(ge)0',
                'limit'        => '200',
                'offset'       => '0',
                'log'          => 'testuser',
                'pwd'          => 'testing123',
                'clientId'     => 'newRelic',
            ],
            'slim.request.form_hash'     => [],
            'responseStatusCode'         => NULL,
            'unixtime'                   => 1582585312,
            'requestDuration'            => 0.25609493255615234,
        ];
        $responseBody = $this->callAndCheckResponse($expectedString);
        $this->checkTestDuration(10);
        $this->checkQueryCount(4);
    }
    public $expectedResponseSizes = [];
}
