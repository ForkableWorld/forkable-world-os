<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Models\Variable;
use App\PhpUnitJobs\Cleanup\CommonVariableCleanupJob;
use App\Variables\QMVariable;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\Solution;
class StupidVariableException extends BaseException {
    /**
     * @var string
     */
    protected $variableName;
    protected $variable;
    /**
     * @param string $userMessage
     * @param $v
     */
    public function __construct(string $userMessage, $v){
        $this->variable = $v;
        parent::__construct($userMessage);
    }
    public function getSolution(): Solution{
        return BaseSolution::create("View and Delete")
            ->setSolutionDescription("Consider deleting this variable")
            ->setDocumentationLinks([
                "View $this->variableName Variable" => $this->getVariable()->getUrl(),
                "Delete It with CommonVariableCleanupJob->testDeleteStupidBoringVariables" =>
	                CommonVariableCleanupJob::getJobPHPStormUrl(),
            ]);
    }
    /**
     * @return VariableTrait|Variable
     */
    public function getVariable(){
        if($this->variable instanceof QMVariable){
            return $this->variable->getCommonVariable()->l();
        }
        return $this->variable;
    }
}
