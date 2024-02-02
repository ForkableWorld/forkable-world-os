<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage;
use App\Buttons\Admin\PHPStormButton;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Storage\DB\Writable;
use App\Traits\LoggerTrait;
use App\Utils\EnvOverride;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Database\Events\QueryExecuted;
use App\Types\QMStr;
class QMQueryExecuted extends QueryExecuted
{
    use LoggerTrait;
    public string $preparedQuery;
    public array $fullTrace;
    public string $type;
    public $relevantTrace;
    /**
     * @var string
     */
    public string $callerFunction;
    /**
     * @var string|null
     */
    public ?string $table;
    public array $callerFrame;
    public function __construct(QueryExecuted $qe){
        parent::__construct($qe->sql, $qe->bindings, $qe->time, $qe->connection);
        $length = count($qe->bindings);
	    $table = $this->getTable();
        if($length > 1000){
            $this->preparedQuery = $qe->sql ." (Too slow to prepare this large $length character query)";
        } elseif ($table === 'sessions' || $table === 'jobs') {
	        $this->preparedQuery = $qe->sql ." (Not preparing query because the fixtures change constantly)";
        } else {
            $this->preparedQuery = self::replaceBindings($qe);
            if(!$this->preparedQuery){le( "No prepared query from: ", $qe);}
        }
        $this->fullTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 45); // Need 45 for deep RoutesNotifications.php:18 calls

