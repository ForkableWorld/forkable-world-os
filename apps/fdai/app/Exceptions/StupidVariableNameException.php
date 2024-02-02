<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Models\Variable;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use App\Variables\QMVariable;
use App\PhpUnitJobs\Cleanup\CommonVariableCleanupJob;
class StupidVariableNameException extends BaseException implements ProvidesSolution {
    /**
     * @var string
     */
    protected $variableName;
    protected $variable;
    /**
     * @param string $name
     * @param string $userMessage
     * @param $v
     */
    public function __construct(string $name, string $userMessage, $v){
        $this->variable = $v;
        $this->variableName = $name;
        parent::__construct("Invalid Variable Name",
            "$name is an invalid variable name. $userMessage");
    }
    public function getSolution(): Solution{
        return BaseSolution::create("View and Delete")
            ->setSolutionDescription("Consider deleting or renaming this variable")
            ->setDocumentationLinks([
                "View $this->variableName Variable" => $this->getVariable()->getUrl(),
                "Delete It with CommonVariableCleanupJob->testDeleteStupidBoringVariables" => CommonVariableCleanupJob::getJobPHPStormUrl(),
            ]);
    }
    /**
     * @return Variable
     */
    public function getVariable(){
        if($this->variable instanceof QMVariable){
            return $this->variable->getCommonVariable()->l();
        }
        return $this->variable;
    }
}
