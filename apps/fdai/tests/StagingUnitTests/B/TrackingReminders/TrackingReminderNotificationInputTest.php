<?php /** @noinspection PhpMissingReturnTypeInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\B\TrackingReminders;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Models\Variable;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Model\QMUnit;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Slim\Model\User\QMUser;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\CommonVariables\PhysiqueCommonVariables\BodyWeightCommonVariable;
use Tests\SlimStagingTestCase;

class TrackingReminderNotificationInputTest extends SlimStagingTestCase
{
    public function testNotificationTime(){
	    $parnate = Variable::findByNameIdOrSynonym("Tranylcypromine (Parnate)");
	    $uv = $parnate->getOrCreateUserVariable(230);
	    $uv->tracking_reminders()->forceDelete();
	    $reminders = $uv->tracking_reminders()
		    //->where(TrackingReminder::FIELD_REMINDER_START_TIME, "08:00:00")
		    ->get();
	    $this->assertEquals(0, $reminders->count());
	    $r = $uv->getOrCreateTrackingReminder();
	    //$r->reminder_frequency = 86400;
	    //$r->save();
	    $this->assertEquals(86400, $r->reminder_frequency);
	    $this->assertEquals("20:00:00", $r->reminder_start_time);
	    $this->assertEquals("20:00:00", $r->getReminderStartTimeLocal());
	    $this->assertEquals("01:00:00", $r->getReminderStartTimeUtc());
	    $r->deleteNotifications();
	    $r->createNotifications();
	    $n = $r->getOrCreateTrackingReminderNotification();
	    $this->assertContains("8:00 PM", $n->getNotifyAtHumanized());
	    $notifications = QMTrackingReminderNotification::getPastTrackingReminderNotifications([
		    TrackingReminderNotification::FIELD_VARIABLE_ID => $r->variable_id,
		    TrackingReminderNotification::FIELD_USER_ID => 230
	    ]);
	    $this->assertGreaterThan(0, $notifications);
	    foreach($notifications as $n){
		    $this->assertStringContainsString("01:00:00", $n->getNotifyAtUtc());
		    $this->assertStringContainsString("20:00:00", $n->getNotifyAtLocal());
		    $this->assertStringContainsString("8:00 PM", $n->getNotifyAtHumanized());
	    }
    }
    public function testNotificationInputs(): void{
        $this->checkForVariable("Back Pain", 5, QMUnit::INPUT_TYPE_saddestFaceIsFive);
        //$this->checkForVariable("Fresh Air", 3, Unit::INPUT_TYPE_yesOrNo);
        $this->checkForVariable("Ate Lunch", 3, QMUnit::INPUT_TYPE_yesOrNo);
        $this->checkForVariable(BodyWeightCommonVariable::NAME, 4, QMUnit::INPUT_TYPE_value);
        $this->checkForVariable(OverallMoodCommonVariable::NAME, 5, QMUnit::INPUT_TYPE_happiestFaceIsFive);
        $this->checkForVariable("Psoriasis Severity", 5, QMUnit::INPUT_TYPE_saddestFaceIsFive);
	}
    /**
     * @group Production
     * @param string $variableName
     * @param int $expectedNumberOfButtons
     * @param string $expectedInputType
     */
    public function checkForVariable(string $variableName, int $expectedNumberOfButtons, string $expectedInputType): void{
        $n = $this->getOrCreateTrackingReminderNotification($variableName);
        $buttons = $n->getNotificationActionButtons();
        if(count($buttons) !== $expectedNumberOfButtons){
            foreach($buttons as $button){
                $button->logInfo($variableName);
            }
        }
        $this->assertCount($expectedNumberOfButtons, $buttons);
        $inputType = $n->getInputType();
        $this->assertEquals($expectedInputType, $inputType);
    }
    /**
     * @param string $variableName
     * @return QMTrackingReminderNotification
     */
    protected function getOrCreateTrackingReminderNotification(string $variableName){
        $u = QMUser::getAnyOldTestUser()->getQMUser();
        $this->assertNotNull($u->getId());
        $v = $u->getOrCreateQMUserVariable($variableName);
        $this->assertEquals($variableName, $v->name);
        $r = QMTrackingReminder::getOrCreate([
            'userId' => $u->getId(),
            'variableId' => $v->getVariableIdAttribute(),
            TrackingReminder::FIELD_CLIENT_ID => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT
        ]);
        $this->assertEquals($r->variableName, $v->name);
        $notifications = $r->getOrCreateNotifications();
        $n = $notifications[0];
        return $n;
    }
}
