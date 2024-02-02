<?php /** @noinspection SpellCheckingInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\C\Studies\CohortStudies;
use App\Models\Study;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Charts\UserVariableCharts\UserVariableChartGroup;
use App\Studies\QMCohortStudy;
use App\Variables\QMUserVariable;
use Tests\SlimStagingTestCase;
use Tests\Traits\TestsStudies;
class CohortStudyCreationTest extends SlimStagingTestCase {
	use TestsStudies;
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testCohortStudyCreation(){
        $expectedString = '';
        $responseBody = $this->callAndCheckResponse($expectedString);
        $this->checkTestDuration(22);
        $this->checkQueryCount(19);
        /** @var QMCohortStudy $study */
        /** @noinspection PhpUndefinedFieldInspection */
        $study = $responseBody->study;
        /** @var QMUserVariable $sleep */
        $sleep = $study->causeVariable;
        /** @var UserVariableChartGroup $charts */
        $charts = $sleep->charts;
        $byMonth = $charts->monthlyColumnChart;
        $config = $byMonth->highchartConfig;
        $this->compareStudyHtml($study->studyHtml->fullStudyHtml);
		$s = Study::find($study->id);
		$this->assertEquals('https://staging.quantimo.do/study/cause-1867-effect-1398-user-18535-cohort-study', $s->getUrl());
        //$this->assertNotNull($config);
    }
    public $expectedResponseSizes = [];
    public $slimEnvironmentSettings = array (
        'REQUEST_METHOD' => 'POST',
        'REMOTE_ADDR' => '10.190.186.209',
        'SCRIPT_NAME' => '',
        'PATH_INFO' => '/api/v3/study/create',
        'SERVER_NAME' => '_',
        'SERVER_PORT' => '443',
        'HTTP_X_FORWARDED_FOR' => '52.201.131.218',
        'HTTP_COOKIE' => '_ga=GA1.2.846851356.1542584222; _gid=GA1.2.1443760597.1542584222; laravel_session=eyJpdiI6Imc3SVJmdDcrc0xZb2Q4YzV1K2Q5NHc9PSIsInZhbHVlIjoiSDd6a2xwcXQ5RmtGb1JVM3Z1T211VjlGV1BkTmJnb3ByaDhza2JwZ0gxd25UOE9FMzUrZHdNVnhTRWpWaFwvNmYycFoxTGZBYjAxXC9XbE1IaW1pQ3VhQT09IiwibWFjIjoiMTE5Y2NmNjI1MTdjMDViNWVhYWJiM2VmMmE2NTZiMDE2Yjg1NThlODkyZjdjZDhhMjJkNGI4NmJmODIzMzI4MyJ9',
        'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
        'HTTP_REFERER' => 'https://staging.quantimo.do/ionic/Modo/www/index.html',
        'HTTP_X_FRAMEWORK' => 'ionic',
        'HTTP_X_PLATFORM' => 'web',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0 Safari/537.36 Ghost Inspector',
        'HTTP_X_CLIENT_ID' => 'quantimodo',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_CONTENT_TYPE' => 'application/json',
        'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,
        'HTTP_X_APP_VERSION' => '2.8.1113',
        'HTTP_ORIGIN' => 'https://staging.quantimo.do',
        'CONTENT_LENGTH' => '90',
        'CONTENT_TYPE' => 'application/json',
        'slim.url_scheme' => 'http',
        'slim.input' => '{"causeVariableName":"Sleep Duration","effectVariableName":"Overall Mood","type":"cohort"}',
        'slim.request.form_hash' =>
            array (
            ),
        'slim.request.query_hash' =>
            array (
                'clientId' => 'quantimodo',
                'platform' => 'web',
            ),
        'responseStatusCode' => NULL,
        'unixtime' => 1542584358,
        'requestDuration' => 1.112138032913208,
    );
}
