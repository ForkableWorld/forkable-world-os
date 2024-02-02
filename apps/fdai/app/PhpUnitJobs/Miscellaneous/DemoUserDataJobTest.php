<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Miscellaneous;
use App\DataSources\Connectors\GithubConnector;
use App\Logging\QMLog;
use App\Models\Connection;
use App\Models\UserVariableRelationship;
use App\Models\Measurement;
use App\Models\TrackingReminder;
use App\Models\User;
use App\Models\UserVariable;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\User\UserIdProperty;
use App\Reports\GradeReport;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\QMUserRelatedModel;
use App\Storage\DB\ReadonlyDB;
use App\Storage\DB\Writable;
use App\Types\QMStr;
use Illuminate\Support\Collection;
use Throwable;
/** Class GlobalVariableRelationshipsTest
 * @package App\PhpUnitJobs
 */
class DemoUserDataJobTest extends JobTestCase {
	private $tablesToCopy = [
		UserVariable::TABLE,
		TrackingReminder::TABLE,
		Measurement::TABLE,
		UserVariableRelationship::TABLE,
	];
	private $connectorsToExclude = [
		GithubConnector::ID,
	];
	public function testGenerateDemoReports(){
		GradeReport::publishDemoReport();
	}
	public function testDemoUserDataJob(){
		$this->setDemoUserName();
		$this->deleteGithubDataFromDemoUser();
		foreach($this->tablesToCopy as $table){
			$maxUpdatedAt = $this->maxUpdatedAtForDemoUser($table);
			$records = $this->getMikesRecordsNewerThan($table, $maxUpdatedAt);
			$this->insertRecordsAsUserOne($records, $table);
		}
		$this->assertTrue(true);
	}
	private function setDemoUserName(): void{
		QMLog::infoWithoutContext(__FUNCTION__);
		User::whereId(1)->update([
			User::FIELD_DISPLAY_NAME => "Demo User",
			User::FIELD_FIRST_NAME => "Demo",
			User::FIELD_LAST_NAME => "User",
		]);
	}
	private function deleteGithubDataFromDemoUser(): void{
		QMLog::infoWithoutContext("Deleting Github data from Demo user...");
		foreach($this->connectorsToExclude as $id){
			QMMeasurement::writable()->where(Measurement::FIELD_USER_ID, 1)
				->where(Measurement::FIELD_CONNECTOR_ID, $id)->hardDelete(__METHOD__, true);
			Connection::writable()->where(Measurement::FIELD_USER_ID, 1)
				->where(Measurement::FIELD_CONNECTOR_ID, $id)->hardDelete(__METHOD__, true);
		}
	}
	/**
	 * @param $table
	 * @return string
	 */
	private function maxUpdatedAtForDemoUser(string $table): ?string{
		$maxUpdatedAt = ReadonlyDB::getBuilderByTable($table)->where(QMUserRelatedModel::FIELD_USER_ID, 1)
			->max(QMUserRelatedModel::FIELD_UPDATED_AT);
		QMLog::infoWithoutContext("Most recent $table measurement for demo user is $maxUpdatedAt");
		return $maxUpdatedAt;
	}
	/**
	 * @param $table
	 * @param $minUpdatedAt
	 * @return Collection
	 */
	private function getMikesRecordsNewerThan(string $table, string $minUpdatedAt): Collection{
		QMLog::infoWithoutContext("Getting Mike's $table newer than $minUpdatedAt");
		$records =
			ReadonlyDB::getBuilderByTable($table)->where(QMUserRelatedModel::FIELD_USER_ID, UserIdProperty::USER_ID_MIKE)
				->where(QMUserRelatedModel::FIELD_UPDATED_AT, '>', $minUpdatedAt)
				->orderBy(QMUserRelatedModel::FIELD_UPDATED_AT, 'asc')->get();
		$total = $records->count();
		QMLog::infoWithoutContext("Got $total of Mike's $table records newer than $minUpdatedAt");
		return $records;
	}
	/**
	 * @param Collection $collection
	 * @param $table
	 */
	private function insertRecordsAsUserOne(Collection $collection, string $table): void{
		$objects = $collection->toArray();
		$inserted = $failed = 0;
		if($objects){
			foreach($objects as $obj){
				if(isset($obj->connector_id) && in_array($obj->connector_id, $this->connectorsToExclude)){
					continue;
				}
				$obj->user_id = 1;
				unset($obj->id);
				try {
					Writable::getBuilderByTable($table)->insert((array)$obj);
					$inserted++;
				} catch (Throwable $e) {
					$failed++;
					$message = QMStr::before("(SQL", $e->getMessage());
					QMLog::info($message);
				}
			}
			QMLog::infoWithoutContext("Inserted $inserted and failed to insert $failed $table records");
		}
	}
}
