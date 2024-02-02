<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Solutions\GoToAdminerSolution;
use App\Solutions\UpdateTestDBSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Illuminate\Database\QueryException;
use App\Storage\DB\QMQB;
use App\Types\QMStr;
class QMQueryException extends QueryException implements ProvidesSolution
{
	/**
	 * QMQueryException constructor.
	 * @param QueryException $queryException
	 * @param array|null $dataToInsert
	 * @param QMQB|null $qb
	 */
    public function __construct($queryException, array $dataToInsert = null, QMQB $qb = null){
        parent::__construct($queryException->getSql(), $queryException->getBindings(), $queryException);
        $m = $queryException->getMessage();
        if($qb){$m .= $qb->getCallerSetTypeAndStartTime();}
        if($dataToInsert && stripos($m, 'FOREIGN KEY') !== false){
            $localKey = QMStr::between($m, 'FOREIGN KEY (`', '`) REFERENCES');
            $otherTable = QMStr::between($m, 'REFERENCES `', '` (`');
            $value = $dataToInsert[$localKey];
            $m = "$otherTable $value not found!\n".$m;
        }
        if($dataToInsert){
            $m .= "\nTried to insert: \n".\App\Logging\QMLog::print_r($dataToInsert, true);
        }
        $this->message = $m;
    }
    public function getSolution(): Solution{
        if(stripos($this->message, "Unknown column") !== false){
            return new UpdateTestDBSolution();
        } else{
            return new GoToAdminerSolution();
        }
    }
}
