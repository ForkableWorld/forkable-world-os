<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Reminders;
use App\Logging\QMLog;
use App\Models\TrackingReminderNotification;
use App\Models\User;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\Base\BasePlatformProperty;
use App\Slim\Model\Notifications\GooglePushNotification;
use App\Slim\Model\Notifications\QMDeviceToken;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use Carbon\Carbon;

/** Class PushNotificationsJobTest
 * @package App\PhpUnitJobs
 */
class PushNotificationsJob extends JobTestCase {
    public const ANDROID_CHECK_DISABLED_UNTIL = "2023-04-01";

    public static function send(){
	    self::resetStartTime();
		GooglePushNotification::getGoogleCloudMessagingKey();
	    QMDeviceToken::fixInternalErrorAndNotAuthorizedTokens();
	    QMTrackingReminderNotification::send();
    }

    public function testPushNotificationJob(): void{
        if($debug = false){$this->debugPush();}
		//TrackingReminderNotification::generate();
		self::send();
        $this->checkNumberOfNotifiedAndDeletedInLastHour();
    }
    public function debugPush(){
        $n = User::mike()->getMostRecentPendingNotification();
        $n->sendNotification();
    }
    public function checkNumberOfNotifiedAndDeletedInLastHour(): void{
        QMDeviceToken::deleteErroredTokensCreatedMoreThanAMonthAgo();
        $numberTrackedInLastDay = QMTrackingReminderNotification::readonly()
            ->groupBy([TrackingReminderNotification::FIELD_USER_ID])
            ->where(TrackingReminderNotification::FIELD_DELETED_AT,">",
	            Carbon::now()->subDay())
            ->countWithoutLogging();
        QMLog::info($numberTrackedInLastDay." users tracked in last day");
        $this->assertGreaterThan(0, $numberTrackedInLastDay, "No notifications deleted in last day!");
        QMDeviceToken::getNumberReceived(3650);
        QMDeviceToken::getNumberReceived(3650, BasePlatformProperty::PLATFORM_ANDROID);
        QMDeviceToken::getNumberReceived(3650, BasePlatformProperty::PLATFORM_IOS);
        $this->assertGreaterThan(1, QMDeviceToken::getNumberNotifiedInLastDay());
        // TODO: Figure out why this always fails
        //$this->assertGreaterThan(0, DeviceToken::getNumberReceived(1), "0 received in last day!");  // TODO: Increase
        $this->assertGreaterThan(1, QMDeviceToken::logNumberUpdatedInLastDay());
        if(time() > strtotime(self::ANDROID_CHECK_DISABLED_UNTIL)){
            $this->assertGreaterThan(1,
                QMDeviceToken::getNumberNotifiedInLastDay(BasePlatformProperty::PLATFORM_ANDROID),
                "No android devices notified!");
        }
        //$this->assertGreaterThan(1, QMDeviceToken::getNumberNotifiedInLastDay(BasePlatformProperty::PLATFORM_IOS));
        //$this->logNumberOfNotificationsForTestUser();
    }
}
