<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Logging\QMClockwork;
use App\Logging\QMLog;
use App\Providers\DBQueryLogServiceProvider;
use App\Solutions\SlowQuerySolution;
use App\Storage\DB\QMQB;
use App\Storage\DB\Writable;
use App\Storage\QMQueryExecuted;
use App\Utils\AppMode;
use Facade\IgnitionContracts\ProvidesSolution;
use Illuminate\Database\Events\QueryExecuted;
use Tests\QMDebugBar;
class SlowQueryException extends BaseException implements ProvidesSolution {
    public static bool $disabled = false;
    public mixed $explanation;
    public QMQueryExecuted $queryExecuted;
	/**
	 * SlowQueryException constructor.
	 * @param QMQueryExecuted $queryExecuted
	 */
    public function __construct(QMQueryExecuted $queryExecuted){
        $seconds = $queryExecuted->time / 1000;
        $this->queryExecuted = $queryExecuted;
        try {
            $this->explanation = QMDebugBar::explainQuery($queryExecuted);
        } catch (\Throwable $e){
           $this->explanation = "Could not explain query because ".$e->getMessage();
        }
        $sql = DBQueryLogServiceProvider::getRealSQL($queryExecuted);
		$msg = self::generateMessage($seconds, $sql);
		QMClockwork::logSlowOperation($msg);
        parent::__construct( $msg);
    }
    public static function queryIsSlow(float $seconds): bool{
        if(AppMode::isApiRequest()){
            $slow = $seconds > QMQB::MAX_API_QUERY_DURATION;
        }else{
            $slow = $seconds > QMQB::MAX_WORKER_QUERY_DURATION;
        }
        return $slow;
    }
    /**
     * Format duration.
     * @param QueryExecuted $query
     * @return string
     */
    public static function formatDuration(QueryExecuted $query): string{
        $seconds = $query->time / 1000;
        if($seconds < 0.001){
            return round($seconds * 1000000).'μs';
        }elseif($seconds < 1){
            return round($seconds * 1000, 2).'ms';
        }
        return round($seconds, 2).'s';
    }
    public static function disable(): void{
        self::$disabled = true;
    }
    public function getSolution(): \Facade\IgnitionContracts\Solution{
        return new SlowQuerySolution($this);
    }
    /**
     * @param float $duration
     * @param QueryExecuted|string $sql
     * @return string
     */
    public static function generateMessage(float $duration, $sql): string {
        if($sql instanceof QueryExecuted){
			if(count($sql->bindings) > 100){
				$sql = 'Too many bindings to display raw SQL for this query:'.$sql->sql;
			} else {
				$sql = DBQueryLogServiceProvider::getRealSQL($sql);
			}
        }
        return "SLOW QUERY took $duration seconds: ".$sql;
    }
    /**
     * @param float $duration
     * @param string|QMQueryExecuted $queryExecuted
     */
    public static function logIfSlow(float $duration, QueryExecuted|string $queryExecuted){
        if(self::$disabled){return;}
        if(self::queryIsSlow($duration)){
            if(is_string($queryExecuted)){
                $sql = $queryExecuted;
                $queryExecuted = new QueryExecuted($sql, [], $duration, Writable::db());
            }
			if(!$queryExecuted instanceof QMQueryExecuted){
				$queryExecuted = new QMQueryExecuted($queryExecuted);
			}
	        $slowQuerySolutions = [
		        'solution' => new SlowQuerySolution(new SlowQueryException($queryExecuted))
	        ];
	        QMLog::error(self::generateMessage($duration, $queryExecuted), $slowQuerySolutions);
        }
    }
}
