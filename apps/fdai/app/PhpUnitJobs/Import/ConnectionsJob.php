<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Import;
use App\DevOps\XDebug;
use App\Logging\QMLog;
use App\Models\Connection;
use App\Models\ConnectorImport;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\Connection\ConnectionUpdateStatusProperty;
use Carbon\Carbon;

/** Class ConnectionsJobTest
 * @package App\PhpUnitJobs
 */
class ConnectionsJob extends JobTestCase {
	//protected const SLACK_CHANNEL = 'connections';
	protected const SLACK_CHANNEL = '#emergency';
    /**
	 * @return void
	 */
	public function testConnectionsJob(){
		$l = Connection::find(14672);
		//$l->import();
		//$c = MoodscopeConnector::getConnectionByUserId(230);
		//$c->import(__FILE__);
		//$connection = $this->checkTigerview();
		self::import();
		//QMConnector::updateDatabaseTableFromHardCodedConstants();
		$this->assertTrue(true);
	}
	public static function import(){
		ConnectorImport::logMetabaseLink();
		self::resetStartTime();
		Connection::logStuck();
		Connection::importWaitingStaleStuck();
		ConnectorImport::logNew();
		Connection::logErrorsFromLast24();
		Connection::logStuck();
		self::outputImportErrors();
	}
	public function debugConnections(){
		Connection::logSuccessfulImports();
		$this->outputStuckConnections();
		$this->outputImportErrors();
		$this->assertTrue(true);
	}
	private function outputStuckConnections(){
		/** @var Connection[] $stuck */
		$stuck = Connection::whereStuck()->getDBModels();
		if(!$stuck){
			QMLog::info("No stuck connections");
			return;
		} else{
			QMLog::info(count($stuck) . " stuck connections");
		}
		$message = "";
		foreach($stuck as $c){
			$c->logError("I'm stuck with Importing status! \n" . "ImportStartedAt: " . $c->getImportStartedAt() . "\n" .
				"ImportEndedAt: " . $c->getImportEndedAt());
			if(XDebug::active()){
				$c->import("Stuck!");
				$newStuck = Connection::whereStuck()->getDBModels();
				if(count($newStuck) >= count($stuck)){
					le("Still stuck after retry! $c");
				} else{
					$stuck = $newStuck;
				}
			}
		}
		\App\Logging\ConsoleLog::info($message);
	}
	private static function outputImportErrors(){
		$qb = Connection::whereUpdateStatus(ConnectionUpdateStatusProperty::IMPORT_STATUS_ERROR)
			->where(Connection::FIELD_IMPORT_STARTED_AT, '>',  Carbon::now()->subDay())
			->orderBy(Connection::FIELD_IMPORT_STARTED_AT, 'ASC');
		$withErrors = $qb->get();
		if(!$withErrors->count()){
			QMLog::infoWithoutContext("No errored connections in last day");
			static::assertTrue(true);
			return;
		}
		QMLog::info("Here are errored updates from last day: ");
		$bByName = [];
		/** @var Connection $c */
		foreach($withErrors as $c){
			$bByName[$c->getConnectorName()][] = $c;
		}
		QMLog::info(" =============   START ERROR COUNTS   =============== ");
		foreach($bByName as $key => $value){
			QMLog::info($key . " had " . count($value) . " update error(s)");
		}
		QMLog::info(" =============   END ERROR COUNTS   =============== ");
		foreach($withErrors as $c){
			$updateError = $c->update_error;
			if($c->update_error){
				$c->logInfo($updateError);
				$test = $c->getPHPUnitTestUrl();
				QMLog::infoWithoutContext($test);
			} else{
				$c->logError("Update error not set!");
			}
			if(XDebug::active()){
				$c->import($updateError);
			}
			ConnectorImport::logMetabaseLink();
		}
	}
}
