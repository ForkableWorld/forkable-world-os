<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\B\TrackingReminders;
use App\Computers\ThisComputer;
use App\Exceptions\ModelValidationException;
use App\Properties\Base\BaseAccessTokenProperty;
use Tests\QMBaseTestCase;
use Tests\SlimStagingTestCase;

class PostTrackingRemindersTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testPostTrackingReminders(): void{
		$expectedString = '';
		QMBaseTestCase::setExpectedRequestException(ModelValidationException::class);
		$responseBody = $this->callAndCheckResponse($expectedString);
		$this->checkTestDuration(5);
		$this->checkQueryCount(7);
	}
	public $expectedResponseSizes = [];
	public $slimEnvironmentSettings = array (
  'REQUEST_METHOD' => 'POST',
  'REMOTE_ADDR' => '10.0.2.2',
  'SCRIPT_NAME' => '',
  'PATH_INFO' => '/api/v3/trackingReminders',
  'SERVER_NAME' => ThisComputer::LOCAL_HOST_NAME,
  'SERVER_PORT' => '443',
  'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
  'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
  'HTTP_REFERER' => 'https://dev-web.quantimo.do/',
  'HTTP_SEC_FETCH_SITE' => 'same-site',
  'HTTP_ORIGIN' => 'https://dev-web.quantimo.do',
  'HTTP_CONTENT_TYPE' => 'application/json',
  'HTTP_SEC_FETCH_MODE' => 'cors',
  'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.100 Safari/537.36',
  'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,
  'HTTP_ACCEPT' => 'application/json',
  'HTTP_CACHE_CONTROL' => 'no-cache',
  'HTTP_PRAGMA' => 'no-cache',
  'HTTP_CONNECTION' => 'keep-alive',
  'CONTENT_LENGTH' => '761',
  'CONTENT_TYPE' => 'application/json',
  'slim.url_scheme' => 'https',
  'slim.input' => '[{"variableName":"REM Sleep Duration","combinationOperation":null,"variableCategoryName":"Sleep","unitAbbreviatedName":"h","pngPath":"img/variable_categories/sleep.png","svgPath":"img/variable_categories/sleep.svg","reminderFrequency":86400,"firstDailyReminderTime":null,"secondDailyReminderTime":null,"thirdDailyReminderTime":null,"enabled":true,"unitId":34,"valueAndFrequencyTextDescription":"Daily","startTrackingDate":null,"stopTrackingDate":null,"defaultValue":null,"reminderStartTimeEpochTime":1565571600,"reminderStartTimeLocal":"20:00:00","valueAndFrequencyTextDescriptionWithTime":"Daily at 8:00 PM","reminderStartTime":"01:00:00","reminderStartTimeEpochSeconds":1565571600,"nextReminderTimeEpochSeconds":1565571600,"timeZoneOffset":300}]',
  'slim.request.form_hash' =>
  array (
  ),
  'slim.request.query_hash' =>
  array (
    'appName' => 'QuantiModo',
    'clientId' => 'quantimodo',
    'appVersion' => '2.9.809',
  ),
  'responseStatusCode' => NULL,
  'unixtime' => 1565557968,
  'requestDuration' => 4.563576936721802,
);
}
