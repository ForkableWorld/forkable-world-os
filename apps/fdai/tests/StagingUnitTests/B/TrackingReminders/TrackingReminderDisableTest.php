<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\B\TrackingReminders;
use App\Properties\Base\BaseAccessTokenProperty;
use Tests\SlimStagingTestCase;

class TrackingReminderDisableTest extends SlimStagingTestCase
{
    public function testTrackingReminderDisable(){
		$expectedString = '';
		$responseBody = $this->callAndCheckResponse($expectedString);
		$this->checkTestDuration(5);
		$this->checkQueryCount(13);
		//$this->comparePreviousResponse($this->responseBody);
	}
	public $expectedResponseSizes = [
        //'data' => 2122.0,  // Too big for memory to check
    ];
	public $slimEnvironmentSettings = [
        'REQUEST_METHOD' => 'POST',
        'REMOTE_ADDR' => '10.0.2.2',
        'SCRIPT_NAME' => '',
        'PATH_INFO' => '/api/v3/trackingReminders',
        'QUERY_STRING' => 'appName=QuantiModo&appVersion=2.8.929&clientId=quantimodo',
        'SERVER_NAME' => '_',
        'SERVER_PORT' => '443',
        'HTTP_X_FIRELOGGER' => '1.3',
        'HTTP_COOKIE' => 'XDEBUG_SESSION=PHPSTORM; _ga=GA1.2.956197214.1538009354; __cfduid=d1d1a0e2822985ef9d386e30f478657f01538012107; final_callback_url=https%3A%2F%2Flocal.quantimo.do%2Fionic%2FModo%2Fsrc%2Findex.html%23%2Fapp%2Flogin%3Ffinal_callback_url%3Dhttps%253A%252F%252Flocal.quantimo.do%252Fionic%252FModo%252Fsrc%252Findex.html%2523%252Fapp%252Flogin%26clientId%3Dquantimodo%26clientSecret%3DTcQArZOoUecO9O4aBvntUl6v1QzzsU38%26message%3DConnected%2BGoogle%2BPlus%2521; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1539234481%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; _gid=GA1.2.1384267982.1538253686; _gat=1',
        'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
        'HTTP_REFERER' => 'https://local.quantimo.do/ionic/Modo/src/index.html',
        'HTTP_CONTENT_TYPE' => 'application/json',
        'HTTP_USER_AGENT'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
        'HTTP_AUTHORIZATION'      => 'Bearer '. BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,
        'HTTP_ORIGIN'             => 'https://local.quantimo.do',
        'HTTP_ACCEPT'             => 'application/json',
        'HTTP_CACHE_CONTROL'      => 'no-cache',
        'HTTP_PRAGMA'             => 'no-cache',
        'HTTP_CONNECTION'         => 'keep-alive',
        'CONTENT_LENGTH'          => '3264',
        'CONTENT_TYPE'            => 'application/json',
        'slim.url_scheme'         => 'https',
        'slim.input'              => '[{"unitAbbreviatedName":"mg","reminderFrequency":86400,"variableCategoryName":"Treatments","variableName":"Remeron Powder","actionArray":[{"action":"track","callback":"trackLastValueAction","modifiedValue":30,"title":"30 mg","longTitle":"Record 30 mg","shortTitle":"30"},{"action":"track","callback":"trackSecondToLastValueAction","modifiedValue":0,"title":"0 mg","longTitle":"Record 0 mg","shortTitle":"0"},{"action":"snooze","callback":"snoozeAction","modifiedValue":null,"title":"Snooze","longTitle":"Snooze","shortTitle":"Snooze"},{"action":"skip","callback":"skipAction","modifiedValue":null,"title":"Skip","longTitle":"Skip","shortTitle":"Skip"}],"userOptimalValueMessage":"Higher Remeron Powder intake predicts lower Overall Mood.  Your Overall Mood is generally highest after around 81.11 milligrams of Remeron Powder over the previous 7 days. ","clientId":"local","combinationOperation":"SUM","createdAt":"2016-02-08 03:48:00","displayName":"Remeron Powder","unitCategoryId":3,"unitCategoryName":"Weight","unitId":7,"unitName":"Milligrams","defaultValue":30,"fillingValue":0,"firstDailyReminderTime":null,"frequencyTextDescription":"Daily","frequencyTextDescriptionWithTime":"Daily at 10PM","id":100776,"inputType":"value","ionIcon":"ion-ios-medkit-outline","lastValue":30,"latestTrackingReminderNotificationReminderTime":"2018-09-29 03:00:00","localDailyReminderNotificationTimes":["22:00:00"],"localDailyReminderNotificationTimesForAllReminders":["07:00:00","07:00:09","07:00:21","08:00:00","09:00:00","10:00:00","12:00:00","13:00:00","18:00:00","19:00:00","20:00:00","20:19:24","21:00:00","22:00:00","22:30:00"],"manualTracking":true,"minimumAllowedValue":0,"nextReminderTimeEpochSeconds":1538276400,"numberOfRawMeasurements":691,"numberOfUniqueValues":2,"outcome":false,"pngPath":"img/variable_categories/treatments.png","pngUrl":"https://static.quantimo.do/img/variable_categories/treatments.png","productUrl":"https://www.amazon.com/MORINGA-OLEIFERA-Strength-Supplement-Capsules/dp/B071RXKRP6?linkCode=xm2&camp=2025&creative=165953&creativeASIN=B071RXKRP6","question":"Remeron Powder (mg)","longQuestion":"How many milligrams Remeron Powder did you have today?","reminderStartEpochSeconds":1507172400,"reminderStartTime":"03:00:00","reminderStartTimeLocal":"22:00:00","reminderStartTimeLocalHumanFormatted":"10PM","repeating":true,"secondToLastValue":0,"startTrackingDate":"2017-10-04","svgUrl":"https://static.quantimo.do/img/variable_categories/treatments.svg","trackingReminderId":100776,"updatedAt":"2018-09-27 04:16:48","userId":18535,"userVariableVariableCategoryId":13,"valueAndFrequencyTextDescription":"Daily","valueAndFrequencyTextDescriptionWithTime":"30 mg daily at 10:00 PM","variableCategoryId":13,"variableCategoryImageUrl":"https://static.quantimo.do/img/variable_categories/pill-96.png","variableId":5627161,"svgPath":"img/variable_categories/treatments.svg","total":null,"$$hashKey":"object:724","fromState":"app.variableListCategory","secondDailyReminderTime":null,"thirdDailyReminderTime":null,"enabled":false,"stopTrackingDate":"2018-09-28","reminderStartTimeEpochTime":1538276400,"reminderStartTimeEpochSeconds":1538276400,"timeZoneOffset":300}]',
        'slim.request.form_hash'  =>
      [
      ],
        'slim.request.query_hash' =>
      [
        'appName' => 'QuantiModo',
        'appVersion' => '2.8.929',
        'clientId' => 'quantimodo',
      ],
        'responseStatusCode'      => 201,
        'unixtime'                => 1538276190,
    ];
}
