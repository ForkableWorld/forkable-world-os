<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Models\BaseModel;
class InvalidVariableValueAttributeException extends InvalidAttributeException
{
	/**
	 * @param \App\Models\BaseModel $model
	 * @param string $attributeName
	 * @param $attributeValue
	 * @param string $ruleDescription
	 */
	public function __construct(BaseModel $model, string $attributeName, $attributeValue, string $ruleDescription){
        parent::__construct($model, $attributeName, $attributeValue, $ruleDescription);
    }
}
