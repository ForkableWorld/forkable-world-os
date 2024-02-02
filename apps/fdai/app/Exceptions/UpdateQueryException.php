<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Solutions\GoToAdminerSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use App\Storage\DB\QMQB;
class UpdateQueryException extends QMQueryException implements ProvidesSolution
{
    /**
     * QMQueryException constructor.
     * @param \Illuminate\Database\QueryException $queryException
     * @param array|null $dataToInsert
     * @param QMQB $qb
     * @param string|null $reason
     */
    public function __construct($queryException, array $dataToInsert = null, QMQB $qb = null, string $reason = null){
        parent::__construct($queryException);
        $this->message .= $qb->getCaller().": ".$queryException->getMessage()."
            Wanted to update $qb->from
            where ".$qb->getWhereString(true)."
            with values: ".\App\Logging\QMLog::print_r($dataToInsert, true)."
            because $reason. ";
    }
    public function getSolution(): Solution{
        return new GoToAdminerSolution();
    }
}