	    Bugsnag::leaveBreadcrumb('Query Executed', 'process', [
            'table' => $table,
            'caller' => $this->getCallerFunction(),
            'duration' => $this->getDurationStringIfAbove1Second(),
            'where' => $this->getWhereOrTruncatedQuery(100),
        ]);
    }
    /**
     * @return string
     */
    public function getPreparedQuery(): string {
        return $this->preparedQuery;
    }
    /**
     * @return string
     */
    public function getPreparedQueryWithoutCaller(): string {
        return QMStr::afterLast($this->getPreparedQuery(), "*/");
    }
    public function getWhere(bool $stripTable = true, ?string $default = "NO WHERE CLAUSE FOUND"): ?string {
        $q = $this->getPreparedQuery();
        if(!$q){return "Nothing from getPreparedQuery!";}
        $str = QMStr::after(' where', $q,
            null, true);
		if(!$str){
			return $default;
		}
        if($stripTable){
            $str = str_replace("`".$this->getTable()."`.", '', $str);
        }
        return $str;
    }
    public function getWhereOrTruncatedQuery(int $maxLength):string{
        $str = $this->getWhere(true,null);
        if(!$str){
            $prepared = $str = $this->getPreparedQuery();
	        if(!$str){le("no getPreparedQuery!");}
            if(stripos($str, ') values ') !== false){
                $str = QMStr::after(') values ', $str);
            }
			if(!$str){
				return $prepared;
			}
        }
        $str = QMStr::afterLast($str, '*/');
        $str = QMStr::trimWhitespaceAndLineBreaks($str);
        $str = str_replace(' = ', '=', $str);
        $str = str_replace(' is ', '=', $str);
        $str = str_replace(' and ', ' & ', $str);
        if($type = $this->getType()){
            if($type === "insert"){$str = "insert ". $str;}
            if($type === "update"){
                $str = "update where ". $str;
                if(stripos($this->preparedQuery, 'set ') !== false){
                    $str = 'set '.QMStr::after(' set ', $this->preparedQuery);
                }
            }
            if($type === "delete"){
                $str = "delete ". $str;
                $str = str_replace("delete delete", "delete", $str);
            }
        }
        $str = QMStr::truncate($str, $maxLength);
		$str = self::removeQuotes($str);
        return $str;
    }
    public function getTable(): string {
        return $this->table = self::getTableFromSQL($this->sql);
    }
    public static function getTableFromSQL(string $sql): string {
        if(stripos($sql, '* from ') !== false){
            $t = QMStr::between($sql." ", '* from ', ' ', null, true);
			if(!$t){
				return $t;
			}
	        $t = self::formatTable($t);
	        return $t;
        }
        if(stripos($sql, ' from ') !== false){
            $t = QMStr::between($sql, 'from ', ' ', null, true);
			if(!$t){
				$t = QMStr::after('from ', $sql,  null, true);
			}
	        if(!$t){return $t;}
            return self::formatTable($t);
        }
        if(stripos($sql, 'insert into') !== false){
            $t = QMStr::between($sql, 'insert into'." ", ' ', null, true);
	        if(!$t){return $t;}
            return self::formatTable($t);
        }
        $tables = Writable::getBaseModelTableNames();
        foreach($tables as $t){
            if(str_contains($sql, $t)){
	            if(!$t){return $t;}
                return self::formatTable($t);
            }
        }
	    return $sql;
        //return "No table: $sql";
        //throw new \LogicException("could not find table for ".$this->sql);
    }
    public function getCallerFunction(): string {
        $frame = $this->getCallerFrame();
        if(!$frame){
            return $this->callerFunction = 'callerUnknown';
        }
        if(strlen($frame["function"]) < 10 && isset($frame["class"])){
            $class = QMStr::toShortClassName($frame["class"]);
            return $this->callerFunction = $class."::".$frame["function"];
        }
        return $this->callerFunction = $frame["function"];
    }
	/**
	 * @param string|null $t
	 * @return string
	 */
	public static function formatTable(string $t): string{
		$t = str_replace('`', '', $t);
		$t = QMQueryExecuted::removeQuotes($t);
		$t = trim(QMStr::stripNewLines($t));
		return $t;
	}
	public function getCallerFileLine(): string {
        $frame = $this->getCallerFrame();
        if(!$frame){return 'callerUnknown';}
        if(!isset($frame["file"])){
            return 'no file in this frame to getCallerFileLine '.QMLog::print_r($frame, true);
        }
		if(!EnvOverride::isLocal()){
			return PHPStormButton::redirectUrl($frame["file"], $frame['line']);
		}
		return $frame["file"].":".$frame['line'];
    }
    public function getCallerFrame(): array {
        $backTrace = $this->fullTrace;
		while($frame = array_shift($backTrace)){
			$file = $frame["file"] ?? null;
			if($file && str_contains($file, 'vendor')){continue;}
			if(QMLog::isLoggyFunction($frame["function"])){continue;}
			return $this->callerFrame = $frame;
		}
	    $backTrace = $this->fullTrace;
	    while($frame = array_shift($backTrace)){
		    if(QMLog::isLoggyFunction($frame["function"])){continue;}
		    return $this->callerFrame = $frame;
	    }
        return $this->callerFrame = $this->fullTrace[0];
    }
    /**
     * Replace the placeholders with the actual bindings.
     * @param \Illuminate\Database\Events\QueryExecuted $queryExecuted
     * @return string
     * @noinspection DuplicatedCode
     */
    public static function replaceBindings(QueryExecuted $queryExecuted): string {
        $sql = $queryExecuted->sql;
        $formatted = $queryExecuted->connection->prepareBindings($queryExecuted->bindings);
        foreach ($formatted as $key => $binding) {
            $regex = is_numeric($key)
                ? "/\?(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/"
                : "/:$key(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/";
            if ($binding === null) {
                $binding = 'null';
            } elseif (! is_int($binding) && ! is_float($binding)) {
                $binding = "'" . $binding . "'"; // Faster but doesn't work for WP posts sometimes
            }
            $sql = preg_replace($regex, $binding, $sql, 1);
        }
        if(!$sql){ // Have to use $queryExecuted->connection->getPdo()->quote($binding)
            $sql = $queryExecuted->sql;
            foreach ($formatted as $key => $binding) {
                $regex = is_numeric($key) ? "/\?(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/"
                    : "/:{$key}(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/";
                if ($binding === null) {
                    $binding = 'null';
                } elseif (! is_int($binding) && ! is_float($binding)) {
                    $binding = $queryExecuted->connection->getPdo()->quote($binding); // Slower but more versatile
                }
                $sql = preg_replace($regex, $binding, $sql, 1);
            }
        }
        if(!$sql){
			le("No prepared query from: ", [
	            'QueryExecuted' => $queryExecuted,
	            'formatted' => $formatted
	        ]);
		}
        return $sql;
    }
	/**
	 * @return string
	 */
    public function __toString(){
        $table = $this->getTable();
        $type = str_replace('getArray', 'get', $this->type);
        $string = "$type $table | ".$this->getDurationStringIfAbove1Second()." | ".$this->getCallerFunction();
        if(!empty($this->resultMessage)){
            $string .= " | ".$this->resultMessage;
        }
        if(!empty($message)){
            $string .= " | ".$message;
        }
        if(!empty($this->whereString)){
            $string .= " \n ".$this->whereString;
        }
        return $string."\n";
    }
    /**
     * @return string
     */
    private function getDurationStringIfAbove1Second(): string {
        $duration = $this->getDurationInSeconds();
        if($duration > 0){return $duration.'s';}
        return ""; // Reduce variation spam in test logs;
    }
    /**
     * @return int
     */
    private function getDurationInSeconds(): int {
        return round($this->time/1000);
    }
    public function getTableName():string{
        return $this->getTable();
    }
    public function toArray(): array {
        return [
            'Caller' => QMStr::truncate($this->getCallerFunction(), 30),
            'Table' => $this->getFormattedTable(),
            'Where or Query' => $this->getWhereOrTruncatedQuery(60),
            'Time' => $this->getDurationStringIfAbove1Second(),
        ];
    }
    public function toGenericArray(): array {
        $sql = $this->getWhereOrTruncatedQuery(1000);
        $sql = QMStr::afterLast($sql, "*/",$sql);
        foreach($this->bindings as $binding){
            if(strlen((string) $binding) > 9){
                $sql = str_replace($binding, "?", $sql);
            }
        }
        $sql = QMStr::truncate($sql, 60);
	    $sql = QMQueryExecuted::removeQuotes($sql);
	    return [
            'Caller' => QMStr::truncate($this->getCallerFunction(), 30),
            'Table' => $this->getFormattedTable(),
            'SQL' => $sql
        ];
    }
    protected function getFormattedTable():string{
        $table = str_replace('bshaffer_', '', $this->getTable());
        return QMStr::truncate($table, 21, "");
    }
    public function logQuery(){
        try {
            ConsoleLog::info($this->getMessage());
        } catch (\Throwable $e){
            ConsoleLog::info($this->getMessage());
        }
    }
    public function getType(): string {
        return QMStr::getFirstWordOfString($this->getPreparedQueryWithoutCaller());
    }
    /**
     * @return string
     */
    public function getMessage(): string{
        $msg = $this->getTableName().": ".$this->getDurationStringIfAbove1Second().
            " ". $this->getWhereOrTruncatedQuery(100).
            "\n\t@ ".
            $this->getCallerFunction(). " ". $this->getCallerFileLine(). "\n";
        if(str_contains($msg, "Factory::{closure}")){
            QMLog::exceptionIfTesting($msg);
        }
        return $msg;
    }
	public function shouldLog(): bool{
		$table = $this->table;
		if(str_contains($table, 'tddd_')){
			return false;
		}
		if(str_starts_with($table, 'insert into "migrations"')){
			return false;
		}
		return true;
	}
	public function shouldLogIfDuplication(){
		$query = $this->preparedQuery;
		return !str_starts_with($query, "Too slow ") &&
		       !str_contains(" sessions ", $query) &&
		       $query !== 'select "migration" from "migrations" order by "batch" asc, "migration" asc';
	}
	public function logDuplicateQueryIfNecessary(){
		if(!$this->shouldLogIfDuplication()){
			return;
		}
		$message = $this->getMessage();
		$str = "Duplicate Query:\n".$message;
		if(str_contains($message, "sessions")){
			ConsoleLog::debug($str);
		} else {
			QMLog::warning($str);
		}
	}
	/**
	 * @param string $sql
	 * @return string
	 */
	public static function removeQuotes(string $sql): string{
		$sql = str_replace('"', '', $sql);
		$sql = str_replace('`', '', $sql);
		return $sql;
	}
}
