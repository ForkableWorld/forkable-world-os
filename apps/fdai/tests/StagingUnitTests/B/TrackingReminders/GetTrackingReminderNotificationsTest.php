<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\B\TrackingReminders;
use App\Properties\Base\BaseAccessTokenProperty;
use Tests\SlimStagingTestCase;

class GetTrackingReminderNotificationsTest extends SlimStagingTestCase {
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public $expectedResponseSizes = [];
    public $slimEnvironmentSettings = array(
        'REQUEST_METHOD'          => 'GET',
        'REMOTE_ADDR'             => '216.165.246.53',
        'SCRIPT_NAME'             => '',
        'PATH_INFO'               => '/api/v3/trackingReminderNotifications',
        'SERVER_NAME'             => '_',
        'SERVER_PORT'             => '443',
        'HTTP_ACCEPT_LANGUAGE'    => 'en-US,en;q=0.9',
        'HTTP_ACCEPT_ENCODING'    => 'gzip, deflate, br',
        'HTTP_REFERER'            => 'https://medimodo.quantimo.do/index.html',
        'HTTP_SEC_FETCH_SITE'     => 'same-site',
        'HTTP_X_FRAMEWORK'        => 'ionic',
        'HTTP_X_PLATFORM'         => 'web',
        'HTTP_USER_AGENT'         => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36',
        'HTTP_X_CLIENT_ID'        => 'medimodo',
        'HTTP_ACCEPT'             => 'application/json',
        'HTTP_CONTENT_TYPE'       => 'application/json',
        'HTTP_AUTHORIZATION'      => 'Bearer '. BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,
        'HTTP_X_APP_VERSION'      => '2.9.919',
        'HTTP_ORIGIN'             => 'https://medimodo.quantimo.do',
        'HTTP_X_TIMEZONE'         => 'America/Los_Angeles',
        'HTTP_SEC_FETCH_MODE'     => 'cors',
        'HTTP_CONNECTION'         => 'keep-alive',
        'CONTENT_LENGTH'          => '',
        'CONTENT_TYPE'            => 'application/json',
        'slim.url_scheme'         => 'https',
        'slim.input'              => '',
        'slim.request.query_hash' => array(
            'sort'         => '-reminderTime',
            'limit'        => '100',
            'reminderTime' => '(lt)2019-10-09 12:57:42',
            'clientId'     => 'medimodo',
        ),
        'responseStatusCode'      => NULL,
        'unixtime'                => 1570625583,
        'requestDuration'         => 23.83959412574768,
    );
    public function testGetTrackingReminderNotifications(): void {
        $expectedString = '';
        $responseBody = $this->callAndCheckResponse($expectedString);
        $this->checkTestDuration(10);
        $this->checkQueryCount(6);
    }
}
