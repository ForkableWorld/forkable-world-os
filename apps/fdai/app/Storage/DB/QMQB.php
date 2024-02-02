<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\DB;
use App\DataSources\QMConnector;
use App\DevOps\XDebug;
use App\Exceptions\ProtectedDatabaseException;
use App\Exceptions\QMQueryException;
use App\Exceptions\SlowQueryException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\UpdateQueryException;
use App\Files\TestArtifacts\TestQueryLogFile;
use App\Http\Controllers\Admin\QMArtisanController;
use App\Logging\QMIgnition;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\CtTreatmentSideEffect;
use App\Models\Measurement;
use App\Models\User;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Models\Vote;
use App\Models\WpPost;
use App\Models\WpPostmetum;
use App\PhpUnitJobs\JobTestCase;
use App\Providers\DBQueryLogServiceProvider;
use App\Repos\QMAPIRepo;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\DBModel;
use App\Slim\Model\QMUnit;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Slim\View\Request\QMRequest;
use App\Storage\Memory;
use App\Storage\QMQueryExecuted;
use App\Storage\QueryBuilderHelper;
use App\Traits\LoggerTrait;
use App\Types\QMStr;
use App\Utils\AppMode;
use App\Utils\Constraint;
use App\Utils\SecretHelper;
use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LogicException;
use SqlFormatter;
use stdClass;
use Tests\QMBaseTestCase;
use Tests\QMDebugBar;
use Yajra\DataTables\QueryDataTable;
class QMQB extends Builder {
	use LoggerTrait;
	// Exclude these when getting multiple records because it really slows down queries
	public const LARGE_FIELDS = [
		Variable::FIELD_CHARTS,
	];
	public const MAX_API_QUERY_DURATION = 8;      // TODO: reduce after limiting slow global update queries
	public const MAX_WORKER_QUERY_DURATION = 13;  // Let's allow 15 seconds so number of common tags can update?  But it crashes mysql?
	const QUERY_WORDS = [
		"Dispatcher", "QMQB", QueryExecuted::class, QMQueryExecuted::class, Connection::class, Builder::class,
		DBQueryLogServiceProvider::class, 'database', 'query', 'Request', 'rows', 'getUserVariables',
		'getCommonVariables', 'getByName', 'setUpdatedAtJsonEncodeAndCheckForDuplicateUpdate', 'updateDbRow', 'first',
		'construct', 'getArray', 'closure',
	];
	const CALLER_SUFFIX = " */\n";
	const CALLER_PREFIX = "/* Caller: ";
	public ?string $class = null;
	protected array $backtrace = [];
	protected array|false|null|Collection $result = null;
	protected string|null $caller = null;
	protected null|float $duration = null;
	protected string $resultMessage;
	protected float|null $startTime;
	protected bool $suppressLogs = false;
	protected bool $disableWhereChecks = false;
	protected string $type;
	protected array|null $values = null;
	protected static bool $allowFullTableQueries = false;
	protected static bool $loggingEnabled = false;
	public static bool $alreadyOutputQueries = false;
	protected string $preparedQuery;
	protected static array $protectedTables = [
		Measurement::TABLE, User::TABLE, //CommonVariable::TABLE,
		//GlobalVariableRelationship::TABLE
	];
	private static array $smallTables = [
		QMUnit::TABLE,
		QMConnector::TABLE,
		VariableCategory::TABLE,
		Vote::TABLE,
		WpPost::TABLE,
		WpPostmetum::TABLE,
		CtTreatmentSideEffect::TABLE,
	];
	/**
	 * @var string
	 */
	public string $whereString;
	const IGNORE_FUNCTIONS = [
		"{closure}",
		"__call",
		"__callStatic",
		"__get",
		"App\DataTableServices\{closure}",
		"call_user_func",
		"create",
		"Factory::{closure}",
		//"find",
		"findByData",
		"findInMemoryOrDB",
		"findLaravelModel",
		"first",
		"firstLaravelModel",
		"firstOrNewLaravelModel",
		"findInDB",
		"get",
		"getAttribute",
		"getRawAttributes",
		"getDefault",
		"Illuminate\Database\Eloquent\{closure}",
		"Illuminate\Database\Eloquent\Relations\{closure}",
		"insertAndUpdateMeta",
		"l",
		"make",
		"makeInstance",
		"save",
		"setUserMetaValue",
		"tap",
		"update",
		"unguarded",
		"Illuminate\Routing\{closure}",
	];
	/**
	 * Create a new query builder instance.
	 * @param ConnectionInterface $connection
	 * @param Grammar             $grammar
	 * @param Processor           $processor
	 * @return void
	 */
	public function __construct(ConnectionInterface $connection, Grammar $grammar, Processor $processor){
		parent::__construct($connection, $grammar, $processor);
	}
	public static function getUrlOrPathToQueryLogger(): string {
		if(AppMode::isJenkins()){
			return DBQueryLogServiceProvider::getUrl();
		}
		return DBQueryLogServiceProvider::getAbsolutePath();
	}
	/**
	 * @param \Illuminate\Database\Eloquent\Builder|Builder|QMQB $qb
	 * @return string
	 */
	public static function toHumanizedWhereClause(\Illuminate\Database\Eloquent\Builder|QMQB|Builder $qb): string{
		if($qb instanceof \Illuminate\Database\Eloquent\Builder){
			$qb = $qb->getQuery();
		}
		return QueryBuilderHelper::getHumanizedWhereClause($qb->wheres, $qb->from);
	}
	public static function saveQueryExecuted(QueryExecuted $query): QMQueryExecuted{
		$query = new QMQueryExecuted($query);
		Memory::addByPrimaryKey(Memory::QUERIES_EXECUTED, $query);
		Memory::add($query->getTable(), $query, Memory::QUERIES_EXECUTED_BY_TABLE);
		if(AppMode::getCurrentTestName()){
			TestQueryLogFile::addQueryByTest($query);
			$count = TestDB::getTotalQueryCount(false);
			$max = 100;
			if($count < $max && $query->shouldLog()) {$query->logQuery();}
			if($count === $max) {QMLog::info("Not logging any more queries because there are over $max so far...");}
		}
		return $query;
	}
	public static function getDBTable(string $table): DBTable{
		return DBTable::find($table);
	}
    private function like(): string{
        return $this->db()->like();
    }
    /**
     * @param \Illuminate\Database\Eloquent\Builder|Builder $qb
     * @param string $tableField
     * @param string $notLike
     * @return Builder|\Illuminate\Database\Eloquent\Builder
     */
    public static function notLike(\Illuminate\Database\Eloquent\Builder|Builder $qb, string $tableField, string $notLike): \Illuminate\Database\Eloquent\Builder|Builder{
        $qb->where($tableField, 'not like', "'$notLike'");
        return $qb;
    }

