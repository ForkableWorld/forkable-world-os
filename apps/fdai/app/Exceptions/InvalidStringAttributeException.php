<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Models\BaseModel;
use App\Traits\ExceptionTraits\IsStringException;
use Facade\IgnitionContracts\ProvidesSolution;
class InvalidStringAttributeException extends InvalidAttributeException implements ProvidesSolution {
    use IsStringException;
    /**
     * @param string $ruleDescription
     * @param string $fullString
     * @param string $attributeName
     * @param BaseModel $model
     */
    public function __construct(string $ruleDescription, string $fullString, string $attributeName, BaseModel $model){
        $this->fullString = $fullString;
        $this->attributeName = $attributeName;
        parent::__construct($model, $attributeName, $this->getInvalidStringSegment(),
            $ruleDescription);
    }
}
