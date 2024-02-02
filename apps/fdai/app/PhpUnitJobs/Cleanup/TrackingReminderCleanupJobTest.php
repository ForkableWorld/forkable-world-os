<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Cleanup;
use App\Logging\QMLog;
use App\Models\TrackingReminder;
use App\Models\UserVariable;
use App\Models\Variable;
use App\PhpUnitJobs\JobTestCase;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Storage\DB\Writable;
use App\Variables\QMUserVariable;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
class TrackingReminderCleanupJobTest extends JobTestCase {
	public function testDeleteActivitiesReminders(){
		$qb = TrackingReminder::query()->select([
			TrackingReminder::TABLE . '.' .
			TrackingReminder::FIELD_ID])
		                        ->join(Variable::TABLE,
		                               Variable::TABLE . '.' . Variable::FIELD_ID, '=',
		                               TrackingReminder::TABLE . '.' . TrackingReminder::FIELD_VARIABLE_ID)
		                        ->where(Variable::TABLE . '.' . Variable::FIELD_NAME, \App\Storage\DB\ReadonlyDB::like(),
		                                "% activities%");
		$rows = $qb->get();
		foreach($rows as $row){
            $reminder = QMTrackingReminder::find($row->id);
            $reminder->hardDelete("is activities");
        }
    }
    public static function deleteNotificationsForNonExistentReminders()
    {
	    Writable::pdoStatement('
          DELETE tracking_reminder_notifications
          FROM tracking_reminder_notifications
          WHERE tracking_reminder_id not in (SELECT id FROM tracking_reminders);');
    }
    public function testHardDeleteSoftDeletedMeasurements(){
        $deleted = $this->getGroupedSoftDeletedReminders();
        $numberOfUserVariables = $deleted->count();
        QMLog::infoWithoutContext("$numberOfUserVariables user variables with soft-deleted measurements");
        foreach ($deleted as $item){
            $measurementsDeleted = $item->total;
            $v = QMUserVariable::getOrCreateById($item->user_id, $item->variable_id);
            $v->logInfo("reminder was deleted");
        }
    }
    public function testCreateUserVariablesForOrphanedMeasurements(){
        $rows = $this->getOrphanedReminders();
        $total = $rows->count();
        QMLog::infoWithoutContext("$total orphaned ". TrackingReminder::TABLE);
        foreach ($rows as $row){
            //$cv = CommonVariable::getById($row->variable_id);
            //$cv->logInfo("has an orphan reminder");
            $v = QMUserVariable::getOrCreateById($row->user_id, $row->variable_id);
            $v->logInfo($v->name);
        }
    }
    /**
     * @return Collection
     */
    private function getOrphanedReminders(): Collection {
        $qb = TrackingReminder::query()
            ->select([
                TrackingReminder::TABLE.".id as id",
                TrackingReminder::TABLE.".deleted_at as deleted_at",
                TrackingReminder::TABLE.".user_id as user_id",
                TrackingReminder::TABLE.".variable_id as variable_id",
                'user_variables.variable_id as user_variable_id',
            ])
            ->leftJoin(UserVariable::TABLE, static function ($join) {
                /** @var JoinClause $join */
                $join->on(TrackingReminder::TABLE.'.variable_id', '=', 'user_variables.variable_id');
                $join->on(TrackingReminder::TABLE.'.user_id', '=', 'user_variables.user_id');
            })
            ->whereNull("user_variables.variable_id")
        ;
        $rows = $qb->get();
        return $rows;
    }
    /**
     * @return Collection
     */
    private function getGroupedSoftDeletedReminders(): Collection{
        $deleted = TrackingReminder::query()
            ->whereNotNull(TrackingReminder::FIELD_DELETED_AT)
            ->get();
        return $deleted;
    }
}
