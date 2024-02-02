<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Models;
use App\PhpUnitJobs\Reminders\ReminderNotificationGeneratorJob;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Storage\DB\Writable;
use Tests\UnitTestCase;
class UpdateTrackingReminderImagesTaskTest extends UnitTestCase
{
    public function testUpdateTrackingReminderImagesTask(){
        $DISABLED_UNTIL = "2023-04-01";
        if(time() < strtotime($DISABLED_UNTIL)){ // Might be temporarily broken
            $this->skipTest('This is failing. API down temporarily.');
            return;
        }
        $this->setAuthenticatedUser(1);
        $db = Writable::db();
        $db->table('tracking_reminder_notifications')->delete();
        $db->table('tracking_reminders')->delete();
        $this->postApiV6('trackingReminders', [
            'variableName' => 'Wellbutrin',
            'variableCategoryName' => 'Treatments',
            'unitAbbreviatedName' => 'mg',
            'reminderFrequency' => 3600,
            'defaultValue' => 150,
            'timeZoneOffset' => 300
        ]);
        ReminderNotificationGeneratorJob::deleteOldAndCreateNewNotifications();
        TrackingReminderNotificationsTest::checkLatestNotifyAtOnReminders();
	    $trackingRemindersFromApi = $this->getAndCheckTrackingReminders();
        $this->assertNotNull($trackingRemindersFromApi[0]->imageUrl);
        //$this->assertEquals($trackingReminderFromApi->trackingReminderImageUrl, $trackingReminderFromApi->imageUrl);
        $trackingReminderNotifications = QMTrackingReminderNotification::getTrackingReminderNotifications(
            $this->setAuthenticatedUser(1), []);
        foreach ($trackingReminderNotifications as $trackingReminderNotification){
            if($trackingReminderNotification->getVariableName() === "Wellbutrin"){
                $this->assertNotNull($trackingReminderNotification->getTrackingReminderImageUrl(),
                    "No Wellbutrin image from NIH API");
                $this->assertEquals($trackingReminderNotification->getTrackingReminderImageUrl(),
                    $trackingReminderNotification->getImageUrl());
            }
        }
    }
}
