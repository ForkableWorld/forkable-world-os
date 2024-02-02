<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\DataSources\QMConnector;
use App\Logging\QMLog;
use App\Models\User;
use App\Slim\Model\User\QMUser;
use App\Storage\DB\ReadonlyDB;
use App\Utils\AppMode;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use Carbon\Carbon;

/** Class APIStats
 * @package App\Slim\Model
 */
class APIStats {
	public $monthlyActiveUsers;
	public $totalVariables;
	public $totalUsers;
	public $totalAggregatedStudies;
	public $credentials;
	public $correlations;
	public $aggregateCorrelations;
	public $variables;
	public $userVariables;
	public $measurements;
	public $connections;
	public $users;
	public $trackerLog;
	public $trackerSessions;
	public function __construct(){
		$this->monthlyActiveUsers =
			QMUserVariable::readonly()->join(User::TABLE, 'user_variables.user_id', '=', User::TABLE . '.ID')
				->whereNotLike(User::TABLE . '.user_email', "%test%")
				->whereRaw('user_variables.updated_at > "'.db_date(time()-30*86400).'"')
				->groupBy('user_variables.user_id')
				->count();
		$this->totalUsers = QMUser::readonly()->whereNotLike(User::TABLE . '.user_email', "%test%")->count();
		$this->totalVariables = QMCommonVariable::readonly()->whereNotLike('variables.name', "'%test%'")->count();
		$this->totalAggregatedStudies = QMGlobalVariableRelationship::readonly()->count();
		//$errorField = 'status';  // Don't get errored credential records because we cant json_encode them properly in response
		//$this->credentials = APIStats::getStatsForTable('credentials', $errorField);
		$this->credentials = self::getStatsForTable('credentials');
		$this->correlations = self::getStatsForTable('user_variable_relationships');
		$this->aggregateCorrelations = self::getStatsForTable('global_variable_relationships');
		$errorField = 'status';
		$this->variables = self::getStatsForTable('variables', $errorField);
		$errorField = 'status';
		$this->userVariables = self::getStatsForTable('user_variables', $errorField);
		//$this->measurements = APIStats::getStatsForTable('measurements');  Too slow!
		$errorField = 'update_status';
		$errorField2 = 'connect_status';
		$this->connections = self::getStatsForTable('connections', $errorField, $errorField2);
		$this->users = self::getStatsForTable(User::TABLE);
		$this->trackerLog = self::getStatsForField('tracker_log', 'created');
		$this->trackerSessions = self::getStatsForField('tracker_sessions', 'created');
	}
	/**
	 * @param $tableName
	 * @param null $errorField
	 * @param null $errorField2
	 */
	public static function outputStatsForTable($tableName, $errorField = null, $errorField2 = null){
		$numberUpdated = self::outputStatsForField($tableName, 'updated');
		self::outputStatsForField($tableName, 'created');
		if($errorField && $numberUpdated != 0){
			self::outputErrorStats($tableName, $errorField, $numberUpdated);
		}
		if($errorField2 && $numberUpdated != 0){
			self::outputErrorStats($tableName, $errorField2, $numberUpdated);
		}
	}
	/**
	 * @param $tableName
	 * @param null $errorField
	 * @param null $errorField2
	 * @return mixed
	 */
	public static function getStatsForTable($tableName, $errorField = null, $errorField2 = null){
		$stats['updated'] = self::getStatsForField($tableName, 'updated');
		$stats['created'] = self::getStatsForField($tableName, 'created');
		if($errorField && $stats['updated']['numberUpdatedInLastDay'] != 0){
			$stats['errorStats'] =
				self::getErrorStats($tableName, $errorField, $stats['updated']['numberUpdatedInLastDay']);
		}
		if($errorField2 && $stats['updated']['numberUpdatedInLastDay'] != 0){
			$stats['errorStats'] =
				self::getErrorStats($tableName, $errorField2, $stats['updated']['numberUpdatedInLastDay']);
		}
		return $stats;
	}
	/**
	 * @param $tableName
	 * @param $updatedOrCreated
	 * @return mixed
	 */
	public static function outputStatsForField($tableName, $updatedOrCreated){
		$stats = self::getStatsForField($tableName, $updatedOrCreated);
		$percentChangeText = 'Change incalculable because we had 0 previously';
		$percentChangeValue = 0;
		if($stats['numberUpdatedTwoDaysAgo']){
			$percentChangeValue = round(($stats['numberUpdatedInLastDay'] - $stats['numberUpdatedTwoDaysAgo']) /
				$stats['numberUpdatedTwoDaysAgo'] * 100);
			$percentChangeText = $percentChangeValue . '% change from previous day';
		}
		if($percentChangeValue < -90 || $percentChangeValue > 100){
			QMLog::warning(strtoupper($updatedOrCreated) . ' ' . strtoupper($tableName) . ': ' .
				$stats['numberUpdatedInLastDay'] . ' (' . $percentChangeText . ')');
		} else{
			QMLog::info(strtoupper($updatedOrCreated) . ' ' . strtoupper($tableName) . ': ' .
				$stats['numberUpdatedInLastDay'] . ' (' . $percentChangeText . ')');
		}
		return $stats['numberUpdatedInLastDay'];
	}
	/**
	 * @param $tableName
	 * @param $updatedOrCreated
	 * @return mixed
	 */
	public static function getStatsForField($tableName, $updatedOrCreated){
		$stats['numberUpdatedInLastDay'] =
			ReadonlyDB::getBuilderByTable($tableName)
                ->where(strtolower($updatedOrCreated) .'_at', ">", Carbon::now()->subDay())
                ->count();
		GoogleAnalyticsEvent::logEventToGoogleAnalytics($tableName . '_' . $updatedOrCreated,
			$tableName . '_' . $updatedOrCreated, $stats['numberUpdatedInLastDay'], 1, gethostname());
		QMLog::info("$tableName $updatedOrCreated In Last 24 Hours: " . $stats['numberUpdatedInLastDay']);
		$stats['numberUpdatedTwoDaysAgo'] =
			ReadonlyDB::getBuilderByTable($tableName)
                ->where(strtolower($updatedOrCreated) .'_at', "<", Carbon::now()->subDay())
                ->where(strtolower($updatedOrCreated).'_at', ">", Carbon::now()->subDays(2))
                ->count();
		QMLog::info("$tableName $updatedOrCreated Two Days Ago: " . $stats['numberUpdatedTwoDaysAgo']);
		return $stats;
	}
	/**
	 * @param $tableName
	 * @param $statusField
	 * @param $numberUpdated
	 * @return int
	 */
	public static function outputErrorStats($tableName, $statusField, $numberUpdated){
		$stats = self::getErrorStats($tableName, $statusField, $numberUpdated);
		if(!AppMode::isProduction()){
			return $stats['numberErroredYesterday'];
		}
		$maximumAllowedErrorRate = 1;
		if($stats['errorRate'] > $maximumAllowedErrorRate){
			if(isset($stats['erroredRecords'][0]->connector_id)){
				foreach($stats['erroredRecords'] as $iValue){
					$iValue->connectorName =
						QMConnector::getConnectorById($stats['erroredRecords']->connector_id)->name;
				}
			}
			QMLog::error('Error rate ' . $stats['errorRate'] . '% for ' . $tableName . ' ' . $statusField, [
				'Error Rate' => $stats['errorRate'] . '%',
				'Number of ' . strtoupper($statusField) . ' Errors in Last 24' => $stats['numberErroredYesterday'],
				'Number of Updates in Last 24' => $numberUpdated,
				'errored records' => $stats['erroredRecords'],
			]);
		} elseif($stats['errorRate']){
			QMLog::warning('Error rate > ' . $maximumAllowedErrorRate . '% for ' . $tableName . ' ' . $statusField .
				' in last day', [
				'Error Rate' => $stats['errorRate'] . '%',
				strtoupper($statusField) . ' ERRORS' => $stats['numberErroredYesterday'],
				'UPDATES' => $numberUpdated,
			]);
		}
		return $stats['numberErroredYesterday'];
	}
	/**
	 * @param $tableName
	 * @param $statusField
	 * @param $numberUpdated
	 * @return int
	 */
	public static function getErrorStats($tableName, $statusField, $numberUpdated){
		$stats['erroredRecords'] = ReadonlyDB::getBuilderByTable($tableName)
            ->where($statusField, 'ERROR')
			->where('updated_at', ">", Carbon::now()->subDay())
            ->getArray();
		$stats['numberErroredYesterday'] = count($stats['erroredRecords']);
		QMLog::info("$tableName $statusField numberErroredYesterday: " . $stats['numberErroredYesterday']);
		$stats['errorRate'] = round($stats['numberErroredYesterday'] / $numberUpdated * 100);
		QMLog::info("$tableName $statusField errorRate: " . $stats['errorRate']);
		return $stats;
	}
	/**
	 * @return self
	 */
	public static function getApiStats(){
		return new self();
	}
}
