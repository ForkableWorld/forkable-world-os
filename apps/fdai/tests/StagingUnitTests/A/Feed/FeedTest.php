<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\A\Feed;
use App\Cards\QMCard;
use App\Cards\TrackingReminderNotificationCard;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\Logging\QMLog;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Properties\Study\StudyUserTitleProperty;
use App\Slim\Controller\Feed\UserFeedResponse;
use App\Utils\QMProfile;
use Tests\SlimStagingTestCase;
class FeedTest extends SlimStagingTestCase
{
    public const DISABLED_UNTIL = "2021-11-11";
    public function testGlobalVariableRelationshipButtons(){
        $c = QMGlobalVariableRelationship::find(65694280);
        $card = $c->findInMemoryOrNewQMStudy()->getCard();
        $buttons = $card->getButtons();
        foreach($buttons as $b){
            $this->assertNotContains("local", $b->link ?? $b->webhookUrl);
        }
    }
    public function testFeed(){
        if($this->weShouldSkip()){return;}
		$expectedString = '';
        /** @var UserFeedResponse $response */
        $response = $this->callAndCheckResponse($expectedString);
	    $cards = $response->cards;
        $nonTestNotifications = $notificationsByVariable = $studies = $notifications = $byNames = [];
		foreach ($cards as $card){
		    if($card->type === QMCard::TYPE_tracking_reminder_notification){
                /** @var TrackingReminderNotificationCard $card */
                $notifications[] = $card;
                if(stripos($card->headerTitle, "test") === false){
                    $nonTestNotifications[] = $card->headerTitle;
                }
		        $notificationsByVariable[$card->headerTitle][] = $card;
		    }
            if($card->type === QMCard::TYPE_study){
                $studies[] = $card;
            }
            $string = json_encode($card);
            if(strpos($string, 'local.quantimo') !== false || strpos($string, 'utopia.quantimo') !== false){
                le("Should not contain local or utopia: ". $string);
            }
            $byNames[$card->id] = $card;
        }
		$numberOfStudies = count($studies);
        $numberOfNonTestNotifications =  count($nonTestNotifications);
        QMLog::info($numberOfStudies." studies returned");
        QMLog::info($numberOfNonTestNotifications." non-test notifications returned");
        $studyTitles = implode("\n\t-", StudyUserTitleProperty::pluckColumn($studies));
        $variableNames = implode("\n\t-", array_keys($notificationsByVariable));
        // TODO: Change to $numberOfStudies > $numberOfNonTestNotifications/2
        $this->assertTrue($numberOfStudies > $numberOfNonTestNotifications/3,
            $numberOfStudies." studies returned and $numberOfNonTestNotifications non-test notifications returned
studies: 
$studyTitles

notification variables: 
$variableNames
");
		$this->checkTestDuration(10);
		$this->checkQueryCount(14);
	}
	public $expectedResponseSizes = [
      //'cards' => 659.0,
      //'errors' => 2.0,
    ];
	public $slimEnvironmentSettings = [
      'REQUEST_METHOD' => 'GET',
      'REMOTE_ADDR' => '10.0.2.2',
      'SCRIPT_NAME' => '',
      'PATH_INFO' => '/api/v3/feed',
      'QUERY_STRING' => 'clientId=quantimodo&platform=web',
      'SERVER_NAME' => '_',
      'SERVER_PORT' => '443',
      'HTTP_X_FIRELOGGER' => '1.3',
      'HTTP_COOKIE' => 'XDEBUG_SESSION=PHPSTORM; _ga=GA1.2.956197214.1538009354; __cfduid=d1d1a0e2822985ef9d386e30f478657f01538012107; __utma=109117957.956197214.1538009354.1538493640.1538493640.1; __utmc=109117957; __utmz=109117957.1538493640.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); fbm_225078261031461=base_domain=.quantimo.do; heateorSsSLOptin=1; PHPSESSID=cache-sync-status; _gid=GA1.2.278455847.1539094919; final_callback_url=https%3A%2F%2Fquantimo.do%2Fideas%2Fidea%2Fgovernment-exchange-policy%2F',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_REFERER' => 'https://local.quantimo.do/ionic/Modo/src/index.html',
      'HTTP_X_FRAMEWORK' => 'ionic',
      'HTTP_X_PLATFORM' => 'web',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
      'HTTP_X_CLIENT_ID' => 'quantimodo',
      'HTTP_ACCEPT' => 'application/json',
      'HTTP_CONTENT_TYPE' => 'application/json',
      'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::ADMIN_TEST_TOKEN,
      'HTTP_X_APP_VERSION' => '2.8.930',
      'HTTP_CACHE_CONTROL' => 'no-cache',
      'HTTP_PRAGMA' => 'no-cache',
      'HTTP_CONNECTION' => 'keep-alive',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => 'application/json',
      'slim.url_scheme' => 'https',
      'slim.input' => '',
      'slim.request.query_hash' =>
      [
        'clientId' => 'quantimodo',
        'platform' => 'web',
      ],
      'responseStatusCode' => 200,
      'unixtime' => 1539219056,
      'requestDuration' => 28.668803930282593,
    ];
}
