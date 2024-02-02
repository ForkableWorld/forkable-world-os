<?php
namespace Tests\StagingUnitTests\B\TrackingReminders;
use App\Logging\QMLog;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Models\User;
use App\PhpUnitJobs\Reminders\ReminderNotificationGeneratorJob;
use App\Properties\TrackingReminder\TrackingReminderLatestTrackingReminderNotificationNotifyAtProperty;
use App\Storage\QueryBuilderHelper;
use Tests\SlimStagingTestCase;
class GenerateReminderNotificationsTest extends SlimStagingTestCase
{
    public function testTimeZone(){
        $user = User::whereUserLogin("losinglucas-icloud-com")->first();
        $qmUser = $user->getQMUser();
        $first = $qmUser->utcToLocalHis(1591160400);
        $second =  $qmUser->utcToLocalHis(1591160400);
        $this->assertEquals($first, $second, "utcToHis should return the same value on repeated calls!");
    }
    public function testGenerateReminderNotifications(){
        ReminderNotificationGeneratorJob::cleanup();
        $needNotifications = TrackingReminder::whereNeedNotifications()->count();
        if(!$needNotifications){
            $M = $this->deleteNotifications();
        } else {
            $M = "Already had $needNotifications reminders that needed notifications";
        }
        $countBefore = TrackingReminderNotification::count();
        ReminderNotificationGeneratorJob::deleteOldAndCreateNewNotifications();
        $countAfter = TrackingReminderNotification::count();
        $this->assertGreaterThan($countBefore, $countAfter, $M.".
        $countBefore notifications after that deletion and before generation
        $countAfter notifications after generation");
    }
    /**
     * @return string
     */
    protected function deleteNotifications(): string{
        $notifications = TrackingReminderNotification::query()
                ->orderBy(TrackingReminderNotification::FIELD_NOTIFY_AT, 'desc')
                ->whereRaw(TrackingReminderNotification::FIELD_NOTIFY_AT."> NOW()")
                ->limit(1)
                ->get();
        $allDeleted = 0;
        foreach($notifications as $one){
            $one->logInfo("Deleting...");
            $tr = $one->tracking_reminder;
            $qb = $tr->future_notifications();
            $sql = QueryBuilderHelper::toPreparedSQL($qb);
            $this->assertGreaterThan(0, $qb->count(),
                "Should have a notification for reminder:
$tr 
because we just got this notification: 
$one
notify_at: $one->notify_at
SQL:   
             $sql");
            $allDeleted += $qb->count();
            $qb->forceDelete();
            $afterDeletion = $qb->count();
            $this->assertEquals(0, $afterDeletion, "Should have 0 future $tr notifications after deletion");
        }
        TrackingReminderLatestTrackingReminderNotificationNotifyAtProperty::updateAll();
        foreach($notifications as $one){
            /** @var TrackingReminder $reminder */
            $reminder = TrackingReminder::find($one->tracking_reminder_id);
            $at = $reminder->latest_tracking_reminder_notification_notify_at;
            if($at){
                try {
                    $this->assertPast($at);
                } catch (\Throwable $e){
                    QMLog::info(__METHOD__.": ".$e->getMessage());
                }
            }
            $this->assertTrue($reminder->needsNotifications());
        }
        $M = "Deleted $allDeleted notifications due later (so we'll need to make some for this test)";
        \App\Logging\ConsoleLog::info($M);
        return $M;
    }
}
