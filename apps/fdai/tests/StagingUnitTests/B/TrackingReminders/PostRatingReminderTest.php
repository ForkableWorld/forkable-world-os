<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\B\TrackingReminders;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Variables\QMUserVariable;
use Tests\SlimStagingTestCase;

class PostRatingReminderTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testPostRatingReminder(){
        $expectedString = 'Aaa Test Reminder Snooze';
        $responseBody = $this->callAndCheckResponse($expectedString);
        $this->checkTestDuration(9);
        $this->checkQueryCount(16);
    }
    public $expectedResponseSizes = [];
    public $slimEnvironmentSettings = array (
        'REQUEST_METHOD' => 'POST',
        'REMOTE_ADDR' => '10.190.186.216',
        'SCRIPT_NAME' => '',
        'PATH_INFO' => '/api/v3/trackingReminders',
        'SERVER_NAME' => '_',
        'SERVER_PORT' => '443',
        'HTTP_CF_CONNECTING_IP' => '52.201.131.218',
        'HTTP_ORIGIN' => 'https://quantimodo.quantimo.do',
        'HTTP_REFERER' => 'https://quantimodo.quantimo.do/ionic/Modo/www/index.html',
        'HTTP_CONTENT_TYPE' => 'application/json',
        'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,
        'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64; rv:45.9.0) Gecko/20100101 Firefox/45.9.0 Ghost Inspector',
        'HTTP_CF_VISITOR' => '{"scheme":"https"}',
        'HTTP_X_FORWARDED_PROTO' => 'https',
        'HTTP_CF_RAY' => '47291fbaa82cc19a-IAD',
        'HTTP_X_FORWARDED_FOR' => '162.158.78.20',
        'HTTP_CF_IPCOUNTRY' => 'US',
        'HTTP_ACCEPT_ENCODING' => 'gzip',
        'CONTENT_LENGTH' => '807',
        'CONTENT_TYPE' => 'application/json',
        'slim.url_scheme' => 'https',
        'slim.input' => '[{"variableId":5949711,"variableName":"Aaa Test Reminder Snooze","combinationOperation":"MEAN",
            "variableCategoryName":"Foods","unitAbbreviatedName":"/10","pngPath":"img/variable_categories/foods.png",
            "svgPath":"img/variable_categories/foods.svg","upc":"089836001191","reminderFrequency":1800,
            "firstDailyReminderTime":null,"secondDailyReminderTime":null,"thirdDailyReminderTime":null,"enabled":true,
            "unitId":203,"valueAndFrequencyTextDescription":"Every 30 minutes","startTrackingDate":null,
            "stopTrackingDate":null,"defaultValue":null,"reminderStartTimeEpochTime":1541016000,
            "reminderStartTimeLocal":"20:00:00","valueAndFrequencyTextDescriptionWithTime":"Every 0.5 hours",
            "reminderStartTime":"20:00:00","reminderStartTimeEpochSeconds":1541016000,
            "nextReminderTimeEpochSeconds":1541016000,"timeZoneOffset":0}]',
        'slim.request.form_hash' =>
            array (
            ),
        'slim.request.query_hash' =>
            array (
                'appName' => 'QuantiModo',
                'appVersion' => '2.8.1031',
                'clientId' => 'quantimodo',
            ),
        'responseStatusCode' => NULL,
        'unixtime' => 1541020390,
        'requestDuration' => 0.8237819671630859,
    );
}
