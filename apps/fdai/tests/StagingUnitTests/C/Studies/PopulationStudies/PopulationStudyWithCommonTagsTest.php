<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\C\Studies\PopulationStudies;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use Tests\SlimStagingTestCase;
class PopulationStudyWithCommonTagsTest extends SlimStagingTestCase {
    public const JOB_NAME = "Production-C-phpunit";
    public function testPopulationStudyWithCommonTags(): void{
        if($this->skipIfQueued(static::JOB_NAME)){return;}
        $mood = OverallMoodCommonVariable::instance();
        //$mood->setAllRawMeasurementsWithTagsJoinsChildrenInCommonUnitInChronologicalOrder();
        $expectedString = "Acetyl-L-Carnitine";
        $this->callAndCheckResponse($expectedString);
    }
    public $slimEnvironmentSettings = [
        'REQUEST_METHOD' => 'GET',
        'REMOTE_ADDR' => '10.0.2.2',
        'SCRIPT_NAME' => '',
        'PATH_INFO' => '/api/v4/study',
        'SERVER_NAME' => '_',
        'SERVER_PORT' => '443',
        'HTTP_X_FIRELOGGER' => '1.3',
        'HTTP_COOKIE' => '__stripe_mid=6f430ca4-9d0b-4469-9f73-cdb54da696e6; _ga=GA1.2.1225964174.1534196305; __cfduid=da1d1624dc26672dff7aab1d71c4dc2071534362124; PHPSESSID=cache-sync-status; bp-members-scope=all; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=testuser%7C1538244433%7C86db9a3d39d98100ae332be88d45d355%7Cquantimodo; XDEBUG_SESSION=PHPSTORM; final_callback_url=https%3A%2F%2Fquantimo.do%2Fwp-admin%2Fplugins.php; laravel_session=eyJpdiI6IkgrMG1aODc5WmRPenk5SzhrTThBWGc9PSIsInZhbHVlIjoidHJBZjJYak9NQnBtTUgrVlhWZ0h3cUh5QjdkQkhmMEhESjZ6MG5VREVpWWMrOUhlNjRwMXQrc3VSbWM5UFRLQUVsOXU4QmRUSXNrMnZKeStybTNHZnc9PSIsIm1hYyI6ImI2NDE5YWFmZDA5NjhhMWYyYmQyNGU4OGE0NzQ1ZmU1ZGQ1MDkyOWEyZDIyNGM2NTI3YTMxNTQ5OWQ4ZGExYTMifQ%3D%3D; _gid=GA1.2.749958063.1537994383',
        'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
        'HTTP_REFERER' => 'https://local.quantimo.do/ionic/Modo/src/index.html',
        'HTTP_X_FRAMEWORK' => 'ionic',
        'HTTP_X_PLATFORM' => 'web',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
        'HTTP_X_CLIENT_ID' => 'quantimodo',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_CONTENT_TYPE' => 'application/json',
        'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,
        'HTTP_X_APP_VERSION' => '2.8.926',
        'HTTP_CACHE_CONTROL' => 'no-cache',
        'HTTP_PRAGMA' => 'no-cache',
        'HTTP_CONNECTION' => 'keep-alive',
        'CONTENT_LENGTH' => '',
        'CONTENT_TYPE' => 'application/json',
        'slim.url_scheme' => 'https',
        'slim.input' => '',
        'slim.request.query_hash' => [
            "causeVariableName"  => "Acetyl-L-Carnitine",
            "effectVariableName" => "Overall Mood",
            "clientId"           => "oauth_test_client",
            "includeCharts"      => "true",
            "platform"           => "web",
            "studyId"            => "cause-1248-effect-1398-population-study"
        ],
        'slim.request.form_hash' => [],
        'responseStatusCode' => 200,
        'unixtime' => 1537999141,
    ];
}
