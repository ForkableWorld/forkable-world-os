<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Traits\HasBaseSolution;
use Exception;
use Facade\IgnitionContracts\ProvidesSolution;
use App\Utils\IonicHelper;
use App\Slim\Model\QMUnit;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
class IncompatibleUnitException extends Exception implements ProvidesSolution
{
    use HasBaseSolution;
    /**
     * @var QMUnit
     */
    private $fromUnit;
    /**
     * @var QMUnit
     */
    private $toUnit;
    /**
     * @var QMUserVariable|QMCommonVariable|Variable|UserVariable
     */
    private $variable;
    /**
     * IncompatibleUnitException constructor.
     * @param QMUnit $fromUnit
     * @param QMUnit $toUnit
     * @param QMUserVariable|QMCommonVariable|Variable|UserVariable $variable
     * @param string $message
     */
    public function __construct($fromUnit, $toUnit, $variable = null, string $message = ""){
        $url = IonicHelper::getIonicAppUrl();
        $this->fromUnit = $fromUnit;
        $this->toUnit = $toUnit;
        $this->variable = $variable;
        if($variable){
            $url = $variable->getEditUrl();
        }
        $message .="
Cannot convert $fromUnit->name to $toUnit->name!
Change your custom unit to one of the following compatible units at $url
Compatible Units:
".$fromUnit->getCompatibleUnitsList();
        parent::__construct($message, 400);
    }
    public function getSolutionTitle(): string{
        return "Use Compatible Unit";
    }
    public function getSolutionDescription(): string{
        return "Change your custom unit to one of the following compatible units: \n".
            $this->fromUnit->getCompatibleUnitsList();
    }
    public function getDocumentationLinks(): array{
        return $this->variable->getDataLabUrls();
    }
}
