<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Solutions\ViewAnalyzableDataSolution;
use Facade\IgnitionContracts\Solution;
use App\Storage\DB\QMQB;
use App\Variables\QMUserVariable;
class NotEnoughVariablesToCorrelateWithException extends BaseException {
    /**
     * @var QMUserVariable
     */
    private $userVariable;
    /**
     * @var QMQB
     */
    private $queryForVariablesToCorrelateWith;
    /**
     * NotEnoughVariablesToCorrelateWithException constructor.
     * @param QMUserVariable $userVariableToCorrelate
     * @param QMQB $queryForVariablesToCorrelateWith
     */
    public function __construct(QMUserVariable $userVariableToCorrelate, QMQB $queryForVariablesToCorrelateWith){
        $this->queryForVariablesToCorrelateWith = $queryForVariablesToCorrelateWith;
        $v = $this->userVariable = $userVariableToCorrelate;
        parent::__construct("Not Enough Data",
            $v->getCorrelationDataRequirementAndCurrentDataQuantityString(),
            "Query for Variables to Correlate with: \n".$queryForVariablesToCorrelateWith->getWhereString(),
            $v->getNoCorrelationsDataRequirementAndCurrentDataQuantityHtml());
    }
    public function getSolution(): Solution{
        return new ViewAnalyzableDataSolution($this->userVariable);
    }
}
