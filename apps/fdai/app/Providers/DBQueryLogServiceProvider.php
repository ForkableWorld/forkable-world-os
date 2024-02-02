<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Providers;
use App\Buttons\Admin\PHPStormButton;
use App\Exceptions\SlowQueryException;
use App\Files\FileHelper;
use App\Storage\DB\QMQB;
use App\Storage\QMQueryExecuted;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
class DBQueryLogServiceProvider extends LaravelServiceProvider
{
    const RELATIVE_PATH = 'app/Providers/DBQueryLogServiceProvider.php';
    /**
     * Bootstrap the application services.
     */
    public function boot(){
        DB::listen(function (QueryExecuted $query) {
            $qmQuery = QMQB::saveQueryExecuted($query);
            SlowQueryException::logIfSlow($qmQuery->time/1000, $qmQuery);
            //SlowQueryException::throwIfNecessary($query);
        });
    }
    /**
     * Register the application services.
     */
    public function register()
    {
    }
    /**
     * @param QueryExecuted $queryExecuted
     * @return string|string[]
     */
    public static function getRealSQL(QueryExecuted $queryExecuted): array|string{
        return QMQueryExecuted::replaceBindings($queryExecuted);
    }
    public static function getUrl(): string {
        return PHPStormButton::redirectUrl(self::RELATIVE_PATH, 20);
    }
    public static function getAbsolutePath(): string {
        return FileHelper::absPath(self::RELATIVE_PATH).":20";
    }
    /**
     * @param \Illuminate\Database\Eloquent\Builder|Builder $qb
     * @return string
     */
    public static function toSQL(\Illuminate\Database\Eloquent\Builder|Builder $qb): string {
        return QMQB::addBindingsToSql($qb);
    }
	/**
	 * @param string|Builder $sqlOrBuilder
	 * @param bool $pretty
	 * @return string
	 */
    public static function toWhereString(string|Builder $sqlOrBuilder, bool $pretty = false): string{
        return QMQB::toWhereString($sqlOrBuilder, $pretty);
    }
}
