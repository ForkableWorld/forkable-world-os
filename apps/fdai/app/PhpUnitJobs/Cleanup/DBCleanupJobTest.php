<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Cleanup;
use App\Computers\ThisComputer;
use App\DataSources\QMConnector;
use App\Logging\QMLog;
use App\Models\Connection;
use App\Models\Measurement;
use App\Models\TrackingReminder;
use App\Models\User;
use App\Models\UserVariable;
use App\Models\Variable;
use App\PhpUnitJobs\JobTestCase;
use App\Storage\DB\ReadonlyDB;
use App\Storage\DB\StagingDB;
use App\Storage\DB\Writable;
use Illuminate\Support\Facades\DB;
use Throwable;
class DBCleanupJobTest extends JobTestCase {
	public function testGenerateDBSeeders(){
		$tables = Writable::getTableNames();
		foreach($tables as $table){
			ThisComputer::terminalRun("php artisan seed $table");
		}
	}
	public function testCallOneOfTheFunctionsHereBecauseLeavingTestInTheNamesIsDangerous(){
		// If we run a function from PHPStorm, it might accidentally run all the tests in this file
		$this->listTablesWithFewRecords();
	}
	public function listAllTables(){
		$tables = Writable::getTableNames();
		foreach($tables as $table){
			QMLog::infoWithoutObfuscation("'" . $table . "',");
		}
	}
	public function listTablesWithFewRecords(){
		$tables = Writable::getTableNames();
		$records = [];
		foreach($tables as $table){
			$number = $records[$table] = ReadonlyDB::getBuilderByTable($table)->count();
			$line = "'" . $table . "', // $number records ";
			if($number > 10){
				$line = "//" . $line;
			}
			\App\Logging\ConsoleLog::info($line);
		}
	}
	public function listStupidTables(){
		$this->listTablesWithFewRecords();
	}
	public function copyNewRecordsFromOneDbToAnother(){
		$dateField = Writable::FIELD_CREATED_AT;
		$newConnection = DB::connection('supabase');
		$tables = Writable::getTableNamesWithColumn($dateField);
		$tables = array_merge([
			User::TABLE,
			Variable::TABLE,
			UserVariable::TABLE,
			TrackingReminder::TABLE,
			Measurement::TABLE,
			Connection::TABLE,
		], $tables);
		$tables = array_unique($tables);
		foreach($tables as $table){
			if($table === QMConnector::TABLE){
				continue;
			}
			$newTable = $newConnection->table($table);
			$max = $newTable->max($dateField);
            $qb = Writable::getBuilderByTable($table);
			if(!$max){
				//continue;
			} else {
                $qb->where($dateField, '>', $max);
            }
			$newRecords = $qb->getArray();
			QMLog::infoWithoutObfuscation(count($newRecords) . " from $table...");
			if(!$newRecords){
				continue;
			}
            $qb->getConnection()->getSchemaBuilder()->enableForeignKeyConstraints();
            $newTable->getConnection()->getSchemaBuilder()->disableForeignKeyConstraints();
			foreach($newRecords as $record){
				$arr = (array)$record;
				try {
					$newTable->insert([$arr]);
				} catch (Throwable $e) {
					if(stripos($e->getMessage(), "Duplicate entry") === false){
						throw $e;
					}
					QMLog::info(__METHOD__.": ".$e->getMessage());
				}
			}
            $newTable->getConnection()->getSchemaBuilder()->enableForeignKeyConstraints();
		}
	}
	public function testStagingMigration(){
		StagingDB::migrate();
	}
}
