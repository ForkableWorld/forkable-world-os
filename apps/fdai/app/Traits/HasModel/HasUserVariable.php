<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\Measurement;
use App\Models\TrackingReminder;
use App\Models\UserVariable;
use App\Slim\Model\QMUnit;
use App\Units\YesNoUnit;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
trait HasUserVariable {
	use HasVariable, HasUser;
	abstract public function getUserId(): ?int;
	public function getUserVariableId(): int{
		if(property_exists($this, 'userVariableId') && $this->userVariableId){
			return $this->userVariableId;
		}
		if(property_exists($this, 'userVariable') && $this->userVariable instanceof QMUserVariable){
			return $this->userVariable->getUserVariableId();
		}
		if(property_exists($this, 'attributes') && isset($this->attributes[TrackingReminder::FIELD_USER_VARIABLE_ID])){
			return $this->attributes[TrackingReminder::FIELD_USER_VARIABLE_ID];
		}
		if(static::class === QMUserVariable::class){
			return $this->getId();
		}
		return $this->getUserVariable()->id;
	}
	public function getUserVariableButton(): QMButton{
		if(method_exists($this, 'relationLoaded') && $this->relationLoaded('user_variable')){
			return $this->getUserVariable()->getButton();
		}
		return UserVariable::generateDataLabShowButton($this->getUserVariableId());
	}
	/**
	 * @return UserVariable
	 */
	public function getUserVariable(): UserVariable{
		if($this instanceof UserVariable){
			/** @noinspection PhpIncompatibleReturnTypeInspection */
			return $this;
		}
		if(property_exists($this, 'parentModel') && $this->parentModel instanceof UserVariable){
			return $this->parentModel;
		}
		if(property_exists($this, 'laravelModel') && $this->laravelModel instanceof UserVariable){
			return $this->laravelModel;
		}
		$id = $this->attributes[Measurement::FIELD_USER_VARIABLE_ID] ?? null;
		if($id){
			$uv = UserVariable::findInMemoryOrDB($id);
			if(!$uv){
				le("No user variable with id $id");
			}
			return $uv;
		}
		return UserVariable::findOrCreateByNameOrVariableId($this->getUserId(), $this->getVariableIdAttribute());
	}
	/**
	 * @return bool
	 */
	public function isYesNoOrCountWithOnlyOnesAndZeros(): bool{
		return $this->isYesNo() || $this->isCountWithOnlyZerosAndOnes();
	}
	/**
	 * @return QMUnit
	 */
	public function getUserOrCommonUnit(): QMUnit{
		return $this->getUserVariable()->getUserOrCommonUnit();
	}
	/**
	 * @return bool
	 */
	public function isYesNo(): bool{
		return $this->getUserOrCommonUnit()->name == YesNoUnit::NAME;
	}
	/**
	 * @return UserVariable
	 */
	public function getUserVariableFromMemory(): ?UserVariable{
		if(property_exists($this, 'userVariable')){
			$uv = $this->userVariable;
			if($uv instanceof UserVariable){
				return $uv;
			} elseif($uv instanceof QMUserVariable){
				return $uv->l();
			}
		}
		if(property_exists($this, 'parentModel') && $this->parentModel instanceof UserVariable){
			return $this->parentModel;
		}
		if(property_exists($this, 'relations') && isset($this->relations["user_variable"])){
			$uv = $this->relations["user_variable"];
			if($v = $this->getRelationIfLoaded('variable')){
				$uv->setRelationAndAddToMemory('variable', $v);
			}
			return $uv;
		}
		if(property_exists($this, 'laravelModel') && $this->laravelModel){
			/** @var BaseModel $l */
			$l = $this->l();
			if($l instanceof UserVariable){
				return $l;
			}
			if($uv = $l->getRelationIfLoaded('user_variable')){
				return $uv;
			}
		}
		if(static::class === QMUserVariable::class){
			/** @var QMUserVariable $this */
			return $this->l();
		}
		if(property_exists($this, 'userVariableId')){
			if($id = $this->userVariableId){
				$uv = QMUserVariable::findInMemory($id);
				if($uv){
					return $uv->l();
				}
			}
		}
		return null;
	}
	public function getQMUserVariable(): QMUserVariable{
		$l = $this->parentModel ?? null;
		if($l instanceof UserVariable){
			return $l->getDBModel();
		}
		$uv = QMUserVariable::find($this->getUserVariableId());
		/** @var QMUserVariable $uv */
		$uv->validateUnit();
		return $uv;
	}
	public function getUserVariableNameLink(): string{
		return $this->getUserVariable()->getDataLabDisplayNameLink();
	}
	public function getUserVariableImageNameLink(): string{
		return $this->getUserVariable()->getDataLabImageNameLink();
	}
	/**
	 * @return QMVariable|QMUserVariable
	 * @noinspection PhpIncompatibleReturnTypeInspection
	 */
	public function getQMVariable(): QMVariable{
		return $this->getQMUserVariable();
	}
	protected function validateBetweenMinMaxRecorded(?float $value){
		if($value === null){
			return;
		}
		$v = $this->getUserVariable();
		$minDaily = $v->minimum_recorded_daily_value;
		$min = $v->minimum_recorded_value;
		$max = $v->maximum_recorded_value;
		if($minDaily !== null && $value < $minDaily){
			le('$minDaily !== null && $value < $minDaily');
		}
		if($minDaily !== null && $value > $max){
			le('$minDaily !== null && $value > $max');
		}
	}
	public function getVariableIdAttribute(): ?int{
		if($this instanceof BaseModel){
			$id = $this->attributes['variable_id'] ?? null;
		} else{
			$id = $this->variableId ?? null;
		}
		if(!$id){
			$uv = $this->getUserVariable();
			$id = $uv->getVariableIdAttribute();
		}
		if(!$id){
			le('!$id');
		}
		return $id;
	}
	/**
	 * @param array|object $data
	 * @return UserVariable
	 */
	private function findOrCreateUserVariable($data): UserVariable{
		if(is_array($data) && isset($data['user_variable'])){
			$uv = $data['user_variable'];
			$this->setRelationAndAddToMemory('user_variable', $uv);
		} else{
			$uv = $this->getRelationIfLoaded('user_variable');
		}
		if(!$uv){
			$uv = UserVariable::firstOrCreateByForeignData($data);
			$this->setRelationAndAddToMemory('user_variable', $uv);
		}
		return $uv;
	}
	public function getVariableCategoryId(): int{
		return $this->getUserVariable()->getVariableCategoryId();
	}
	public function getUnitIdAttribute(): ?int{
		if(property_exists($this, 'unitId') && $this->unitId){
			return $this->unitId;
		}
		if(property_exists($this, 'defaultUnitId') && $this->defaultUnitId){
			return $this->defaultUnitId;
		}
		if(property_exists($this, 'commonUnitId') && $this->commonUnitId){
			return $this->commonUnitId;
		}
		$uv = $this->getUserVariable();
		return $uv->getAttributeOrVariableFallback('default_unit_id');
	}
	/**
	 * @return bool
	 */
	public function isCountWithOnlyZerosAndOnes(): bool{
		$unit = $this->getUserUnit();
		if(!$unit->isCountCategory()){
			return false;
		}
		$lastValues = $this->getLastValuesInUserUnit();
		if(count($lastValues) > 2){
			return false;
		}
		foreach($lastValues as $value){
			if($value != 1 && $value != 0){
				return false;
			}
		}
		return true;
	}
}
