<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests;
use App\Models\Application;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Model\Notifications\SlackNotification;
use App\Slim\Model\Slack\SlackAttachment;
use App\Slim\Model\Slack\SlackMessage;
use Tests\UnitTestCase;
class SlackNotificationTest extends UnitTestCase{
    public function testSlackAttachments(){
        $m = new SlackMessage(__FUNCTION__);
        $attachments = $m->getAttachments();
        $this->assertCount(1, $attachments, "We should only have a link to the test");
        $titles = collect($attachments)->map(function($a){
            /** @var SlackAttachment $a */
            return $a->getTitle();
        });
        $this->assertContains(__FUNCTION__, $titles->toArray());
        //$m->send(__FUNCTION__);
    }
    public function testSendSlackNotification() {
        $enabled = false;
        if($enabled){
            $slackNotification = new SlackNotification("test message", "build");
            $result = $slackNotification->sendSlackMessage();
            $this->assertEquals("ok", $result->error);
            $slackNotification = new SlackNotification();
            $appSettings = Application::getClientAppSettings(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
            $appSettings->appStatus->betaDownloadLinks->chromeExtension = "https://s3.com";
            $result = $slackNotification->sendBuildNotifications(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT, $appSettings->appStatus->buildStatus, $appSettings->appStatus->betaDownloadLinks);
            $this->assertNotNull($result['chromeExtension']);
        } else {
            $this->skipTest("Skipped for some reason");
        }
    }
}
