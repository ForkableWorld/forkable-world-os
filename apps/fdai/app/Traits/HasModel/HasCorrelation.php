<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Exceptions\InvalidVariableValueException;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasCorrelation {
	public function getCorrelationId(): int{
		$nameOrId = $this->getAttribute('correlation_id');
		return $nameOrId;
	}
	public function getCorrelationButton(): QMButton{
		$correlation = $this->getCorrelation();
		if($correlation){
			return $correlation->getButton();
		}
		return UserVariableRelationship::generateDataLabShowButton($this->getCorrelationId());
	}
	/**
	 * @return UserVariableRelationship
	 */
	public function getCorrelation(): UserVariableRelationship{
		if($this instanceof BaseProperty && $this->parentModel instanceof UserVariableRelationship){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('correlation')){
			return $l;
		}
		$id = $this->getCorrelationId();
		$correlation = UserVariableRelationship::findInMemoryOrDB($id);
		if(!$correlation){
			$dbm = QMUserVariableRelationship::getExistingUserVariableRelationshipByVariableIds($this->getUserId(),
				$this->getCauseVariableId(), $this->getEffectVariableId());
			if($dbm){
				$correlation = $dbm->l();
			}
		}
		if(property_exists($this, 'relations')){
			$this->relations['correlation'] = $correlation;
		}
		if(property_exists($this, 'correlation')){
			$this->correlation = $correlation;
		}
		return $correlation;
	}
	public function getCorrelationNameLink(): string{
		return $this->getCorrelation()->getDataLabDisplayNameLink();
	}
	public function getCorrelationImageNameLink(): string{
		return $this->getCorrelation()->getDataLabImageNameLink();
	}
	/**
	 * @param float $value
	 * @param string $key
	 * @throws InvalidVariableValueException
	 */
	public function validateCauseValue(float $value, string $key, int $duration = null){
		$correlation = $this->getCorrelation();
		$cause = $correlation->getCauseVariable()->getDBModel();
		try {
			$cause->validateValueForCommonVariableAndUnit($value, $key, $duration, $cause);
		} catch (InvalidVariableValueException $e) {
			$this->throwException(__METHOD__.": ".$e->getMessage());
		}
	}
	/**
	 * @param float $value
	 * @param string $key
	 * @throws InvalidVariableValueException
	 */
	public function validateEffectValue(float $value, string $key){
		$correlation = $this->getCorrelation();
		$cause = $correlation->getEffectVariable()->getDBModel();
		$cause->validateValueForCommonVariableAndUnit($value, $key, $correlation->duration_of_action, $cause);
	}
}
