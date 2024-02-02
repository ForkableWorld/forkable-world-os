<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Cards;
use App\Models\Variable;
class VariableStatisticsCard extends VariableQMCard {
	/**
	 * @param Variable|\App\Models\UserVariable $variable
	 */
	public function __construct($variable){
		$swaggerDefinition = $variable->getSwaggerDefinition();
		foreach($swaggerDefinition->getProperties() as $property){
			$propertyName = $property->getNameAttribute();
			$description = $property->description;
			if(stripos($description, "Calculated Statistic") === false){
				continue;
			}
			$field = $property->getInputField();
			if(!property_exists($variable, $propertyName)){
				continue;
			}
			$field->setValue($variable->$propertyName);
			if(stripos($description, "Unit: ") && !$field->getUnitAbbreviatedName()){
				$field->setUnit($variable->getUserUnit());
			}
			$field->setShow(true);
			$field->setDisabled(true);
			$this->addInputField($field);
		}
		parent::__construct($variable);
	}
}
