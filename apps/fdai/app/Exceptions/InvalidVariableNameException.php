<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Models\UserVariable;
use App\Models\Variable;
use Exception;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use App\Variables\QMVariable;
class InvalidVariableNameException extends Exception implements ProvidesSolution {
    /**
     * @var QMVariable
     */
    private $variable;
    /**
     * @param string $name
     * @param string $message
     * @param QMVariable|Variable|UserVariable $variable
     */
    public function __construct(string $name, string $message = '', $variable = null){
        $this->variable = $variable;
        parent::__construct("$name is not a valid variable name!  $message", QMException::CODE_BAD_REQUEST);
    }
    public function getSolution(): Solution{
        if($this->alreadyExists()){
            return BaseSolution::create("Rename this Variable")
                ->setSolutionDescription("Contact help@curedao.org if you need assistance")
                ->setDocumentationLinks([
                    "Change Name" => $this->getVariable()->getEditUrl()
                ]);
        } else {
            return BaseSolution::create("Select a Different Name")
                ->setSolutionDescription("Contact help@curedao.org if you need assistance")
                ->setDocumentationLinks([
                    "Search Available Variables" => Variable::getDataLabIndexUrl()
                ]);
        }
    }
    /**
     * @return QMVariable|Variable
     */
    public function getVariable(){
        return $this->variable;
    }
    /**
     * @return bool
     */
    public function alreadyExists(): bool {
        return $this->variable && $this->getVariable()->getId();
    }
}
