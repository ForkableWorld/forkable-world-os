<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Files\TestArtifacts\TestQueryLogFile;
use App\Logging\QMClockwork;
use App\Solutions\DebugQueriesSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
class TooManyQueriesException extends \Exception implements ProvidesSolution
{
    /**
     * TooManyQueriesException constructor.
     * @param string $message
     * @param null $queries
     */
    public function __construct(string $message, $queries = null){
        $table = "";
        if($queries){
            foreach($queries as $q){
				if(is_string($q)){
					$table .="\n".$q;
				} else {
					$table .="\n".$q->query;
				}
            }
        } else {
            $table = TestQueryLogFile::getQueryLogCliTable() . TestQueryLogFile::getDuplicateQueryTables();
        }
        $message = $message.$table."\n".$message;
		if(QMClockwork::enabled()){$message .= "\n\n".QMClockwork::getAppUrl()."\n";}
        parent::__construct($message);
    }
    public function getSolution(): Solution{
	    return new DebugQueriesSolution();
    }
}
