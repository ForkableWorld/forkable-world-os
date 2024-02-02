<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\B\Measurements;
use App\Exceptions\NoChangesException;
use App\Models\Measurement;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Properties\Measurement\MeasurementStartTimeProperty;
use App\Properties\User\UserIdProperty;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\QMResponseBody;
use Tests\QMBaseTestCase;
use Tests\SlimStagingTestCase;
class PostMeasurementsTest extends SlimStagingTestCase {
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    const START_TIME = 1572491113;
    const VARIABLE_NAME = "Aaa Test Treatment";
    public $expectedResponseSizes = [];
    public $slimEnvironmentSettings = [
        'REQUEST_METHOD'          => 'POST',
        'REMOTE_ADDR'             => '52.201.131.218',
        'SCRIPT_NAME'             => '',
        'PATH_INFO'               => '/api/v3/measurements',
        'SERVER_NAME'             => '_',
        'SERVER_PORT'             => '443',
        'HTTP_CONNECTION'         => 'keep-alive',
        'HTTP_ORIGIN'             => 'https://web.quantimo.do',
        'HTTP_REFERER'            => 'https://web.quantimo.do/',
        'HTTP_CONTENT_TYPE'       => 'application/json',
        'HTTP_AUTHORIZATION'      => 'Bearer '. BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,
        'HTTP_ACCEPT_ENCODING'    => 'gzip, deflate, br',
        'HTTP_ACCEPT_LANGUAGE'    => 'en-US,en;q=0.8',
        'HTTP_ACCEPT'             => 'application/json',
        'HTTP_USER_AGENT'         => 'Mozilla/5.0 (X11; Linux x86_64; rv:59.0.3) Gecko/20100101 Firefox/59.0.3 Ghost Inspector',
        'CONTENT_LENGTH'          => '791',
        'CONTENT_TYPE'            => 'application/json',
        'slim.url_scheme'         => 'https',
        'slim.input'              => '[{"variableName":"'.self::VARIABLE_NAME.'","value":48,"note":"","startTimeEpoch":'.self::START_TIME.',"unitAbbreviatedName":"mg","variableCategoryName":"Treatments","combinationOperation":"SUM","latitude":null,"longitude":null,"location":null,"sourceName":"QuantiModo for web","valueUnitVariableName":"48 mg Aaa Test Treatment","icon":"ion-ios-medkit-outline","pngPath":"img/variable_categories/treatments.png"}]',
        'slim.request.form_hash'  => [],
        'slim.request.query_hash' => [
            'appName'    => 'QuantiModo',
            'clientId'   => 'quantimodo',
            'appVersion' => '2.9.1031',
        ],
        'responseStatusCode'      => NULL,
        'unixtime'                => 1572491123,
        'requestDuration'         => 0.5607879161834717,
    ];
    public function testPostMeasurementsStaging(): void{
        //VariableIsPublicProperty::updateAll();
        Measurement::whereId(0)->forceDelete();
        QMBaseTestCase::setExpectedRequestException(null);
        $this->expectedStatusCode = 201;
        Measurement::deleteByVariableUserStart(UserIdProperty::USER_ID_TEST_USER,
            self::VARIABLE_NAME, self::START_TIME);
        /** @var QMResponseBody $responseBody */
        $responseBody = $this->callAndCheckResponse(self::VARIABLE_NAME, true, false);
        $byVarName = $responseBody->data->measurements;
        $rounded = MeasurementStartTimeProperty::pluckRounded([
            'variableName' => self::VARIABLE_NAME,
            Measurement::FIELD_START_TIME => self::START_TIME,
        ]);
        foreach($byVarName as $variableName => $measurements){
            foreach($measurements as $m){
                /** @var QMMeasurement $m */
                $this->assertNotNull($m->unitName);
                $this->assertEquals(self::VARIABLE_NAME, $m->variableName);
                $this->assertEquals(db_date($rounded), $m->startAt);
                $this->assertEquals(db_date(self::START_TIME), $m->originalStartAt);
                $this->assertEquals($rounded, $m->startTime);
                $this->assertEquals(db_date($rounded), $m->startAt);
            }
        }
        $this->assertCount(1, $responseBody->data->userVariables);
        QMBaseTestCase::setExpectedRequestException(NoChangesException::class);
        $this->expectedStatusCode = 400;
        $responseBody = $this->callAndCheckResponse('This record already existed and nothing changed',
            true, false);
        $this->checkTestDuration(14);
        $this->checkQueryCount(23);
    }
}
