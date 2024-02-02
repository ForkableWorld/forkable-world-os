<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Cards;
use App\Variables\QMVariable;
class VariableQMCard extends QMCard {
	private $variable;
	/**
	 * @param QMVariable $variable
	 */
	public function __construct($variable){
		$this->variable = $variable;
		if(!$this->buttons){
			$this->buttons = $variable->getButtons();
		}
		$this->setImage($variable->getSvgUrl());
		$this->setIonIcon($variable->getIonIcon());
		$this->setAvatar($variable->getSvgUrl());
		$this->getParameters();
		parent::__construct($variable->getOrSetVariableDisplayName());
	}
	/**
	 * @return array
	 */
	public function getParameters(): array{
		$variable = $this->getVariable();
		$this->addParameter('variableName', $variable->getVariableName());
		$this->addParameter('variableId', $variable->getVariableIdAttribute());
		if(isset($variable->userId)){
			$this->addParameter('userId', $variable->userId);
		}
		return $this->parameters;
	}
	/**
	 * @return QMVariable
	 */
	public function getVariable(){
		return $this->variable;
	}
}
