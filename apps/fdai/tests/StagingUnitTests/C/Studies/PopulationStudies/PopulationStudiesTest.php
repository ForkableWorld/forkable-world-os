<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\C\Studies\PopulationStudies;
use App\Computers\ThisComputer;
use App\Models\TrackingReminder;
use App\Properties\User\UserIdProperty;
use App\Studies\QMPopulationStudy;
use App\Variables\QMCommonVariable;
use Tests\SlimStagingTestCase;
class PopulationStudiesTest extends SlimStagingTestCase {
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testPopulationStudies(){
        $fat = QMCommonVariable::find("Fat Burn Heart Rate Zone Minutes");
        TrackingReminder::whereVariableId($fat->getId())->delete();
        $study = QMPopulationStudy::findOrCreateQMStudy("Fat Burn Heart Rate Zone Minutes",
            "Heart Rate (Pulse)", UserIdProperty::USER_ID_MIKE);
        $reminders = TrackingReminder::whereVariableId($fat->getId())->get();
        $this->assertCount(0, $reminders->all());
        $card = $study->getStudyCard();
        $this->assertNotNull($card->sharingButtons);
		$expectedString = '';
		$responseBody = $this->callAndCheckResponse($expectedString);
		$this->checkTestDuration(13);
		$this->checkQueryCount(12);
	}
	public $expectedResponseSizes = array (
        'studies'       => 1518,
        'ionIcon'       => 0.022,
        'image'         => 0.142,
        'startTracking' => 0.27,
        'description'   => 0.073,
        'title'         => 0.034,
        'html'          => ['min' => 20, 'max' => 100],
        'success'       => 0.004,
        'status'        => 0.009,
        'summary'       => 0.034,
        'avatar'        => 0.062,
);
	public $slimEnvironmentSettings = array (
  'REQUEST_METHOD' => 'GET',
  'REMOTE_ADDR' => '10.0.2.2',
  'SCRIPT_NAME' => '',
  'PATH_INFO' => '/api/v3/studies',
  'SERVER_NAME' => ThisComputer::LOCAL_HOST_NAME,
  'SERVER_PORT' => '443',
  'HTTP_X_FIRELOGGER' => '1.3',
  'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
  'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
  'HTTP_REFERER' => 'https://dev-web.quantimo.do/',
  'HTTP_X_FRAMEWORK' => 'ionic',
  'HTTP_X_PLATFORM' => 'web',
  'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36',
  'HTTP_X_CLIENT_ID' => 'quantimodo',
  'HTTP_ACCEPT' => 'application/json',
  'HTTP_CONTENT_TYPE' => 'application/json',
  //'HTTP_AUTHORIZATION' => 'Bearer mike-test-token',
  'HTTP_X_APP_VERSION' => '2.8.1218',
  'HTTP_ORIGIN' => 'https://dev-web.quantimo.do',
  'HTTP_X_TIMEZONE' => 'America/Chicago',
  'HTTP_CONNECTION' => 'keep-alive',
  'CONTENT_LENGTH' => '',
  'CONTENT_TYPE' => 'application/json',
  'slim.url_scheme' => 'https',
  'slim.input' => '',
  'slim.request.query_hash' =>
  array (
    'clientId' => 'quantimodo',
    'platform' => 'web',
    'limit' => '10',
  ),
  'slim.request.form_hash' =>
  array (
  ),
  'responseStatusCode' => 200,
  'unixtime' => 1545622878,
  'requestDuration' => 33.59093689918518,
);
}
