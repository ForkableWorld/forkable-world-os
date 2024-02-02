<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\TestArtifacts;
use App\Buttons\Admin\PHPUnitButton;
use App\Exceptions\DiffException;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileHelper;
use App\Files\TextFile;
use App\Logging\QMClockwork;
use App\Logging\QMLog;
use App\Storage\DB\QMQB;
use App\Storage\DB\TestDB;
use App\Storage\QMQueryExecuted;
use App\Tables\QMTable;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Utils\AppMode;
use App\Utils\DiffFile;
use Tests\QMDebugBar;
class TestQueryLogFile extends TextFile {
	use IsTestArtifactFile;
	public static $queriesByTest;
	/**
	 * @throws QMFileNotFoundException
	 * @throws DiffException
	 */
	public static function compareQueryLog(){
		static::assertSame();
	}
	/**
	 * @return string
	 * @throws QMFileNotFoundException
	 */
	public static function getDiffUrl(): string{
		$previousTestQueries = self::getCollectedQueriesCurrentTest();
		$actual = self::getQueriesExecutedArray(false);
		$path = AppMode::getCurrentTestName();
		$url = DiffFile::generateDiffUrl($previousTestQueries, $actual, $path);
		return $url;
	}
	public static function getQueryLogCliTable(bool $includeLinks = true, bool $sortByDuration = false): string{
		$data = self::getQueriesExecutedArray($sortByDuration);
		if(!$data){
			return "No Queries Found";
		}
		$str = "\n" . QMTable::arrayToCliTable($data, "Query", false) . "\n";
		if($includeLinks){
			$logger = QMQB::getUrlOrPathToQueryLogger();
			$str .= "Add break point at: \n$logger\n" . "and run: \n" . PHPUnitButton::getPathOrUrlToCurrentTest() .
				"\n";
			$str .= "\nClockwork: " . QMClockwork::getAppUrl() . "\n";
		}
		return $str;
	}
	/**
	 * @return array
	 */
	public static function getDuplicateQueries(): array{
		$queries = self::getQueriesExecuted();
		$byTableWhereCount = [];
		foreach($queries as $query){
			$tableWhere = QMStr::trimWhitespaceAndLineBreaks($query->getTable() . "\n\t " .
				$query->getWhereOrTruncatedQuery(100) . "\n");
			if(!isset($byTableWhereCount[$tableWhere])){
				$byTableWhereCount[$tableWhere]['count'] = 0;
			}
			$byTableWhereCount[$tableWhere]['count']++;
			$byTableWhereCount[$tableWhere]['queries'][] = $query;
		}
		$duplicates = [];
		$byTableWhereCount = QMArr::sortAssociativeArrayByFieldDescending($byTableWhereCount, 'count');
		foreach($byTableWhereCount as $str => $arr){
			$count = $arr['count'];
			if($count > 1){
				$arr['message'] = $count . " times: " . $str;
				$duplicates[] = $arr;
			}
		}
		return $duplicates;
	}
	public static function flushTestQueryLog(): void{
		if(empty(self::$queriesByTest[AppMode::getCurrentTestName()])){
			return;
		} // Avoid redundant logging
		QMLog::info(__FUNCTION__);
		self::$queriesByTest[AppMode::getCurrentTestName()] = [];
	}
	/**
	 * @return void
	 */
	public static function resetQueryCount(): void{
		self::flushTestQueryLog();
	}
	/**
	 * @return array|object
	 * @throws QMFileNotFoundException
	 */
	public static function getCollectedQueriesCurrentTest(){
		$path = self::getPathToCollectedQueriesCurrentTest();
		return FileHelper::readJsonFile($path);
	}
	/**
	 * @return QMQueryExecuted[]
	 */
	public static function getQueriesByTest(): array{
		$all = self::$queriesByTest;
		return $all[AppMode::getCurrentTestName()] ?? [];
	}
	public static function getPathToCollectedQueriesCurrentTest(): string{
		$folder = QMDebugBar::getCollectorFolderForCurrentTest();
		return $folder . "/queries.json";
	}
	/**
	 * @param bool $sortByDuration
	 * @param bool $generic
	 * @return QMQueryExecuted[]
	 */
	public static function getQueriesExecutedArray(bool $sortByDuration = false, bool $generic = false): array{
		$data = [];
		foreach(self::getQueriesExecuted(true, $sortByDuration) as $q){
			if($generic){
				$one = $q->toGenericArray();
			} else{
				$one = $q->toArray();
			}
			$data[] = $one;
		}
		return $data;
	}
	/**
	 * @return string
	 */
	public static function getDuplicateQueryTables(): ?string{
		if($duplicates = self::getDuplicateQueries()){
			$str = "";
			foreach($duplicates as $group){
				$data = [];
				/** @var QMQueryExecuted $query */
				foreach($group['queries'] as $query){
					$data[] = $query->toArray();
				}
				$str .= "\n" . QMTable::arrayToCliTable($data, "Duplicate Queries", false) . "\n";
			}
			return $str;
		}
		return null;
	}
	/**
	 * @return void
	 */
	public static function outputQueryLogs(): void{
		if(QMQB::$alreadyOutputQueries){
			return;
		}
		QMQB::$alreadyOutputQueries = true;
		self::logQueriesAndCallersOrderedByDuration();
		TestDB::logDuplicateQueries();
	}
	public static function logQueriesAndCallersOrderedByDuration(): void{
		\App\Logging\ConsoleLog::info(self::getQueryLogCliTable(false, true));
	}
	public static function getQueryLogMarkdown(bool $sortByDuration = false): string{
		$data = self::getQueriesExecutedArray($sortByDuration);
		if(!$data){
			return "No Queries Found";
		}
		return QMTable::arrayToMarkdownTable($data);
	}
	/**
	 * @param QMQueryExecuted $queryExecuted
	 */
	public static function addQueryByTest(QMQueryExecuted $queryExecuted): void{
		if(!$queryExecuted->shouldLog()){return;}
		$queries = self::$queriesByTest[AppMode::getCurrentTestName()] ?? [];
		if($queries){
			$last = end($queries);
			if($last && $last->preparedQuery === $queryExecuted->preparedQuery){
				$queryExecuted->logDuplicateQueryIfNecessary();
			}
		}
		if($queryExecuted->callerFunction === "getLogMetaData"){
			QMLog::info("getLogMetaData made DB Query:\n$queryExecuted->preparedQuery");
		}
		self::$queriesByTest[AppMode::getCurrentTestName()][] = $queryExecuted;
	}
	/**
	 * @param bool $excludeTelescope
	 * @param bool $sortByDuration
	 * @return QMQueryExecuted[]
	 */
	public static function getQueriesExecuted(bool $excludeTelescope = true, bool $sortByDuration = false): array{
		$all = self::getQueriesByTest();
		if($excludeTelescope){
			$all = collect($all)->filter(function($one){
				return !str_contains($one->sql, 'telescope');
			})->all();
		}
		if($sortByDuration){
			QMArr::sortDescending($all, 'time');
		}
		return $all;
	}
	public static function getData(): ?string{
		$data = self::getQueriesExecutedArray(false, true);
		if(!$data){
			QMLog::info("No queries from " . __METHOD__);
			return null;
		}
		$str = QMTable::arrayToCliTable($data, "Query", false);
		//$str = str_replace('"', '', $str);
		return $str;
	}
	public static function count(): int{
		return count(self::getQueriesExecutedArray());
	}
}