    public function logSlowQuery(){
		$duration = round($this->getDurationInSeconds());
		SlowQueryException::logIfSlow($duration, $this->getPreparedQuery());
	}
	public static function toSimpleSQL(string $sql): string {
		$sql = "select * from " . QMStr::after(" from ", $sql, null, true);
		return self::formatSQL($sql, false);
	}
	public static function formatSQL(string $sql, bool $highlight):string{
		return SqlFormatter::format($sql, $highlight);
	}
	/**
	 * @param bool $pretty
	 * @return string
	 */
	public function getWhereString(bool $pretty = false): string{
		$sql = $this->getPreparedQuery();
		$where = QMQB::toWhereString($sql, $pretty);
		$where = str_replace("`".$this->getTableName()."`.", "", $where);
		if($this->getTableAlias()){
			$where = str_replace("`".$this->getTableAlias()."`.", "", $where);
		}
		$where = trim($where);
		return $this->whereString = $where;
	}
	/**
	 * @return string
	 */
	public function getPreparedQuery(): string{
		if(isset($this->preparedQuery)){
			return $this->preparedQuery;
		}
		$q = QMQB::addBindingsToSql($this);
		//$q = Str::replaceArray('/?', $bindings, $sql);  // Not sure why that slash was there?
		//$q = SqlFormatter::format($q, false);
		return $this->preparedQuery = $q;
	}
	/**
	 * @return bool
	 */
	private static function allowFullTableQueries(): bool{
		return self::$allowFullTableQueries;
	}
	/**
	 * @param bool $allowFullTableQueries
	 */
	public static function setAllowFullTableQueries(bool $allowFullTableQueries): void{
		self::$allowFullTableQueries = $allowFullTableQueries;
	}
	/**
	 * @param int|array|Collection $result
	 */
	private function saveReadonlyQuery(int|array|Collection $result): void{
		$count = $result ? 1 : 0;
		if(is_array($result)){
			$count = count($result);
		}
		$this->resultMessage = "got $count records";
		if(!$this->getSuppressLogs() && SlowQueryException::queryIsSlow($this->getDurationInSeconds())){
			$this->logSlowQuery();
			//}else if(!$count){$this->logInfo();
		}else{$this->logDebug();}
		$this->getWhereString();
		$qb = $this->getCloneAndReset();
		$this->cache();
		Memory::addByPrimaryKey(Memory::QUERY_BUILDERS, $qb);  // Keep separate so memory is flushed between requests in tests
	}
	/**
	 * @return QMQB
	 */
	private function getCloneAndReset(): QMQB{
		$clone = clone $this;
		$this->caller = $this->startTime = $this->duration = $this->values = null;
		return $clone;
	}
	/**
	 * @param string $message
	 */
	private function logInfo(string $message = ""): void{
		if(!AppMode::consoleAvailable()){return;}
		$message = $this->__toString()."\n$message";
		QMLog::infoWithoutContext($message);
	}
	/**
	 * @param string $message
	 */
	private function logDebug(string $message = ""): void{
		if(!AppMode::consoleAvailable()){
			return;
		}
		$message = $this->__toString().$message;
		QMLog::debug($message);
	}
	/**
	 * @param string $caller
	 * @return string
	 */
	private function setType(string $caller): string{
		if($this->aggregate && isset($this->aggregate["function"])){
			return $this->type = $this->aggregate["function"];
		}
		return $this->type = $caller;
	}
	/**
	 * @return string
	 * @noinspection DuplicatedCode
	 */
	public function __toString(){
		$table = $this->getTableName();
		$type = str_replace('getArray', 'get', $this->type);
		$string = "$type $table | ".$this->getDurationString()." | ".$this->getCaller();
		if(!empty($this->resultMessage)){$string .= " | ".$this->resultMessage;}
		if(!empty($message)){$string .= " | ".$message;}
		if(!empty($this->whereString)){$string .= " \n ".$this->whereString;}
		return $string."\n";
	}
	/**
	 * @param string $message
	 */
	private function logWarning(string $message = ""){
		QMLog::warning($this->__toString() . $message);
	}
	/**
	 * @param string $message
	 * @param array|null $meta
	 * @param bool $obfuscate
	 */
	public function logError(string $name, $meta = [], bool $obfuscate = true, string $message = null){
		QMLog::error($this->__toString() . $name, $meta, $obfuscate, $message);
	}
	/**
	 * @return bool
	 */
	public function getSuppressLogs(): bool {
		return $this->suppressLogs;
	}
	/**
	 * @param bool $suppressLogs
	 * @return QMQB
	 */
	public function setSuppressLogs(bool $suppressLogs): QMQB {
		$this->suppressLogs = $suppressLogs;
		return $this;
	}
	/**
	 * @return bool
	 */
	public function getDisableWhereChecks(): bool{
		return $this->disableWhereChecks;
	}
	/**
	 * @param bool $disableWhereChecks
	 */
	public function setDisableWhereChecks(bool $disableWhereChecks): void{
		$this->disableWhereChecks = $disableWhereChecks;
	}
	/**
	 * @return int
	 */
	public function countAndLog(): int {
		$this->getCallerSetTypeAndStartTime();
		//$this->explain();
		$count = $this->count();
		$this->saveReadonlyQuery($count);
		QMLog::info("$count $this->from where:\n".
		                 $this->getWhereString(true));
		return $count;
	}
	/**
	 * @param array $toAdd
	 */
	public function addWheres(array $toAdd){
		if(!$toAdd){return;}
		if(!isset($toAdd[0])){$toAdd = [$toAdd];}
		foreach($toAdd as $where){
			if(isset($where['operator'])){
				$this->where($where['column'], $where['operator'], $where['value'], $where['boolean'] ?? 'and');
			} else { // Simple key value where
				$this->where(key($where), '=', reset($where));
			}
		}
	}
	/**
	 * @return string
	 */
	public static function getSqlComments(): string{
		$comments = "";
		if($test = \App\Utils\AppMode::getCurrentTestName()){
			$comments .= "Test: $test\n";
		}
		if($job = JobTestCase::getJobName()){
			$comments .= "Job: $job\n";
		}
		if($branch = QMAPIRepo::getBranchFromMemory()){
			$comments .= "Branch: $branch\n";
		}
		return "/*
$comments
*/
";
	}
	/**
	 * @param Builder $qb
	 * @return string
	 */
	public static function addBindingsToSql(Builder $qb): string{
		$sql = $qb->toSql();
		$bindings = $qb->getBindings();
		foreach($bindings as $key => $value){
			if(is_string($value)){
				$bindings[$key] = '"'.$value.'"';
			}
			if($value === false){
				$bindings[$key] = 0;
			}
		}
		$q = Str::replaceArray('?', $bindings, $sql);
		return $q;
	}
	/**
	 * @return void
	 */
	public function logQuery(): void{
		$this->logInfo("Running query: ".$this->getPreparedQuery());
	}

