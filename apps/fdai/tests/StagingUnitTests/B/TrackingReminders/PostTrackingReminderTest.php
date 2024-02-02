<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\B\TrackingReminders;
use App\DataSources\QMConnector;
use App\Properties\Base\BaseAccessTokenProperty;
use Tests\SlimStagingTestCase;

class PostTrackingReminderTest extends SlimStagingTestCase {
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testPostTrackingReminderStaging(): void{
		$expectedString = 'Sleep';
		$responseBody = $this->callAndCheckResponse($expectedString);
		$connector = QMConnector::getCurrentlyImportingConnector();
		$this->assertNull($connector);
		$this->checkTestDuration(19);
		$this->checkQueryCount(13);
	}
	public $expectedResponseSizes = [
        //'data' => 235.876,
    ];
	public $slimEnvironmentSettings = [
      'REQUEST_METHOD' => 'POST',
      'REMOTE_ADDR' => '10.0.2.2',
      'SCRIPT_NAME' => '',
      'PATH_INFO' => '/api/v3/trackingReminders',
      'SERVER_NAME' => '_',
      'SERVER_PORT' => '443',
      'HTTP_X_FIRELOGGER' => '1.3',
      'HTTP_COOKIE' => '_ga=GA1.2.506304397.1541100803; _gid=GA1.2.1750451632.1541100803; __cfduid=d6fc81d9344afdf0201f6ec5be411fa501541101912; final_callback_url=https%3A%2F%2Flocal.quantimo.do%2Fionic%2FModo%2Fwww%2Findex.html%23%2Fapp%2Flogin%3Ffinal_callback_url%3Dhttps%253A%252F%252Flocal.quantimo.do%252Fionic%252FModo%252Fwww%252Findex.html%2523%252Fapp%252Flogin%26clientId%3Dquantimodo%26register%3Dfalse%26message%3DConnected%2BQuantiModo%2521; laravel_session=eyJpdiI6ImhlbE9GNWMxbzY3WmdzTnNDRnJGVXc9PSIsInZhbHVlIjoiQ0hDYU5QdXJzdXkzQ3pLYzdNczQwMFBJcStcLzZ4d0I4TUR0bXFPYVNTWjFZUEFVbDhyNFhQZ2d0Y01VM084ZEwxcmFxUVkremdXVmFWYWl6S2JnU3l3PT0iLCJtYWMiOiI5ZDBlOWE2ZWZjNmRmYmQ5YjNiMDU5ZWFlZjBmMDliMWFlYTk2YTljNTljZmU5NDQxMDc2MjU5OTFhMzAzMGI4In0%3D; remember_82e5d2c56bdd0811318f0cf078b78bfc=eyJpdiI6IkZCejNCRWQ1XC9VN0hMTDd5ZnJ4ZW5BPT0iLCJ2YWx1ZSI6IlVZT1luTDRqOHhOa0lVcHFMV2tXUnU2T0JlUXc5TktvR00rdTZkTCtxb1p0bDBQc0N4SzdUMzdEeHNmOGlFVDk0bkhzRmpLT1VQZ0EwWUtuZnIrWW41VEo5VkhoeDBTM0hcLzk4Wkl2QXA4az0iLCJtYWMiOiIxM2E0YTNmZDllZDI2MDA2NDAzMDkzYTMzMGNmMTBjZjgyNTQ0OGY2MzRkYzVkNDVjN2EzNDA2YWI5YzJmMmU0In0%3D; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=testuser%7C1542320470%7C86db9a3d39d98100ae332be88d45d355%7Cquantimodo; _gat=1',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_REFERER' => 'https://local.quantimo.do/ionic/Modo/www/index.html',
      'HTTP_CONTENT_TYPE' => 'application/json',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36',
      'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,
      'HTTP_ORIGIN' => 'https://local.quantimo.do',
      'HTTP_ACCEPT' => 'application/json',
      'HTTP_CACHE_CONTROL' => 'no-cache',
      'HTTP_PRAGMA' => 'no-cache',
      'HTTP_CONNECTION' => 'keep-alive',
      'CONTENT_LENGTH' => '797',
      'CONTENT_TYPE' => 'application/json',
      'slim.url_scheme' => 'https',
      'slim.input' => '[{"variableId":1867,"variableName":"Sleep Duration","combinationOperation":"MEAN","variableCategoryName":"Sleep","unitAbbreviatedName":"min","pngPath":"img/variable_categories/sleep.png","svgPath":"img/variable_categories/sleep.svg","upc":"067981966602","reminderFrequency":1800,"firstDailyReminderTime":null,"secondDailyReminderTime":null,"thirdDailyReminderTime":null,"enabled":true,"defaultValue":8,"unitId":2,"valueAndFrequencyTextDescription":"Every 30 minutes","startTrackingDate":null,"stopTrackingDate":null,"reminderStartTimeEpochTime":1541120400,"reminderStartTimeLocal":"20:00:00","valueAndFrequencyTextDescriptionWithTime":"8 min every 0.5 hours","reminderStartTime":"01:00:00","reminderStartTimeEpochSeconds":1541120400,"nextReminderTimeEpochSeconds":1541120400,"timeZoneOffset":300}]',
      'slim.request.form_hash' =>
      [
      ],
      'slim.request.query_hash' =>
      [
        'appName' => 'QuantiModo',
        'appVersion' => '2.8.1101',
        'clientId' => 'quantimodo',
      ],
      'responseStatusCode' => 201,
      'unixtime' => 1541110937,
      'requestDuration' => 7.2121570110321045,
    ];
}
