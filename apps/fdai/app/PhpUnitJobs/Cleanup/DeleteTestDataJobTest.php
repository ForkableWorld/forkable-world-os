<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Cleanup;
use App\Logging\ConsoleLog;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Models\User;
use App\Models\Variable;
use App\Astral\Actions\DeleteTestUsersAction;
use App\PhpUnitJobs\JobTestCase;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Utils\QMStripe;
use App\Variables\QMCommonVariable;
use Carbon\Carbon;

class DeleteTestDataJobTest extends JobTestCase {
	public function testDeleteTestDataJob(){
		$this->deleteOldDeletedNotifications();
		DeleteTestUsersAction::deleteOldTestUsers();
		AppCleanupJob::deleteTestAppsCreatedMoreThan24HoursAgo();
		$this->deleteOldRemindersWithMinutelyFrequency();
		$this->deleteVariablesOlderThan48HoursWithTestInName();
		//QMWordPressApi::deleteTestPosts();
		$this->deleteTestCustomersAndSubscriptions();
		$this->assertTrue(true);
	}
	private function deleteOldRemindersWithMinutelyFrequency(){
		$minutelyTestReminders = TrackingReminder::query()->select([
				TrackingReminder::TABLE . "." . TrackingReminder::FIELD_USER_ID,
				TrackingReminder::TABLE . "." . TrackingReminder::FIELD_ID,
				TrackingReminder::TABLE . "." . TrackingReminder::FIELD_REMINDER_FREQUENCY,
			])
			->join(User::TABLE, TrackingReminder::TABLE . "." . TrackingReminder::FIELD_USER_ID,
				'=', User::TABLE . "." . User::FIELD_ID)
			//->whereRaw(\App\Models\User::TABLE . '.user_email LIKE "%testuser%"')
			->where(TrackingReminder::TABLE . "." . TrackingReminder::FIELD_CREATED_AT,
				"<", Carbon::now()->subDay())
            ->where(TrackingReminder::TABLE . "." .
				TrackingReminder::FIELD_REMINDER_FREQUENCY, ">", 0)
            ->where(TrackingReminder::TABLE . "." .
				TrackingReminder::FIELD_REMINDER_FREQUENCY, "<", 30 * 60)
            ->get();
		foreach($minutelyTestReminders as $reminderRow){
			QMTrackingReminder::deleteTrackingReminder($reminderRow->user_id, $reminderRow->id);
		}
	}
	private function deleteVariablesOlderThan48HoursWithTestInName(){
		$rows = QMCommonVariable::readonly()->where(Variable::FIELD_NAME, \App\Storage\DB\ReadonlyDB::like(), "%test variable%")
			->where(Variable::FIELD_CREATED_AT, '<', db_date(time() - 2 * 86400))->getArray();
		foreach($rows as $row){
			$v = QMCommonVariable::find($row->id);
			$v->deleteCommonVariableAndAllAssociatedRecords("is test variable", true);
		}
	}
	private function deleteTestCustomersAndSubscriptions(){
		$stripe = new QMStripe();
		$stripe->deleteTestCustomers();
	}
	/**
	 * @return void
	 */
	private function deleteOldDeletedNotifications(): void{
		$soFar = 0;
		$qb = TrackingReminderNotification::whereNotNull(TrackingReminderNotification::FIELD_DELETED_AT)
			->where(TrackingReminderNotification::FIELD_DELETED_AT, Carbon::now()->subDay());
		while($count = $qb->count()){
			if(!isset($total)){
				$total = $count;
			}
			$percent = round($count / $total * 100);
			ConsoleLog::info("Deleted $percent% ($soFar/$total) TrackingReminderNotifications...");
			$qb->limit(1000)->forceDelete();
			$soFar += $count;
		}
	}
}