    public function whereLike(string $column, string $value, string $boolean = 'and'): Builder
    {
        return $this->where($column, $this->like(), $value, $boolean);
    }

    /**
	 * @param string $message
	 */
	private function logErrorOrDebugIfTesting(string $message){
		QMLog::errorOrDebugIfTesting($this->__toString().$message);
	}
	/**
	 * @param string $message
	 */
	private function logInfoOrDebugIfTesting(string $message){
		QMLog::infoOrDebugIfTesting($this->__toString().$message);
	}
	/**
	 * @return string
	 */
	private function getTableName(): string {
		return QMStr::before(" ", $this->from, $this->from);
	}
	/**
	 * @return string|null
	 */
	private function getTableAlias(): ?string {
		return QMStr::after("as ", $this->from, null);
	}
	/**
	 * @return string
	 */
	private function getTableOrAlias(): string {
		return QMStr::after("as ", $this->from, $this->from);
	}
	/**
	 * @return float
	 */
	private function getDurationInSeconds(): float{
		if($this->duration){
			return $this->duration;
		}
		$current = microtime(true);
		$start = $this->startTime;
		if($start === null){
			le("Please call getCallerSetTypeAndStartTime so startTime is set before query");
		}
		$duration = $current - $start;
		return $this->duration = $duration;
	}
	/**
	 * @return string
	 */
	private function getDurationString(): string{
		$duration = $this->getDurationInSeconds();
		if($duration > 2){
			return round($duration).'s';
		}
		return round($duration * 1000).'ms';
	}
	/**
	 * @param array $columns
	 * @return Collection
	 */
	public function get($columns = ['*']): Collection{
		$this->validateTableForTest();
		if($this->aggregate){
			return parent::get($columns);
		}
		$arrFromDb = $this->getArray($columns);
		if($class = $this->class){
			$arrFromDb = QMQB::instantiateResults($arrFromDb, $class);
		}
		return $this->result = collect($arrFromDb);
	}
	protected function validateTableForTest(){
		$white = TestDB::getWhiteListedTables();
		if($white && !in_array($this->from, $white)){
			le("Why are we trying to query $this->from? \n" . \App\Logging\QMLog::print_r($this->wheres, true));
		}
		$black = TestDB::getBlackListedTables();
		if(in_array($this->from, $black)){
			le("Why are we trying to query $this->from? \n" . \App\Logging\QMLog::print_r($this->wheres, true));
		}
	}
	/**
	 * Get the SQL representation of the query.
	 *
	 * @return string
	 */
	public function toSql(): string{
		$comments = $this->getComments();
		$sql = parent::toSql();
		if(!empty($comments)){$sql = $comments.$sql;}
		return $sql;
	}
	public function getComments(): string{
		$comments = self::getSqlComments();
		if($caller = $this->getOrSetCaller()){
			$comments .= self::CALLER_PREFIX."$caller".self::CALLER_SUFFIX;
		}
		return $comments;
	}
	/**
	 * @param array $columns
	 * @param bool $suppressLog
	 * @return array|bool
	 */
	public function getFromCacheIfPossible(array $columns = ['*'], bool $suppressLog = false): bool|array{
		$previousResult = $this->getPreviousResults();
		if($previousResult !== false){return $previousResult;}
		return $this->getArray($columns, $suppressLog);
	}
	/**
	 * @return Collection
	 */
	public function getLaravelModels(): Collection {
		$ids = $this->getIds();
		$dbClass = $this->getDBModelClass();
		return $dbClass::findLaravelModels($ids);
	}
	/**
	 * @return Collection
	 */
	public function getBaseModels(): Collection {
		return $this->getLaravelModels();
	}
	/**
	 * @return DBModel[]
	 */
	public function getDBModels(): array {
		$ids = $this->getIds();
		$models = [];
		$class = $this->getDBModelClass();
		foreach($ids as $id){
			$models[] = $class::find($id);
		}
		return $models;
	}
	public function getIds(): Collection{
		/** @noinspection UnknownColumnInspection */
		return $this->pluck($this->from.'.id');
	}
	/**
	 * @param array $columns
	 * @param bool $suppressLog
	 * @return array
	 */
	public function getArray(array $columns = ['*'], bool $suppressLog = false): array {
		if(XDebug::active()){static::$loggingEnabled = true;}
		$this->setSuppressLogs($suppressLog);
		$original = $this->columns;
		if($original === null){$this->columns = $columns;}
		$this->getCallerSetTypeAndStartTime();
		$previousResult = $this->getPreviousResults();
		try {
			$this->checkWhereClausesOnGetIfNecessary();
			$this->result = $result = $this->processor->processSelect($this, $this->runSelect());
			if($previousResult === $result){
				$previousQB = $this->getPreviousQB();
				if($this->limit == $previousQB->limit){
					$this->logInfo("Already made identical query and got same result: " . $previousQB->getWhereString());
					return $previousQB->result;
				}
			}
			$this->saveReadonlyQuery($result);
		} catch (QueryException $e) {
			throw new QMQueryException($e, null, $this);
		}
		if(!$result){return [];}
		// TODO: UNCOMMENT THIS if($class = $this->class){$result = QMQB::instantiateResults($result, $class);}
		return $result;
	}
	/**
	 * @param array $values
	 * @param string|null $reason
	 * @return int
	 */
	public function softDelete(array $values = [], string $reason = null): int{
		$this->getCallerSetTypeAndStartTime();
		if($reason){
			$this->logInfoOrDebugIfTesting("Soft-deleting because $reason.");
		}
		$values[QMDB::FIELD_DELETED_AT] =  date('Y-m-d H:i:s');
		return $this->update($values);
	}
	/**
	 * @return bool|array
	 */
	public function getPreviousResults(): bool|array{
		$qb = $this->getPreviousQB();
		if(!$qb){
			return false;
		}
		return $qb->result;
	}
	/**
	 * @return QMQB|null
	 */
	private function getPreviousQB(): ?QMQB{
		/** @var QMQB $qb */
		$executed = Memory::getByPrimaryKey(Memory::QUERY_BUILDERS);
		foreach($executed as $qb){
			if($qb->from === $this->from && $this->wheres === $qb->wheres && $this->aggregate === $qb->aggregate){
				return $qb;
			}
		}
		return null;
	}
	public function cache(){
		Memory::add($this->getTableName(), $this, Memory::QUERY_CACHE);
	}
	/**
	 * @return null
	 */
	public function getCachedResult(){
		$cached = Memory::get($this->getTableName(),Memory::QUERY_CACHE, []);
		/** @var QMQB $qb */
		foreach($cached as $qb){
			if($qb->getTableName() === $this->getTableName() && $this->wheres === $qb->wheres && $this->aggregate === $qb->aggregate){
				return $qb->result;
			}
		}
		return null;
	}
	/**
	 * @param string $message
	 */
	public function throwException(string $message): void{
		throw new LogicException($this->__toString().$message);
	}
	/**
	 * @param string $string
	 * @return bool
	 */
	public static function containsQueryWord(string $string): bool{
		if($string === QueryDataTable::class){return false;}
		$queryWords = self::QUERY_WORDS;
		foreach($queryWords as $queryWord){
			if(stripos($string, $queryWord) !== false){
				return true;
			}
		}
		return false;
	}
	/**
	 * @return string
	 */
	public function getCallerSetTypeAndStartTime(): string{
		$this->validateTableForTest();
		//if(!self::$loggingEnabled){return false;}
		if($this->caller){
			return $this->caller;
		}
		$this->startTime = microtime(true);
		$backTrace = $this->getBackTrace();
		if(!isset($backTrace[0])){
			return $this->caller = 'callerUnknown';
		}
		return $this->caller = $backTrace[0]["function"];
	}
	/**
	 * Insert a new record into the database.
	 * @param array $values
	 * @return bool
	 */
	public function insert(array $values): bool{
		$this->getCallerSetTypeAndStartTime();
		try {
			$result = parent::insert($values);
		} catch (QueryException $e) {
			throw new QMQueryException($e, null, $this);
		}
		return $result;
	}
	/**
	 * Update a record in the database.
	 * @param array $values
	 * @param string|null $reason
	 * @return int
	 */
	public function update(array $values, string $reason = null): int {
		$this->getCallerSetTypeAndStartTime();
		$this->checkWheresOnUpdate();
		if($reason){$this->logDebug("Updating because $reason.");}
		try {
			$result = parent::update($values);
		} catch (QueryException $e) {
			throw new UpdateQueryException($e, $values, $this, $reason);
		}
		return $result;
	}
	/**
	 * Execute the query and get the first result.
	 * @param array $columns
	 * @return DBModel|stdClass|null
	 */
	public function first($columns = ['*']): DBModel|\stdClass|null{
		$this->limit = 1;
		$collection = $this->get();
		/** @var DBModel $dbModel */
		$dbModel = $collection->first();
        if(!$dbModel){
            return null;
        }// Calls get
        if(property_exists($dbModel, 'userId')){
            $dbModel->userId = (int) $dbModel->userId;
        }
        foreach ($dbModel as $key => $value){
            if($key === 'id' || str_ends_with($key, '_id') || str_ends_with($key, 'Id') || $key === 'ID'){
                $dbModel->$key = (int) $value;
            } else if(is_numeric($value)){
                $dbModel->$key = (float) $value;
            }
        }
		return $dbModel;
	}
	/**
	 * @return string
	 */
	public function getCaller(): string{
		if(!$this->caller){le("No caller!");}
		return $this->caller;
	}
	public static function flushQueryLog(){
		self::$alreadyOutputQueries = false;
		Memory::setByPrimaryKey(Memory::QUERY_BUILDERS, []);
		QMDebugBar::flush();
		try {
			QMIgnition::queryRecorder()->reset();
		} catch (\Throwable $e){
			QMLog::info(__METHOD__.": ".$e->getMessage());
		}
		TestQueryLogFile::flushTestQueryLog();
	}
	/**
	 * @param bool $loggingEnabled
	 */
	public static function setLoggingEnabled(bool $loggingEnabled): void{
		self::$loggingEnabled = $loggingEnabled;
	}
	/**
	 * @return float
	 */
	public function getStartTime(): float{
		return $this->startTime;
	}
	private function checkWhereClausesOnGetIfNecessary(){
		if($this->wheres){return;}
		if($this->aggregate){return;}
		if($this->groups){return;}
		if($this->limit){return;}
		if($this->joins){return;}
		if($this->getDisableWhereChecks()){return;}
		$table = $this->getTableName();
		if($this->callerIsTestMethod()){return;}
		if(self::allowFullTableQueries()){return;}
		if(in_array($table, self::$smallTables, true)){return;}
		$this->logDebug("No where clauses on $this->type $table query!");
		//$this->throwException("No where clauses on $this->type $table query!");
	}
	/**
	 * @return bool
	 */
	private function callerIsTestMethod(): bool{
		$backtrace = $this->getBackTrace();
		if(!count($backtrace)){
			return false;
		}
		if(!isset($backtrace[0]["class"])){return false;}
		return stripos($backtrace[0]["class"], "Test") !== false;
	}
	/**
	 * @return array
	 */
	private function getBackTrace(): array{
		if($this->backtrace){
			return $this->backtrace;
		}
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 30);
		while(count($backtrace) > 1 && isset($backtrace[0]['class']) && $backtrace[0]['class'] === \App\Storage\DB\QMQB::class){
			$this->setType($backtrace[0]['function']);
			array_shift($backtrace);
		}
		return $this->backtrace = $backtrace;
	}
	private function checkWheresOnUpdate(): void{
		$table = $this->getTableName();
		if($this->callerIsTestMethod()){
			return;
		}
		if(self::allowFullTableQueries()){
			return;
		}
		if($this->wheres){
			return;
		}
		if(in_array($table, self::$smallTables, true)){
			return;
		}
		le("No where clauses on update $table query!");
	}
	/**
	 * @throws ProtectedDatabaseException
	 */
	public function truncate(){
		if($this->tableIsProtected()){
			throw new ProtectedDatabaseException($this->getTableName()." is protected from truncation");
		}
		parent::truncate();
	}
	/**
	 * @param string $reason
	 * @param bool $logAndCountFirst
	 * @return int
	 */
	public function hardDelete(string $reason, bool $logAndCountFirst = true): int {
		if($logAndCountFirst){
			$this->getCallerSetTypeAndStartTime();
			$count = $this->count();
			if(!$count){
				$this->logInfo("No records to delete because $reason");
				return false;
			}
			$whiteList = [
				QMTrackingReminderNotification::TABLE
			];
			if(in_array($this->from, $whiteList)){
				$this->logInfo("Hard deleting $count $this->from records because $reason");
			} else {
				$this->logError("Hard deleting $count $this->from records because $reason");
			}
		}
		return $this->connection->delete($this->grammar->compileDelete($this), $this->cleanBindings($this->grammar->prepareBindingsForDelete($this->bindings)));
	}
	/**
	 * @param null $id
	 * @param string|null $reason
	 * @return int
	 */
	public function delete($id = null, string $reason = null): int{
		$this->getCallerSetTypeAndStartTime();
		if(!$id && $this->tableIsProtected()){
			if(!$this->wheres){
				throw new InvalidArgumentException($this->getTableName()." is protected from deletion without where clauses");
			}
			if(!QMRequest::urlContains('userVariables/delete') && !XDebug::active()){ // Let's allow this
				$this->logWarning("Soft-deleting because ".$this->getTableName()." is protected.  Deleting because $reason.");
				return $this->softDelete([], $reason);
			}
		}
		$this->logQuery();
		try {
			return parent::delete($id);
		} catch (QueryException $e){
			throw new QMQueryException($e);
		}
	}
	/**
	 * @return bool
	 */
	private function tableIsProtected(): bool{
		return in_array($this->getTableName(), self::$protectedTables, true) && QMDB::dbIsProductionOrStaging();
	}
	/**
	 * Retrieve the "count" result of the query.
	 * @param string $columns
	 * @return int
	 */
	public function countWithoutLogging(string $columns = '*'): int{
		$this->setSuppressLogs(true);
		$result = $this->count($columns);
		return $result;
	}
	/**
	 * @param string $sql
	 * @param array $bindings
	 * @param string $boolean
	 * @return QMQB
	 */
	public function whereRaw($sql, $bindings = [], $boolean = 'and'): QMQB{
		if(!str_contains($sql, '.') && !str_contains($sql, '(')){
			// This doesn't work for length(charts) > 199999.  It makes it user_variable_relationships.length(charts)
			$sql = $this->getTableOrAlias().'.'.$sql;
		}
		$this->wheres[] = ['type' => 'raw', 'sql' => $sql, 'boolean' => $boolean];
		$this->addBinding((array) $bindings, 'where');
		return $this;
	}
	/**
	 * @param array $arrFromDb
	 * @param string $class
	 * @return mixed
	 */
	public static function instantiateResults(array $arrFromDb, string $class): array{
		foreach($arrFromDb as $index => $stdClassFromDb){
			/** @var DBModel $m */
			$m = new $class();
			$m->populateFieldsByArrayOrObject($stdClassFromDb);
			$arrFromDb[$index] = $m;
		}
		return $arrFromDb;
	}
	private function getOrSetCaller(): string {
		if($this->caller){return $this->caller;}
		return $this->getCallerSetTypeAndStartTime();
	}
	/**
	 * @param array $params
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function getUrl(array $params = []): string {
		$table = $this->from;
		$wheres = $this->wheres;
		return BaseModel::generateDataLabIndexUrl($wheres, $table);
	}
	/**
	 * @param array $values
	 * @param null $sequence
	 * @return int
	 */
	public function insertGetId(array $values, $sequence = null): int{
		try {
			return parent::insertGetId($values, $sequence);
		} catch (QueryException $e){
			throw new QMQueryException($e, $values, $this);
		}
	}
	public static function getQueriesWithBackTraces(): array {
		return QMDebugBar::getQueryStatements();
	}
	public function generateIndexMigrationSQL(): string {
		$table = $this->from;
		$columns = $this->getWhereAndOrderColumns();
		$name = $this->generateIndexMigrationName();
		$columnStr = implode(', ',$columns);
		return "create index $name on $table ($columnStr);";
	}
	public function generateIndexMigrationName(): string {
		$table = $this->from;
		$columns = $this->getWhereAndOrderColumns();
		return $table.'_'.implode('_',$columns).'_index';
	}
	public function getIndexMigrationUrl(): string {
		$name = $this->generateIndexMigrationName();
		return QMArtisanController::getMigrationUrl($name, $this->generateIndexMigrationSQL());
	}
	/**
	 * @return mixed
	 */
	public function getWhereAndOrderColumns(): array {
		$columns = [];
		if($this->orders){
			foreach($this->orders as $arr){
				$columns[] = $arr['column'];
			}
		}
		foreach($this->wheres as $arr){
			$columns[] = $arr['column'];
		}
		return $columns;
	}
	public function getHumanizedWhereClause(): string{
		$constraints = $this->getConstraints();
		$strings = [];
		foreach($constraints as $constraint){
			//if(!$constraint->columnExists()){continue;}
			$strings[] = $constraint->humanize();
		}
		return implode(" and ", $strings);
	}
	/**
	 * @return Constraint[]
	 */
	public function getConstraints(): array {
		$arr = [];
		$wheres = $this->wheres;
		foreach($wheres as $where){
			$const = Constraint::fromWhere($where, $this->from);
			$arr[] = $const;
		}
		return $arr;
	}
	/**
	 * @return string
	 * @throws UnauthorizedException
	 */
	public function getPluralTitleWithHumanizedQuery():string{
		$title = $this->getTableTitle();
		$userId = $this->getUserId();
		if($userId){
			$u = QMAuth::getQMUser();
			if($u && $userId === $u->getId()){
				$title = "Your $title";
			} else{
				$title = $title." for User ".$userId;
			}
		}
		$where = $this->getHumanizedWhereClause();
		return $title." ".$where;
	}
	public function getTableTitle(): string {
		return QMStr::tableToTitle($this->from);
	}
	private function getUserId(): ?int {
		$wheres = $this->getConstraints();
		foreach($wheres as $constraint){
			if($constraint->isUserId()){
				return $constraint->getValue();
			}
		}
		return null;
	}
	/**
	 * @return DBModel
	 */
	protected function getDBModelClass(): string {
		return $this->class;
	}
	public function firstDBModel(): ?DBModel {
		$class = $this->getDBModelClass();
		$this->limit = 1;
		$collection = $this->getIds();
		$id = $collection->first();
		if(!$id){return null;}
		return $class::find($id);
	}
	public function dumpTable(): string {
		$t = $this->getTable();
		return $t->dumpToJson();
	}
	public function getTable():DBTable{
		return $this->getDBTable($this->getTableName());
	}
	/**
	 * @return \Illuminate\Database\ConnectionInterface
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function getConnection(){
		return parent::getConnection();
	}
	public function credentialsCommand(): string {
		$qmDB = $this->db();
		return $qmDB->credentialsCommand();
	}
	public function getDBName(): string {
		$qmDB = $this->db();
		return $qmDB->getDBName();
	}
	public function getConnectionName(): string {
		$qmDB = $this->db();
		return $qmDB->getConnectionName();
	}
	public function commit(){
		$this->getConnection()->commit();
	}
	public function db(): QMDB {
		$name = $this->getConnection()->getName();
		if($name === ProductionDB::CONNECTION_NAME){return ProductionDB::db();}
		if($name === StagingDB::CONNECTION_NAME){return StagingDB::db();}
		if($name === DOStaging::CONNECTION_NAME){return DOStaging::db();}
		if($name === DOProduction::CONNECTION_NAME){return DOProduction::db();}
		if($name === BackupDB::CONNECTION_NAME){return BackupDB::db();}
		if($name === TBNGoDaddyDB::CONNECTION_NAME){return TBNGoDaddyDB::db();}
		if($name === TBNDigitalOceanDB::CONNECTION_NAME){return TBNDigitalOceanDB::db();}
		if($name === QMGoDaddyDB::CONNECTION_NAME){return QMGoDaddyDB::db();}
		if($name === Migrations::CONNECTION_NAME){return Migrations::db();}
		if($name === TestDB::DB_NAME){return TestDB::db();}
		if($name === WPDB::DB_NAME){return WPDB::db();}
		if($name === Writable::DB_NAME){return Writable::db();}
		if($name === ReadonlyDB::DB_NAME){return ReadonlyDB::db();}
		if($name === 'mysql'){return Writable::db();}
		if($name === 'sqlite'){return Writable::db();}
		le("Please set DB class to be used for $name in ".__METHOD__);
	}
	/**
	 * @param string $needle
	 * @return bool
	 */
	public function whereClausesContainString(string $needle): bool {
		if(!$this->wheres){return false;}
		$haystack = json_encode($this->wheres);
		return str_contains($haystack, $needle);
	}
	public function logTable(): Collection{
		$rows = $this->get();
		QMLog::table($rows, "Got ".count($rows)." where ".\App\Logging\QMLog::print_r($this->wheres, true));
		return $rows;
	}
	/**
	 * @param bool $pretty
	 * @param string|Builder $sqlOrBuilder
	 * @return string|string[]|null
	 */
	public static function toWhereString(string|Builder $sqlOrBuilder, bool $pretty = false): array|string|null{
		if(!is_string($sqlOrBuilder)){
			$sql = DBQueryLogServiceProvider::toSQL($sqlOrBuilder);
		} else {
			$sql = $sqlOrBuilder;
		}
		if($pretty){ // Only do this if necessary because it uses lots of memory
			$sql = self::formatSQL($sql, false);
		}
		$where = QMStr::after('where ', $sql, "");
		$where = str_replace(" = ", "=", $where);
		$where = SecretHelper::obfuscateString($where);
		$where = str_replace(" and ", " and \n", $where);
		return $where;
	}
    public function whereNotLike(string $column, string $value): \Illuminate\Database\Eloquent\Builder|Builder{
        return self::notLike($this, $column, $value);
    }
}
