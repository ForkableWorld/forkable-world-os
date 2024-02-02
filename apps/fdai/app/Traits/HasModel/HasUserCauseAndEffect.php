<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\VariableRelationships\QMVariableRelationship;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\NotEnoughDataException;
use App\Models\UserVariable;
use App\Properties\Study\StudyTypeProperty;
use App\Slim\Model\QMUnit;
trait HasUserCauseAndEffect {
	abstract public function getCauseUserVariableId(): int;
	/**
	 * @return QMUnit
	 */
	public function getCauseVariableUserUnit(): QMUnit{
		return QMUnit::getUnitById($this->getCauseVariableUserUnitId());
	}
	/**
	 * @return int
	 */
	public function getCauseVariableUserUnitId(): int{
		return $this->getCauseUserVariable()->getUnitIdAttribute();
	}
	abstract public function getCauseUrl(): string;
	abstract public function getEffectUserVariableId(): int;
	/**
	 * @return QMUnit
	 */
	public function getEffectVariableUserUnit(): QMUnit{
		return QMUnit::getUnitById($this->getEffectVariableUserUnitId());
	}
	/**
	 * @return int
	 */
	public function getEffectVariableUserUnitId(): int{
		return $this->getEffectUserVariable()->getUnitIdAttribute();
	}
	public function getEffectUserVariable(): UserVariable{
		/** @var UserVariable $uv */
		$uv = $this->getRelationIfLoaded('effect_user_variable');
		if(!$uv){
			$uv = UserVariable::findInMemoryOrDB($this->getEffectUserVariableId());
		}
		if($uv && $this->hasId()){
			$this->setRelationAndAddToMemory('effect_user_variable', $uv);
		}
		return $uv;
	}
	public function getCauseUserVariable(): UserVariable{
		/** @var UserVariable $uv */
		$uv = $this->getRelationIfLoaded('cause_user_variable');
		if(!$uv){
			$id = $this->getCauseUserVariableId();
			$uv = UserVariable::findInMemoryOrDB($id);
		}
		if($uv && $this->hasId()){
			$this->setRelationAndAddToMemory('cause_user_variable', $uv);
		}
		return $uv;
	}
	/**
	 * @return float
	 * @throws NotEnoughDataException
	 */
	public function getLastProcessedDailyValueForCauseInCommonUnit(): ?float {
		return $this->getCauseUserVariable()->getLastProcessedDailyValueInCommonUnit();
	}
	/**
	 * @return string
	 * @throws NotEnoughDataException
	 */
	protected function getBasedOnString(): string{
		$days = $this->getOrCalculateNumberOfDays();
		return "<br>based on $days days of data";
	}
	public function getStudyType(): string{
		return StudyTypeProperty::TYPE_INDIVIDUAL;
	}
	/**
	 * @param float $inCommonUnit
	 * @param int $precision
	 * @return string|null
	 * @throws InvalidVariableValueException
	 * @throws IncompatibleUnitException
	 */
	protected function causeValueUserUnit(float $inCommonUnit, int $precision = QMVariableRelationship::SIG_FIGS): string{
		$uv = $this->getCauseUserVariable();
		$inUserUnit = $uv->toUserUnit($inCommonUnit);
		return $uv->getUserUnit()->getValueAndUnitString($inUserUnit, false, $precision);
	}

    /**
     * @param float $inCommonUnit
     * @param int $precision
     * @param bool $validate
     * @return string|null
     * @throws IncompatibleUnitException
     * @throws InvalidVariableValueException
     */
	protected function effectValueUserUnit(float $inCommonUnit, int $precision = QMVariableRelationship::SIG_FIGS,
		bool $validate = true): string{
		$uv = $this->getEffectUserVariable();
		$inUserUnit = $uv->toUserUnit($inCommonUnit, $validate);
		return $uv->getUserUnit()->getValueAndUnitString($inUserUnit, false, $precision);
	}
	public function getCauseVariableChartsButtonHtml():string{
		return $this->getCauseUserVariable()->getChartsButtonHtml();
	}
    public function setCauseUserVariable(UserVariable $uv){
        $this->setRelationAndAddToMemory('cause_user_variable', $uv);
    }
    public function setEffectUserVariable(UserVariable $uv){
        $this->setRelationAndAddToMemory('effect_user_variable', $uv);
    }
}
