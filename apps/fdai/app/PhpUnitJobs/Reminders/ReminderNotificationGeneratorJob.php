<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Reminders;
use App\Computers\ThisComputer;
use App\Exceptions\UserNotFoundException;
use App\Logging\QMLog;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\Base\BaseLatestTrackingReminderNotificationNotifyAtProperty;
use App\Properties\TrackingReminder\TrackingReminderLastTrackedProperty;
use App\Properties\TrackingReminder\TrackingReminderLatestTrackingReminderNotificationNotifyAtProperty;
use App\Properties\TrackingReminder\TrackingReminderStartTrackingDateProperty;
use App\Properties\User\UserEarliestReminderTimeProperty;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Utils\AppMode;
use App\Utils\QMProfile;
use Tests\QMBaseTestCase;
class ReminderNotificationGeneratorJob extends JobTestCase {
    private const PROFILE = false;
    protected static int $numberOfNewNotifications;
    public static function cleanup(): void{
        UserEarliestReminderTimeProperty::fixInvalidRecords();
        QMTrackingReminderNotification::deleteOldNotifications();
        TrackingReminderStartTrackingDateProperty::updateWhereNull( date('Y-m-d'));
        BaseLatestTrackingReminderNotificationNotifyAtProperty::updateAll();
        TrackingReminderLastTrackedProperty::updateAll();
        TrackingReminderLatestTrackingReminderNotificationNotifyAtProperty::updateAll();
        TrackingReminder::deleteForDeletedUsers();
        //$changed = TrackingReminderReminderFrequencyProperty::reduceFrequencyForInactiveUsers();
    }
    public function testGenerateReminderNotificationsJob(): void {
        $created = TrackingReminderNotification::createdInLastXHours(24);
        $due = TrackingReminderNotification::dueInNextXMinutes(24*60);
        $active = TrackingReminder::whereActive()->count();
        QMProfile::profileIfEnvSet();
        ReminderNotificationGeneratorJob::deleteOldAndCreateNewNotifications();
        QMProfile::endProfile();
        $this->assertGreaterThan(0, $due);
        $this->assertGreaterThan(0, $created);
        if($due < $active){
            $this->assertGreaterThan(0, ReminderNotificationGeneratorJob::getNumberOfNewNotifications());
        }
    }
    public function checkThatWeHaveFutureNotificationsForAllActiveReminders(): void{
        $qb = TrackingReminder::query()
            ->where(TrackingReminder::FIELD_REMINDER_FREQUENCY, '<', 2 * 86400)
            ->active()
            ->withoutFutureNotifications();
        $ids = $qb->pluck(TrackingReminder::FIELD_ID);
        \App\Logging\ConsoleLog::info(count($ids)." active reminders without future notifications");
        foreach($ids as $id){
            $reminder = TrackingReminder::find($id);
            $notifications = $reminder->getFutureNotifications();
            $count = $notifications->count();
            if($count){
                $reminder->logError("There are $count future notifications but ".
                    TrackingReminder::FIELD_LATEST_TRACKING_REMINDER_NOTIFICATION_NOTIFY_AT." is ".
                    $reminder->latest_tracking_reminder_notification_notify_at.": ".$reminder->getUrl());
            } else {
                $reminder->logError("No future notifications! ");
            }
        }
        $this->assertCount(0, $ids);
    }
	public static function generateForUser(int $userId = null): int{
		return self::createTrackingReminderNotifications($userId);
	}
    public static function deleteOldAndCreateNewNotifications(){
		self::resetStartTime();
        self::cleanup();
        self::$numberOfNewNotifications = self::createTrackingReminderNotifications();
		return self::$numberOfNewNotifications;
    }
    /**
     * @return int
     */
    public static function createTrackingReminderNotifications(int $userId = null): int{
        $qb = TrackingReminder::whereNeedNotifications($userId);
		try {
			$ids = $qb->pluck('id');
		} catch (\Throwable $e){
			$sql = $qb->toSql();
		    QMLog::info($sql);
		    le($e);
		}
        $total = count($ids);
        QMLog::info(count($ids)." active reminders without any notifications due more than 24 hours from now...");
        $reminderCount = 0;
        $numberOfNotificationsTotal = 0;
        $alreadyUpToDate = 0;
        foreach ($ids as $id) {
            if($profile = false){QMProfile::startLiveProf();}
            $r = TrackingReminder::find($id);
            if(!$r){
                QMLog::error("Could not find reminder with id: $id.  Maybe they just deleted it.");
                continue;
            }
            $reminderCount++;
            $percentComplete = round($reminderCount / count($ids) * 100);
            QMLog::info("$reminderCount of $total tracking reminders ($percentComplete% complete)...");
            ThisComputer::outputMemoryUsageIfEnabledOrDebug();
            try {
                $newNotifications = $r->createNotifications();
            } catch (UserNotFoundException $e){
                QMLog::info(__METHOD__.": ".$e->getMessage());
                continue;
            }
            if(!$newNotifications){
                QMLog::error("Why did we get this reminder $r if it didn't need notifications? latest_tracking_reminder_notification_notify_at is ".$r->latest_tracking_reminder_notification_notify_at." and frequency is ".$r->reminder_frequency." so it shouldn't have been gotten if latest_tracking_reminder_notification_notify_at was greater than ".
                $r->getLatestNotificationCutoffAt());
                $alreadyUpToDate++;
                continue;
            }
            $numberOfNotificationsTotal += $newNotifications;
            if($profile){QMProfile::endProfile();}
			if(AppMode::isStagingUnitTesting() && QMBaseTestCase::getDuration() > 15){
				QMLog::info("Breaking because test takes too long");
				break;
			}
        }
        QMLog::info("Inserted $numberOfNotificationsTotal new notifications. $alreadyUpToDate reminders were already up to date.");
        return $numberOfNotificationsTotal;
    }
    /**
     * @return int
     */
    public static function getNumberOfNewNotifications(): int{
        return self::$numberOfNewNotifications;
    }
}
