<?php
namespace Tests;
use App\DevOps\XDebug;
use App\Exceptions\DiffException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\GitAlreadyUpToDateException;
use App\Exceptions\GitConflictException;
use App\Exceptions\GitLockException;
use App\Exceptions\GitNoStashException;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileHelper;
use App\Files\TestArtifacts\DebugBarEventsFile;
use App\Logging\QMLog;
use App\Repos\ResponsesRepo;
use App\Storage\Memory;
use App\Types\QMStr;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\EnvOverride;
use Barryvdh\Debugbar\DataCollector\QueryCollector;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DebugBarException;
use Illuminate\Database\Events\QueryExecuted;
class QMDebugBar
{
    /**
     * @return array
     */
    public static function getQueries(): array {
        $collectors = self::getCollectorData();
        return $collectors['queries'];
    }
    public static function getQueryStatements(): array {
        $queries = self::getQueries();
        return $queries['statements'];
    }
    public static function enable(): void {
        // Must be done in bootstrap before DB interactions or we get exception: 'queries' is not a registered collector
        // This must be called again in \Tests\TestHelper::setUp to make sure it didn't get disabled when the application was recreated
	    \Barryvdh\Debugbar\Facades\Debugbar::enable();
    }
    public static function getCollectorData():array {
        if(AppMode::isApiRequest()){
            $data = \Barryvdh\Debugbar\Facades\Debugbar::collect();
            if($data === null){
                le("Null returned from \Barryvdh\Debugbar\Facades\Debugbar::collect");
            }
            if($data === []){QMLog::warning("Null returned from \Barryvdh\Debugbar\Facades\Debugbar::collect");}
        } else {

            $data = \Barryvdh\Debugbar\Facades\Debugbar::collectConsole();
            if($data === null){
                $enabled = self::enabled();
                if(!$enabled){
                    le("DebugBar is NOT Enabled. 
\Tests\QMDebugBar::enable be enabled once in bootstrap before DB interactions or we get exception: 'queries' is not a registered collector
\Tests\QMDebugBar::enable must be called again in \Tests\TestHelper::setUp to make sure it didn't get disabled when the application was recreated");
                }
                le("Null returned from \Barryvdh\Debugbar\Facades\Debugbar::collectConsole event though debugbar enabled! 
debugbar config: ".
                    \App\Logging\QMLog::print_r(config('debugbar'), true));
            }
            if($data === []){QMLog::warning("Null returned from \Barryvdh\Debugbar\Facades\Debugbar::collectConsole");}
        }
        Memory::setByPrimaryKey(Memory::COLLECTOR_DATA, $data); // Makes it visible in xDebug
        return $data;
    }
    /**
     * @throws DebugBarException
     * @noinspection PhpUnused
     * Don't use this because it's really slow to explain every single query
     * Just use \Tests\QMDebugBar::explainQuery on slow ones
     */
    public static function enableQueryExplainer(){
        $queryCollector = self::getQueryCollector();
        $queryCollector->setExplainSource(true, ['SELECT']);
    }
    /**
     * @return QueryCollector
     * @throws DebugBarException
     */
    public static function getQueryCollector():QueryCollector{
        try {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return \Barryvdh\Debugbar\Facades\Debugbar::getCollector('queries');
        } catch (\Throwable $e){
            ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
            /** @var DebugBarException $e */
            throw $e;
        }
    }
    /**
     * @return DataCollector[]
     */
    public static function getCollectors():array {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return \Barryvdh\Debugbar\Facades\Debugbar::getCollectors();
    }
    public static function saveCollectorData(){
	    QMLog::info(__METHOD__);
        ResponsesRepo::saveCollectorData('events', self::getEvents());
        ResponsesRepo::saveCollectorData('messages', self::getMessages());
        ResponsesRepo::saveCollectorData("sql",self::getSqlStatements());
    }
    /**
     * @param QueryExecuted $query
     * @return mixed
     * @throws DebugBarException
     */
    public static function explainQuery(QueryExecuted $query){
		if(!self::enabled()){
			return "could not explain query because DEBUG_BAR env is false";
		}
        $queryCollector = self::getQueryCollector();
        $queryCollector->addQuery((string) $query, $query->bindings, $query->time, $query->connection);
        $queries = self::getQueries();
        return end($queries);
    }
    public static function enableIfTesting(){
        if(Env::isTesting() ){
            self::enable(); // Disabled by default on testing urls at vendor/barryvdh/laravel-debugbar/src/LaravelDebugbar.php:770
        }
    }
    public static function flush(){
        $collectors = self::getCollectors();
        foreach($collectors as $c){
            if(method_exists($c, 'reset')){
                $c->reset();
            }
        }
        Memory::set(Memory::COLLECTOR_DATA, null);
    }
    public static function disable(){\Barryvdh\Debugbar\Facades\Debugbar::disable();}
    public static function getPathToCollectorData(string $type): string{
        return self::getCollectorFolderForCurrentTest()."/$type.json";
    }
    public static function getCollectorFolderForCurrentTest(): string{
        $filePath = FileHelper::getFilePathToClass(get_class(AppMode::getCurrentTest()));
        $folder = str_replace(QMBaseTestCase::TEST_FOLDER . DIRECTORY_SEPARATOR, QMBaseTestCase::COLLECTOR_DATA_FIXTURES_FOLDER . DIRECTORY_SEPARATOR, $filePath);
        $folder = str_replace('.php', DIRECTORY_SEPARATOR . AppMode::getCurrentTestName(), $folder);
        return $folder;
    }
	/**
	 * @param string $type
	 * @return array|object
	 * @throws QMFileNotFoundException
	 */
    public static function getPrevious(string $type){
        return FileHelper::readJsonFile(QMDebugBar::getPathToCollectorData($type));
    }
	/**
	 * @throws GitAlreadyUpToDateException
	 * @throws GitConflictException
	 * @throws GitLockException
	 * @throws GitNoStashException
	 * @throws OutOfMemoryException
	 */
    public static function updateCollectorData(){
        Env::setTesting(); // Have to do this so app is bootstrapped and GITHUB_ACCESS_TOKEN is available
        if(!XDebug::active()){
            ResponsesRepo::clonePullAndOrUpdateRepo();
            /** @noinspection PhpUnhandledExceptionInspection */
            ResponsesRepo::stash();
            /** @noinspection PhpUnhandledExceptionInspection */
            ResponsesRepo::hardReset("master");
        }
        Env::set(Env::UPDATE_COLLECTOR_DATA, 1);
        Env::set(Env::DEBUGBAR_WHILE_TESTING, 1);
        $testFolder = \App\Utils\Env::get(Env::TEST_FOLDER);
        if(!$testFolder){
            $testFolder = EnvOverride::getFormatted(Env::TEST_FOLDER);
        }
        if($testFolder){
            QMLog::warning("ONLY RUNNING tests in $testFolder");
            QMBaseTestCase::runTestsInFolder($testFolder);
        }else{
            QMBaseTestCase::runTestsInFolder("tests/SlimTests/Reminders");
            QMBaseTestCase::runTestsInFolder("tests/SlimTests/Controllers");
            QMBaseTestCase::runTestsInFolder("tests/SlimTests/Analytics");
            QMBaseTestCase::runTestsInFolder("tests/SlimTests/Connectors");
            QMBaseTestCase::runTestsInFolder("tests/SlimTests/AppSettings");
            QMBaseTestCase::runTestsInFolder("tests/SlimTests/Measurements");
            QMBaseTestCase::runTestsInFolder("tests/SlimTests/Studies");
            QMBaseTestCase::runTestsInFolder("tests/SlimTests/Tasks");
            QMBaseTestCase::runTestsInFolder("tests/SlimTests/Variables");
            QMBaseTestCase::runTestsInFolder("tests/UnitTests");
            QMBaseTestCase::runTestsInFolder("tests/StagingUnitTests");
        }
        $msg = "updated collector data fixtures";
        if($testFolder){
            $msg .= " for $testFolder";
        }
        ResponsesRepo::addAllCommitAndPush($msg);
    }
	/**
	 * @throws DiffException
	 * @throws QMFileNotFoundException
	 */
    public static function compare(){
	    DebugBarEventsFile::assertSameObject('events', self::getEvents());
	    ResponsesRepo::saveCollectorData('messages', self::getMessages());
        ResponsesRepo::saveCollectorData("sql",self::getSqlStatements());
    }
    /**
     * @return array
     */
    public static function getEvents(): array{
        $data = self::getCollectorData();
        $events = [];
        foreach($data["event"]["measures"] as $event){
            $events[] = $event['label'];
        }
        return $events;
    }
    /**
     * @return array
     */
    public static function getMessages(): array{
        $messages = [];
        $data = self::getCollectorData();
        foreach($data["messages"]["messages"] as $message){
            if(QMStr::isNullString($message['message'])){
                continue;
            }
            $fullMessage = $message['message'];
            $withoutPath = QMStr::before("/mnt/", $fullMessage, $fullMessage, false);
            $withoutDates = QMStr::removeDatesAndTimes($withoutPath);
            if(stripos($withoutDates, 'seconds')){
                $withoutDates = QMStr::truncate($withoutDates,
                    20,
                    "[TRUNCATED BECAUSE CONTAINS SECONDS]");
            }
            if(stripos($withoutDates, '000Z')){
                $withoutDates = QMStr::truncate($withoutDates,
                    20,
                    "[TRUNCATED BECAUSE CONTAINS 000Z]");
            }
            $messages[] = $withoutDates;
        }
        return $messages;
    }
    /**
     * @return array
     */
    public static function getSqlStatements(): array{
        $statements = [];
        $data = self::getCollectorData();
        foreach($data["queries"]["statements"] as $statement){
            $sql = $statement["sql"];
            $sql = QMStr::removeDatesAndTimes($sql);
            $statements[] = //$statement['stmt_id']." | ".
                $sql;
        }
        return $statements;
    }
    /**
     * @return bool
     */
    public static function enabled(): bool{
        $enabled = \Barryvdh\Debugbar\Facades\Debugbar::isEnabled();
        return $enabled;
    }

    public static function addMessage(string $nameMessage, string $severity): void{
		if(self::enabled()){
			try {
				\Barryvdh\Debugbar\Facades\Debugbar::addMessage($nameMessage, $severity);
			} catch (\Throwable $e) {
				error_log("Error adding this $severity message to debugbar:
	$nameMessage
	because of this error: 
	$e");
			}
		}
    }
}
