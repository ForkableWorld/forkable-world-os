<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\A\AppSettings;
use App\AppSettings\AppSettings;
use App\AppSettings\AppSettingsResponse;
use App\Logging\QMLog;
use App\Mail\CollaboratorInvitationEmail;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Slim\Middleware\QMAuth;
use App\Types\QMArr;
use Tests\SlimStagingTestCase;
class AppSettingsForAppBuilderTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testCollaboratorInvitationEmail(){
        $m = new CollaboratorInvitationEmail("m@thinkbynumbers.org", "open-cures");
        $html = $m->render();
        $this->compareHtmlPage("email", $html);
    }
    public function testAppSettingsForAppBuilder(){
		QMAuth::loginMike();
		$expectedString = '';
        /** @var AppSettingsResponse $responseBody */
        $responseBody = $this->callAndCheckResponse($expectedString);
        QMLog::info("Got ".count($responseBody->allAppSettings)." apps");
        foreach($responseBody->allAppSettings as $appSettings){
            /** @var AppSettings $appSettings */
            $this->assertNull($appSettings->clientSecret, "We should only get users and client secret for a single app");
	        $this->assertNull($appSettings->users, "We should only get users and client secret for a single app");
	        $this->assertNull($appSettings->collaborators, "We should only get users and client secret for a single app");
        }
        $matches = QMArr::getElementsWithPropertyMatching('clientId', 'quantimodo',
            $responseBody->allAppSettings);
        /** @var AppSettings $qmAppSettings */
        $qmAppSettings = $matches[0];
		$this->checkTestDuration(12);
		$this->checkQueryCount(6);
	}
	public $expectedResponseSizes = [
      'allAppSettings' => 6943.784,
    ];
	public $slimEnvironmentSettings = [
      'REQUEST_METHOD' => 'GET',
      'REMOTE_ADDR' => '10.0.2.2',
      'SCRIPT_NAME' => '',
      'PATH_INFO' => '/api/v1/appSettings',
      'SERVER_NAME' => '_',
      'SERVER_PORT' => '443',
      'HTTP_X_FIRELOGGER' => '1.3',
      'HTTP_COOKIE' => '_ga=GA1.2.956197214.1538009354; __utmc=109117957; __utmz=109117957.1538493640.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); fbm_225078261031461=base_domain=.quantimo.do; heateorSsSLOptin=1; PHPSESSID=cache-sync-status; redirect=1; testing=1; sid=49b5db98a7d73aeed4abd91787eef508; tk_tc=Kx%2BnLDE5JBN3ngi1; __cfduid=d53bafe7d8296608d7b879d87ce1442af1539920894; _gid=GA1.2.537874804.1540136447; __utma=109117957.956197214.1538009354.1538493640.1540352295.2; final_callback_url=https%3A%2F%2Flocal.quantimo.do%2Fionic%2FModo%2Fsrc%2Fconfiguration-index.html',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
      'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
      'HTTP_CACHE_CONTROL' => 'no-cache',
      'HTTP_PRAGMA' => 'no-cache',
      'HTTP_CONNECTION' => 'keep-alive',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => '',
      'slim.url_scheme' => 'https',
      'slim.input' => '',
      'slim.request.query_hash' => [
        'all' => 'true',
        'designMode' => 'true',
        'appName' => 'QuantiModo',
        'accessToken' => BaseAccessTokenProperty::ADMIN_TEST_TOKEN,
        'clientId' => 'quantimodo',
        'platform' => 'web',
	      'includeClientSecret' => 'true',
      ],
      'responseStatusCode' => 200,
      'unixtime' => 1540412859,
      'requestDuration' => 23.78239893913269,
    ];
}
